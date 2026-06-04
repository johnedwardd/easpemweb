<?php
// StepX Store — includes/filter_helper.php (Helper Komponen Filter)

function renderStatusFilter(string $currentStatus): void {
    $options = [
        ''           => '-- Semua Status Pesanan --',
        'proses'     => '⏳ Diproses',
        'dikemas'    => '📦 Dikemas',
        'dikirim'    => '🚚 Dikirim',
        'selesai'    => '✅ Selesai',
        'dibatalkan' => '❌ Dibatalkan'
    ];

    echo '<select name="status" style="flex:1; min-width:160px; padding:10px 14px; border:1.5px solid var(--border); border-radius:8px; font-size:14px; background:var(--surface); color:var(--text); cursor:pointer;">';
    foreach ($options as $val => $label) {
        $selected = ($currentStatus === $val) ? 'selected' : '';
        echo '<option value="' . htmlspecialchars($val) . '" ' . $selected . '>' . htmlspecialchars($label) . '</option>';
    }
    echo '</select>';
}
?> ```