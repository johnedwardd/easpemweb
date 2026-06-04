<?php
//  StepX Store — profile.php (Profil + Edit + Alamat)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user   = currentUser();
$db     = getDB();
$userId = (int)$user['id'];
$error  = '';
$success = '';

// Ambil data lengkap user dari DB
$stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $userId);
$stmt->execute();
$u = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ── POST HANDLER ────────────────────────────────────────────────
$action = $_POST['action'] ?? '';

// 1. Edit profil (nama, no_hp)
if ($action === 'edit_profil') {
    $nama  = trim($_POST['nama']  ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    if (!$nama) {
        $error = 'Nama tidak boleh kosong!';
    } else {
        $st = $db->prepare('UPDATE users SET nama = ?, no_hp = ? WHERE id = ?');
        $st->bind_param('ssi', $nama, $no_hp, $userId);
        $st->execute();
        $st->close();
        // Refresh session nama
        $_SESSION['nama']  = $nama;
        $_SESSION['no_hp'] = $no_hp;
        $success = 'Profil berhasil diperbarui!';
        // Reload data
        $stmt = $db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }
}

// 2. Ganti password
if ($action === 'ganti_password') {
    $passLama  = trim($_POST['pass_lama']  ?? '');
    $passBaru  = trim($_POST['pass_baru']  ?? '');
    $passBaru2 = trim($_POST['pass_baru2'] ?? '');

    if (!$passLama || !$passBaru || !$passBaru2) {
        $error = 'Semua field password wajib diisi!';
    } elseif (strlen($passBaru) < 6) {
        $error = 'Password baru minimal 6 karakter!';
    } elseif ($passBaru !== $passBaru2) {
        $error = 'Konfirmasi password baru tidak cocok!';
    } else {
        // Verifikasi password lama
        $passLamaValid = password_verify($passLama, $u['password'])
                      || $passLama === $u['password']; // fallback plain text
        if (!$passLamaValid) {
            $error = 'Password lama tidak sesuai!';
        } else {
            $hashed = password_hash($passBaru, PASSWORD_DEFAULT);
            $st = $db->prepare('UPDATE users SET password = ? WHERE id = ?');
            $st->bind_param('si', $hashed, $userId);
            $st->execute();
            $st->close();
            $success = 'Password berhasil diubah!';
        }
    }
}

// 3. Tambah / edit alamat
if ($action === 'simpan_alamat') {
    $alamatId      = (int)($_POST['alamat_id'] ?? 0);
    $label         = trim($_POST['label']          ?? 'Rumah');
    $namaPenerima  = trim($_POST['nama_penerima']  ?? '');
    $noHp          = trim($_POST['no_hp_alamat']   ?? '');
    $provinsi      = trim($_POST['provinsi']       ?? '');
    $kota          = trim($_POST['kota']           ?? '');
    $kecamatan     = trim($_POST['kecamatan']      ?? '');
    $kodePos       = trim($_POST['kode_pos']       ?? '');
    $alamatLengkap = trim($_POST['alamat_lengkap'] ?? '');
    $isUtama       = isset($_POST['is_utama']) ? 1 : 0;

    if (!$namaPenerima || !$noHp || !$provinsi || !$kota || !$alamatLengkap) {
        $error = 'Nama penerima, no HP, provinsi, kota, dan alamat lengkap wajib diisi!';
    } else {
        // Kalau set utama, reset semua alamat lain dulu
        if ($isUtama) {
            $st = $db->prepare('UPDATE alamat_pembeli SET is_utama = 0 WHERE user_id = ?');
            $st->bind_param('i', $userId);
            $st->execute();
            $st->close();
        }

        if ($alamatId > 0) {
            // Edit alamat existing — pastikan milik user ini
            $st = $db->prepare('UPDATE alamat_pembeli SET label=?, nama_penerima=?, no_hp=?, provinsi=?, kota=?, kecamatan=?, kode_pos=?, alamat_lengkap=?, is_utama=? WHERE id=? AND user_id=?');
            $st->bind_param('sssssssiii', $label, $namaPenerima, $noHp, $provinsi, $kota, $kecamatan, $kodePos, $alamatLengkap, $isUtama, $alamatId, $userId);
            $st->execute();
            $st->close();
            $success = 'Alamat berhasil diperbarui!';
        } else {
            // Tambah alamat baru
            $st = $db->prepare('INSERT INTO alamat_pembeli (user_id, label, nama_penerima, no_hp, provinsi, kota, kecamatan, kode_pos, alamat_lengkap, is_utama) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $st->bind_param('issssssssi', $userId, $label, $namaPenerima, $noHp, $provinsi, $kota, $kecamatan, $kodePos, $alamatLengkap, $isUtama);
            $st->execute();
            $st->close();
            $success = 'Alamat baru berhasil ditambahkan!';
        }
    }
}

// 4. Hapus alamat
if ($action === 'hapus_alamat') {
    $alamatId = (int)($_POST['alamat_id'] ?? 0);
    if ($alamatId > 0) {
        $st = $db->prepare('DELETE FROM alamat_pembeli WHERE id = ? AND user_id = ?');
        $st->bind_param('ii', $alamatId, $userId);
        $st->execute();
        $st->close();
        $success = 'Alamat berhasil dihapus!';
    }
}

// 5. Set alamat utama
if ($action === 'set_utama') {
    $alamatId = (int)($_POST['alamat_id'] ?? 0);
    if ($alamatId > 0) {
        $st = $db->prepare('UPDATE alamat_pembeli SET is_utama = 0 WHERE user_id = ?');
        $st->bind_param('i', $userId);
        $st->execute();
        $st->close();
        $st = $db->prepare('UPDATE alamat_pembeli SET is_utama = 1 WHERE id = ? AND user_id = ?');
        $st->bind_param('ii', $alamatId, $userId);
        $st->execute();
        $st->close();
        $success = 'Alamat utama berhasil diubah!';
    }
}

// ── AMBIL DATA UNTUK TAMPILAN ────────────────────────────────────
$st = $db->prepare('SELECT * FROM alamat_pembeli WHERE user_id = ? ORDER BY is_utama DESC, id ASC');
$st->bind_param('i', $userId);
$st->execute();
$daftarAlamat = $st->get_result()->fetch_all(MYSQLI_ASSOC);
$st->close();

// Alamat yang sedang diedit (dari GET ?edit_alamat=id)
$editAlamat = null;
$editAlamatId = (int)($_GET['edit_alamat'] ?? 0);
if ($editAlamatId > 0) {
    foreach ($daftarAlamat as $al) {
        if ((int)$al['id'] === $editAlamatId) { $editAlamat = $al; break; }
    }
}

$roleClass = [
    'admin'   => 'role-admin',
    'penjual' => 'role-penjual',
    'pembeli' => 'role-pembeli',
][$u['role']] ?? '';

$initials = strtoupper(implode('', array_map(
    fn($w) => $w[0],
    array_slice(explode(' ', $u['nama'] ?: 'SX'), 0, 2)
)));

$pageTitle  = 'Profil Saya';
$activePage = 'profile';
require_once __DIR__ . '/includes/header.php';
?>

<style>
.profile-wrap { padding:32px; max-width:560px; margin:0 auto; }
.profile-tabs { display:flex; gap:0; border-bottom:2px solid var(--border); margin-bottom:24px; }
.profile-tab  { padding:10px 20px; cursor:pointer; font-weight:600; font-size:14px; color:var(--muted); border-bottom:2px solid transparent; margin-bottom:-2px; transition:.2s; }
.profile-tab.active { color:var(--text); border-bottom-color:var(--text); }
.tab-content { display:none; }
.tab-content.active { display:block; }
.form-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
@media(max-width:600px){ .form-grid { grid-template-columns:1fr; } }
.alamat-card { border:1.5px solid var(--border); border-radius:10px; padding:14px 16px; position:relative; }
.alamat-card.utama { border-color:var(--accent); }
</style>

<div class="profile-wrap">
  <a class="back-link" href="catalog.php">← Kembali</a>
  <h2>Profil Saya</h2>

  <?php if ($error): ?>
    <div class="alert alert-error" style="margin-bottom:16px"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="alert alert-success" style="margin-bottom:16px"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <!-- TABS -->
  <div class="profile-tabs">
    <div class="profile-tab <?= !$editAlamat ? 'active' : '' ?>" onclick="switchTab('tab-profil', this)">👤 Data Profil</div>
    <?php if ($u['role'] === 'pembeli'): ?>
    <div class="profile-tab <?= $editAlamat ? 'active' : '' ?>" onclick="switchTab('tab-alamat', this)">📍 Alamat</div>
    <?php endif; ?>
    <div class="profile-tab" onclick="switchTab('tab-password', this)">🔒 Ubah Password</div>
  </div>

  <!-- TAB: PROFIL -->
  <div id="tab-profil" class="tab-content <?= !$editAlamat ? 'active' : '' ?>">
    <div class="profile-card">
      <div class="profile-avatar"><?= htmlspecialchars($initials) ?></div>

      <div class="profile-row"><span class="key">Email</span><span><?= htmlspecialchars($u['email']) ?></span></div>
      <div class="profile-row"><span class="key">Role</span><span class="role-badge <?= $roleClass ?>"><?= htmlspecialchars($u['role']) ?></span></div>
      <div class="profile-row"><span class="key">Bergabung</span><span><?= date('d M Y', strtotime($u['created_at'])) ?></span></div>
    </div>

    <!-- Form edit profil -->
    <div style="margin-top:20px">
      <h3 style="margin-bottom:14px;font-size:16px">Edit Profil</h3>
      <form method="POST" action="profile.php">
        <input type="hidden" name="action" value="edit_profil">
        <div class="form-grid">
          <div class="field">
            <label>Nama Lengkap <span style="color:var(--accent2)">*</span></label>
            <input type="text" name="nama" value="<?= htmlspecialchars($u['nama']) ?>" required>
          </div>
          <div class="field">
            <label>Nomor HP</label>
            <input type="tel" name="no_hp" value="<?= htmlspecialchars($u['no_hp'] ?? '') ?>" placeholder="08123456789">
          </div>
        </div>
        <button type="submit" class="btn-primary" style="margin-top:4px">Simpan Perubahan</button>
      </form>
    </div>
  </div>

  <!-- TAB: ALAMAT -->
  <?php if ($u['role'] === 'pembeli'): ?>
  <div id="tab-alamat" class="tab-content <?= $editAlamat ? 'active' : '' ?>">

    <!-- Daftar alamat tersimpan -->
    <?php if (!empty($daftarAlamat)): ?>
    <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:24px">
      <?php foreach ($daftarAlamat as $al): ?>
      <div class="alamat-card <?= $al['is_utama'] ? 'utama' : '' ?>">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap">
          <div>
            <span style="font-size:12px;background:var(--surface2);padding:2px 8px;border-radius:20px;font-weight:500"><?= htmlspecialchars($al['label']) ?></span>
            <?php if ($al['is_utama']): ?>
              <span style="font-size:11px;color:var(--accent);font-weight:600;margin-left:6px">✓ Utama</span>
            <?php endif; ?>
            <div style="font-weight:600;margin-top:6px"><?= htmlspecialchars($al['nama_penerima']) ?></div>
            <div style="font-size:13px;color:var(--muted)"><?= htmlspecialchars($al['no_hp']) ?></div>
            <div style="font-size:13px;margin-top:2px"><?= htmlspecialchars($al['alamat_lengkap']) ?>, <?= htmlspecialchars($al['kecamatan'] ? $al['kecamatan'].', ' : '') ?><?= htmlspecialchars($al['kota']) ?></div>
            <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($al['provinsi']) ?><?= $al['kode_pos'] ? ' '.$al['kode_pos'] : '' ?></div>
          </div>
          <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-end">
            <a href="profile.php?edit_alamat=<?= $al['id'] ?>&tab=alamat" class="btn-sm">Edit</a>
            <?php if (!$al['is_utama']): ?>
            <form method="POST" action="profile.php" style="margin:0">
              <input type="hidden" name="action" value="set_utama">
              <input type="hidden" name="alamat_id" value="<?= $al['id'] ?>">
              <button type="submit" class="btn-sm" style="background:transparent;border:1px solid var(--border);cursor:pointer;font-size:12px;padding:4px 10px;border-radius:6px">Jadikan Utama</button>
            </form>
            <form method="POST" action="profile.php" style="margin:0" onsubmit="return confirm('Hapus alamat ini?')">
              <input type="hidden" name="action" value="hapus_alamat">
              <input type="hidden" name="alamat_id" value="<?= $al['id'] ?>">
              <button type="submit" class="btn-danger" style="font-size:12px;padding:4px 10px">Hapus</button>
            </form>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p style="color:var(--muted);font-size:14px;margin-bottom:20px">Belum ada alamat tersimpan.</p>
    <?php endif; ?>

    <!-- Form tambah / edit alamat -->
    <div style="border-top:1.5px solid var(--border);padding-top:20px">
      <h3 style="margin-bottom:14px;font-size:16px"><?= $editAlamat ? '✏️ Edit Alamat' : '+ Tambah Alamat Baru' ?></h3>
      <form method="POST" action="profile.php<?= $editAlamat ? '?tab=alamat' : '' ?>">
        <input type="hidden" name="action" value="simpan_alamat">
        <input type="hidden" name="alamat_id" value="<?= $editAlamat ? $editAlamat['id'] : 0 ?>">
        <div class="form-grid">
          <div class="field">
            <label>Label (contoh: Rumah, Kantor)</label>
            <input type="text" name="label" value="<?= htmlspecialchars($editAlamat['label'] ?? 'Rumah') ?>" placeholder="Rumah">
          </div>
          <div class="field">
            <label>Nama Penerima <span style="color:var(--accent2)">*</span></label>
            <input type="text" name="nama_penerima" value="<?= htmlspecialchars($editAlamat['nama_penerima'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>No. HP Penerima <span style="color:var(--accent2)">*</span></label>
            <input type="tel" name="no_hp_alamat" value="<?= htmlspecialchars($editAlamat['no_hp'] ?? '') ?>" placeholder="08123456789" required>
          </div>
          <div class="field">
            <label>Provinsi <span style="color:var(--accent2)">*</span></label>
            <input type="text" name="provinsi" value="<?= htmlspecialchars($editAlamat['provinsi'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Kota / Kabupaten <span style="color:var(--accent2)">*</span></label>
            <input type="text" name="kota" value="<?= htmlspecialchars($editAlamat['kota'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Kecamatan</label>
            <input type="text" name="kecamatan" value="<?= htmlspecialchars($editAlamat['kecamatan'] ?? '') ?>">
          </div>
          <div class="field">
            <label>Kode Pos</label>
            <input type="text" name="kode_pos" value="<?= htmlspecialchars($editAlamat['kode_pos'] ?? '') ?>" placeholder="60111">
          </div>
        </div>
        <div class="field" style="margin-top:4px">
          <label>Alamat Lengkap <span style="color:var(--accent2)">*</span></label>
          <textarea name="alamat_lengkap" rows="3" placeholder="Nama jalan, no rumah, RT/RW, dll..." required
            style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:14px;background:var(--surface);color:var(--text);resize:vertical"><?= htmlspecialchars($editAlamat['alamat_lengkap'] ?? '') ?></textarea>
        </div>
        <div style="display:flex;align-items:center;gap:8px;margin:10px 0">
          <input type="checkbox" name="is_utama" id="is_utama" value="1"
            <?= ($editAlamat && $editAlamat['is_utama']) || empty($daftarAlamat) ? 'checked' : '' ?>>
          <label for="is_utama" style="font-size:14px;cursor:pointer">Jadikan alamat utama</label>
        </div>
        <div style="display:flex;gap:10px;margin-top:4px">
          <button type="submit" class="btn-primary"><?= $editAlamat ? 'Simpan Perubahan' : 'Tambah Alamat' ?></button>
          <?php if ($editAlamat): ?>
            <a href="profile.php?tab=alamat" class="btn-ghost" style="padding:10px 16px;text-decoration:none">Batal</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>
</div>

  <!-- TAB: PASSWORD -->
  <div id="tab-password" class="tab-content">
    <div class="profile-card">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
        <div style="width:40px;height:40px;background:var(--surface2);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px">🔒</div>
        <div>
          <div style="font-weight:700;font-size:15px">Ubah Password</div>
          <div style="font-size:12px;color:var(--muted)">Pastikan password baru minimal 6 karakter</div>
        </div>
      </div>
      <form method="POST" action="profile.php">
        <input type="hidden" name="action" value="ganti_password">
        <div class="field">
          <label>Password Lama <span style="color:var(--accent2)">*</span></label>
          <input type="password" name="pass_lama" placeholder="Masukkan password lama" required>
        </div>
        <div class="field">
          <label>Password Baru <span style="color:var(--accent2)">*</span></label>
          <input type="password" name="pass_baru" placeholder="Min. 6 karakter" required>
        </div>
        <div class="field">
          <label>Konfirmasi Password Baru <span style="color:var(--accent2)">*</span></label>
          <input type="password" name="pass_baru2" placeholder="Ulangi password baru" required>
        </div>
        <button type="submit" class="btn-primary">Simpan Password Baru</button>
      </form>
    </div>
  </div>

<script>
function switchTab(id, el) {
  document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
  document.querySelectorAll('.profile-tab').forEach(t => t.classList.remove('active'));
  document.getElementById(id).classList.add('active');
  el.classList.add('active');
}
// Buka tab otomatis kalau ada ?tab= di URL
const tabParam = new URLSearchParams(location.search).get('tab');
if (tabParam === 'alamat') {
  const tabAlamat = document.querySelector('.profile-tab:nth-child(2)');
  if (tabAlamat) switchTab('tab-alamat', tabAlamat);
} else if (tabParam === 'password') {
  const tabPass = document.querySelector('.profile-tab:last-child');
  if (tabPass) switchTab('tab-password', tabPass);
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>