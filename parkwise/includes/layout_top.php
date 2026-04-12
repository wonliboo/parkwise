<?php
/**
 * Layout Top - Header & Sidebar
 * Input  : $pageTitle (string), $activePage (string)
 * Output : HTML head + sidebar + topbar
 */
requireLogin();
$flash = getFlash();
$role  = currentRole();

// Notifikasi jika akses ditolak
if (!empty($_GET["akses"]) && $_GET["akses"] === "ditolak" && !$flash) {
    $flash = ["type" => "error", "msg" => "Akses ditolak. Halaman tersebut tidak tersedia untuk role Anda."];
}

// Navigasi berdasarkan role
$navMap = [
    'admin' => [
        ['icon' => 'grid',       'label' => 'Dashboard',      'href' => BASE_URL . '/modules/admin/dashboard.php',  'key' => 'dashboard'],
        ['icon' => 'users',      'label' => 'Kelola User',     'href' => BASE_URL . '/modules/admin/user.php',       'key' => 'user'],
        ['icon' => 'tag',        'label' => 'Tarif Parkir',    'href' => BASE_URL . '/modules/admin/tarif.php',      'key' => 'tarif'],
        ['icon' => 'map-pin',    'label' => 'Area Parkir',     'href' => BASE_URL . '/modules/admin/area.php',       'key' => 'area'],
        ['icon' => 'truck',      'label' => 'Data Kendaraan',  'href' => BASE_URL . '/modules/admin/kendaraan.php',  'key' => 'kendaraan'],
        ['icon' => 'activity',   'label' => 'Log Aktivitas',   'href' => BASE_URL . '/modules/admin/log.php',        'key' => 'log'],
        ['icon' => 'book-open',  'label' => 'Dokumentasi',     'href' => BASE_URL . '/docs/index.php',               'key' => 'docs'],
    ],
    'petugas' => [
        ['icon' => 'grid',       'label' => 'Dashboard',       'href' => BASE_URL . '/modules/petugas/dashboard.php','key' => 'dashboard'],
        ['icon' => 'log-in',     'label' => 'Kendaraan Masuk', 'href' => BASE_URL . '/modules/petugas/masuk.php',   'key' => 'masuk'],
        ['icon' => 'log-out',    'label' => 'Kendaraan Keluar','href' => BASE_URL . '/modules/petugas/keluar.php',  'key' => 'keluar'],
        ['icon' => 'file-text',  'label' => 'Riwayat Transaksi','href' => BASE_URL . '/modules/petugas/riwayat.php','key' => 'riwayat'],
        ['icon' => 'book-open',  'label' => 'Dokumentasi',     'href' => BASE_URL . '/docs/index.php',              'key' => 'docs'],
    ],
    'owner' => [
        ['icon' => 'grid',       'label' => 'Dashboard',       'href' => BASE_URL . '/modules/owner/dashboard.php', 'key' => 'dashboard'],
        ['icon' => 'bar-chart-2','label' => 'Rekap Transaksi', 'href' => BASE_URL . '/modules/owner/rekap.php',     'key' => 'rekap'],
        ['icon' => 'book-open',  'label' => 'Dokumentasi',     'href' => BASE_URL . '/docs/index.php',              'key' => 'docs'],
    ],
];

$navItems = $navMap[$role] ?? [];
$activePage = $activePage ?? 'dashboard';
$pageTitle  = $pageTitle  ?? APP_NAME;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> — <?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body>

<!-- Overlay mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- ===== SIDEBAR ===== -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon">
      <i data-lucide="parking-square"></i>
    </div>
    <div class="brand-text">
      <span class="brand-name"><?= APP_NAME ?></span>
      <span class="brand-tag"><?= APP_TAGLINE ?></span>
    </div>
  </div>

  <div class="sidebar-role-badge">
    <span class="role-pill role-<?= $role ?>"><?= strtoupper($role) ?></span>
    <span class="user-greeting">Halo, <?= currentUserName() ?></span>
  </div>

  <nav class="sidebar-nav">
    <?php foreach ($navItems as $item): ?>
    <a href="<?= $item['href'] ?>"
       class="nav-item <?= ($activePage === $item['key']) ? 'active' : '' ?>">
      <i data-lucide="<?= $item['icon'] ?>"></i>
      <span><?= $item['label'] ?></span>
      <?php if ($activePage === $item['key']): ?><div class="nav-active-bar"></div><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= BASE_URL ?>/logout.php" class="nav-item nav-logout" onclick="return confirm('Yakin ingin logout?')">
      <i data-lucide="log-out"></i>
      <span>Logout</span>
    </a>
  </div>
</aside>

<!-- ===== MAIN WRAPPER ===== -->
<div class="main-wrapper">

  <!-- Topbar -->
  <header class="topbar">
    <button class="topbar-toggle" onclick="toggleSidebar()">
      <i data-lucide="menu"></i>
    </button>
    <div class="topbar-title">
      <h1><?= htmlspecialchars($pageTitle) ?></h1>
    </div>
    <div class="topbar-right">
      <div class="topbar-time" id="topbarTime"></div>
      <div class="topbar-notif" id="topbarNotif">
        <i data-lucide="bell"></i>
        <span class="notif-dot" id="notifDot" style="display:none"></span>
      </div>
    </div>
  </header>

  <!-- Flash Message -->
  <?php if ($flash): ?>
  <div class="flash flash-<?= $flash['type'] ?>" id="flashMsg">
    <i data-lucide="<?= $flash['type'] === 'success' ? 'check-circle' : 'alert-circle' ?>"></i>
    <span><?= htmlspecialchars($flash['msg']) ?></span>
    <button onclick="this.parentElement.remove()"><i data-lucide="x"></i></button>
  </div>
  <?php endif; ?>

  <!-- Content Area -->
  <main class="content-area">
