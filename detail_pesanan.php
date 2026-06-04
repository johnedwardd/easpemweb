<?php
//  StepX Store — detail_pesanan.php (Rincian Barang Belanjaan)
require_once __DIR__ . '/includes/config.php';
requireLogin();

$user = currentUser();
if ($user['role'] !== 'pembeli') {
    header('Location: catalog.php');
    exit;
}

$pesanan_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$userId     = (int)$user['id'];
$db         = getDB();

// Validasi Keamanan: Pastikan pesanan ini memang milik user yang sedang aktif login
$query_pesanan = "SELECT * FROM pesanan WHERE id = ? AND pembeli_id = ?";
$stmt_p = $db->prepare($query_pesanan);
$stmt_p->bind_param("ii", $pesanan_id, $userId);
$stmt_p->execute();
$cek_pesanan = $stmt_p->get_result();

if ($cek_pesanan->num_rows == 0) {
    echo "<script>alert('Akses pesanan tidak valid!'); window.location='pesanan.php';</script>";
    exit();
}

$data_pesanan = $cek_pesanan->fetch_assoc();
$stmt_p->close();

// Ambil item produk di dalam pesanan tersebut dari tabel detail_pesanan milik stepx_db
$query_detail = "SELECT * FROM detail_pesanan WHERE pesanan_id = ?";
$stmt_d = $db->prepare($query_detail);
$stmt_d->bind_param("i", $pesanan_id);
$stmt_d->execute();
$result_detail = $stmt_d->get_result();
$stmt_d->close();

// Label status badge
function badgeStatus(string $status, string $type): string {
    $map = [
        'status_pesanan' => [
            'proses'     => ['label' => 'Diproses',   'color' => '#f59e0b'],
            'dikemas'    => ['label' => 'Dikemas',     'color' => '#3b82f6'],
            'dikirim'    => ['label' => 'Dikirim',     'color' => '#8b5cf6'],
            'selesai'    => ['label' => 'Selesai',     'color' => '#10b981'],
            'dibatalkan' => ['label' => 'Dibatalkan',  'color' => '#ef4444'],
        ],
        'status_bayar' => [
            'menunggu' => ['label' => 'Belum Bayar', 'color' => '#f59e0b'],
            'lunas'    => ['label' => 'Lunas',        'color' => '#10b981'],
            'gagal'    => ['label' => 'Gagal',        'color' => '#ef4444'],
            'refund'   => ['label' => 'Refund',       'color' => '#6b7280'],
        ],
    ];
    $info  = $map[$type][$status] ?? ['label' => $status, 'color' => '#6b7280'];
    return '<span style="background:' . $info['color'] . '1a;color:' . $info['color'] . ';border:1px solid ' . $info['color'] . '40;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600">'
         . htmlspecialchars($info['label']) . '</span>';
}

function labelMetode(string $m): string {
    return match($m) {
        'transfer_bank' => '🏦 Transfer Bank',
        'ewallet'       => '📱 E-Wallet',
        'cod'           => '🚪 COD',
        'kartu_kredit'  => '💳 Kartu Kredit',
        default         => $m,
    };
}

$pageTitle  = 'Detail Pesanan';
$activePage = 'pesanan';
require_once __DIR__ . '/includes/header.php';
?>

<div class="cart-wrap" style="max-width: 800px; margin: 0 auto;">
    <a class="back-link" href="pesanan.php" style="display:inline-flex;align-items:center;gap:6px;padding-top:8px; text-decoration: none; color: var(--accent); font-weight: 600;">← Kembali ke Riwayat</a>
    
    <div style="margin-top: 20px; background: var(--surface); border: 1.5px solid var(--border); border-radius: 12px; padding: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1.5px solid var(--border); padding-bottom: 16px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
            <div>
                <h3 style="margin: 0; font-size: 18px;">Detail Transaksi <span style="color: var(--accent);"><?= htmlspecialchars($data_pesanan['kode_pesanan']) ?></span></h3>
                <small style="color: var(--muted);"><?= date('d F Y, H:i', strtotime($data_pesanan['created_at'])) ?></small>
            </div>
            <div style="display: flex; gap: 8px;">
                <?= badgeStatus($data_pesanan['status_pesanan'], 'status_pesanan') ?>
                <?= badgeStatus($data_pesanan['status_bayar'], 'status_bayar') ?>
            </div>
        </div>

        <div style="font-size: 14px; color: var(--text); line-height: 1.6;">
            <p style="margin: 4px 0;">Metode Pembayaran: <strong><?= labelMetode($data_pesanan['metode_bayar']) ?></strong></p>
            <?php if (!empty($data_pesanan['catatan'])): ?>
                <div style="background: rgba(0,0,0,0.02); border: 1px solid var(--border); border-radius: 8px; padding: 12px; margin: 12px 0;">
                    <strong style="font-size: 13px; display: block; margin-bottom: 2px;">Catatan Pembelian:</strong>
                    <span style="color: var(--muted); font-size: 13px;"><?= htmlspecialchars($data_pesanan['catatan']) ?></span>
                </div>
            <?php endif; ?>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-top: 24px; font-size: 14px;">
            <thead>
                <tr style="border-bottom: 2px solid var(--border); text-align: left; color: var(--muted);">
                    <th style="padding: 10px 0;">Nama Sepatu</th>
                    <th style="text-align: center; padding: 10px 0;">Ukuran</th>
                    <th style="text-align: center; padding: 10px 0;">Jumlah</th>
                    <th style="text-align: right; padding: 10px 0;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $result_detail->fetch_assoc()): ?>
                    <tr style="border-bottom: 1px solid var(--border);">
                        <td style="padding: 14px 0; font-weight: 600; color: var(--text);"><?= htmlspecialchars($item['nama_produk']) ?></td>
                        <td style="text-align: center; padding: 14px 0; color: var(--muted);"><?= $item['ukuran'] ? htmlspecialchars($item['ukuran']) : '-' ?></td>
                        <td style="text-align: center; padding: 14px 0;"><?= $item['qty'] ?>x</td>
                        <td style="text-align: right; padding: 14px 0; font-weight: 600;"><?= rp($item['subtotal']) ?></td>
                    </tr>
                <?php endwhile; ?>
                
                <tr>
                    <td colspan="3" style="text-align: right; padding: 12px 0 4px 0; color: var(--muted);">Subtotal Produk:</td>
                    <td style="text-align: right; padding: 12px 0 4px 0; font-weight: 600;"><?= rp($data_pesanan['subtotal']) ?></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: right; padding: 4px 0; color: var(--muted);">Ongkos Kirim:</td>
                    <td style="text-align: right; padding: 4px 0; color: #10b981; font-weight: 600;">+ <?= rp($data_pesanan['ongkos_kirim']) ?></td>
                </tr>
                <?php if ($data_pesanan['diskon'] > 0): ?>
                    <tr>
                        <td colspan="3" style="text-align: right; padding: 4px 0; color: #ef4444;">Potongan Diskon:</td>
                        <td style="text-align: right; padding: 4px 0; color: #ef4444; font-weight: 600;">- <?= rp($data_pesanan['diskon']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr style="border-top: 1.5px solid var(--border); font-size: 16px; font-weight: 700;">
                    <td colspan="3" style="text-align: right; padding: 16px 0 0 0;">Total Bayar:</td>
                    <td style="text-align: right; padding: 16px 0 0 0; color: var(--accent);"><?= rp($data_pesanan['total_bayar']) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>