<?php
//  StepX Store — edit.php (Edit Produk)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
if ($user['role'] === 'pembeli') {
    header('Location: catalog.php');
    exit;
}

$db    = getDB();
$error = '';
$id    = intval($_GET['id'] ?? $_POST['id'] ?? 0);

// Ambil daftar kategori untuk dropdown
$kategori = $db->query('SELECT * FROM kategori ORDER BY id')->fetch_all(MYSQLI_ASSOC);

// Ambil data produk
$stmt = $db->prepare('SELECT * FROM produk WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$produk = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$produk) {
    $_SESSION['flash'] = ['msg' => 'Produk tidak ditemukan!', 'ok' => false];
    header('Location: catalog.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama        = trim($_POST['nama']  ?? '');
    $brand       = trim($_POST['brand'] ?? '');
    $harga       = floatval($_POST['harga'] ?? 0);
    $hpp         = floatval($_POST['hpp']   ?? 0);
    $stok        = intval($_POST['stok']    ?? 0);
    $kategori_id = intval($_POST['kategori_id'] ?? $produk['kategori_id']);
    $emoji       = trim($_POST['emoji'] ?? '👟') ?: '👟';

    if (!$nama || !$brand || $harga <= 0) {
        $error = 'Nama, Brand, dan Harga wajib diisi!';
    } else {
        // UPDATE mencakup semua kolom yang bisa diedit sesuai skema DB
        $stmt = $db->prepare(
            'UPDATE produk SET nama=?, brand=?, harga_jual=?, hpp=?, stok=?, kategori_id=?, emoji=?
             WHERE id=?'
        );
        $stmt->bind_param('ssddiisi', $nama, $brand, $harga, $hpp, $stok, $kategori_id, $emoji, $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash'] = ['msg' => 'Produk berhasil diperbarui!', 'ok' => true];
        header('Location: catalog.php');
        exit;
    }

    // Merge POST back ke $produk supaya form terisi ulang
    $produk = array_merge($produk, compact('nama','brand','harga','hpp','stok','kategori_id','emoji'));
}

$pageTitle  = 'Edit Sepatu';
$activePage = 'catalog';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-page">
  <a class="back-link" href="catalog.php">← Kembali</a>
  <h2>Edit Sepatu</h2>
  <p class="sub">Perbarui informasi produk sepatu</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="edit.php">
    <input type="hidden" name="id" value="<?= $id ?>">
    <div class="field">
      <label>Nama Sepatu</label>
      <input type="text" name="nama" value="<?= htmlspecialchars($produk['nama']) ?>" required>
    </div>
    <div class="field">
      <label>Brand</label>
      <input type="text" name="brand" value="<?= htmlspecialchars($produk['brand']) ?>" required>
    </div>
    <div class="form-row">
      <div class="field">
        <label>Kategori</label>
        <select name="kategori_id" required>
          <option value="">Pilih kategori...</option>
          <?php foreach ($kategori as $k): ?>
            <option value="<?= $k['id'] ?>"
              <?= ($produk['kategori_id'] == $k['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Emoji</label>
        <input type="text" name="emoji" placeholder="👟" maxlength="4"
               style="font-size:24px" value="<?= htmlspecialchars($produk['emoji'] ?? '👟') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="field">
        <label>Harga Jual (Rp)</label>
        <input type="number" name="harga" value="<?= htmlspecialchars($produk['harga_jual']) ?>" min="0" required>
      </div>
      <div class="field">
        <label>HPP / Modal (Rp)</label>
        <input type="number" name="hpp" value="<?= htmlspecialchars($produk['hpp'] ?? '0') ?>" min="0">
      </div>
    </div>
    <div class="field">
      <label>Stok</label>
      <input type="number" name="stok" value="<?= htmlspecialchars($produk['stok']) ?>" min="0">
    </div>
    <button type="submit" class="btn-primary full">Update Data</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
