<?php
/**
 * ParkWise - Area Parkir (Admin)
 * Input  : POST (tambah/edit/hapus area)
 * Proses : CRUD tb_area_parkir
 * Output : Tabel area dengan kapasitas bar
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['admin']);

$pageTitle  = 'Area Parkir';
$activePage = 'area';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah') {
        $pdo->prepare('INSERT INTO tb_area_parkir (nama_area,kapasitas,terisi) VALUES (:n,:k,0)')
            ->execute([':n' => clean($_POST['nama_area']), ':k' => (int)$_POST['kapasitas']]);
        logAktivitas('Tambah area: ' . clean($_POST['nama_area']));
        setFlash('success', 'Area berhasil ditambahkan.');

    } elseif ($action === 'edit') {
        $id = (int)($_POST['id_area'] ?? 0);
        $pdo->prepare('UPDATE tb_area_parkir SET nama_area=:n, kapasitas=:k, terisi=:t WHERE id_area=:id')
            ->execute([':n' => clean($_POST['nama_area']), ':k' => (int)$_POST['kapasitas'], ':t' => (int)$_POST['terisi'], ':id' => $id]);
        logAktivitas('Edit area ID: ' . $id);
        setFlash('success', 'Area berhasil diperbarui.');

    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id_area'] ?? 0);
        $pdo->prepare('DELETE FROM tb_area_parkir WHERE id_area=:id')->execute([':id' => $id]);
        logAktivitas('Hapus area ID: ' . $id);
        setFlash('success', 'Area berhasil dihapus.');
    }

    redirect(BASE_URL . '/modules/admin/area.php');
}

$areas = $pdo->query('SELECT * FROM tb_area_parkir ORDER BY id_area')->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div style="margin-bottom:16px;display:flex;justify-content:flex-end;">
  <button class="btn btn-primary" onclick="openModal('modalTambah')">
    <i data-lucide="plus"></i> Tambah Area
  </button>
</div>

<div class="panel">
  <div class="panel-header"><h2>Daftar Area Parkir</h2></div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Nama Area</th><th>Kapasitas</th><th>Terisi</th><th>Sisa</th><th>Penggunaan</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (empty($areas)): ?>
        <tr><td colspan="7" class="text-center text-muted" style="padding:24px;">Belum ada area.</td></tr>
        <?php else: foreach ($areas as $i => $a):
          $pct  = $a['kapasitas'] > 0 ? round(($a['terisi'] / $a['kapasitas']) * 100) : 0;
          $sisa = $a['kapasitas'] - $a['terisi'];
        ?>
        <tr>
          <td class="text-muted text-small"><?= $i + 1 ?></td>
          <td style="font-weight:500;"><?= htmlspecialchars($a['nama_area']) ?></td>
          <td><?= $a['kapasitas'] ?></td>
          <td><?= $a['terisi'] ?></td>
          <td><?= $sisa ?></td>
          <td style="min-width:140px;">
            <div style="font-size:0.75rem;color:var(--gray-500);margin-bottom:3px;"><?= $pct ?>%</div>
            <div class="capacity-bar">
              <div class="capacity-fill <?= $pct >= 90 ? 'full' : ($pct >= 70 ? 'warn' : '') ?>"
                   data-pct="<?= $pct ?>" style="width:0%"></div>
            </div>
          </td>
          <td>
            <button class="btn btn-outline btn-sm" onclick='editArea(<?= json_encode($a) ?>)'>
              <i data-lucide="edit-2"></i> Edit
            </button>
            <form method="POST" style="display:inline;" id="del<?= $a['id_area'] ?>">
              <input type="hidden" name="action" value="hapus">
              <input type="hidden" name="id_area" value="<?= $a['id_area'] ?>">
              <button type="button" class="btn btn-danger btn-sm"
                      onclick="confirmDelete('del<?= $a['id_area'] ?>', '<?= htmlspecialchars($a['nama_area'],ENT_QUOTES) ?>')">
                <i data-lucide="trash-2"></i>
              </button>
            </form>
          </td>
        </tr>
        <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
  <div class="modal-box">
    <div class="modal-header">
      <h3>Tambah Area Parkir</h3>
      <button class="modal-close" onclick="closeModal('modalTambah')"><i data-lucide="x"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-body">
        <div class="form-group">
          <label>Nama Area</label>
          <input type="text" name="nama_area" class="form-control" placeholder="cth: Zona A - Motor" required>
        </div>
        <div class="form-group">
          <label>Kapasitas (slot)</label>
          <input type="number" name="kapasitas" class="form-control" min="1" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
  <div class="modal-box">
    <div class="modal-header">
      <h3>Edit Area Parkir</h3>
      <button class="modal-close" onclick="closeModal('modalEdit')"><i data-lucide="x"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_area" id="editId">
      <div class="modal-body">
        <div class="form-group">
          <label>Nama Area</label>
          <input type="text" name="nama_area" id="editNama" class="form-control" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Kapasitas</label>
            <input type="number" name="kapasitas" id="editKap" class="form-control" min="1" required>
          </div>
          <div class="form-group">
            <label>Terisi (saat ini)</label>
            <input type="number" name="terisi" id="editTerisi" class="form-control" min="0">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
        <button type="submit" class="btn btn-primary"><i data-lucide="save"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function editArea(a) {
  document.getElementById('editId').value     = a.id_area;
  document.getElementById('editNama').value   = a.nama_area;
  document.getElementById('editKap').value    = a.kapasitas;
  document.getElementById('editTerisi').value = a.terisi;
  openModal('modalEdit');
  lucide.createIcons();
}
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
