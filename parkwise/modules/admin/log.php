<?php
/**
 * ParkWise - Log Aktivitas (Admin)
 * Input  : GET (filter tanggal, search, page)
 * Proses : SELECT tb_log_aktivitas dengan JOIN user, filter opsional
 * Output : Tabel log dengan filter tanggal & pencarian
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['admin']);

$pageTitle  = 'Log Aktivitas';
$activePage = 'log';
$perPage    = 20;
$page       = max(1, (int)($_GET['page'] ?? 1));
$search     = clean($_GET['q'] ?? '');
$tglDari    = clean($_GET['dari'] ?? '');
$tglSampai  = clean($_GET['sampai'] ?? '');
$offset     = ($page - 1) * $perPage;

// Build query dynamically
$conds  = [];
$params = [];

if ($search) {
    $conds[] = "(u.nama_lengkap LIKE :q OR l.aktivitas LIKE :q)";
    $params[':q'] = "%$search%";
}
if ($tglDari) {
    $conds[] = "DATE(l.waktu_aktivitas) >= :dari";
    $params[':dari'] = $tglDari;
}
if ($tglSampai) {
    $conds[] = "DATE(l.waktu_aktivitas) <= :sampai";
    $params[':sampai'] = $tglSampai;
}

$whereSQL = $conds ? 'WHERE ' . implode(' AND ', $conds) : '';

$totalStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM tb_log_aktivitas l LEFT JOIN tb_user u ON l.id_user=u.id_user $whereSQL"
);
$totalStmt->execute($params);
$total    = (int)$totalStmt->fetchColumn();
$totalPgs = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare(
    "SELECT l.*, u.nama_lengkap, u.role
     FROM tb_log_aktivitas l
     LEFT JOIN tb_user u ON l.id_user = u.id_user
     $whereSQL
     ORDER BY l.waktu_aktivitas DESC
     LIMIT :lim OFFSET :off"
);
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$logs = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="filter-bar">
  <div class="search-wrap">
    <i data-lucide="search"></i>
    <input type="text" class="search-input" placeholder="Cari user atau aktivitas..."
           value="<?= htmlspecialchars($search) ?>"
           onkeyup="liveSearch(this.value)">
  </div>
  <input type="date" class="form-control" style="width:auto;" id="filterDari"
         value="<?= htmlspecialchars($tglDari) ?>" placeholder="Dari tanggal"
         onchange="applyFilter()">
  <input type="date" class="form-control" style="width:auto;" id="filterSampai"
         value="<?= htmlspecialchars($tglSampai) ?>" placeholder="Sampai tanggal"
         onchange="applyFilter()">
  <a href="<?= BASE_URL ?>/modules/admin/log.php" class="btn btn-outline"><i data-lucide="refresh-cw"></i> Reset</a>
</div>

<div class="panel">
  <div class="panel-header">
    <h2>Log Aktivitas (<?= number_format($total) ?> entri)</h2>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Waktu</th><th>User</th><th>Role</th><th>Aktivitas</th></tr>
      </thead>
      <tbody>
        <?php if (empty($logs)): ?>
        <tr><td colspan="5" class="text-center text-muted" style="padding:24px;">Tidak ada log.</td></tr>
        <?php else: foreach ($logs as $i => $l): ?>
        <tr>
          <td class="text-muted text-small"><?= $offset + $i + 1 ?></td>
          <td class="text-small" style="white-space:nowrap;">
            <?= date('d/m/Y H:i:s', strtotime($l['waktu_aktivitas'])) ?>
          </td>
          <td style="font-weight:500;"><?= htmlspecialchars($l['nama_lengkap'] ?? 'Sistem') ?></td>
          <td>
            <?php if ($l['role']): ?>
            <span class="badge badge-<?= $l['role'] ?>"><?= ucfirst($l['role']) ?></span>
            <?php else: ?>
            <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($l['aktivitas']) ?></td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPgs > 1): ?>
  <div class="pagination">
    <?php for ($p = 1; $p <= min($totalPgs, 20); $p++): ?>
    <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>&dari=<?= urlencode($tglDari) ?>&sampai=<?= urlencode($tglSampai) ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<script>
function liveSearch(q) {
  clearTimeout(window._st);
  window._st = setTimeout(applyFilter, 500);
}

function applyFilter() {
  const q       = document.querySelector('.search-input')?.value || '';
  const dari    = document.getElementById('filterDari')?.value || '';
  const sampai  = document.getElementById('filterSampai')?.value || '';
  window.location.href = `/modules/admin/log.php?q=${encodeURIComponent(q)}&dari=${dari}&sampai=${sampai}&page=1`;
}
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
