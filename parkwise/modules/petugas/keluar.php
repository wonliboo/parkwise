<?php
/**
 * ParkWise - Kendaraan Keluar (Petugas)
 * Input  : GET id_parkir (search) | POST id_parkir, metode_bayar
 * Proses : Panggil sp_kendaraan_keluar (hitung durasi & biaya) → cetak struk
 * Output : Struk keluar + info pembayaran + QR QRIS jika dipilih
 *
 * FLOWCHART CETAK STRUK / PROSES KELUAR:
 * [START] → Cari transaksi aktif (by plat / id)
 * → [Ditemukan?] → NO → Alert tidak ditemukan
 *                → YES → Tampilkan preview (durasi & est. biaya)
 * → Pilih metode bayar (Tunai / QRIS)
 *   → [QRIS?] → YES → Tampilkan QR Code
 *             → NO  → Lanjut
 * → [Konfirmasi] → Panggil sp_kendaraan_keluar
 *   → Hitung fn_hitung_durasi(waktu_masuk, NOW())
 *   → Hitung fn_hitung_biaya(durasi, tarif_per_jam)
 *   → UPDATE tb_transaksi SET status='keluar', biaya_total, durasi_jam, waktu_keluar
 *   → UPDATE tb_area_parkir terisi - 1
 *   → INSERT tb_log_aktivitas
 * → Tampilkan struk keluar → [END]
 */
require_once __DIR__ . '/../../includes/config.php';
requireRole(['petugas']);

$pageTitle  = 'Kendaraan Keluar';
$activePage = 'keluar';

$cariPlat  = clean($_GET['plat'] ?? '');
$trxFound  = null;
$strukDone = null;

// ---- Cari transaksi aktif ----
if ($cariPlat) {
    $trxFound = $pdo->prepare(
        "SELECT t.id_parkir, t.waktu_masuk, t.id_area,
                k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik,
                tf.tarif_per_jam, tf.id_tarif, a.nama_area
         FROM tb_transaksi t
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
         JOIN tb_tarif tf    ON t.id_tarif = tf.id_tarif
         LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
         WHERE k.plat_nomor = :p AND t.status = 'masuk'
         ORDER BY t.waktu_masuk ASC
         LIMIT 1"
    );
    $trxFound->execute([':p' => strtoupper($cariPlat)]);
    $trxFound = $trxFound->fetch();

    if (!$trxFound) {
        setFlash('warning', "Kendaraan dengan plat $cariPlat tidak ditemukan atau sudah keluar.");
    }
}

// ---- Proses keluar ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idParkir  = (int)($_POST['id_parkir'] ?? 0);
    $metode    = in_array($_POST['metode_bayar'] ?? '', ['tunai','qris']) ? $_POST['metode_bayar'] : 'tunai';

    if ($idParkir < 1) {
        setFlash('error', 'ID transaksi tidak valid.');
        redirect(BASE_URL . '/modules/petugas/keluar.php');
    }

    // Panggil stored procedure
    $stmt = $pdo->prepare('CALL sp_kendaraan_keluar(:id, :metode, :uid, @biaya, @durasi)');
    $stmt->execute([':id' => $idParkir, ':metode' => $metode, ':uid' => currentUserId()]);
    $stmt->closeCursor();

    $result = $pdo->query('SELECT @biaya AS biaya, @durasi AS durasi')->fetch();

    // Ambil data lengkap untuk struk
    $strukDone = $pdo->prepare(
        "SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik,
                tf.tarif_per_jam, a.nama_area, u.nama_lengkap AS petugas_nama
         FROM tb_transaksi t
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
         JOIN tb_tarif tf    ON t.id_tarif = tf.id_tarif
         LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
         LEFT JOIN tb_user u ON t.id_user = u.id_user
         WHERE t.id_parkir = :id
         LIMIT 1"
    );
    $strukDone->execute([':id' => $idParkir]);
    $strukDone = $strukDone->fetch();

    setFlash('success', "Kendaraan {$strukDone['plat_nomor']} berhasil diproses keluar. Biaya: " . formatRupiah($strukDone['biaya_total']));
}

// ---- Daftar kendaraan aktif untuk tabel referensi ----
$aktifList = $pdo->query(
    "SELECT t.id_parkir, k.plat_nomor, k.jenis_kendaraan, t.waktu_masuk, a.nama_area, tf.tarif_per_jam
     FROM tb_transaksi t
     JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
     JOIN tb_tarif tf    ON t.id_tarif = tf.id_tarif
     LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
     WHERE t.status = 'masuk'
     ORDER BY t.waktu_masuk ASC
     LIMIT 50"
)->fetchAll();

require_once __DIR__ . '/../../includes/layout_top.php';
?>

<!-- Struk Keluar (muncul setelah proses) -->
<?php if ($strukDone): ?>
<div style="display:grid;grid-template-columns:1fr 400px;gap:24px;align-items:start;margin-bottom:24px;">
  <div class="panel">
    <div class="panel-header" style="background:linear-gradient(90deg,var(--success),#1a5c38);">
      <h2 style="color:#fff;">✓ Kendaraan Berhasil Keluar</h2>
    </div>
    <div class="panel-body">
      <div class="stats-grid" style="margin-bottom:0;">
        <div class="stat-card"><div class="stat-icon stat-icon-oxford"><i data-lucide="clock"></i></div>
          <div class="stat-info"><div class="stat-value"><?= $strukDone['durasi_jam'] ?> jam</div><div class="stat-label">Durasi Parkir</div></div>
        </div>
        <div class="stat-card card-oxford"><div class="stat-icon stat-icon-tan"><i data-lucide="banknote"></i></div>
          <div class="stat-info">
            <div class="stat-value" style="font-size:1.2rem;"><?= formatRupiah($strukDone['biaya_total']) ?></div>
            <div class="stat-label">Total Biaya</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div id="strukKeluarWrap" class="struk-wrap">
      <div class="struk-header">
        <div class="struk-title">ParkWise</div>
        <div class="struk-sub">STRUK KELUAR PARKIR</div>
        <div class="struk-sub"><?= date('d/m/Y H:i:s', strtotime($strukDone['waktu_keluar'])) ?></div>
      </div>
      <hr class="struk-divider">
      <div class="struk-row"><span>No. Transaksi</span><span><b>#<?= str_pad($strukDone['id_parkir'], 6, '0', STR_PAD_LEFT) ?></b></span></div>
      <div class="struk-row"><span>Plat Nomor</span><span><b><?= htmlspecialchars($strukDone['plat_nomor']) ?></b></span></div>
      <div class="struk-row"><span>Jenis</span><span><?= ucfirst($strukDone['jenis_kendaraan']) ?></span></div>
      <?php if ($strukDone['nama_area']): ?>
      <div class="struk-row"><span>Area</span><span><?= htmlspecialchars($strukDone['nama_area']) ?></span></div>
      <?php endif; ?>
      <hr class="struk-divider">
      <div class="struk-row"><span>Masuk</span><span><?= date('d/m/Y H:i', strtotime($strukDone['waktu_masuk'])) ?></span></div>
      <div class="struk-row"><span>Keluar</span><span><?= date('d/m/Y H:i', strtotime($strukDone['waktu_keluar'])) ?></span></div>
      <div class="struk-row"><span>Durasi</span><span><b><?= $strukDone['durasi_jam'] ?> jam</b></span></div>
      <div class="struk-row"><span>Tarif/Jam</span><span><?= formatRupiah($strukDone['tarif_per_jam']) ?></span></div>
      <hr class="struk-divider">
      <div class="struk-row"><span>Metode Bayar</span><span><?= strtoupper($strukDone['metode_bayar']) ?></span></div>

      <?php if ($strukDone['metode_bayar'] === 'qris'): ?>
      <div style="text-align:center;margin:10px 0;">
        <div style="font-size:0.7rem;color:#666;margin-bottom:4px;">SCAN UNTUK KONFIRMASI QRIS</div>
        <div id="qrKeluar" class="qr-box"></div>
      </div>
      <?php endif; ?>

      <div class="struk-total">
        <span>TOTAL BAYAR</span>
        <span><?= formatRupiah($strukDone['biaya_total']) ?></span>
      </div>
      <div class="struk-footer">
        Petugas: <?= htmlspecialchars($strukDone['petugas_nama'] ?? '—') ?><br>
        Terima kasih — ParkWise
      </div>
    </div>

    <div style="display:flex;gap:8px;margin-top:12px;">
      <button onclick="printStruk('strukKeluarWrap')" class="btn btn-primary btn-block">
        <i data-lucide="printer"></i> Cetak Struk
      </button>
      <a href="<?= BASE_URL ?>/modules/petugas/keluar.php" class="btn btn-outline">
        <i data-lucide="refresh-cw"></i> Baru
      </a>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Search & Form -->
<div style="display:grid;grid-template-columns:1fr <?= $trxFound ? '1fr' : '' ?>;gap:24px;align-items:start;">

  <!-- Cari Kendaraan -->
  <div class="panel">
    <div class="panel-header">
      <h2><i data-lucide="search" style="display:inline;width:16px;height:16px;margin-right:6px;"></i> Cari Kendaraan</h2>
    </div>
    <div class="panel-body">
      <form method="GET" action="<?= BASE_URL ?>/modules/petugas/keluar.php">
        <div class="form-group">
          <label>Plat Nomor Kendaraan</label>
          <div style="display:flex;gap:8px;">
            <input type="text" name="plat" class="form-control"
                   style="text-transform:uppercase;font-weight:700;font-family:'Courier New',monospace;font-size:1.1rem;letter-spacing:2px;"
                   placeholder="B 1234 ABC"
                   value="<?= htmlspecialchars($cariPlat) ?>"
                   oninput="this.value=this.value.toUpperCase()" required autofocus>
            <button type="submit" class="btn btn-primary">
              <i data-lucide="search"></i> Cari
            </button>
          </div>
        </div>
      </form>

      <!-- Klik cepat dari daftar aktif -->
      <div style="margin-top:16px;">
        <div style="font-size:0.78rem;font-weight:600;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">Kendaraan Aktif — Klik untuk Proses</div>
        <div style="display:flex;flex-wrap:wrap;gap:8px;max-height:220px;overflow-y:auto;">
          <?php foreach ($aktifList as $row): ?>
          <a href="<?= BASE_URL ?>/modules/petugas/keluar.php?plat=<?= urlencode($row['plat_nomor']) ?>"
             class="btn btn-outline btn-sm"
             style="font-family:'Courier New',monospace;font-weight:700;">
            <?= htmlspecialchars($row['plat_nomor']) ?>
          </a>
          <?php endforeach; ?>
          <?php if (empty($aktifList)): ?>
          <span class="text-muted text-small">Tidak ada kendaraan parkir.</span>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Form Proses Keluar -->
  <?php if ($trxFound): ?>
  <?php
    $durMenit = max(0, (time() - strtotime($trxFound['waktu_masuk'])) / 60);
    $durJam   = max(1, ceil($durMenit / 60));
    $estBiaya = $durJam * $trxFound['tarif_per_jam'];
  ?>
  <div class="panel">
    <div class="panel-header" style="background:linear-gradient(90deg,#1a5c38,#236b40);">
      <h2 style="color:#fff;">Proses Keluar: <?= htmlspecialchars($trxFound['plat_nomor']) ?></h2>
    </div>
    <div class="panel-body">
      <!-- Info kendaraan -->
      <div style="background:var(--gray-100);border-radius:var(--radius-sm);padding:14px 16px;margin-bottom:20px;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:0.82rem;">
          <div><span class="text-muted">Plat:</span> <b><?= htmlspecialchars($trxFound['plat_nomor']) ?></b></div>
          <div><span class="text-muted">Jenis:</span> <?= ucfirst($trxFound['jenis_kendaraan']) ?></div>
          <div><span class="text-muted">Masuk:</span> <?= date('H:i d/m/Y', strtotime($trxFound['waktu_masuk'])) ?></div>
          <div><span class="text-muted">Area:</span> <?= htmlspecialchars($trxFound['nama_area'] ?? '—') ?></div>
          <div><span class="text-muted">Durasi est.:</span> <b id="liveDur"><?= $durJam ?> jam</b></div>
          <div><span class="text-muted">Biaya est.:</span> <b id="liveBiaya" style="color:var(--success);"><?= formatRupiah($estBiaya) ?></b></div>
        </div>
      </div>

      <form method="POST">
        <input type="hidden" name="id_parkir" value="<?= $trxFound['id_parkir'] ?>">

        <div class="form-group">
          <label>Metode Pembayaran</label>
          <div style="display:flex;gap:12px;margin-top:6px;">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 16px;border:2px solid var(--gray-200);border-radius:8px;flex:1;transition:all 0.2s;" id="lbl-tunai">
              <input type="radio" name="metode_bayar" value="tunai" checked onchange="toggleMetode('tunai')">
              <i data-lucide="banknote" style="width:18px;height:18px;color:var(--oxford);"></i>
              <span style="font-weight:600;">Tunai</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;padding:10px 16px;border:2px solid var(--gray-200);border-radius:8px;flex:1;transition:all 0.2s;" id="lbl-qris">
              <input type="radio" name="metode_bayar" value="qris" onchange="toggleMetode('qris')">
              <i data-lucide="qr-code" style="width:18px;height:18px;color:var(--oxford);"></i>
              <span style="font-weight:600;">QRIS</span>
            </label>
          </div>
        </div>

        <!-- QR QRIS Section -->
        <div id="qrSection" class="hidden" style="text-align:center;margin-bottom:16px;">
          <input type="hidden" id="trxIdForQr" value="TRX-<?= str_pad($trxFound['id_parkir'], 6, '0', STR_PAD_LEFT) ?>">
          <div style="font-size:0.78rem;color:var(--gray-500);margin-bottom:8px;">Scan QR Code untuk Pembayaran QRIS</div>
          <div id="qrCanvas" class="qr-box" style="width:160px;height:160px;margin:0 auto;"></div>
          <div style="font-size:0.72rem;color:var(--gray-500);margin-top:6px;">
            Nominal: <b id="qrNominal"><?= formatRupiah($estBiaya) ?></b>
          </div>
        </div>

        <button type="submit" class="btn btn-success btn-block btn-lg"
                onclick="return confirm('Konfirmasi pembayaran dan proses kendaraan keluar?')">
          <i data-lucide="check-circle"></i> Konfirmasi Pembayaran & Keluar
        </button>
      </form>
    </div>
  </div>

  <script>
  function toggleMetode(m) {
    const lblT = document.getElementById('lbl-tunai');
    const lblQ = document.getElementById('lbl-qris');
    lblT.style.borderColor = m === 'tunai' ? 'var(--oxford)' : 'var(--gray-200)';
    lblQ.style.borderColor = m === 'qris'  ? 'var(--oxford)' : 'var(--gray-200)';
    const qrSec = document.getElementById('qrSection');
    if (m === 'qris') {
      qrSec.classList.remove('hidden');
      const biayaLive = document.getElementById('liveBiaya')?.textContent || 'Rp <?= $estBiaya ?>';
      generateQR('qrCanvas', 'http://192.168.1.3/parkwise/modules/petugas/keluar.php?trx=<?= $trxFound['id_parkir'] ?>&nominal=<?= $estBiaya ?>&plat=<?= urlencode($trxFound['plat_nomor']) ?>');
    } else {
      qrSec.classList.add('hidden');
    }
  }

  // Live durasi & biaya counter
  const masukTs   = <?= strtotime($trxFound['waktu_masuk']) * 1000 ?>;
  const tarifJam  = <?= $trxFound['tarif_per_jam'] ?>;

  function updateLive() {
    const now    = Date.now();
    const diffMs = now - masukTs;
    const jam    = Math.max(1, Math.ceil(diffMs / 3600000));
    const biaya  = jam * tarifJam;
    document.getElementById('liveDur').textContent   = jam + ' jam';
    document.getElementById('liveBiaya').textContent = 'Rp ' + biaya.toLocaleString('id-ID');
    if (document.getElementById('qrNominal')) {
      document.getElementById('qrNominal').textContent = 'Rp ' + biaya.toLocaleString('id-ID');
    }
  }

  updateLive();
  setInterval(updateLive, 60000);
  </script>
  <?php endif; ?>
</div>

<?php if ($strukDone && $strukDone['metode_bayar'] === 'qris'): ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
  generateQR('qrKeluar', 'http://192.168.1.3/parkwise/modules/petugas/riwayat.php?q=<?= urlencode($strukDone["plat_nomor"]) ?>');
});
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/layout_bot.php'; ?>
