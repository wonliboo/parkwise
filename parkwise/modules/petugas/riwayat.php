<?php
/**
 * ParkWise - Riwayat Transaksi (Petugas)
 * Input  : GET (search plat, tanggal, status, page)
 * Proses : SELECT tb_transaksi JOIN kendaraan+tarif+area LIMIT/OFFSET
 * Output : Tabel riwayat dengan filter & cetak ulang struk
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['petugas']);

$pageTitle  = 'Riwayat Transaksi';
$activePage = 'riwayat';
$perPage    = 20;
$page       = max(1, (int)($_GET['page'] ?? 1));
$search     = clean($_GET['q'] ?? '');
$statusF    = clean($_GET['status'] ?? '');
$tglF       = clean($_GET['tgl'] ?? date('Y-m-d'));
$offset     = ($page - 1) * $perPage;

$conds  = ['1=1'];
$params = [];

if ($search) {
    $conds[] = 'k.plat_nomor LIKE :q';
    $params[':q'] = "%$search%";
}
if ($statusF) {
    $conds[] = "t.status = :st";
    $params[':st'] = $statusF;
}
if ($tglF) {
    $conds[] = "DATE(t.waktu_masuk) = :tgl";
    $params[':tgl'] = $tglF;
}

$where = 'WHERE ' . implode(' AND ', $conds);

$totalStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM tb_transaksi t JOIN tb_kendaraan k ON t.id_kendaraan=k.id_kendaraan $where"
);
$totalStmt->execute($params);
$total    = (int)$totalStmt->fetchColumn();
$totalPgs = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare(
    "SELECT t.id_parkir, t.waktu_masuk, t.waktu_keluar, t.durasi_jam,
            t.biaya_total, t.status, t.metode_bayar,
            k.plat_nomor, k.jenis_kendaraan,
            tf.tarif_per_jam, a.nama_area
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     JOIN tb_tarif tf    ON t.id_tarif = tf.id_tarif
     LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
     $where
     ORDER BY t.waktu_masuk DESC
     LIMIT :lim OFFSET :off"
);
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->execute();
$trxList = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="filter-bar">
  <div class="search-wrap">
    <i data-lucide="search"></i>
    <input type="text" class="search-input" id="searchInput" placeholder="Cari plat nomor..."
           value="<?= htmlspecialchars($search) ?>"
           onkeyup="applyFilter()">
  </div>
  <input type="date" class="form-control" style="width:auto;" id="filterTgl"
         value="<?= htmlspecialchars($tglF) ?>" onchange="applyFilter()">
  <select class="form-control" style="width:auto;" id="filterStatus" onchange="applyFilter()">
    <option value="">Semua Status</option>
    <option value="masuk"  <?= $statusF === 'masuk'  ? 'selected' : '' ?>>Masuk</option>
    <option value="keluar" <?= $statusF === 'keluar' ? 'selected' : '' ?>>Keluar</option>
  </select>
  <a href="<?= BASE_URL ?>/modules/petugas/riwayat.php" class="btn btn-outline">
    <i data-lucide="refresh-cw"></i> Reset
  </a>
</div>

<div class="panel">
  <div class="panel-header">
    <h2>Riwayat Transaksi (<?= number_format($total) ?>)</h2>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Plat</th><th>Jenis</th><th>Masuk</th><th>Keluar</th>
          <th>Durasi</th><th>Biaya</th><th>Metode</th><th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($trxList)): ?>
        <tr><td colspan="10" class="text-center text-muted" style="padding:24px;">Tidak ada transaksi.</td></tr>
        <?php else: foreach ($trxList as $row): ?>
        <tr>
          <td class="text-muted text-small">#<?= str_pad($row['id_parkir'], 6, '0', STR_PAD_LEFT) ?></td>
          <td style="font-weight:700;font-family:'Courier New',monospace;"><?= htmlspecialchars($row['plat_nomor']) ?></td>
          <td><span class="badge badge-<?= strtolower($row['jenis_kendaraan']) ?>"><?= ucfirst($row['jenis_kendaraan']) ?></span></td>
          <td class="text-small"><?= date('H:i d/m', strtotime($row['waktu_masuk'])) ?></td>
          <td class="text-small"><?= $row['waktu_keluar'] ? date('H:i d/m', strtotime($row['waktu_keluar'])) : '—' ?></td>
          <td><?= $row['durasi_jam'] ? $row['durasi_jam'] . ' jam' : '—' ?></td>
          <td style="font-weight:600;"><?= $row['biaya_total'] ? formatRupiah($row['biaya_total']) : '—' ?></td>
          <td class="text-small"><?= $row['metode_bayar'] ? strtoupper($row['metode_bayar']) : '—' ?></td>
          <td><span class="badge badge-<?= $row['status'] ?>"><?= ucfirst($row['status']) ?></span></td>
          <td>
            <?php if ($row['status'] === 'keluar'): ?>
            <button class="btn btn-outline btn-sm"
                    onclick="cetakUlang(<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>)">
              <i data-lucide="printer"></i>
            </button>
            <?php elseif ($row['status'] === 'masuk'): ?>
            <a href="<?= BASE_URL ?>/modules/petugas/keluar.php?plat=<?= urlencode($row['plat_nomor']) ?>"
               class="btn btn-success btn-sm">
              <i data-lucide="log-out"></i> Keluar
            </a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($totalPgs > 1): ?>
  <div class="pagination">
    <?php for ($p = 1; $p <= min($totalPgs, 15); $p++): ?>
    <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>&status=<?= urlencode($statusF) ?>&tgl=<?= urlencode($tglF) ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Cetak Ulang -->
<div class="modal-overlay" id="modalStruk">
  <div class="modal-box" style="max-width:440px;">
    <div class="modal-header">
      <h3><i data-lucide="printer"></i> Cetak Ulang Struk</h3>
      <button class="modal-close" onclick="closeModal('modalStruk')"><i data-lucide="x"></i></button>
    </div>
    <div class="modal-body">
      <div id="strukUlang" class="struk-wrap" style="border:none;padding:0;"></div>
    </div>
    <div class="modal-footer">
      <button onclick="printStruk('strukUlang')" class="btn btn-primary">
        <i data-lucide="printer"></i> Print
      </button>
      <button onclick="closeModal('modalStruk')" class="btn btn-outline">Tutup</button>
    </div>
  </div>
</div>

<script>
function applyFilter() {
  const q      = document.getElementById('searchInput')?.value || '';
  const tgl    = document.getElementById('filterTgl')?.value || '';
  const status = document.getElementById('filterStatus')?.value || '';
  clearTimeout(window._ft);
  window._ft = setTimeout(() => {
    window.location.href = `/modules/petugas/riwayat.php?q=${encodeURIComponent(q)}&tgl=${tgl}&status=${status}&page=1`;
  }, 400);
}

function cetakUlang(row) {
  const idFmt = '#' + String(row.id_parkir).padStart(6, '0');
  document.getElementById('strukUlang').innerHTML = `
    <div class="struk-header">
      <div class="struk-title">ParkWise</div>
      <div class="struk-sub">STRUK PARKIR (CETAK ULANG)</div>
    </div>
    <hr class="struk-divider">
    <div class="struk-row"><span>No. Transaksi</span><span><b>${idFmt}</b></span></div>
    <div class="struk-row"><span>Plat Nomor</span><span><b>${row.plat_nomor}</b></span></div>
    <div class="struk-row"><span>Jenis</span><span>${row.jenis_kendaraan}</span></div>
    <hr class="struk-divider">
    <div class="struk-row"><span>Masuk</span><span>${row.waktu_masuk}</span></div>
    <div class="struk-row"><span>Keluar</span><span>${row.waktu_keluar || '—'}</span></div>
    <div class="struk-row"><span>Durasi</span><span><b>${row.durasi_jam || '—'} jam</b></span></div>
    <hr class="struk-divider">
    <div class="struk-row"><span>Metode</span><span>${(row.metode_bayar || '').toUpperCase()}</span></div>
    <div class="struk-total"><span>TOTAL BAYAR</span><span>Rp ${parseInt(row.biaya_total || 0).toLocaleString('id-ID')}</span></div>
    <div class="struk-footer">ParkWise — Cetak Ulang</div>
  `;
  openModal('modalStruk');
  lucide.createIcons();
}
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
