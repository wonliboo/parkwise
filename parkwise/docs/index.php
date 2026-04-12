<?php
/**
 * ParkWise - Halaman Dokumentasi
 * Berisi: flowchart, pseudocode, modul input-proses-output,
 *         cara modifikasi, debugging, & coding guidelines
 */
require_once __DIR__ . '/../includes/config.php';
requireLogin();

$pageTitle  = 'Dokumentasi';
$activePage = 'docs';

$section = clean($_GET['s'] ?? 'overview');

require_once __DIR__ . '/../includes/layout_top.php';
?>

<div class="docs-layout">
  <!-- NAV -->
  <nav class="docs-nav">
    <div class="docs-section-head">Umum</div>
    <a href="?s=overview"    class="<?= $section==='overview'    ?'active':'' ?>">Gambaran Umum</a>
    <a href="?s=struktur"    class="<?= $section==='struktur'    ?'active':'' ?>">Struktur Proyek</a>
    <a href="?s=database"    class="<?= $section==='database'    ?'active':'' ?>">Skema Database</a>
    <a href="?s=setup"       class="<?= $section==='setup'       ?'active':'' ?>">Cara Setup</a>

    <div class="docs-section-head">Flowchart & Pseudocode</div>
    <a href="?s=flow-login"  class="<?= $section==='flow-login'  ?'active':'' ?>">Proses Login</a>
    <a href="?s=flow-masuk"  class="<?= $section==='flow-masuk'  ?'active':'' ?>">Proses Transaksi</a>
    <a href="?s=flow-struk"  class="<?= $section==='flow-struk'  ?'active':'' ?>">Cetak Struk</a>

    <div class="docs-section-head">Dokumentasi Modul</div>
    <a href="?s=mod-auth"    class="<?= $section==='mod-auth'    ?'active':'' ?>">Autentikasi</a>
    <a href="?s=mod-transaksi" class="<?= $section==='mod-transaksi'?'active':'' ?>">Transaksi</a>
    <a href="?s=mod-admin"   class="<?= $section==='mod-admin'   ?'active':'' ?>">Modul Admin</a>
    <a href="?s=mod-owner"   class="<?= $section==='mod-owner'   ?'active':'' ?>">Modul Owner</a>

    <div class="docs-section-head">Panduan</div>
    <a href="?s=modifikasi"  class="<?= $section==='modifikasi'  ?'active':'' ?>">Cara Modifikasi</a>
    <a href="?s=debugging"   class="<?= $section==='debugging'   ?'active':'' ?>">Debugging</a>
    <a href="?s=guidelines"  class="<?= $section==='guidelines'  ?'active':'' ?>">Coding Guidelines</a>
  </nav>

  <!-- CONTENT -->
  <div class="docs-content">

    <?php if ($section === 'overview'): ?>
    <h2>Gambaran Umum ParkWise</h2>
    <p><strong>ParkWise</strong> adalah sistem manajemen parkir berbasis web yang dibangun dengan PHP, MySQL, HTML, CSS, dan JavaScript. Sistem ini dirancang untuk memudahkan pengelolaan parkir dengan tiga peran pengguna utama.</p>

    <h3>Peran Pengguna</h3>
    <table>
      <thead><tr><th>Role</th><th>Akses</th></tr></thead>
      <tbody>
        <tr><td><span class="badge badge-admin">Admin</span></td><td>CRUD User, Tarif, Area, Kendaraan, Log Aktivitas</td></tr>
        <tr><td><span class="badge badge-petugas">Petugas</span></td><td>Transaksi masuk/keluar, cetak struk, riwayat</td></tr>
        <tr><td><span class="badge badge-owner">Owner</span></td><td>Dashboard pendapatan, rekap transaksi dengan filter waktu</td></tr>
      </tbody>
    </table>

    <h3>Teknologi</h3>
    <table>
      <thead><tr><th>Layer</th><th>Teknologi</th></tr></thead>
      <tbody>
        <tr><td>Backend</td><td>PHP 8+ (PDO, session, password_hash)</td></tr>
        <tr><td>Database</td><td>MySQL 8 (Stored Procedure, Function)</td></tr>
        <tr><td>Frontend</td><td>HTML5, CSS3, Vanilla JS</td></tr>
        <tr><td>Icons</td><td>Lucide Icons (CDN)</td></tr>
        <tr><td>Charts</td><td>Chart.js 4 (CDN)</td></tr>
        <tr><td>QR Code</td><td>qrcodejs (CDN)</td></tr>
      </tbody>
    </table>

    <h3>Fitur Utama</h3>
    <p>Login/logout multi-role · CRUD lengkap untuk admin · Transaksi masuk/keluar otomatis · Hitung durasi & biaya via Stored Procedure · Pembayaran Tunai & QRIS dengan QR Code · Cetak struk · Rekap laporan owner · Log aktivitas · Notifikasi alert · Filter & pencarian · Responsive design</p>

    <?php elseif ($section === 'struktur'): ?>
    <h2>Struktur Proyek</h2>
    <pre>parkwise/
├── index.php                  ← Redirect ke login/dashboard
├── login.php                  ← Halaman login
├── logout.php                 ← Proses logout
├── database.sql               ← SQL schema + seed data
│
├── includes/
│   ├── config.php             ← Konfigurasi, DB, helper functions
│   ├── layout_top.php         ← Header, sidebar, topbar
│   └── layout_bot.php         ← Footer, close tags
│
├── assets/
│   ├── css/style.css          ← Master stylesheet
│   └── js/app.js              ← JavaScript global
│
├── modules/
│   ├── admin/
│   │   ├── dashboard.php      ← Dashboard admin
│   │   ├── user.php           ← Kelola user
│   │   ├── tarif.php          ← Kelola tarif
│   │   ├── area.php           ← Kelola area parkir
│   │   ├── kendaraan.php      ← Kelola kendaraan
│   │   └── log.php            ← Log aktivitas
│   │
│   ├── petugas/
│   │   ├── dashboard.php      ← Dashboard petugas
│   │   ├── masuk.php          ← Kendaraan masuk + struk
│   │   ├── keluar.php         ← Kendaraan keluar + struk
│   │   └── riwayat.php        ← Riwayat transaksi
│   │
│   └── owner/
│       ├── dashboard.php      ← Dashboard owner
│       └── rekap.php          ← Rekap transaksi
│
├── api/
│   └── notifications.php      ← API notifikasi (JSON)
│
└── docs/
    └── index.php              ← Halaman dokumentasi ini</pre>

    <?php elseif ($section === 'database'): ?>
    <h2>Skema Database</h2>
    <p>Database <code>parkwise</code> terdiri dari 6 tabel utama yang saling berrelasi:</p>

    <h3>Tabel tb_user</h3>
    <table>
      <thead><tr><th>Kolom</th><th>Tipe</th><th>Keterangan</th></tr></thead>
      <tbody>
        <tr><td>id_user</td><td>INT PK AI</td><td>Primary key</td></tr>
        <tr><td>nama_lengkap</td><td>VARCHAR(50)</td><td>Nama lengkap user</td></tr>
        <tr><td>username</td><td>VARCHAR(50) UNIQUE</td><td>Username login</td></tr>
        <tr><td>password</td><td>VARCHAR(100)</td><td>Bcrypt hash</td></tr>
        <tr><td>role</td><td>ENUM</td><td>admin / petugas / owner</td></tr>
        <tr><td>status_aktif</td><td>TINYINT(1)</td><td>1=aktif, 0=nonaktif</td></tr>
      </tbody>
    </table>

    <h3>Stored Procedure & Function</h3>
    <table>
      <thead><tr><th>Nama</th><th>Jenis</th><th>Fungsi</th></tr></thead>
      <tbody>
        <tr><td>fn_hitung_durasi(masuk, keluar)</td><td>FUNCTION</td><td>Hitung durasi jam (dibulatkan ke atas)</td></tr>
        <tr><td>fn_hitung_biaya(durasi, tarif)</td><td>FUNCTION</td><td>Hitung biaya = durasi × tarif</td></tr>
        <tr><td>sp_kendaraan_keluar(id, metode, uid, OUT biaya, OUT durasi)</td><td>PROCEDURE</td><td>Proses keluar: hitung + update transaksi + kurangi kapasitas area</td></tr>
        <tr><td>sp_log(uid, aktivitas)</td><td>PROCEDURE</td><td>Insert log aktivitas</td></tr>
      </tbody>
    </table>

    <?php elseif ($section === 'setup'): ?>
    <h2>Cara Setup Lokal</h2>
    <h3>Persyaratan</h3>
    <p>PHP 8.0+, MySQL 8.0+, Web server (Apache/Nginx), atau XAMPP/Laragon.</p>

    <h3>Langkah Instalasi</h3>
    <pre>1. Clone / ekstrak folder parkwise ke htdocs atau www

2. Buat database:
   mysql -u root -p
   &gt; CREATE DATABASE parkwise;
   &gt; EXIT;

3. Import schema:
   mysql -u root -p parkwise &lt; database.sql

4. Edit konfigurasi database di includes/config.php:
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');     ← sesuaikan password MySQL

5. Akses: http://localhost/parkwise/

6. Login dengan:
   - Admin   : admin    / parkwise123
   - Petugas : petugas1 / parkwise123
   - Owner   : owner    / parkwise123</pre>

    <h3>Pengaturan URL (Apache)</h3>
    <p>Jika menggunakan subfolder, pastikan path asset di layout_top.php menggunakan path absolut dari root domain. Untuk subdomain/root, tidak perlu perubahan.</p>

    <?php elseif ($section === 'flow-login'): ?>
    <h2>Flowchart: Proses Login</h2>
    <div class="flowchart-wrap">
      <svg viewBox="0 0 520 680" xmlns="http://www.w3.org/2000/svg" style="max-width:520px;font-family:'DM Sans',sans-serif;">
        <!-- START -->
        <ellipse cx="260" cy="40" rx="70" ry="26" fill="#002147"/>
        <text x="260" y="46" text-anchor="middle" fill="#d2b48c" font-size="13" font-weight="600">MULAI</text>
        <line x1="260" y1="66" x2="260" y2="100" stroke="#002147" stroke-width="2" marker-end="url(#arr)"/>

        <!-- Cek Session -->
        <rect x="150" y="100" width="220" height="46" rx="8" fill="#e8f2fb" stroke="#1a6fa8" stroke-width="1.5"/>
        <text x="260" y="122" text-anchor="middle" fill="#1a6fa8" font-size="12">Cek session aktif</text>
        <text x="260" y="138" text-anchor="middle" fill="#1a6fa8" font-size="11">isLoggedIn()?</text>
        <line x1="260" y1="146" x2="260" y2="175" stroke="#002147" stroke-width="2" marker-end="url(#arr)"/>

        <!-- Decision: sudah login -->
        <polygon points="260,175 370,210 260,245 150,210" fill="#fef9e7" stroke="#d68910" stroke-width="1.5"/>
        <text x="260" y="214" text-anchor="middle" fill="#d68910" font-size="11" font-weight="600">Sudah login?</text>

        <!-- YES branch -->
        <line x1="370" y1="210" x2="450" y2="210" stroke="#2d7a4f" stroke-width="2" marker-end="url(#arr)"/>
        <text x="410" y="205" text-anchor="middle" fill="#2d7a4f" font-size="10">Ya</text>
        <rect x="450" y="184" width="60" height="52" rx="8" fill="#e8f5ee" stroke="#2d7a4f" stroke-width="1.5"/>
        <text x="480" y="213" text-anchor="middle" fill="#2d7a4f" font-size="10">Redirect</text>
        <text x="480" y="227" text-anchor="middle" fill="#2d7a4f" font-size="10">Dashboard</text>

        <!-- NO branch -->
        <line x1="260" y1="245" x2="260" y2="280" stroke="#002147" stroke-width="2" marker-end="url(#arr)"/>
        <text x="270" y="267" fill="#c0392b" font-size="10">Tidak</text>

        <!-- Tampilkan Form -->
        <rect x="150" y="280" width="220" height="40" rx="8" fill="#f8f5f0" stroke="#002147" stroke-width="1.5"/>
        <text x="260" y="305" text-anchor="middle" fill="#002147" font-size="12">Tampilkan form login</text>
        <line x1="260" y1="320" x2="260" y2="350" stroke="#002147" stroke-width="2" marker-end="url(#arr)"/>

        <!-- POST input -->
        <rect x="150" y="350" width="220" height="46" rx="8" fill="#f8f5f0" stroke="#002147" stroke-width="1.5"/>
        <text x="260" y="370" text-anchor="middle" fill="#002147" font-size="12">Input username + password</text>
        <text x="260" y="387" text-anchor="middle" fill="#8a7a6a" font-size="11">[POST form submit]</text>
        <line x1="260" y1="396" x2="260" y2="420" stroke="#002147" stroke-width="2" marker-end="url(#arr)"/>

        <!-- Cari user -->
        <polygon points="260,420 390,450 260,480 130,450" fill="#fef9e7" stroke="#d68910" stroke-width="1.5"/>
        <text x="260" y="447" text-anchor="middle" fill="#d68910" font-size="11">User ditemukan</text>
        <text x="260" y="462" text-anchor="middle" fill="#d68910" font-size="11">& aktif?</text>

        <!-- NO: error -->
        <line x1="130" y1="450" x2="60" y2="450" stroke="#c0392b" stroke-width="2" marker-end="url(#arr)"/>
        <text x="93" y="444" fill="#c0392b" font-size="10">Tidak</text>
        <rect x="10" y="434" width="50" height="32" rx="6" fill="#fdecea" stroke="#c0392b"/>
        <text x="35" y="453" text-anchor="middle" fill="#c0392b" font-size="9">Flash</text>
        <text x="35" y="463" text-anchor="middle" fill="#c0392b" font-size="9">Error</text>

        <!-- YES: verify pw -->
        <line x1="260" y1="480" x2="260" y2="510" stroke="#002147" stroke-width="2" marker-end="url(#arr)"/>
        <text x="270" y="500" fill="#2d7a4f" font-size="10">Ya</text>

        <!-- password_verify -->
        <polygon points="260,510 390,540 260,570 130,540" fill="#fef9e7" stroke="#d68910" stroke-width="1.5"/>
        <text x="260" y="538" text-anchor="middle" fill="#d68910" font-size="11">password_verify</text>
        <text x="260" y="553" text-anchor="middle" fill="#d68910" font-size="11">valid?</text>
        <line x1="260" y1="570" x2="260" y2="600" stroke="#2d7a4f" stroke-width="2" marker-end="url(#arr)"/>
        <text x="270" y="590" fill="#2d7a4f" font-size="10">Ya</text>

        <!-- Set session + log -->
        <rect x="150" y="600" width="220" height="40" rx="8" fill="#e8f5ee" stroke="#2d7a4f" stroke-width="1.5"/>
        <text x="260" y="617" text-anchor="middle" fill="#2d7a4f" font-size="12">Set session · Log aktivitas</text>
        <text x="260" y="632" text-anchor="middle" fill="#2d7a4f" font-size="11">Redirect ke dashboard</text>

        <!-- Arrow marker -->
        <defs>
          <marker id="arr" markerWidth="8" markerHeight="8" refX="6" refY="3" orient="auto">
            <path d="M0,0 L0,6 L8,3 z" fill="#002147"/>
          </marker>
        </defs>
      </svg>
    </div>

    <h3>Pseudocode Login</h3>
    <pre>FUNCTION proses_login(username, password):
  IF isLoggedIn() THEN
    REDIRECT getDashboardUrl()
  END IF

  IF username IS EMPTY OR password IS EMPTY THEN
    SET flash('error', 'Wajib diisi')
    REDIRECT '/login.php'
  END IF

  user = SELECT * FROM tb_user WHERE username=:u LIMIT 1

  IF NOT user THEN
    SET flash('error', 'Username/password salah')
    REDIRECT '/login.php'
  END IF

  IF user.status_aktif != 1 THEN
    SET flash('error', 'Akun nonaktif')
    REDIRECT '/login.php'
  END IF

  IF NOT password_verify(password, user.password) THEN
    SET flash('error', 'Username/password salah')
    REDIRECT '/login.php'
  END IF

  session_regenerate_id(true)
  SESSION['user_id']      = user.id_user
  SESSION['nama_lengkap'] = user.nama_lengkap
  SESSION['role']         = user.role

  CALL sp_log(user.id_user, 'Login berhasil')
  REDIRECT getDashboardUrl()
END FUNCTION</pre>

    <?php elseif ($section === 'flow-masuk'): ?>
    <h2>Flowchart: Proses Transaksi Masuk</h2>

    <h3>Pseudocode Transaksi Masuk</h3>
    <pre>FUNCTION proses_kendaraan_masuk(POST data):
  plat    = strtoupper(clean(POST.plat_nomor))
  jenis   = clean(POST.jenis_kendaraan)
  idTarif = int(POST.id_tarif)
  idArea  = int(POST.id_area) OR null

  // Validasi
  IF plat IS EMPTY OR idTarif < 1 THEN
    SET flash('error', 'Input tidak valid')
    REDIRECT '/masuk.php'
  END IF

  // Cek apakah kendaraan masih aktif parkir
  aktif = SELECT t.id_parkir FROM tb_transaksi t
          JOIN tb_kendaraan k ON t.id_kendaraan=k.id_kendaraan
          WHERE k.plat_nomor=plat AND t.status='masuk' LIMIT 1

  IF aktif THEN
    SET flash('warning', 'Kendaraan masih parkir')
    REDIRECT '/masuk.php'
  END IF

  // Cek/buat kendaraan
  kendaraan = SELECT id_kendaraan FROM tb_kendaraan WHERE plat_nomor=plat LIMIT 1

  IF kendaraan THEN
    idKendaraan = kendaraan.id_kendaraan
    UPDATE tb_kendaraan SET jenis, warna, pemilik WHERE id=idKendaraan
  ELSE
    INSERT INTO tb_kendaraan (plat, jenis, warna, pemilik, id_user)
    idKendaraan = lastInsertId()
  END IF

  // Insert transaksi
  INSERT INTO tb_transaksi
    (id_kendaraan, waktu_masuk=NOW(), id_tarif, status='masuk', id_user, id_area)
  newTrxId = lastInsertId()

  // Update area kapasitas
  IF idArea THEN
    UPDATE tb_area_parkir SET terisi = LEAST(terisi+1, kapasitas) WHERE id_area=idArea
  END IF

  CALL sp_log(uid, 'Kendaraan masuk: ' + plat)
  REDIRECT '/masuk.php?struk=' + newTrxId
END FUNCTION</pre>

    <h3>Pseudocode Transaksi Keluar (via Stored Procedure)</h3>
    <pre>PROCEDURE sp_kendaraan_keluar(p_id_parkir, p_metode, p_id_user, OUT p_biaya, OUT p_durasi):
  // Ambil data transaksi
  SELECT waktu_masuk, tarif_per_jam, id_area
  INTO   v_masuk, v_tarif, v_id_area
  FROM   tb_transaksi JOIN tb_tarif WHERE id_parkir=p_id_parkir AND status='masuk'

  v_keluar = NOW()

  // Hitung durasi: dibulatkan ke atas
  p_durasi = fn_hitung_durasi(v_masuk, v_keluar)
  // CEIL(TIMESTAMPDIFF(MINUTE, masuk, keluar) / 60), minimum 1

  // Hitung biaya
  p_biaya = fn_hitung_biaya(p_durasi, v_tarif)
  // = p_durasi * tarif_per_jam

  // Update transaksi
  UPDATE tb_transaksi
  SET waktu_keluar=v_keluar, durasi_jam=p_durasi,
      biaya_total=p_biaya, metode_bayar=p_metode, status='keluar'
  WHERE id_parkir=p_id_parkir

  // Kurangi kapasitas area
  IF v_id_area IS NOT NULL THEN
    UPDATE tb_area_parkir SET terisi = GREATEST(terisi-1, 0) WHERE id_area=v_id_area
  END IF

  INSERT INTO tb_log_aktivitas VALUES (p_id_user, 'Kendaraan keluar - ID: ' + p_id_parkir)
END PROCEDURE</pre>

    <?php elseif ($section === 'flow-struk'): ?>
    <h2>Flowchart: Cetak Struk</h2>

    <h3>Pseudocode Cetak Struk</h3>
    <pre>FUNCTION cetak_struk(id_parkir, jenis='masuk'|'keluar'):
  // Ambil data transaksi lengkap
  data = SELECT t.*, k.plat_nomor, k.jenis_kendaraan, k.warna, k.pemilik,
                tf.tarif_per_jam, a.nama_area, u.nama_lengkap AS petugas
         FROM tb_transaksi t
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
         JOIN tb_tarif tf    ON t.id_tarif = tf.id_tarif
         LEFT JOIN tb_area_parkir a ON t.id_area = a.id_area
         LEFT JOIN tb_user u ON t.id_user = u.id_user
         WHERE t.id_parkir = id_parkir

  // Render HTML struk
  RENDER: header (ParkWise, tanggal)
          plat nomor, jenis kendaraan, warna, pemilik
          waktu masuk
          IF jenis='keluar' THEN
            waktu keluar, durasi jam, tarif per jam
          END IF
          metode_bayar
          IF metode='qris' THEN
            QR Code (generateQR dengan data TRX|id|QRIS|nominal)
          END IF
          total bayar
          nama petugas

  // Fungsi Print
  FUNCTION printStruk(elId):
    el = getElementById(elId)
    win = window.open('', '_blank', 'width=420,height=600')
    win.document.write('&lt;html&gt;...styles...el.innerHTML...window.print()&lt;/html&gt;')
    win.document.close()
END FUNCTION</pre>

    <h3>Komponen QR Code</h3>
    <pre>// Format data QR:
// Masuk  : "PARKWISE|{id}|MASUK|{plat}"
// Keluar : "PARKWISE|{id}|QRIS|{nominal}"
// Paid   : "PARKWISE|{id}|QRIS|PAID|{biaya}"

FUNCTION generateQR(containerId, data):
  IF window.QRCode NOT EXISTS THEN
    // Load library dari CDN
    script = createElement('script')
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/...'
    script.onload = createQR
    document.head.appendChild(script)
  ELSE
    createQR()
  END IF

  FUNCTION createQR():
    new QRCode(container, {
      text: data,
      width: 120, height: 120,
      colorDark: '#002147',
      colorLight: '#ffffff'
    })
  END FUNCTION
END FUNCTION</pre>

    <?php elseif ($section === 'mod-auth'): ?>
    <h2>Modul Autentikasi</h2>
    <h3>Login (login.php)</h3>
    <table>
      <thead><tr><th>Aspek</th><th>Detail</th></tr></thead>
      <tbody>
        <tr><td>Input</td><td>POST: username (string), password (string)</td></tr>
        <tr><td>Proses</td><td>Sanitasi input → Query SELECT user LIMIT 1 → Cek status_aktif → password_verify → session_regenerate_id → Set $_SESSION → CALL sp_log</td></tr>
        <tr><td>Output</td><td>Redirect ke getDashboardUrl() sesuai role | Flash error jika gagal</td></tr>
        <tr><td>Fungsi</td><td>isLoggedIn(), requireRole(), getDashboardUrl(), logAktivitas()</td></tr>
        <tr><td>Keamanan</td><td>password_hash (BCRYPT), session regeneration, sanitasi input htmlspecialchars</td></tr>
      </tbody>
    </table>

    <h3>Logout (logout.php)</h3>
    <table>
      <thead><tr><th>Aspek</th><th>Detail</th></tr></thead>
      <tbody>
        <tr><td>Input</td><td>Session aktif</td></tr>
        <tr><td>Proses</td><td>logAktivitas('Logout') → $_SESSION = [] → setcookie (hapus) → session_destroy()</td></tr>
        <tr><td>Output</td><td>Redirect ke /login.php</td></tr>
      </tbody>
    </table>

    <h3>Helper Functions (includes/config.php)</h3>
    <table>
      <thead><tr><th>Fungsi</th><th>Input</th><th>Output</th></tr></thead>
      <tbody>
        <tr><td>getDB()</td><td>—</td><td>PDO instance (singleton)</td></tr>
        <tr><td>isLoggedIn()</td><td>—</td><td>bool</td></tr>
        <tr><td>requireLogin()</td><td>—</td><td>void | redirect login</td></tr>
        <tr><td>requireRole(array)</td><td>array roles yang diizinkan</td><td>void | 403</td></tr>
        <tr><td>currentRole()</td><td>—</td><td>string role</td></tr>
        <tr><td>currentUserId()</td><td>—</td><td>int id_user</td></tr>
        <tr><td>logAktivitas(string)</td><td>string aktivitas</td><td>void (CALL sp_log)</td></tr>
        <tr><td>setFlash(type, msg)</td><td>string type, string msg</td><td>void ($_SESSION['flash'])</td></tr>
        <tr><td>getFlash()</td><td>—</td><td>array | null</td></tr>
        <tr><td>formatRupiah(float)</td><td>float nominal</td><td>string "Rp X.XXX"</td></tr>
        <tr><td>clean(string)</td><td>string raw</td><td>string sanitized</td></tr>
        <tr><td>redirect(string)</td><td>string url</td><td>void + exit</td></tr>
      </tbody>
    </table>

    <?php elseif ($section === 'mod-transaksi'): ?>
    <h2>Modul Transaksi</h2>

    <h3>Kendaraan Masuk (modules/petugas/masuk.php)</h3>
    <table>
      <thead><tr><th>Aspek</th><th>Detail</th></tr></thead>
      <tbody>
        <tr><td>Input</td><td>POST: plat_nomor, jenis_kendaraan, warna, pemilik, id_tarif, id_area</td></tr>
        <tr><td>Proses</td><td>(1) Cek kendaraan masih aktif → (2) SELECT/INSERT tb_kendaraan → (3) INSERT tb_transaksi → (4) UPDATE terisi area → (5) logAktivitas</td></tr>
        <tr><td>Output</td><td>Flash sukses + redirect ke ?struk=id_parkir untuk menampilkan struk masuk + QR</td></tr>
        <tr><td>Validasi</td><td>Plat tidak kosong, idTarif > 0, kendaraan tidak sedang aktif parkir</td></tr>
        <tr><td>Array digunakan</td><td>$tarifs (pilihan tarif), $areas (pilihan area)</td></tr>
      </tbody>
    </table>

    <h3>Kendaraan Keluar (modules/petugas/keluar.php)</h3>
    <table>
      <thead><tr><th>Aspek</th><th>Detail</th></tr></thead>
      <tbody>
        <tr><td>Input</td><td>GET: plat (cari) | POST: id_parkir, metode_bayar</td></tr>
        <tr><td>Proses</td><td>CALL sp_kendaraan_keluar(id, metode, uid, @biaya, @durasi) → SELECT @biaya, @durasi → Ambil data struk</td></tr>
        <tr><td>Output</td><td>Struk keluar (HTML + QR jika QRIS) + tombol cetak</td></tr>
        <tr><td>Array digunakan</td><td>$aktifList (shortcut plat aktif), $strukDone (data struk)</td></tr>
        <tr><td>Stored Procedure</td><td>sp_kendaraan_keluar → fn_hitung_durasi + fn_hitung_biaya</td></tr>
      </tbody>
    </table>

    <?php elseif ($section === 'mod-admin'): ?>
    <h2>Modul Admin</h2>

    <h3>Pattern CRUD yang Digunakan</h3>
    <pre>// Semua modul admin mengikuti pattern ini:
1. requireRole(['admin'])
2. Handle POST action (tambah/edit/hapus)
   - Sanitasi input dengan clean()
   - Prepared statement PDO
   - logAktivitas()
   - setFlash() + redirect (PRG pattern)
3. Build query dengan optional WHERE + LIMIT + OFFSET
4. Render HTML table + modal form</pre>

    <h3>Modul User (admin/user.php)</h3>
    <table>
      <thead><tr><th>Action</th><th>Query</th></tr></thead>
      <tbody>
        <tr><td>Tambah</td><td>INSERT tb_user, password di-hash dengan password_hash()</td></tr>
        <tr><td>Edit</td><td>UPDATE tb_user, password hanya di-update jika diisi</td></tr>
        <tr><td>Hapus</td><td>DELETE tb_user, cegah hapus akun sendiri</td></tr>
        <tr><td>List</td><td>SELECT dengan LIKE search + LIMIT/OFFSET pagination</td></tr>
      </tbody>
    </table>

    <?php elseif ($section === 'mod-owner'): ?>
    <h2>Modul Owner</h2>

    <h3>Rekap Transaksi (modules/owner/rekap.php)</h3>
    <table>
      <thead><tr><th>Aspek</th><th>Detail</th></tr></thead>
      <tbody>
        <tr><td>Input</td><td>GET: dari (date), sampai (date), jenis (enum), group (hari/minggu/bulan)</td></tr>
        <tr><td>Proses</td><td>Build WHERE dinamis → 4 query: summary, trend grouped, per jenis, per metode</td></tr>
        <tr><td>Output</td><td>4 stat cards + chart bar+line (Chart.js) + tabel detail + tombol print</td></tr>
        <tr><td>Array</td><td>$trendRows, $perJenis, $perMetode</td></tr>
        <tr><td>Fungsi DB</td><td>DATE_FORMAT, TIMESTAMPDIFF, GROUP BY, COALESCE, BETWEEN</td></tr>
      </tbody>
    </table>

    <h3>Fungsi GROUP BY Dinamis</h3>
    <pre>$groupExpr = match($groupBy) {
    'minggu' => "DATE_FORMAT(t.waktu_keluar, '%Y-W%u')",
    'bulan'  => "DATE_FORMAT(t.waktu_keluar, '%Y-%m')",
    default  => "DATE(t.waktu_keluar)",
};</pre>

    <?php elseif ($section === 'modifikasi'): ?>
    <h2>Cara Modifikasi</h2>

    <h3>Menambah Field Baru ke Transaksi</h3>
    <pre>1. ALTER TABLE tb_transaksi ADD COLUMN nama_kolom TYPE AFTER kolom_lain;
2. Tambah input di masuk.php atau keluar.php
3. Tambah binding parameter di INSERT/UPDATE PDO
4. Tampilkan di struk dan tabel riwayat</pre>

    <h3>Menambah Role Baru</h3>
    <pre>1. ALTER TABLE tb_user MODIFY COLUMN role ENUM('admin','petugas','owner','kasir');
2. Tambah navigasi di $navMap di layout_top.php
3. Buat folder modules/kasir/
4. Tambah case di getDashboardUrl() di config.php</pre>

    <h3>Mengubah Warna Tema</h3>
    <pre>// Edit assets/css/style.css bagian :root
:root {
  --oxford:       #002147;   ← ganti warna utama
  --tan:          #d2b48c;   ← ganti warna aksen
  --oxford-light: #003166;   ← varian terang
  --tan-dark:     #b8955a;   ← varian gelap
}</pre>

    <h3>Menambah Notifikasi Baru</h3>
    <pre>// Edit api/notifications.php
// Tambah query dan push ke array $items:
$custom = $pdo->query("SELECT ... FROM ... WHERE kondisi");
foreach ($custom as $c) {
    $items[] = ['type' => 'info', 'msg' => 'Pesan notifikasi'];
}</pre>

    <h3>Mengubah Tarif Minimum Parkir</h3>
    <pre>// Edit di database.sql atau langsung di MySQL:
// Ubah stored function fn_hitung_durasi:
IF durasi < 1 THEN SET durasi = 1; END IF;
-- Ganti 1 dengan minimum jam yang diinginkan</pre>

    <?php elseif ($section === 'debugging'): ?>
    <h2>Panduan Debugging</h2>

    <h3>Error Umum & Solusi</h3>
    <table>
      <thead><tr><th>Error</th><th>Penyebab</th><th>Solusi</th></tr></thead>
      <tbody>
        <tr><td>Koneksi DB gagal</td><td>Config salah / MySQL tidak jalan</td><td>Cek DB_HOST, DB_USER, DB_PASS di config.php</td></tr>
        <tr><td>White page</td><td>Parse error PHP</td><td>Aktifkan error: <code>ini_set('display_errors',1);</code></td></tr>
        <tr><td>Session tidak persisten</td><td>session_start() terlambat</td><td>Pastikan session_start() di awal config.php</td></tr>
        <tr><td>Redirect loop</td><td>isLoggedIn() tidak terbaca</td><td>Cek apakah config.php di-require sebelum redirect</td></tr>
        <tr><td>QR tidak muncul</td><td>CDN blocked / container null</td><td>Cek console browser, pastikan id container benar</td></tr>
        <tr><td>Stored procedure error</td><td>MySQL mode STRICT</td><td>Jalankan: <code>SET GLOBAL sql_mode='';</code></td></tr>
      </tbody>
    </table>

    <h3>Aktifkan Error Reporting</h3>
    <pre>// Tambah di atas includes/config.php saat development:
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');</pre>

    <h3>Debug PDO Query</h3>
    <pre>// Lihat query yang dijalankan:
$stmt = $pdo->prepare('SELECT * FROM tb_user WHERE username=:u');
$stmt->execute([':u' => $username]);

// Debug:
$stmt->debugDumpParams(); // tampilkan info statement

// Cek row count:
echo $stmt->rowCount(); // jumlah baris affected</pre>

    <h3>Debug Session</h3>
    <pre>// Tambah di halaman manapun sementara:
echo '&lt;pre&gt;';
print_r($_SESSION);
echo '&lt;/pre&gt;';</pre>

    <?php elseif ($section === 'guidelines'): ?>
    <h2>Coding Guidelines & Best Practices</h2>

    <h3>a. Performa Query</h3>
    <pre>// ✅ BENAR: gunakan LIMIT selalu
$pdo->query('SELECT * FROM tb_log_aktivitas ORDER BY id_log DESC LIMIT 10');

// ❌ SALAH: tanpa LIMIT bisa load ribuan baris
$pdo->query('SELECT * FROM tb_log_aktivitas ORDER BY id_log DESC');

// ✅ BENAR: gunakan INDEX (sudah terdefinisi di schema)
// INDEX idx_plat (plat_nomor) → dipakai di WHERE plat_nomor=:p
// INDEX idx_status (status)   → dipakai di WHERE status='masuk'
// INDEX idx_waktu_masuk (waktu_masuk) → dipakai di WHERE DATE(waktu_masuk)=CURDATE()</pre>

    <h3>b. Gunakan Stored Procedure & Function</h3>
    <pre>// ✅ Proses keluar via stored procedure (logika di DB):
$stmt = $pdo->prepare('CALL sp_kendaraan_keluar(:id, :m, :uid, @biaya, @dur)');
$stmt->execute([':id'=>$id, ':m'=>$metode, ':uid'=>$uid]);
$stmt->closeCursor();
$result = $pdo->query('SELECT @biaya AS b, @durasi AS d')->fetch();

// ✅ Function di MySQL:
// fn_hitung_durasi(masuk, keluar) → CEIL(TIMESTAMPDIFF(MINUTE,...)/60)
// fn_hitung_biaya(durasi, tarif)  → durasi * tarif</pre>

    <h3>c. Penggunaan Array</h3>
    <pre>// ✅ Ambil semua tarif ke array, gunakan untuk form & JS
$tarifs = $pdo->query('SELECT * FROM tb_tarif ORDER BY jenis_kendaraan')->fetchAll();

// Pass ke JS sebagai JSON untuk sync form tanpa AJAX:
const tarifData = &lt;?= json_encode(array_column($tarifs, null, 'id_tarif')) ?&gt;;

// ✅ Array untuk navigasi (navMap di layout_top.php):
$navMap = ['admin' => [...items], 'petugas' => [...items], ...];</pre>

    <h3>d. Hindari Looping Tidak Perlu</h3>
    <pre>// ✅ BENAR: agregasi di SQL, bukan PHP loop
SELECT jenis_kendaraan, SUM(biaya_total) AS total
FROM tb_transaksi GROUP BY jenis_kendaraan;

// ❌ SALAH: fetch semua lalu sum di PHP
$rows = $pdo->query('SELECT biaya_total, jenis_kendaraan FROM tb_transaksi')->fetchAll();
$total = 0;
foreach ($rows as $r) { $total += $r['biaya_total']; } // JANGAN LAKUKAN INI

// ✅ BENAR: group dalam sekali query
// Gunakan array_column() untuk transform, bukan loop:
$ids = array_column($tarifs, 'id_tarif');  // extract satu kolom</pre>

    <h3>e. Gunakan LIMIT pada Data Besar</h3>
    <pre>// ✅ Selalu paginate data besar:
$perPage = 20;
$offset  = ($page - 1) * $perPage;

$stmt = $pdo->prepare("SELECT ... LIMIT :lim OFFSET :off");
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset,  PDO::PARAM_INT);

// ✅ Untuk dropdown besar, batasi:
SELECT * FROM tb_kendaraan ORDER BY id_kendaraan DESC LIMIT 100;

// ✅ Untuk laporan tren, batasi periode:
WHERE waktu_keluar >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
LIMIT 90;  // max 90 hari</pre>

    <h3>PRG Pattern (Post-Redirect-Get)</h3>
    <pre>// Semua form POST menggunakan PRG untuk mencegah double submit:
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... proses data ...
    setFlash('success', 'Berhasil!');
    redirect('/modul/halaman.php'); // ← selalu redirect setelah POST
}
// Halaman GET hanya untuk menampilkan data</pre>

    <?php endif; ?>

  </div>
</div>

<?php require_once __DIR__ . '/../includes/layout_bot.php'; ?>
