<?php
/**
 * ParkWise - API Notifications
 * Input  : Session aktif
 * Output : JSON { count, items[] }
 */
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['count' => 0, 'items' => []]);
    exit;
}

$items = [];

// Kendaraan parkir > 12 jam
$lama = $pdo->query(
    "SELECT k.plat_nomor, t.waktu_masuk
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     WHERE t.status = 'masuk'
       AND TIMESTAMPDIFF(HOUR, t.waktu_masuk, NOW()) >= 12
     LIMIT 5"
)->fetchAll();

foreach ($lama as $l) {
    $jam = floor((time() - strtotime($l['waktu_masuk'])) / 3600);
    $items[] = ['type' => 'warning', 'msg' => "Kendaraan {$l['plat_nomor']} sudah parkir $jam jam"];
}

// Area hampir penuh (>= 90%)
$penuh = $pdo->query(
    "SELECT nama_area, kapasitas, terisi
     FROM tb_area_parkir
     WHERE kapasitas > 0 AND (terisi / kapasitas) >= 0.9
     LIMIT 3"
)->fetchAll();

foreach ($penuh as $p) {
    $items[] = ['type' => 'info', 'msg' => "Area {$p['nama_area']} hampir penuh ({$p['terisi']}/{$p['kapasitas']})"];
}

echo json_encode(['count' => count($items), 'items' => $items]);
