<?php
/**
 * ParkWise - Rekap Transaksi (Owner)
 * Input  : GET (dari, sampai, jenis, group_by)
 * Proses : SELECT SUM/GROUP dari tb_transaksi dengan filter rentang waktu
 * Output : Tabel rekap + chart + ekspor ringkasan
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['owner']);

$pageTitle  = 'Rekap Transaksi';
$activePage = 'rekap';

// Default: bulan ini
$dari    = clean($_GET['dari']    ?? date('Y-m-01'));
$sampai  = clean($_GET['sampai']  ?? date('Y-m-d'));
$jenisF  = clean($_GET['jenis']   ?? '');
$groupBy = in_array($_GET['group'] ?? '', ['hari','minggu','bulan']) ? $_GET['group'] : 'hari';

// ---- Rekap ringkasan ----
$conds  = ["t.status = 'keluar'", "DATE(t.waktu_keluar) BETWEEN :dari AND :sampai"];
$params = [':dari' => $dari, ':sampai' => $sampai];
if ($jenisF) {
    $conds[]        = 'k.jenis_kendaraan = :jenis';
    $params[':jenis'] = $jenisF;
}
$whereSQL = 'WHERE ' . implode(' AND ', $conds);

// Summary total
$sumStmt = $pdo->prepare(
    "SELECT COUNT(*) AS jumlah, COALESCE(SUM(t.biaya_total),0) AS total,
            COALESCE(AVG(t.biaya_total),0) AS rata2,
            COALESCE(AVG(t.durasi_jam),0) AS avg_durasi
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     $whereSQL"
);
$sumStmt->execute($params);
$summary = $sumStmt->fetch();

// Tren per hari / minggu / bulan
$groupExpr = match($groupBy) {
    'minggu' => "DATE_FORMAT(t.waktu_keluar, '%Y-W%u')",
    'bulan'  => "DATE_FORMAT(t.waktu_keluar, '%Y-%m')",
    default  => "DATE(t.waktu_keluar)",
};

$trendStmt = $pdo->prepare(
    "SELECT $groupExpr AS periode,
            COUNT(*) AS jumlah,
            COALESCE(SUM(t.biaya_total),0) AS total
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     $whereSQL
     GROUP BY periode
     ORDER BY periode ASC
     LIMIT 90"
);
$trendStmt->execute($params);
$trendRows = $trendStmt->fetchAll();

// Per jenis kendaraan
$perJenisStmt = $pdo->prepare(
    "SELECT k.jenis_kendaraan, COUNT(*) AS jumlah, COALESCE(SUM(t.biaya_total),0) AS total,
            COALESCE(AVG(t.durasi_jam),0) AS avg_dur
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     $whereSQL
     GROUP BY k.jenis_kendaraan
     ORDER BY total DESC"
);
$perJenisStmt->execute($params);
$perJenis = $perJenisStmt->fetchAll();

// Per metode bayar
$perMetodeStmt = $pdo->prepare(
    "SELECT t.metode_bayar, COUNT(*) AS jumlah, COALESCE(SUM(t.biaya_total),0) AS total
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     $whereSQL
     GROUP BY t.metode_bayar"
);
$perMetodeStmt->execute($params);
$perMetode = $perMetodeStmt->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<!-- Filter Bar -->
<div class="panel" style="margin-bottom:20px;">
  <div class="panel-body" style="padding:16px 20px;">
    <form method="GET" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
      <div class="form-group" style="margin:0;">
        <label>Dari Tanggal</label>
        <input type="date" name="dari" class="form-control" value="<?= htmlspecialchars($dari) ?>">
      </div>
      <div class="form-group" style="margin:0;">
        <label>Sampai Tanggal</label>
        <input type="date" name="sampai" class="form-control" value="<?= htmlspecialchars($sampai) ?>">
      </div>
      <div class="form-group" style="margin:0;">
        <label>Jenis Kendaraan</label>
        <select name="jenis" class="form-control">
          <option value="">Semua</option>
          <option value="motor"   <?= $jenisF === 'motor'   ? 'selected':'' ?>>Motor</option>
          <option value="mobil"   <?= $jenisF === 'mobil'   ? 'selected':'' ?>>Mobil</option>
          <option value="lainnya" <?= $jenisF === 'lainnya' ? 'selected':'' ?>>Lainnya</option>
        </select>
      </div>
      <div class="form-group" style="margin:0;">
        <label>Kelompokkan</label>
        <select name="group" class="form-control">
          <option value="hari"   <?= $groupBy === 'hari'   ? 'selected':'' ?>>Per Hari</option>
          <option value="minggu" <?= $groupBy === 'minggu' ? 'selected':'' ?>>Per Minggu</option>
          <option value="bulan"  <?= $groupBy === 'bulan'  ? 'selected':'' ?>>Per Bulan</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i data-lucide="filter"></i> Filter</button>
      <a href="<?= BASE_URL ?>/modules/owner/rekap.php" class="btn btn-outline"><i data-lucide="refresh-cw"></i></a>
    </form>
  </div>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
  <div class="stat-card card-oxford">
    <div class="stat-icon stat-icon-tan"><i data-lucide="wallet"></i></div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.1rem;"><?= formatRupiah($summary['total']) ?></div>
      <div class="stat-label">Total Pendapatan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-oxford"><i data-lucide="receipt"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= number_format($summary['jumlah']) ?></div>
      <div class="stat-label">Total Transaksi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-green"><i data-lucide="trending-up"></i></div>
    <div class="stat-info">
      <div class="stat-value" style="font-size:1.1rem;"><?= formatRupiah($summary['rata2']) ?></div>
      <div class="stat-label">Rata-rata Transaksi</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-icon-tan"><i data-lucide="clock"></i></div>
    <div class="stat-info">
      <div class="stat-value"><?= round($summary['avg_durasi'], 1) ?> jam</div>
      <div class="stat-label">Rata-rata Durasi</div>
    </div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;">
  <!-- Chart Tren -->
  <div class="panel">
    <div class="panel-header">
      <h2>Tren Pendapatan (<?= ucfirst($groupBy) ?>)</h2>
    </div>
    <div class="panel-body">
      <canvas id="chartTren" height="100"></canvas>
    </div>
  </div>

  <!-- Breakdown -->
  <div>
    <!-- Per Jenis -->
    <div class="panel" style="margin-bottom:16px;">
      <div class="panel-header"><h2>Per Jenis Kendaraan</h2></div>
      <div class="panel-body" style="padding:16px;">
        <?php foreach ($perJenis as $j): ?>
        <div style="margin-bottom:12px;">
          <div class="d-flex justify-between" style="margin-bottom:3px;">
            <span class="badge badge-<?= $j['jenis_kendaraan'] ?>"><?= ucfirst($j['jenis_kendaraan']) ?></span>
            <span style="font-size:0.82rem;font-weight:700;"><?= formatRupiah($j['total']) ?></span>
          </div>
          <div class="text-muted text-small"><?= number_format($j['jumlah']) ?> trx · rata <?= round($j['avg_dur'],1) ?>j</div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($perJenis)): ?><p class="text-muted text-small">Tidak ada data.</p><?php endif; ?>
      </div>
    </div>

    <!-- Per Metode -->
    <div class="panel">
      <div class="panel-header"><h2>Per Metode Bayar</h2></div>
      <div class="panel-body" style="padding:16px;">
        <?php foreach ($perMetode as $m): ?>
        <div class="d-flex justify-between" style="margin-bottom:10px;">
          <div>
            <i data-lucide="<?= $m['metode_bayar'] === 'qris' ? 'qr-code' : 'banknote' ?>"
               style="width:14px;height:14px;display:inline;"></i>
            <span style="font-weight:600;margin-left:4px;"><?= strtoupper($m['metode_bayar']) ?></span>
          </div>
          <div style="text-align:right;">
            <div style="font-weight:700;font-size:0.875rem;"><?= formatRupiah($m['total']) ?></div>
            <div class="text-muted text-small"><?= number_format($m['jumlah']) ?> trx</div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($perMetode)): ?><p class="text-muted text-small">Tidak ada data.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Detail tabel -->
<div class="panel" style="margin-top:0;">
  <div class="panel-header">
    <h2>Detail per <?= ucfirst($groupBy) ?></h2>
    <button onclick="window.print()" class="btn btn-tan btn-sm"><i data-lucide="printer"></i> Print</button>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>Periode</th><th>Jumlah Transaksi</th><th>Total Pendapatan</th><th>Rata-rata</th></tr>
      </thead>
      <tbody>
        <?php if (empty($trendRows)): ?>
        <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">Tidak ada data.</td></tr>
        <?php else: foreach ($trendRows as $t): ?>
        <tr>
          <td style="font-weight:600;"><?= htmlspecialchars($t['periode']) ?></td>
          <td><?= number_format($t['jumlah']) ?></td>
          <td style="font-weight:700;color:var(--oxford);"><?= formatRupiah($t['total']) ?></td>
          <td><?= $t['jumlah'] > 0 ? formatRupiah($t['total'] / $t['jumlah']) : '—' ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const trendData = <?= json_encode($trendRows) ?>;
new Chart(document.getElementById('chartTren'), {
  type: 'bar',
  data: {
    labels: trendData.map(r => r.periode),
    datasets: [{
      label: 'Pendapatan',
      data: trendData.map(r => parseFloat(r.total)),
      backgroundColor: 'rgba(0,33,71,0.8)',
      borderRadius: 4,
    },{
      label: 'Transaksi',
      data: trendData.map(r => parseInt(r.jumlah)),
      backgroundColor: 'rgba(210,180,140,0.7)',
      borderRadius: 4,
      yAxisID: 'y2',
    }]
  },
  options: {
    responsive: true,
    interaction: { mode: 'index' },
    plugins: {
      tooltip: {
        callbacks: {
          label: ctx => ctx.datasetIndex === 0
            ? 'Rp ' + ctx.parsed.y.toLocaleString('id-ID')
            : ctx.parsed.y + ' trx'
        }
      }
    },
    scales: {
      y:  { beginAtZero: true, ticks: { callback: v => 'Rp' + (v/1000).toFixed(0)+'k', color:'#8a7a6a' }, grid: { color: '#f1ede7' } },
      y2: { beginAtZero: true, position: 'right', ticks: { color: '#b8955a' }, grid: { display: false } },
      x:  { ticks: { color:'#8a7a6a' }, grid: { display: false } },
    }
  }
});
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
