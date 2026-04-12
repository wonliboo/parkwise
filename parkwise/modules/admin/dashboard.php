<?php
/**
 * ParkWise - Dashboard Admin
 * Input  : Session admin
 * Proses : Ambil statistik ringkasan (user, transaksi, pendapatan, area)
 * Output : Halaman dashboard dengan stat cards, grafik area, log terbaru
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['admin']);

$pageTitle  = 'Dashboard Admin';
$activePage = 'dashboard';

// --- Statistik utama (array-based, satu query per kelompok) ---
$stats = [];

// Total user aktif
$r = $pdo->query('SELECT COUNT(*) FROM tb_user WHERE status_aktif = 1');
$stats['total_user'] = (int)$r->fetchColumn();

// Transaksi hari ini
$r = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE DATE(waktu_masuk) = CURDATE()");
$stats['trx_hari_ini'] = (int)$r->fetchColumn();

// Kendaraan masih parkir
$r = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status = 'masuk'");
$stats['parkir_aktif'] = (int)$r->fetchColumn();

// Pendapatan bulan ini
$r = $pdo->query("SELECT COALESCE(SUM(biaya_total),0) FROM tb_transaksi WHERE status='keluar' AND MONTH(waktu_keluar)=MONTH(CURDATE()) AND YEAR(waktu_keluar)=YEAR(CURDATE())");
$stats['pendapatan_bulan'] = (float)$r->fetchColumn();

// Area parkir (LIMIT 10)
$areas = $pdo->query('SELECT * FROM tb_area_parkir ORDER BY id_area LIMIT 10')->fetchAll();

// Log terbaru (LIMIT 10)
$logs = $pdo->query(
    'SELECT l.*, u.nama_lengkap FROM tb_log_aktivitas l
     LEFT JOIN tb_user u ON l.id_user = u.id_user
     ORDER BY l.waktu_aktivitas DESC LIMIT 10'
)->fetchAll();

// Transaksi 7 hari (untuk mini chart data)
$trx7 = $pdo->query(
    "SELECT DATE(waktu_masuk) AS tgl, COUNT(*) AS total
     FROM tb_transaksi
     WHERE waktu_masuk >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
     GROUP BY DATE(waktu_masuk)
     ORDER BY tgl ASC"
)->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon stat-icon-oxford"><i data-lucide="users"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['total_user'] ?></div>
      <div class="stat-label">User Aktif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-tan"><i data-lucide="car"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['parkir_aktif'] ?></div>
      <div class="stat-label">Kendaraan Parkir</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-green"><i data-lucide="receipt"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $stats['trx_hari_ini'] ?></div>
      <div class="stat-label">Transaksi Hari Ini</div>
    </div>
  </div>
  <div class="stat-card card-oxford">
    <div class="stat-icon stat-icon-tan"><i data-lucide="banknote"></i></div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.2rem;"><?= formatRupiah($stats['pendapatan_bulan']) ?></div>
      <div class="stat-label">Pendapatan Bulan Ini</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;flex-wrap:wrap;">

  <!-- Area Parkir -->
  <div class="panel">
    <div class="panel-header">
      <h2><i data-lucide="map-pin" style="display:inline;width:16px;height:16px;margin-right:6px;"></i> Status Area Parkir</h2>
      <a href="<?= BASE_URL ?>/modules/admin/area.php" class="btn btn-tan btn-sm"><i data-lucide="settings"></i> Kelola</a>
    </div>
    <div class="panel-body">
      <?php if (empty($areas)): ?>
        <p class="text-muted text-center">Belum ada area parkir.</p>
      <?php else: ?>
        <?php foreach ($areas as $a):
          $pct = $a['kapasitas'] > 0 ? round(($a['terisi'] / $a['kapasitas']) * 100) : 0;
          $sisa = $a['kapasitas'] - $a['terisi'];
        ?>
        <div style="margin-bottom:18px;">
          <div class="d-flex justify-between" style="margin-bottom:4px;">
            <span style="font-weight:600;font-size:0.875rem;"><?= htmlspecialchars($a['nama_area']) ?></span>
            <span class="text-muted text-small"><?= $a['terisi'] ?>/<?= $a['kapasitas'] ?> • <?= $sisa ?> kosong</span>
          </div>
          <div class="capacity-bar">
            <div class="capacity-fill <?= $pct >= 90 ? 'full' : ($pct >= 70 ? 'warn' : '') ?>"
                 data-pct="<?= $pct ?>" style="width:0%"></div>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- Log Aktivitas Terbaru -->
  <div class="panel">
    <div class="panel-header">
      <h2><i data-lucide="activity" style="display:inline;width:16px;height:16px;margin-right:6px;"></i> Aktivitas Terbaru</h2>
      <a href="<?= BASE_URL ?>/modules/admin/log.php" class="btn btn-tan btn-sm">Lihat Semua</a>
    </div>
    <div class="panel-body" style="padding:0;">
      <?php if (empty($logs)): ?>
        <p class="text-muted text-center" style="padding:20px;">Belum ada log.</p>
      <?php else: ?>
      <table>
        <tbody>
          <?php foreach ($logs as $l): ?>
          <tr>
            <td style="width:36px;">
              <div style="width:32px;height:32px;background:rgba(0,33,71,0.08);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="user" style="width:14px;height:14px;color:var(--oxford);"></i>
              </div>
            </td>
            <td>
              <div style="font-size:0.82rem;font-weight:500;"><?= htmlspecialchars($l['nama_lengkap'] ?? 'Sistem') ?></div>
              <div class="text-muted text-small"><?= htmlspecialchars($l['aktivitas']) ?></div>
            </td>
            <td class="text-muted text-small" style="white-space:nowrap;text-align:right;">
              <?= date('H:i', strtotime($l['waktu_aktivitas'])) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

</div>

<!-- Mini trend (7 hari) -->
<div class="panel mt-0" style="margin-top:20px;">
  <div class="panel-header">
    <h2><i data-lucide="trending-up" style="display:inline;width:16px;height:16px;margin-right:6px;"></i> Tren Transaksi 7 Hari</h2>
  </div>
  <div class="panel-body">
    <canvas id="trendChart" height="80"></canvas>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const trx7 = <?= json_encode(array_column($trx7, 'total')) ?>;
const lbls  = <?= json_encode(array_map(fn($r) => date('d M', strtotime($r['tgl'])), $trx7)) ?>;

new Chart(document.getElementById('trendChart'), {
  type: 'bar',
  data: {
    labels: lbls,
    datasets: [{
      label: 'Transaksi',
      data: trx7,
      backgroundColor: 'rgba(0,33,71,0.75)',
      borderRadius: 6,
      borderSkipped: false,
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } },
    scales: {
      y: { beginAtZero: true, grid: { color: '#f1ede7' }, ticks: { color: '#8a7a6a', stepSize: 1 } },
      x: { grid: { display: false }, ticks: { color: '#8a7a6a' } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
