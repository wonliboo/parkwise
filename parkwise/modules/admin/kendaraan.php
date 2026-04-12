<?php
/**
 * ParkWise - Kelola Kendaraan (Admin)
 * Input  : GET (list, search) | POST (tambah/edit/hapus)
 * Proses : CRUD tb_kendaraan
 * Output : Tabel kendaraan terdaftar
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['admin']);

$pageTitle  = 'Data Kendaraan';
$activePage = 'kendaraan';
$perPage    = 15;
$page       = max(1, (int)($_GET['page'] ?? 1));
$search     = clean($_GET['q'] ?? '');
$offset     = ($page - 1) * $perPage;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $pdo->prepare(
            'INSERT INTO tb_kendaraan (plat_nomor,jenis_kendaraan,warna,pemilik)
             VALUES (:p,:j,:w,:pem)'
        )->execute([
            ':p'   => strtoupper(clean($_POST['plat_nomor'])),
            ':j'   => clean($_POST['jenis_kendaraan']),
            ':w'   => clean($_POST['warna']),
            ':pem' => clean($_POST['pemilik']),
        ]);
        logAktivitas('Tambah kendaraan: ' . strtoupper(clean($_POST['plat_nomor'])));
        setFlash('success', 'Kendaraan berhasil ditambahkan.');

    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id_kendaraan'] ?? 0);
        $pdo->prepare('DELETE FROM tb_kendaraan WHERE id_kendaraan=:id')->execute([':id' => $id]);
        setFlash('success', 'Kendaraan berhasil dihapus.');
    }

    redirect(BASE_URL . '/modules/admin/kendaraan.php');
}

$where  = $search ? "WHERE (k.plat_nomor LIKE :q OR k.jenis_kendaraan LIKE :q OR k.pemilik LIKE :q)" : '';
$params = $search ? [':q' => "%$search%"] : [];

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM tb_kendaraan k $where");
$totalStmt->execute($params);
$total    = (int)$totalStmt->fetchColumn();
$totalPgs = max(1, (int)ceil($total / $perPage));

$stmt = $pdo->prepare("SELECT k.* FROM tb_kendaraan k $where ORDER BY k.id_kendaraan DESC LIMIT :lim OFFSET :off");
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,  PDO::PARAM_INT);
if ($search) $stmt->bindValue(':q', "%$search%");
$stmt->execute();
$kendaraans = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div class="filter-bar">
  <div class="search-wrap">
    <i data-lucide="search"></i>
    <input type="text" class="search-input" placeholder="Cari plat, jenis, pemilik..."
           value="<?= htmlspecialchars($search) ?>"
           onkeyup="liveSearch(this.value, '/modules/admin/kendaraan.php')">
  </div>
  <button class="btn btn-primary" onclick="openModal('modalTambah')">
    <i data-lucide="plus"></i> Tambah Kendaraan
  </button>
</div>

<div class="panel">
  <div class="panel-header"><h2>Data Kendaraan (<?= $total ?>)</h2></div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Plat Nomor</th><th>Jenis</th><th>Warna</th><th>Pemilik</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (empty($kendaraans)): ?>
        <tr><td colspan="6" class="text-center text-muted" style="padding:24px;">Tidak ada data.</td></tr>
        <?php else: foreach ($kendaraans as $i => $k): ?>
        <tr>
          <td class="text-muted text-small"><?= $offset + $i + 1 ?></td>
          <td style="font-weight:700;font-family:'Courier New',monospace;color:var(--oxford);"><?= htmlspecialchars($k['plat_nomor']) ?></td>
          <td><span class="badge badge-<?= strtolower($k['jenis_kendaraan']) ?>"><?= ucfirst($k['jenis_kendaraan']) ?></span></td>
          <td><?= htmlspecialchars($k['warna'] ?? '-') ?></td>
          <td><?= htmlspecialchars($k['pemilik'] ?? '-') ?></td>
          <td>
            <form method="POST" style="display:inline;" id="delK<?= $k['id_kendaraan'] ?>">
              <input type="hidden" name="action" value="hapus">
              <input type="hidden" name="id_kendaraan" value="<?= $k['id_kendaraan'] ?>">
              <button type="button" class="btn btn-danger btn-sm"
                      onclick="confirmDelete('delK<?= $k['id_kendaraan'] ?>', '<?= htmlspecialchars($k['plat_nomor'],ENT_QUOTES) ?>')">
                <i data-lucide="trash-2"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
  <?php if ($totalPgs > 1): ?>
  <div class="pagination">
    <?php for ($p = 1; $p <= $totalPgs; $p++): ?>
    <a href="?page=<?= $p ?>&q=<?= urlencode($search) ?>"
       class="page-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal-box">
    <div class="modal-header">
      <h3>Tambah Kendaraan</h3>
      <button class="modal-close" onclick="closeModal('modalTambah')"><i data-lucide="x"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-body">
        <div class="form-group">
          <label>Plat Nomor</label>
          <input type="text" name="plat_nomor" class="form-control" style="text-transform:uppercase;" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Jenis Kendaraan</label>
            <select name="jenis_kendaraan" class="form-control">
              <option value="motor">Motor</option>
              <option value="mobil">Mobil</option>
              <option value="lainnya">Lainnya</option>
            </select>
          </div>
          <div class="form-group">
            <label>Warna</label>
            <input type="text" name="warna" class="form-control" placeholder="Hitam">
          </div>
        </div>
        <div class="form-group">
          <label>Nama Pemilik</label>
          <input type="text" name="pemilik" class="form-control">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function liveSearch(q, base) {
  base = base || window.location.pathname;
  clearTimeout(window._st);
  window._st = setTimeout(() => {
    window.location.href = base + '?q=' + encodeURIComponent(q) + '&page=1';
  }, 500);
}
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
