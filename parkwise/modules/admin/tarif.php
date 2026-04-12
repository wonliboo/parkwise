<?php
/**
 * ParkWise - Tarif Parkir (Admin)
 * Input  : POST (tambah/edit/hapus tarif)
 * Proses : CRUD tb_tarif
 * Output : Tabel tarif per jenis kendaraan
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['admin']);

$pageTitle  = 'Tarif Parkir';
$activePage = 'tarif';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'tambah' || $action === 'edit') {
        $jenis = in_array($_POST['jenis_kendaraan'], ['motor','mobil','lainnya']) ? $_POST['jenis_kendaraan'] : 'motor';
        $tarif = max(0, (int)preg_replace('/\D/', '', $_POST['tarif_per_jam'] ?? '0'));

        if ($action === 'tambah') {
            $pdo->prepare('INSERT INTO tb_tarif (jenis_kendaraan,tarif_per_jam) VALUES (:j,:t)')
                ->execute([':j' => $jenis, ':t' => $tarif]);
            logAktivitas('Tambah tarif: ' . $jenis);
            setFlash('success', 'Tarif berhasil ditambahkan.');
        } else {
            $id = (int)($_POST['id_tarif'] ?? 0);
            $pdo->prepare('UPDATE tb_tarif SET jenis_kendaraan=:j, tarif_per_jam=:t WHERE id_tarif=:id')
                ->execute([':j' => $jenis, ':t' => $tarif, ':id' => $id]);
            logAktivitas('Edit tarif ID: ' . $id);
            setFlash('success', 'Tarif berhasil diperbarui.');
        }
    } elseif ($action === 'hapus') {
        $id = (int)($_POST['id_tarif'] ?? 0);
        $pdo->prepare('DELETE FROM tb_tarif WHERE id_tarif=:id')->execute([':id' => $id]);
        logAktivitas('Hapus tarif ID: ' . $id);
        setFlash('success', 'Tarif berhasil dihapus.');
    }

    redirect(BASE_URL . '/modules/admin/tarif.php');
}

$tarifs = $pdo->query('SELECT * FROM tb_tarif ORDER BY id_tarif')->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div style="margin-bottom:16px;display:flex;justify-content:flex-end;">
  <button class="btn btn-primary" onclick="openModal('modalTambah')">
    <i data-lucide="plus"></i> Tambah Tarif
  </button>
</div>

<div class="panel">
  <div class="panel-header">
    <h2>Daftar Tarif Parkir</h2>
  </div>
  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>#</th><th>Jenis Kendaraan</th><th>Tarif per Jam</th><th>Aksi</th></tr>
      </thead>
      <tbody>
        <?php if (empty($tarifs)): ?>
        <tr><td colspan="4" class="text-center text-muted" style="padding:24px;">Belum ada tarif.</td></tr>
        <?php else: foreach ($tarifs as $i => $t): ?>
        <tr>
          <td class="text-muted text-small"><?= $i + 1 ?></td>
          <td>
            <span class="badge badge-<?= $t['jenis_kendaraan'] ?>">
              <i data-lucide="<?= $t['jenis_kendaraan'] === 'motor' ? 'bike' : ($t['jenis_kendaraan'] === 'mobil' ? 'car' : 'truck') ?>"></i>
              <?= ucfirst($t['jenis_kendaraan']) ?>
            </span>
          </td>
          <td style="font-weight:600;color:var(--oxford);"><?= formatRupiah($t['tarif_per_jam']) ?>/jam</td>
          <td>
            <button class="btn btn-outline btn-sm" onclick='editTarif(<?= json_encode($t) ?>)'>
              <i data-lucide="edit-2"></i> Edit
            </button>
            <form method="POST" style="display:inline;" id="del<?= $t['id_tarif'] ?>">
              <input type="hidden" name="action" value="hapus">
              <input type="hidden" name="id_tarif" value="<?= $t['id_tarif'] ?>">
              <button type="button" class="btn btn-danger btn-sm"
                      onclick="confirmDelete('del<?= $t['id_tarif'] ?>', '<?= ucfirst($t['jenis_kendaraan']) ?>')">
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
      <h3>Tambah Tarif</h3>
      <button class="modal-close" onclick="closeModal('modalTambah')"><i data-lucide="x"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="tambah">
      <div class="modal-body">
        <div class="form-group">
          <label>Jenis Kendaraan</label>
          <select name="jenis_kendaraan" class="form-control">
            <option value="motor">Motor</option>
            <option value="mobil">Mobil</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        <div class="form-group">
          <label>Tarif per Jam (Rp)</label>
          <input type="text" name="tarif_per_jam" class="form-control" placeholder="2.000" oninput="formatNominal(this)" required>
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
      <h3>Edit Tarif</h3>
      <button class="modal-close" onclick="closeModal('modalEdit')"><i data-lucide="x"></i></button>
    </div>
    <form method="POST">
      <input type="hidden" name="action" value="edit">
      <input type="hidden" name="id_tarif" id="editId">
      <div class="modal-body">
        <div class="form-group">
          <label>Jenis Kendaraan</label>
          <select name="jenis_kendaraan" id="editJenis" class="form-control">
            <option value="motor">Motor</option>
            <option value="mobil">Mobil</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>
        <div class="form-group">
          <label>Tarif per Jam (Rp)</label>
          <input type="text" name="tarif_per_jam" id="editTarif" class="form-control" oninput="formatNominal(this)" required>
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
function editTarif(t) {
  document.getElementById('editId').value    = t.id_tarif;
  document.getElementById('editJenis').value = t.jenis_kendaraan;
  document.getElementById('editTarif').value = parseInt(t.tarif_per_jam).toLocaleString('id-ID');
  openModal('modalEdit');
  lucide.createIcons();
}
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
