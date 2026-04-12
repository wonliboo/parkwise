<?php
/**
 * ParkWise - Login
 * Input  : POST username, password
 * Proses : Validasi kredensial, set session, log aktivitas
 * Output : Redirect ke dashboard sesuai role
 *
 * FLOWCHART LOGIN:
 * [START] → Cek session → [Sudah login?] → YES → Redirect dashboard
 *                                         → NO  → Tampilkan form
 * [POST] → Sanitasi input → Cari user di DB
 *        → [User ditemukan & aktif?] → NO  → Flash error, redirect login
 *                                    → YES → Verify password
 *        → [Password valid?] → NO  → Flash error, redirect login
 *                            → YES → Set session, log aktivitas
 *        → Redirect dashboard sesuai role → [END]
 */

require_once __DIR__ . '/includes/config.php';

// Jika sudah login, redirect ke dashboard
if (isLoggedIn()) {
    redirect(getDashboardUrl());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input tidak kosong
    if (empty($username) || empty($password)) {
        redirect(BASE_URL . '/landing.php?error=1');
    } else {
        // Query user — gunakan LIMIT 1 agar efisien
        $stmt = $pdo->prepare(
            'SELECT id_user, nama_lengkap, username, password, role, status_aktif
             FROM tb_user
             WHERE username = :u
             LIMIT 1'
        );
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if (!$user) {
            redirect(BASE_URL . '/landing.php?error=1');
        } elseif ((int)$user['status_aktif'] !== 1) {
            redirect(BASE_URL . '/landing.php?error=2');
        } elseif (!password_verify($password, $user['password'])) {
            redirect(BASE_URL . '/landing.php?error=1');
        } else {
            // Regenerate session ID untuk keamanan
            session_regenerate_id(true);

            // Set session
            $_SESSION['user_id']     = $user['id_user'];
            $_SESSION['nama_lengkap']= $user['nama_lengkap'];
            $_SESSION['username']    = $user['username'];
            $_SESSION['role']        = $user['role'];

            // Log aktivitas login
            logAktivitas('Login berhasil');

            setFlash('success', 'Selamat datang, ' . $user['nama_lengkap'] . '!');
            redirect(getDashboardUrl());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — ParkWise</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="login-page">

<div class="login-card">
  <div class="login-logo">
    <div class="login-logo-icon">
      <i data-lucide="parking-square"></i>
    </div>
    <h1>ParkWise</h1>
    <p>Sistem Manajemen Parkir Cerdas</p>
  </div>

  <?php if ($error): ?>
  <div class="flash flash-error" style="margin-bottom:18px;">
    <i data-lucide="alert-circle"></i>
    <span><?= htmlspecialchars($error) ?></span>
  </div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>/login.php" autocomplete="off">
    <div class="form-group">
      <label for="username">Username</label>
      <div style="position:relative;">
        <i data-lucide="user" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--gray-500);pointer-events:none;"></i>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control"
          style="padding-left:38px;"
          placeholder="Masukkan username"
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
          required autofocus>
      </div>
    </div>

    <div class="form-group">
      <label for="password">Password</label>
      <div style="position:relative;">
        <i data-lucide="lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:16px;height:16px;color:var(--gray-500);pointer-events:none;"></i>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          style="padding-left:38px;padding-right:38px;"
          placeholder="Masukkan password"
          required>
        <button type="button" onclick="togglePass()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-500);">
          <i data-lucide="eye" id="eyeIcon"></i>
        </button>
      </div>
    </div>

    <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px;">
      <i data-lucide="log-in"></i>
      Masuk
    </button>
  </form>

  <p class="text-center text-muted" style="margin-top:20px;font-size:0.72rem;">
    Default password: <code>parkwise123</code><br>
    Ganti password setelah login pertama.
  </p>
</div>

<script>
lucide.createIcons();
function togglePass() {
  const p = document.getElementById('password');
  const e = document.getElementById('eyeIcon');
  p.type = p.type === 'password' ? 'text' : 'password';
  e.setAttribute('data-lucide', p.type === 'password' ? 'eye' : 'eye-off');
  lucide.createIcons();
}
</script>
</body>
</html>
