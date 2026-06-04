<?php
//  StepX Store — catalog.php (Katalog Produk + Filter + Modal Detail)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
$db   = getDB();

// ── Ambil semua kategori untuk dropdown filter ──
$kategoriList = $db->query('SELECT id, nama FROM kategori ORDER BY nama')->fetch_all(MYSQLI_ASSOC);

// ── Ambil semua brand unik untuk filter ──
$brandList = $db->query('SELECT DISTINCT brand FROM produk ORDER BY brand')->fetch_all(MYSQLI_ASSOC);

// ── Ambil semua ukuran unik ──
$ukuranList = $db->query('SELECT DISTINCT ukuran FROM produk_ukuran ORDER BY CAST(ukuran AS UNSIGNED)')->fetch_all(MYSQLI_ASSOC);

// ── Baca parameter filter dari GET ──
$keyword    = trim($_GET['q']          ?? '');
$filterKat  = trim($_GET['kategori']   ?? '');
$filterBrand= trim($_GET['brand']      ?? '');
$filterUkuran= trim($_GET['ukuran']   ?? '');
$filterStok = trim($_GET['stok']       ?? '');   // 'ada' | 'habis' | ''
$filterHargaMin = (int)($_GET['harga_min'] ?? 0);
$filterHargaMax = (int)($_GET['harga_max'] ?? 0);
$filterBaru = isset($_GET['baru']) && $_GET['baru'] === '1';

// ── Bangun query dinamis ──
$where  = ['1=1'];
$params = [];
$types  = '';

if ($keyword !== '') {
    $like = '%' . $keyword . '%';
    $where[]  = '(p.nama LIKE ? OR p.brand LIKE ? OR k.nama LIKE ?)';
    $params   = array_merge($params, [$like, $like, $like]);
    $types   .= 'sss';
}
if ($filterKat !== '') {
    $where[]  = 'p.kategori_id = ?';
    $params[] = $filterKat;
    $types   .= 'i';
}
if ($filterBrand !== '') {
    $where[]  = 'p.brand = ?';
    $params[] = $filterBrand;
    $types   .= 's';
}
if ($filterStok === 'ada') {
    $where[] = 'p.stok > 0';
} elseif ($filterStok === 'habis') {
    $where[] = 'p.stok = 0';
}
if ($filterHargaMin > 0) {
    $where[]  = 'p.harga_jual >= ?';
    $params[] = $filterHargaMin;
    $types   .= 'i';
}
if ($filterHargaMax > 0) {
    $where[]  = 'p.harga_jual <= ?';
    $params[] = $filterHargaMax;
    $types   .= 'i';
}
if ($filterBaru) {
    $where[] = 'p.is_new = 1';
}
if ($filterUkuran !== '') {
    $where[]  = 'EXISTS (SELECT 1 FROM produk_ukuran pu WHERE pu.produk_id = p.id AND pu.ukuran = ? AND pu.stok > 0)';
    $params[] = $filterUkuran;
    $types   .= 's';
}

$sql = 'SELECT p.*, k.nama AS kategori_nama
        FROM produk p
        LEFT JOIN kategori k ON k.id = p.kategori_id
        WHERE ' . implode(' AND ', $where) . '
        ORDER BY p.id DESC';

if ($params) {
    $st = $db->prepare($sql);
    $st->bind_param($types, ...$params);
    $st->execute();
    $produk = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    $st->close();
} else {
    $produk = $db->query($sql)->fetch_all(MYSQLI_ASSOC);
}

// Cek apakah ada filter aktif
$hasFilter = ($keyword || $filterKat || $filterBrand || $filterUkuran || $filterStok || $filterHargaMin || $filterHargaMax || $filterBaru);

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

  <!-- ══════════ PANEL FILTER ══════════ -->
  <form method="GET" action="catalog.php" id="filter-form">
    <div class="filter-panel">

      <!-- Search -->
      <div class="filter-search-row">
        <input type="text" name="q" value="<?= htmlspecialchars($keyword) ?>"
               placeholder="Cari nama, brand, atau kategori..."
               class="filter-input filter-input--search">
        <button type="submit" class="btn-primary" style="padding:10px 24px;white-space:nowrap">🔍 Cari</button>
        <?php if ($hasFilter): ?>
          <a href="catalog.php" class="btn-ghost" style="padding:10px 16px;text-decoration:none;white-space:nowrap">✕ Reset</a>
        <?php endif; ?>
      </div>

      <!-- Filter baris bawah -->
      <div class="filter-row">

        <!-- Kategori -->
        <div class="filter-group">
          <label class="filter-label">Kategori</label>
          <select name="kategori" class="filter-input" onchange="this.form.submit()">
            <option value="">Semua Kategori</option>
            <?php foreach ($kategoriList as $k): ?>
              <option value="<?= $k['id'] ?>" <?= $filterKat == $k['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($k['nama']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Brand -->
        <div class="filter-group">
          <label class="filter-label">Brand</label>
          <select name="brand" class="filter-input" onchange="this.form.submit()">
            <option value="">Semua Brand</option>
            <?php foreach ($brandList as $b): ?>
              <option value="<?= htmlspecialchars($b['brand']) ?>" <?= $filterBrand === $b['brand'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($b['brand']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Ukuran -->
        <div class="filter-group">
          <label class="filter-label">Ukuran Tersedia</label>
          <select name="ukuran" class="filter-input" onchange="this.form.submit()">
            <option value="">Semua Ukuran</option>
            <?php foreach ($ukuranList as $u): ?>
              <option value="<?= htmlspecialchars($u['ukuran']) ?>" <?= $filterUkuran === $u['ukuran'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['ukuran']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Stok -->
        <div class="filter-group">
          <label class="filter-label">Ketersediaan</label>
          <select name="stok" class="filter-input" onchange="this.form.submit()">
            <option value="">Semua</option>
            <option value="ada"   <?= $filterStok === 'ada'   ? 'selected' : '' ?>>Stok Tersedia</option>
            <option value="habis" <?= $filterStok === 'habis' ? 'selected' : '' ?>>Habis</option>
          </select>
        </div>

        <!-- Harga Min -->
        <div class="filter-group">
          <label class="filter-label">Harga Min (Rp)</label>
          <input type="number" name="harga_min" value="<?= $filterHargaMin ?: '' ?>"
                 placeholder="0" min="0" step="50000" class="filter-input">
        </div>

        <!-- Harga Max -->
        <div class="filter-group">
          <label class="filter-label">Harga Max (Rp)</label>
          <input type="number" name="harga_max" value="<?= $filterHargaMax ?: '' ?>"
                 placeholder="Tak terbatas" min="0" step="50000" class="filter-input">
        </div>

        <!-- Produk Baru -->
        <div class="filter-group filter-group--checkbox">
          <label class="filter-label">&nbsp;</label>
          <label class="filter-checkbox-label">
            <input type="checkbox" name="baru" value="1" <?= $filterBaru ? 'checked' : '' ?>
                   onchange="this.form.submit()">
            <span>⭐ Produk Baru</span>
          </label>
        </div>

      </div><!-- /filter-row -->
    </div><!-- /filter-panel -->
  </form>

  <!-- Info hasil -->
  <?php if ($hasFilter): ?>
    <p class="filter-result-info">
      Menampilkan <strong><?= count($produk) ?></strong> produk
      <?php if ($keyword): ?> untuk "<em><?= htmlspecialchars($keyword) ?></em>"<?php endif; ?>
    </p>
  <?php endif; ?>

  <!-- ══════════ GRID PRODUK ══════════ -->
  <?php if (empty($produk)): ?>
    <div class="empty-state">
      <div class="icon">👟</div>
      <p>Tidak ada produk yang cocok dengan filter</p>
      <a href="catalog.php" class="btn-ghost" style="margin-top:12px;display:inline-block">Reset Filter</a>
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

        // Data untuk modal — encode JSON untuk JS
        $modalData = json_encode([
          'id'       => $p['id'],
          'nama'     => $p['nama'],
          'brand'    => $p['brand'],
          'kategori' => $p['kategori_nama'] ?? '-',
          'harga'    => rp($p['harga_jual']),
          'stok'     => $stok,
          'emoji'    => $p['emoji'] ?: '👟',
          'is_new'   => (bool)$p['is_new'],
          'deskripsi'=> $p['deskripsi'] ?: '',
          'berat'    => $p['berat_gram'],
          'status'   => $p['status'],
        ], JSON_HEX_QUOT | JSON_HEX_APOS);
      ?>
      <div class="product-card" onclick="openDetail(<?= htmlspecialchars($modalData, ENT_QUOTES) ?>)" style="cursor:pointer">
        <?php if ($p['is_new']): ?><div class="badge-new">NEW</div><?php endif; ?>
        <div class="product-emoji"><?= $emoji ?></div>
        <div class="product-info">
          <div class="product-brand"><?= $brand ?></div>
          <div class="product-name"><?= $nama ?></div>
          <div class="product-category"><?= $kat ?></div>
          <div class="product-price"><?= rp($p['harga_jual']) ?></div>
          <div style="margin-top:4px"><?= $badge ?></div>
          <!-- Tombol aksi — stopPropagation agar tidak trigger modal -->
          <div class="product-actions" onclick="event.stopPropagation()">
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

<!-- ══════════ MODAL DETAIL SEPATU ══════════ -->
<div class="modal-overlay" id="modal-detail">
  <div class="modal-detail-box" onclick="event.stopPropagation()">
    <button class="modal-close-btn" onclick="closeDetail()">✕</button>

    <div class="modal-detail-left">
      <div class="modal-detail-emoji" id="md-emoji">👟</div>
      <div id="md-badge-new" class="badge-new" style="display:none;margin:8px auto 0;width:fit-content">NEW</div>
    </div>

    <div class="modal-detail-right">
      <div class="md-brand" id="md-brand">—</div>
      <h2 class="md-nama" id="md-nama">—</h2>

      <div class="md-meta-row">
        <span class="md-meta-pill" id="md-kategori">—</span>
        <span class="md-meta-pill" id="md-status-stok">—</span>
      </div>

      <div class="md-harga" id="md-harga">—</div>

      <div class="md-desc" id="md-desc"></div>

      <div class="md-info-grid">
        <div class="md-info-item">
          <span class="md-info-label">Stok Total</span>
          <span class="md-info-value" id="md-stok">—</span>
        </div>
        <div class="md-info-item">
          <span class="md-info-label">Berat</span>
          <span class="md-info-value" id="md-berat">—</span>
        </div>
      </div>

      <!-- Ukuran tersedia (load via AJAX) -->
      <div class="md-ukuran-wrap">
        <div class="md-info-label" style="margin-bottom:8px">Ukuran Tersedia</div>
        <div class="md-ukuran-list" id="md-ukuran-list">
          <span style="color:var(--muted);font-size:13px">Memuat...</span>
        </div>
      </div>

      <!-- Aksi di modal -->
      <div class="md-actions" id="md-actions">
        <?php if ($user['role'] === 'pembeli'): ?>
          <button class="btn-primary" id="md-cart-btn" onclick="addToCartModal()" style="flex:1;padding:11px">
            + Tambah ke Keranjang
          </button>
        <?php else: ?>
          <a id="md-edit-link" href="#" class="btn-sm" style="flex:1;text-align:center;padding:10px">✏️ Edit Produk</a>
        <?php endif; ?>
        <button class="btn-ghost" onclick="closeDetail()" style="flex:1;padding:11px">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════ MODAL HAPUS ══════════ -->
<div class="modal-overlay" id="modal-hapus">
  <div class="modal-box">
    <h3>Hapus Produk</h3>
    <p id="modal-hapus-text"></p>
    <div class="modal-actions">
      <form method="POST" action="hapus.php" id="form-hapus">
        <input type="hidden" name="id" id="hapus-id">
        <button type="submit" class="btn-danger">Ya, Hapus</button>
      </form>
      <button class="btn-ghost" onclick="closeHapusModal()">Batal</button>
    </div>
  </div>
</div>

<script>
// ─── State modal detail ───
let currentProductId = null;

function openDetail(data) {
  currentProductId = data.id;

  document.getElementById('md-emoji').textContent   = data.emoji;
  document.getElementById('md-brand').textContent   = data.brand;
  document.getElementById('md-nama').textContent    = data.nama;
  document.getElementById('md-kategori').textContent = '📂 ' + data.kategori;
  document.getElementById('md-harga').textContent   = data.harga;
  document.getElementById('md-stok').textContent    = data.stok + ' pcs';
  document.getElementById('md-berat').textContent   = data.berat + ' gram';

  // Deskripsi
  const descEl = document.getElementById('md-desc');
  descEl.textContent = data.deskripsi || 'Tidak ada deskripsi produk.';

  // Badge NEW
  document.getElementById('md-badge-new').style.display = data.is_new ? 'block' : 'none';

  // Status stok pill
  const stokPill = document.getElementById('md-status-stok');
  if (data.stok === 0) {
    stokPill.textContent = '❌ Habis';
    stokPill.className   = 'md-meta-pill pill-habis';
  } else if (data.stok <= 5) {
    stokPill.textContent = '⚠️ Sisa sedikit';
    stokPill.className   = 'md-meta-pill pill-low';
  } else {
    stokPill.textContent = '✅ Tersedia';
    stokPill.className   = 'md-meta-pill pill-ada';
  }

  // Tombol cart / edit
  const cartBtn  = document.getElementById('md-cart-btn');
  const editLink = document.getElementById('md-edit-link');
  if (cartBtn) {
    cartBtn.disabled = data.stok === 0;
    cartBtn.style.opacity = data.stok === 0 ? '.4' : '1';
  }
  if (editLink) {
    editLink.href = 'edit.php?id=' + data.id;
  }

  // Load ukuran via fetch
  loadUkuran(data.id);

  document.getElementById('modal-detail').classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDetail() {
  document.getElementById('modal-detail').classList.remove('open');
  document.body.style.overflow = '';
  currentProductId = null;
}

// Close jika klik overlay
document.getElementById('modal-detail').addEventListener('click', function(e){
  if (e.target === this) closeDetail();
});

// ─── Load ukuran dari server ───
function loadUkuran(produkId) {
  const list = document.getElementById('md-ukuran-list');
  list.innerHTML = '<span style="color:var(--muted);font-size:13px">Memuat...</span>';

  fetch('get_ukuran.php?id=' + produkId)
    .then(r => r.json())
    .then(data => {
      if (!data.length) {
        list.innerHTML = '<span style="color:var(--muted);font-size:13px">Tidak ada data ukuran</span>';
        return;
      }
      list.innerHTML = data.map(u =>
        `<span class="ukuran-chip ${u.stok > 0 ? '' : 'ukuran-chip--habis'}" title="Stok: ${u.stok}">
          ${u.ukuran}${u.stok === 0 ? '<s></s>' : ''}
        </span>`
      ).join('');
    })
    .catch(() => {
      list.innerHTML = '<span style="color:var(--muted);font-size:13px">—</span>';
    });
}

// ─── Cart ───
function addToCart(id) {
  fetch('cart_action.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: 'action=add&id=' + id
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      toast(d.msg);
      updateCartBadge(d.cartCount);
    } else {
      toast(d.msg, false);
    }
  });
}

function addToCartModal() {
  if (!currentProductId) return;
  addToCart(currentProductId);
}

function updateCartBadge(count) {
  const cartLink = document.querySelector('#nav-links a[href*="cart"]');
  if (!cartLink) return;
  let badge = cartLink.querySelector('span');
  if (!badge) {
    badge = document.createElement('span');
    badge.style.cssText = 'background:var(--accent2);color:#fff;padding:1px 6px;border-radius:10px;font-size:10px;margin-left:4px';
    cartLink.appendChild(badge);
  }
  badge.textContent = count;
}

// ─── Modal hapus ───
function confirmHapus(id, nama) {
  document.getElementById('hapus-id').value = id;
  document.getElementById('modal-hapus-text').textContent =
    'Yakin ingin menghapus "' + nama + '"? Tindakan ini tidak bisa dibatalkan.';
  document.getElementById('modal-hapus').classList.add('open');
}
function closeHapusModal() {
  document.getElementById('modal-hapus').classList.remove('open');
}
document.getElementById('modal-hapus').addEventListener('click', function(e) {
  if (e.target === this) closeHapusModal();
});

// Tutup modal dengan Escape
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') { closeDetail(); closeHapusModal(); }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
