<?php
//  StepX Store — tambah.php (Tambah Produk Baru)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
if ($user['role'] === 'pembeli') {
    header('Location: catalog.php');
    exit;
}

$db      = getDB();
$error   = '';
$success = '';

// Ambil daftar kategori untuk dropdown
$kategori = $db->query('SELECT * FROM kategori ORDER BY id')->fetch_all(MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama']  ?? '');
    $brand      = trim($_POST['brand'] ?? '');
    $harga      = floatval($_POST['harga'] ?? 0);
    $hpp        = floatval($_POST['hpp']   ?? 0);
    $stok       = intval($_POST['stok']    ?? 0);
    $kategori_id = intval($_POST['kategori_id'] ?? 1);
    $emoji      = trim($_POST['emoji'] ?? '👟') ?: '👟';

    if (!$nama || !$brand || $harga <= 0) {
        $error = 'Nama, Brand, dan Harga wajib diisi dan valid!';
    } else {
        $stmt = $db->prepare(
            'INSERT INTO produk (penjual_id, kategori_id, nama, brand, harga_jual, hpp, stok, emoji)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iiisddis',
            $user['id'], $kategori_id, $nama, $brand,
            $harga, $hpp, $stok, $emoji
        );
        $stmt->execute();
        $stmt->close();

        $_SESSION['flash'] = ['msg' => 'Sepatu berhasil ditambahkan!', 'ok' => true];
        header('Location: catalog.php');
        exit;
    }
}

$pageTitle  = 'Tambah Sepatu';
$activePage = 'tambah';
require_once __DIR__ . '/includes/header.php';
?>

<div class="form-page">
  <a class="back-link" href="catalog.php">← Kembali</a>
  <h2>Tambah Sepatu</h2>
  <p class="sub">Isi data lengkap sepatu baru yang akan dijual</p>

  <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="tambah.php">
    <div class="field">
      <label>Nama Sepatu</label>
      <input type="text" name="nama" placeholder="Air Max 90"
             value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
    </div>
    <div class="field">
      <label>Brand</label>
      <input type="text" name="brand" placeholder="Nike"
             value="<?= htmlspecialchars($_POST['brand'] ?? '') ?>" required>
    </div>
    <div class="form-row">
      <div class="field">
        <label>Kategori</label>
        <select name="kategori_id" required>
          <option value="">Pilih kategori...</option>
          <?php foreach ($kategori as $k): ?>
            <option value="<?= $k['id'] ?>"
              <?= (($_POST['kategori_id'] ?? '') == $k['id']) ? 'selected' : '' ?>>
              <?= htmlspecialchars($k['nama']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="field">
        <label>Emoji</label>
        <input type="text" name="emoji" placeholder="👟" maxlength="4"
               style="font-size:24px" value="<?= htmlspecialchars($_POST['emoji'] ?? '👟') ?>">
      </div>
    </div>
    <div class="form-row">
      <div class="field">
        <label>Harga Jual (Rp)</label>
        <input type="number" name="harga" placeholder="1500000" min="0"
               value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label>HPP / Modal (Rp)</label>
        <input type="number" name="hpp" placeholder="900000" min="0"
               value="<?= htmlspecialchars($_POST['hpp'] ?? '0') ?>">
      </div>
    </div>
    <div class="field">
      <label>Stok Awal</label>
      <input type="number" name="stok" placeholder="10" min="0"
             value="<?= htmlspecialchars($_POST['stok'] ?? '0') ?>">
    </div>
    <button type="submit" class="btn-primary full">Simpan Sepatu</button>
  </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>