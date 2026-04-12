<?php
/**
 * ParkWise - Kendaraan Masuk (Petugas)
 * Input  : POST plat_nomor, jenis_kendaraan, id_tarif, id_area, pemilik, warna
 * Proses : Cek/buat kendaraan → insert tb_transaksi → update terisi area
 * Output : Flash sukses + redirect ke struk (id_parkir di session)
 *
 * FLOWCHART TRANSAKSI MASUK:
 * [START] → Input plat nomor + data kendaraan
 * → Cek kendaraan di DB (by plat)
 *   → [Ada?] → YES → pakai id_kendaraan existing
 *             → NO  → INSERT kendaraan baru
 * → Cek apakah kendaraan masih parkir (status='masuk')
 *   → [Masih parkir?] → YES → Alert: kendaraan belum keluar
 *                    → NO  → Lanjut
 * → INSERT tb_transaksi (waktu_masuk=NOW, status='masuk')
 * → UPDATE tb_area_parkir SET terisi = terisi + 1
 * → Log aktivitas
 * → Redirect ke struk masuk → [END]
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['petugas']);

$pageTitle  = 'Kendaraan Masuk';
$activePage = 'masuk';

// Load tarif dan area untuk form
$tarifs = $pdo->query('SELECT * FROM tb_tarif ORDER BY jenis_kendaraan')->fetchAll();
$areas  = $pdo->query('SELECT * FROM tb_area_parkir WHERE terisi < kapasitas ORDER BY nama_area')->fetchAll();

$newTrxId = null; // untuk tampilkan struk setelah submit

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $plat    = strtoupper(trim(clean($_POST['plat_nomor'] ?? '')));
    $jenis   = clean($_POST['jenis_kendaraan'] ?? 'motor');
    $warna   = clean($_POST['warna'] ?? '');
    $pemilik = clean($_POST['pemilik'] ?? '');
    $idTarif = (int)($_POST['id_tarif'] ?? 0);
    $idArea  = (int)($_POST['id_area'] ?? 0) ?: null;

    if (empty($plat) || $idTarif < 1) {
        setFlash('error', 'Plat nomor dan tarif wajib diisi.');
        redirect(BASE_URL . '/modules/petugas/masuk.php');
    }

    // Cek apakah kendaraan masih aktif parkir
    $cekAktif = $pdo->prepare(
        "SELECT t.id_parkir FROM tb_transaksi t
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
         WHERE k.plat_nomor = :plat AND t.status = 'masuk'
         LIMIT 1"
    );
    $cekAktif->execute([':plat' => $plat]);
    if ($cekAktif->fetch()) {
        setFlash('warning', "Kendaraan $plat masih tercatat parkir. Proses keluar dulu.");
        redirect(BASE_URL . '/modules/petugas/masuk.php');
    }

    // Cek/buat kendaraan
    $kStmt = $pdo->prepare('SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor = :p LIMIT 1');
    $kStmt->execute([':p' => $plat]);
    $kendaraan = $kStmt->fetch();

    if ($kendaraan) {
        $idKendaraan = $kendaraan['id_kendaraan'];
        // Update info jika berbeda
        $pdo->prepare(
            'UPDATE tb_kendaraan SET jenis_kendaraan=:j, warna=:w, pemilik=:pem WHERE id_kendaraan=:id'
        )->execute([':j' => $jenis, ':w' => $warna, ':pem' => $pemilik, ':id' => $idKendaraan]);
    } else {
        $pdo->prepare(
            'INSERT INTO tb_kendaraan (plat_nomor,jenis_kendaraan,warna,pemilik,id_user)
             VALUES (:p,:j,:w,:pem,:u)'
        )->execute([':p' => $plat, ':j' => $jenis, ':w' => $warna, ':pem' => $pemilik, ':u' => currentUserId()]);
        $idKendaraan = (int)$pdo->lastInsertId();
    }

    // Insert transaksi
    $pdo->prepare(
        'INSERT INTO tb_transaksi (id_kendaraan,waktu_masuk,id_tarif,status,id_user,id_area)
         VALUES (:k, NOW(), :t, "masuk", :u, :a)'
    )->execute([':k' => $idKendaraan, ':t' => $idTarif, ':u' => currentUserId(), ':a' => $idArea]);
    $newTrxId = (int)$pdo->lastInsertId();

    // Update kapasitas area
    if ($idArea) {
        $pdo->prepare(
            'UPDATE tb_area_parkir SET terisi = LEAST(terisi + 1, kapasitas) WHERE id_area = :id'
        )->execute([':id' => $idArea]);
    }

    logAktivitas("Kendaraan masuk: $plat (TRX #$newTrxId)");

    // Simpan ke session agar bisa cetak struk
    $_SESSION['last_masuk'] = $newTrxId;
    setFlash('success', "Kendaraan $plat berhasil diregistrasi. ID Transaksi: #$newTrxId");
}

// Jika ada transaksi baru, ambil data untuk struk
$strukData = null;
$trxId     = $newTrxId ?? (int)($_GET['struk'] ?? 0);
if ($trxId) {
    $strukData = $pdo->prepare(
        "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik,
                tf.tarif_per_jam, a.nama_area, u.nama_lengkap AS petugas_nama
         FROM tb_transaksi t
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
         JOIN tb_tarif tf    ON t.id_tarif = tf.id_tarif
         LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
         LEFT JOIN tb_user u ON t.id_user = u.id_user
         WHERE t.id_parkir = :id AND t.status = 'masuk'
         LIMIT 1"
    );
    $strukData->execute([':id' => $trxId]);
    $strukData = $strukData->fetch();
}

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<div style="display:grid;grid-template-columns:<?= $strukData ? '1fr 400px' : '1fr' ?>;gap:24px;align-items:start;">

  <!-- FORM INPUT -->
  <div class="panel">
    <div class="panel-header">
      <h2><i data-lucide="log-in" style="display:inline;width:16px;height:16px;margin-right:6px;"></i> Form Kendaraan Masuk</h2>
    </div>
    <div class="panel-body">
      <form method="POST" id="formMasuk">

        <div class="form-group">
          <label>Plat Nomor <span style="color:var(--danger)">*</span></label>
          <input type="text" name="plat_nomor" class="form-control"
                 style="text-transform:uppercase;font-weight:700;font-family:'Courier New',monospace;font-size:1.1rem;letter-spacing:2px;"
                 placeholder="B 1234 ABC" required autofocus
                 oninput="this.value=this.value.toUpperCase()">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Jenis Kendaraan <span style="color:var(--danger)">*</span></label>
            <select name="jenis_kendaraan" id="jenisSelect" class="form-control" onchange="syncTarif()">
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
          <input type="text" name="pemilik" class="form-control" placeholder="Opsional">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Tarif <span style="color:var(--danger)">*</span></label>
            <select name="id_tarif" id="tarifSelect" class="form-control" required onchange="updateTarifPreview()">
              <?php foreach ($tarifs as $t): ?>
              <option value="<?= $t['id_tarif'] ?>"
                      data-jenis="<?= $t['jenis_kendaraan'] ?>"
                      data-tarif="<?= $t['tarif_per_jam'] ?>">
                <?= ucfirst($t['jenis_kendaraan']) ?> — <?= formatRupiah($t['tarif_per_jam']) ?>/jam
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Area Parkir</label>
            <select name="id_area" class="form-control">
              <option value="">— Pilih Area (Opsional) —</option>
              <?php foreach ($areas as $a): ?>
              <option value="<?= $a['id_area'] ?>">
                <?= htmlspecialchars($a['nama_area']) ?> (<?= $a['kapasitas'] - $a['terisi'] ?> sisa)
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Tarif preview -->
        <div style="background:var(--gray-100);border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:18px;">
          <div style="font-size:0.78rem;color:var(--gray-500);margin-bottom:6px;">TARIF BERLAKU</div>
          <div style="font-size:1.3rem;font-weight:700;color:var(--oxford);" id="tarifPreview">—</div>
          <div style="font-size:0.75rem;color:var(--gray-500);margin-top:2px;">per jam (dibulatkan ke atas)</div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i data-lucide="check-circle"></i> Registrasi Kendaraan Masuk
        </button>
      </form>
    </div>
  </div>

  <!-- STRUK MASUK -->
  <?php if ($strukData): ?>
  <div>
    <div id="strukMasukWrap" class="struk-wrap">
      <div class="struk-header">
        <div class="struk-title">ParkWise</div>
        <div class="struk-sub">STRUK MASUK PARKIR</div>
        <div class="struk-sub"><?= date('d/m/Y H:i:s', strtotime($strukData['waktu_masuk'])) ?></div>
      </div>

      <hr class="struk-divider">

      <div class="struk-row"><span>No. Transaksi</span><span><b>#<?= str_pad($strukData['id_parkir'], 6, '0', STR_PAD_LEFT) ?></b></span></div>
      <div class="struk-row"><span>Plat Nomor</span><span><b><?= htmlspecialchars($strukData['plat_nomor']) ?></b></span></div>
      <div class="struk-row"><span>Jenis</span><span><?= ucfirst($strukData['jenis_kendaraan']) ?></span></div>
      <?php if ($strukData['warna']): ?>
      <div class="struk-row"><span>Warna</span><span><?= htmlspecialchars($strukData['warna']) ?></span></div>
      <?php endif; ?>
      <?php if ($strukData['pemilik']): ?>
      <div class="struk-row"><span>Pemilik</span><span><?= htmlspecialchars($strukData['pemilik']) ?></span></div>
      <?php endif; ?>
      <?php if ($strukData['nama_area']): ?>
      <div class="struk-row"><span>Area</span><span><?= htmlspecialchars($strukData['nama_area']) ?></span></div>
      <?php endif; ?>
      <div class="struk-row"><span>Tarif</span><span><?= formatRupiah($strukData['tarif_per_jam']) ?>/jam</span></div>
      <div class="struk-row"><span>Petugas</span><span><?= htmlspecialchars($strukData['petugas_nama'] ?? '—') ?></span></div>

      <hr class="struk-divider">
      <div style="text-align:center;margin:8px 0;">
        <div style="font-size:0.72rem;color:#666;margin-bottom:6px;">SIMPAN STRUK INI</div>
        <div id="qrMasuk" class="qr-box"></div>
        <div style="font-size:0.65rem;color:#999;margin-top:4px;">TRX-<?= str_pad($strukData['id_parkir'], 6, '0', STR_PAD_LEFT) ?></div>
      </div>
      <hr class="struk-divider">

      <div class="struk-footer">Terima kasih telah menggunakan ParkWise<br>Jaga kendaraan Anda dengan baik</div>
    </div>

    <div style="display:flex;gap:8px;margin-top:12px;">
      <button onclick="printStruk('strukMasukWrap')" class="btn btn-primary btn-block">
        <i data-lucide="printer"></i> Cetak Struk
      </button>
      <a href="<?= BASE_URL ?>/modules/petugas/masuk.php" class="btn btn-outline">
        <i data-lucide="plus"></i> Baru
      </a>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
const tarifData = <?= json_encode(array_column($tarifs, null, 'id_tarif')) ?>;

function updateTarifPreview() {
  const sel  = document.getElementById('tarifSelect');
  const opt  = sel.options[sel.selectedIndex];
  const tarif = opt?.dataset?.tarif;
  document.getElementById('tarifPreview').textContent =
    tarif ? 'Rp ' + parseInt(tarif).toLocaleString('id-ID') + ' / jam' : '—';
}

function syncTarif() {
  const jenis = document.getElementById('jenisSelect').value;
  const sel   = document.getElementById('tarifSelect');
  Array.from(sel.options).forEach(opt => {
    if (opt.dataset.jenis === jenis) sel.value = opt.value;
  });
  updateTarifPreview();
}

document.addEventListener('DOMContentLoaded', () => {
  updateTarifPreview();
  <?php if ($strukData): ?>
  generateQR('qrMasuk', 'http://192.168.1.3/parkwise/modules/petugas/riwayat.php?q=<?= urlencode($strukData["plat_nomor"]) ?>');
  <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
