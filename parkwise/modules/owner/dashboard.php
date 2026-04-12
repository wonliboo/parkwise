<?php
/**
 * ParkWise - Dashboard Owner
 * Input  : Session owner
 * Proses : Ambil ringkasan pendapatan & kendaraan
 * Output : Stats + chart pendapatan bulanan
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['owner']);

$pageTitle  = 'Dashboard Owner';
$activePage = 'dashboard';

// Pendapatan bulan ini
$r = $pdo->query("SELECT COALESCE(SUM(biaya_total),0) FROM tb_transaksi WHERE status='keluar' AND MONTH(waktu_keluar)=MONTH(CURDATE()) AND YEAR(waktu_keluar)=YEAR(CURDATE())");
$pendBulanIni = (float)$r->fetchColumn();

// Pendapatan bulan lalu
$r = $pdo->query("SELECT COALESCE(SUM(biaya_total),0) FROM tb_transaksi WHERE status='keluar' AND MONTH(waktu_keluar)=MONTH(CURDATE()-INTERVAL 1 MONTH) AND YEAR(waktu_keluar)=YEAR(CURDATE()-INTERVAL 1 MONTH)");
$pendBulanLalu = (float)$r->fetchColumn();

// Total transaksi bulan ini
$r = $pdo->query("SELECT COUNT(*) FROM tb_transaksi WHERE status='keluar' AND MONTH(waktu_keluar)=MONTH(CURDATE()) AND YEAR(waktu_keluar)=YEAR(CURDATE())");
$totalTrxBulan = (int)$r->fetchColumn();

// Pendapatan per jenis kendaraan bulan ini
$perJenis = $pdo->query(
    "SELECT k.jenis_kendaraan, COUNT(*) AS jumlah, COALESCE(SUM(t.biaya_total),0) AS total
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     WHERE t.status='keluar' AND MONTH(t.waktu_keluar)=MONTH(CURDATE()) AND YEAR(t.waktu_keluar)=YEAR(CURDATE())
     GROUP BY k.jenis_kendaraan"
)->fetchAll();

// Tren 12 bulan terakhir
$tren12 = $pdo->query(
    "SELECT DATE_FORMAT(waktu_keluar,'%Y-%m') AS bln,
            COALESCE(SUM(biaya_total),0) AS total,
            COUNT(*) AS jumlah
     FROM tb_transaksi
     WHERE status='keluar' AND waktu_keluar >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
     GROUP BY DATE_FORMAT(waktu_keluar,'%Y-%m')
     ORDER BY bln ASC"
)->fetchAll();

$growthPct = $pendBulanLalu > 0 ? round((($pendBulanIni - $pendBulanLalu) / $pendBulanLalu) * 100, 1) : null;

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="stats-grid">
  <div class="stat-card card-oxford">
    <div class="stat-icon stat-icon-tan"><i data-lucide="trending-up"></i></div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.2rem;"><?= formatRupiah($pendBulanIni) ?></div>
      <div class="stat-label">Pendapatan Bulan Ini</div>
      <?php if ($growthPct !== null): ?>
      <div style="font-size:0.72rem;margin-top:4px;color:<?= $growthPct >= 0 ? 'var(--success)' : 'var(--danger)' ?>;">
        <?= $growthPct >= 0 ? '▲' : '▼' ?> <?= abs($growthPct) ?>% vs bulan lalu
      </div>
      <?php endif; ?>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-oxford"><i data-lucide="receipt"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($totalTrxBulan) ?></div>
      <div class="stat-label">Transaksi Bulan Ini</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-tan"><i data-lucide="calendar"></i></div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.2rem;"><?= formatRupiah($pendBulanLalu) ?></div>
      <div class="stat-label">Pendapatan Bulan Lalu</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-green"><i data-lucide="bar-chart-2"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= $totalTrxBulan > 0 ? formatRupiah($pendBulanIni / $totalTrxBulan) : 'Rp 0' ?></div>
      <div class="stat-label">Rata-rata per Transaksi</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;">
  <!-- Chart 12 bulan -->
  <div class="panel">
    <div class="panel-header">
      <h2><i data-lucide="bar-chart-2" style="display:inline;width:16px;height:16px;margin-right:6px;"></i> Pendapatan 12 Bulan Terakhir</h2>
      <a href="<?= BASE_URL ?>/modules/owner/rekap.php" class="btn btn-tan btn-sm">Rekap Detail</a>
    </div>
    <div class="panel-body">
      <canvas id="chartBulanan" height="120"></canvas>
    </div>
  </div>

  <!-- Per Jenis Kendaraan -->
  <div class="panel">
    <div class="panel-header"><h2>Per Jenis Kendaraan</h2></div>
    <div class="panel-body">
      <?php if (empty($perJenis)): ?>
      <p class="text-muted text-center">Belum ada data.</p>
      <?php else: foreach ($perJenis as $j):
        $pctJ = $pendBulanIni > 0 ? round(($j['total'] / $pendBulanIni) * 100) : 0;
      ?>
      <div style="margin-bottom:18px;">
        <div class="d-flex justify-between" style="margin-bottom:4px;">
          <span class="badge badge-<?= $j['jenis_kendaraan'] ?>"><?= ucfirst($j['jenis_kendaraan']) ?></span>
          <span style="font-size:0.82rem;font-weight:600;color:var(--oxford);"><?= formatRupiah($j['total']) ?></span>
        </div>
        <div style="font-size:0.72rem;color:var(--gray-500);margin-bottom:4px;">
          <?= number_format($j['jumlah']) ?> transaksi (<?= $pctJ ?>%)
        </div>
        <div class="capacity-bar">
          <div class="capacity-fill" data-pct="<?= $pctJ ?>" style="width:0%;background:linear-gradient(90deg,var(--oxford),var(--oxford-light))"></div>
        </div>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const trenData = <?= json_encode($tren12) ?>;
const lbls  = trenData.map(r => r.bln);
const totals = trenData.map(r => parseFloat(r.total));

new Chart(document.getElementById('chartBulanan'), {
  type: 'line',
  data: {
    labels: lbls,
    datasets: [{
      label: 'Pendapatan',
      data: totals,
      borderColor: '#002147',
      backgroundColor: 'rgba(0,33,71,0.08)',
      borderWidth: 2.5,
      pointBackgroundColor: '#d2b48c',
      pointRadius: 5,
      tension: 0.35,
      fill: true,
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: ctx => 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
        }
      }
    },
    scales: {
      y: {
        beginAtZero: true,
        grid: { color: '#f1ede7' },
        ticks: {
          color: '#8a7a6a',
          callback: v => 'Rp ' + (v/1000).toFixed(0) + 'k'
        }
      },
      x: { grid: { display: false }, ticks: { color: '#8a7a6a' } }
    }
  }
});
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
