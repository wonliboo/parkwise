<?php
/**
 * ParkWise - Dashboard Petugas
 * Input  : Session petugas
 * Proses : Ambil transaksi aktif & statistik shift
 * Output : Dashboard dengan status parkir aktif & aksi cepat
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['petugas']);

$pageTitle  = 'Dashboard Petugas';
$activePage = 'dashboard';

// Statistik shift hari ini
$uid = currentUserId();
$r   = $pdo->prepare("SELECT COUNT(*) FROM tb_transaksi WHERE DATE(waktu_masuk)=CURDATE() AND id_user=:u");
$r->execute([':u' => $uid]);
$trxHariIni = (int)$r->fetchColumn();

$r = $pdo->prepare("SELECT COUNT(*) FROM tb_transaksi WHERE DATE(waktu_keluar)=CURDATE() AND id_user=:u AND status='keluar'");
$r->execute([':u' => $uid]);
$keluarHariIni = (int)$r->fetchColumn();

$r = $pdo->prepare("SELECT COALESCE(SUM(biaya_total),0) FROM tb_transaksi WHERE DATE(waktu_keluar)=CURDATE() AND id_user=:u AND status='keluar'");
$r->execute([':u' => $uid]);
$totalBayar = (float)$r->fetchColumn();

// Kendaraan masih parkir (LIMIT 20)
$aktif = $pdo->query(
    "SELECT t.id_parkir, k.plat_nomor, k.jenis_kendaraan, t.waktu_masuk, a.nama_area, tf.tarif_per_jam
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     JOIN tb_tarif tf    ON t.id_tarif = tf.id_tarif
     LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
     WHERE t.status = 'masuk'
     ORDER BY t.waktu_masuk ASC
     LIMIT 20"
)->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<!-- Quick Actions -->
<div class="d-flex gap-12 mb-24" style="flex-wrap:wrap;margin-bottom:20px;">
  <a href="<?= BASE_URL ?>/modules/petugas/masuk.php" class="btn btn-primary btn-lg">
    <i data-lucide="log-in"></i> Kendaraan Masuk
  </a>
  <a href="<?= BASE_URL ?>/modules/petugas/keluar.php" class="btn btn-tan btn-lg">
    <i data-lucide="log-out"></i> Kendaraan Keluar
  </a>
</div>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon stat-icon-oxford"><i data-lucide="arrow-down-circle"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $trxHariIni ?></div>
      <div class="stat-label">Masuk Hari Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-green"><i data-lucide="arrow-up-circle"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $keluarHariIni ?></div>
      <div class="stat-label">Keluar Hari Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-tan"><i data-lucide="car"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= count($aktif) ?></div>
      <div class="stat-label">Masih Parkir</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-oxford"><i data-lucide="wallet"></i></div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.1rem;"><?= formatRupiah($totalBayar) ?></div>
      <div class="stat-label">Total Dibayar Hari Ini</div>
    </div>
  </div>
</div>

<!-- Kendaraan aktif -->
<div class="panel">
  <div class="panel-header">
    <h2><i data-lucide="car" style="display:inline;width:16px;height:16px;margin-right:6px;"></i> Kendaraan Sedang Parkir</h2>
    <a href="<?= BASE_URL ?>/modules/petugas/keluar.php" class="btn btn-tan btn-sm">Proses Keluar</a>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>ID</th><th>Plat</th><th>Jenis</th><th>Area</th><th>Masuk</th><th>Durasi</th><th>Est. Biaya</th></tr>
      </thead>
      <tbody>
        <?php if (empty($aktif)): ?>
        <tr><td colspan="7" class="text-center text-muted" style="padding:24px;">Tidak ada kendaraan parkir saat ini.</td></tr>
        <?php else: foreach ($aktif as $row):
          $durMenit = max(0, (time() - strtotime($row['waktu_masuk'])) / 60);
          $durJam   = max(1, ceil($durMenit / 60));
          $estBiaya = $durJam * $row['tarif_per_jam'];
        ?>
        <tr>
          <td class="text-muted text-small">#<?= $row['id_parkir'] ?></td>
          <td style="font-weight:700;font-family:'Courier New',monospace;color:var(--oxford);"><?= htmlspecialchars($row['plat_nomor']) ?></td>
          <td><span class="badge badge-<?= strtolower($row['jenis_kendaraan']) ?>"><?= ucfirst($row['jenis_kendaraan']) ?></span></td>
          <td class="text-small"><?= htmlspecialchars($row['nama_area'] ?? '—') ?></td>
          <td class="text-small"><?= date('H:i d/m', strtotime($row['waktu_masuk'])) ?></td>
          <td class="text-small"><?= $durJam ?> jam</td>
          <td style="font-weight:600;color:var(--success);"><?= formatRupiah($estBiaya) ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
