<?php
//  StepX Store — catalog.php (Katalog Produk)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
$db   = getDB();

// Ambil keyword search
$keyword = trim($_GET['q'] ?? '');

// Ambil semua produk + filter search
if ($keyword !== '') {
    $like = '%' . $keyword . '%';
    $st = $db->prepare(
        'SELECT p.*, k.nama AS kategori_nama
         FROM produk p
         LEFT JOIN kategori k ON k.id = p.kategori_id
         WHERE p.nama LIKE ? OR p.brand LIKE ? OR k.nama LIKE ?
         ORDER BY p.id DESC'
    );
    $st->bind_param('sss', $like, $like, $like);
    $st->execute();
    $produk = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
} else {
    $produk = $db->query(
        'SELECT p.*, k.nama AS kategori_nama
         FROM produk p
         LEFT JOIN kategori k ON k.id = p.kategori_id
         ORDER BY p.id DESC'
    )->fetch_all(MYSQLI_ASSOC);
}

$pageTitle  = 'Katalog Sepatu';
$activePage = 'catalog';
require_once __DIR__ . '/includes/header.php';
?>

<div class="catalog-wrap">
  <div class="catalog-header">
    <h2>Katalog Sepatu</h2>
    <div>
      <?php if ($user['role'] === 'pembeli'): ?>
        <a href="cart.php" class="btn-sm">🛒 Lihat Keranjang</a>
      <?php else: ?>
        <a href="tambah.php" class="btn-sm">+ Tambah Sepatu</a>
      <?php endif; ?>
    </div>
  </div>

  <!-- SEARCH BAR -->
  <form method="GET" action="catalog.php" style="margin:16px 0;display:flex;gap:8px">
    <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>"
           placeholder="Cari nama produk, brand, atau kategori..."
           style="flex:1;padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text)">
    <button type="submit" class="btn-primary" style="padding:10px 20px">Cari</button>
    <?php if ($keyword): ?>
      <a href="catalog.php" class="btn-ghost" style="padding:10px 16px;text-decoration:none">Reset</a>
    <?php endif; ?>
  </form>
  <?php if ($keyword): ?>
    <p style="font-size:13px;color:var(--muted);margin-bottom:4px">
      Hasil untuk: <strong>"<?= htmlspecialchars($keyword) ?>"</strong>
      — <?= count($produk) ?> produk ditemukan
    </p>
  <?php endif; ?>

  <?php if (empty($produk)): ?>
    <div class="empty-state">
      <div class="icon">👟</div>
      <p>Belum ada produk</p>
    </div>
  <?php else: ?>
    <div class="product-grid">
      <?php foreach ($produk as $p):
        $stok = (int)$p['stok'];
        if ($stok === 0)       $badge = '<span class="stock-badge out">Habis</span>';
        elseif ($stok <= 5)    $badge = '<span class="stock-badge low">Sisa '.$stok.'</span>';
        else                   $badge = '<span class="stock-badge">Stok: '.$stok.'</span>';

        $emoji = htmlspecialchars($p['emoji'] ?: '👟');
        $nama  = htmlspecialchars($p['nama']);
        $brand = htmlspecialchars($p['brand']);
        $kat   = htmlspecialchars($p['kategori_nama'] ?? '-');
      ?>
      <div class="product-card">
        <div class="product-emoji"><?= $emoji ?></div>
        <div class="product-info">
          <div class="product-brand"><?= $brand ?></div>
          <div class="product-name"><?= $nama ?></div>
          <div class="product-category"><?= $kat ?></div>
          <div class="product-price"><?= rp($p['harga_jual']) ?></div>
          <div style="margin-top:4px"><?= $badge ?></div>
          <div class="product-actions">
            <?php if ($user['role'] !== 'pembeli'): ?>
              <a href="edit.php?id=<?= $p['id'] ?>" class="btn-sm" style="flex:1;text-align:center">Edit</a>
              <button class="btn-danger" style="flex:1"
                onclick="confirmHapus(<?= $p['id'] ?>, '<?= addslashes($nama) ?>')">Hapus</button>
            <?php else: ?>
              <?php if ($stok > 0): ?>
                <button class="btn-primary" style="width:100%;padding:9px"
                  onclick="addToCart(<?= $p['id'] ?>)">+ Keranjang</button>
              <?php else: ?>
                <button class="btn-primary" style="width:100%;padding:9px;opacity:.5" disabled>Stok Habis</button>
              <?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- MODAL HAPUS -->
<div class="modal-overlay" id="modal-hapus">
  <div class="modal-box">
    <h3>Hapus Produk</h3>
    <p id="modal-hapus-text"></p>
    <div class="modal-actions">
      <form method="POST" action="hapus.php" id="form-hapus">
        <input type="hidden" name="id" id="hapus-id">
        <button type="submit" class="btn-danger">Ya, Hapus</button>
      </form>
      <button class="btn-ghost" onclick="closeModal()">Batal</button>
    </div>
  </div>
</div>

<script>
// ---- Cart (session via AJAX) ----
function addToCart(id){
  fetch('cart_action.php', {
    method:'POST',
    headers:{'Content-Type':'application/x-www-form-urlencoded'},
    body:'action=add&id='+id
  })
  .then(r=>r.json())
  .then(d=>{
    if(d.ok){
      toast(d.msg);
      // Update badge di nav — buat span baru kalau belum ada
      const cartLink = document.querySelector('#nav-links a[href*="cart"]');
      if(cartLink){
        let badge = cartLink.querySelector('span');
        if(!badge){
          badge = document.createElement('span');
          badge.style.cssText = 'background:var(--accent2);color:#fff;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:4px';
          cartLink.appendChild(badge);
        }
        badge.textContent = d.cartCount;
      }
    } else {
      toast(d.msg, false);
    }
  });
}

// ---- Modal hapus ----
function confirmHapus(id, nama){
  document.getElementById('hapus-id').value = id;
  document.getElementById('modal-hapus-text').textContent =
    'Yakin ingin menghapus "'+nama+'"? Tindakan ini tidak bisa dibatalkan.';
  document.getElementById('modal-hapus').classList.add('open');
}
function closeModal(){
  document.getElementById('modal-hapus').classList.remove('open');
}
document.getElementById('modal-hapus').addEventListener('click', function(e){
  if(e.target===this) closeModal();
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>