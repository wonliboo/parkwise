<?php
require_once __DIR__ . '/includes/config.php';
if (isLoggedIn()) {
    redirect(getDashboardUrl());
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ParkWise — Sistem Manajemen Parkir Cerdas</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<style>
:root {
  --oxford:       #002147;
  --oxford-light: #003166;
  --oxford-dark:  #001530;
  --tan:          #d2b48c;
  --tan-light:    #e8d5bb;
  --tan-dark:     #b8955a;
  --white:        #ffffff;
  --off-white:    #f8f5f0;
  --gray-100:     #f1ede7;
  --gray-200:     #e2d9cf;
  --gray-300:     #c5b9aa;
  --gray-500:     #8a7a6a;
  --gray-700:     #4a3f35;
  --success:      #2d7a4f;
  --danger:       #c0392b;
  --danger-bg:    #fdecea;
  --radius:       12px;
  --radius-sm:    8px;
  --transition:   0.22s cubic-bezier(0.4,0,0.2,1);
}
/* form-control untuk modal */
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--oxford); margin-bottom: 6px; letter-spacing: 0.3px; text-transform: uppercase; }
.form-control { width: 100%; padding: 10px 14px; border: 1.5px solid var(--gray-200); border-radius: var(--radius-sm); font-size: 0.875rem; color: var(--oxford-dark); background: var(--white); transition: border-color 0.2s; font-family: inherit; appearance: none; }
.form-control:focus { outline: none; border-color: var(--tan); box-shadow: 0 0 0 3px rgba(210,180,140,0.2); }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 9px 18px; border-radius: var(--radius-sm); font-size: 0.85rem; font-weight: 600; cursor: pointer; border: 1.5px solid transparent; transition: all 0.2s; text-decoration: none; font-family: inherit; }
.btn svg { width: 16px; height: 16px; }
.btn-primary { background: var(--oxford); color: var(--tan); border-color: var(--oxford); }
.btn-primary:hover { background: var(--oxford-light); }
.btn-block { width: 100%; justify-content: center; }

* { margin: 0; padding: 0; box-sizing: border-box; }
html, body { overflow-x: hidden; width: 100%; }
body { background: var(--off-white); font-family: 'DM Sans', sans-serif; }

/* NAVBAR */
.navbar {
  position: sticky; top: 0; z-index: 100;
  background: rgba(255,255,255,0.95);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid var(--gray-200);
  padding: 0 48px; height: 66px;
  display: flex; align-items: center; justify-content: space-between;
}
.nav-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; }
.nav-brand-icon {
  width: 40px; height: 40px; background: var(--oxford);
  border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--tan);
}
.nav-brand-icon svg { width: 20px; height: 20px; }
.nav-brand-name { font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: var(--oxford); }
.nav-links { display: flex; align-items: center; gap: 4px; }
.nav-links a {
  text-decoration: none; color: var(--gray-700); font-size: 0.875rem;
  font-weight: 500; padding: 8px 16px; border-radius: 8px; transition: all 0.2s;
}
.nav-links a:hover { background: var(--gray-100); color: var(--oxford); }
.btn-masuk {
  display: inline-flex; align-items: center; gap: 7px;
  background: var(--oxford); color: var(--tan);
  padding: 9px 22px; border-radius: 8px; border: none;
  font-size: 0.875rem; font-weight: 600; cursor: pointer;
  transition: all 0.2s; font-family: inherit; text-decoration: none;
}
.btn-masuk:hover { background: var(--oxford-light); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(0,33,71,0.2); }
.btn-masuk svg { width: 15px; height: 15px; }

/* HERO */
.hero {
  background: var(--oxford);
  padding: 80px 48px;
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 48px; align-items: center;
  width: 100%; position: relative; overflow: hidden;
}
.hero::after {
  content: '';
  position: absolute; top: -120px; right: -120px;
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(210,180,140,0.12) 0%, transparent 65%);
  pointer-events: none;
}
.hero-tag {
  display: inline-flex; align-items: center; gap: 6px;
  border: 1px solid rgba(210,180,140,0.3);
  color: var(--tan); padding: 5px 14px; border-radius: 20px;
  font-size: 0.72rem; font-weight: 600; letter-spacing: 1px;
  text-transform: uppercase; margin-bottom: 20px;
}
.hero-tag svg { width: 12px; height: 12px; }
.hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: 3rem; font-weight: 700; color: #fff;
  line-height: 1.18; margin-bottom: 20px;
}
.hero h1 em { color: var(--tan); font-style: italic; }
.hero-desc { color: rgba(255,255,255,0.65); font-size: 1rem; line-height: 1.75; margin-bottom: 32px; }
.hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }
.btn-hero-primary {
  display: inline-flex; align-items: center; gap: 8px;
  background: var(--tan); color: var(--oxford);
  padding: 12px 28px; border-radius: 8px; font-weight: 700;
  font-size: 0.9rem; text-decoration: none; border: 2px solid var(--tan);
  transition: all 0.2s; cursor: pointer; font-family: inherit;
}
.btn-hero-primary:hover { background: var(--tan-dark); border-color: var(--tan-dark); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(210,180,140,0.3); }
.btn-hero-outline {
  display: inline-flex; align-items: center; gap: 8px;
  background: transparent; color: rgba(255,255,255,0.8);
  padding: 12px 24px; border-radius: 8px; font-weight: 500;
  font-size: 0.9rem; text-decoration: none;
  border: 2px solid rgba(255,255,255,0.2); transition: all 0.2s;
}
.btn-hero-outline:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.45); color: #fff; }
.btn-hero-primary svg, .btn-hero-outline svg { width: 16px; height: 16px; }

/* Hero right: mock UI card */
.hero-visual {
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(210,180,140,0.2);
  border-radius: 16px; padding: 28px;
  position: relative; z-index: 1;
}
.hero-visual-title {
  font-size: 0.7rem; color: var(--tan); font-weight: 600;
  letter-spacing: 1px; text-transform: uppercase; margin-bottom: 16px;
  display: flex; align-items: center; gap: 6px;
}
.hero-visual-title::before {
  content: ''; width: 6px; height: 6px;
  background: #4ade80; border-radius: 50%; display: inline-block;
}
.mock-stat-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
.mock-stat {
  background: rgba(255,255,255,0.06); border-radius: 10px;
  padding: 14px 12px; text-align: center;
}
.mock-stat-val { font-family: 'Playfair Display', serif; font-size: 1.5rem; color: var(--tan); font-weight: 700; }
.mock-stat-lbl { font-size: 0.65rem; color: rgba(255,255,255,0.45); margin-top: 2px; }
.mock-row {
  background: rgba(255,255,255,0.05); border-radius: 8px;
  padding: 10px 14px; margin-bottom: 8px;
  display: flex; align-items: center; justify-content: space-between;
}
.mock-plat { font-family: 'Courier New', monospace; font-weight: 700; color: #fff; font-size: 0.85rem; }
.mock-badge {
  font-size: 0.65rem; font-weight: 600; padding: 3px 10px;
  border-radius: 12px;
}
.mock-badge.masuk  { background: rgba(74,222,128,0.15); color: #4ade80; }
.mock-badge.keluar { background: rgba(210,180,140,0.15); color: var(--tan); }
.mock-time { font-size: 0.7rem; color: rgba(255,255,255,0.4); }

/* TENTANG */
.about-section {
  padding: 80px 48px;
  display: grid; grid-template-columns: 1fr 1fr;
  gap: 48px; align-items: start;
  max-width: 1140px; margin: 0 auto; width: 100%;
}
.about-tag {
  display: inline-block; background: rgba(0,33,71,0.08);
  color: var(--oxford); font-size: 0.7rem; font-weight: 700;
  letter-spacing: 1px; text-transform: uppercase;
  padding: 4px 12px; border-radius: 20px; margin-bottom: 14px;
}
.about-section h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2.1rem; color: var(--oxford); margin-bottom: 16px; line-height: 1.3;
}
.about-section p { color: var(--gray-500); line-height: 1.8; font-size: 0.95rem; margin-bottom: 14px; }
.about-points { margin-top: 24px; display: flex; flex-direction: column; gap: 12px; }
.about-point {
  display: flex; align-items: flex-start; gap: 12px;
  background: var(--white); border: 1px solid var(--gray-200);
  border-radius: 10px; padding: 14px 16px;
}
.about-point-icon {
  width: 36px; height: 36px; flex-shrink: 0;
  background: rgba(0,33,71,0.07); border-radius: 8px;
  display: flex; align-items: center; justify-content: center; color: var(--oxford);
}
.about-point-icon svg { width: 17px; height: 17px; }
.about-point-text strong { display: block; font-size: 0.875rem; color: var(--oxford); font-weight: 600; margin-bottom: 2px; }
.about-point-text span { font-size: 0.8rem; color: var(--gray-500); line-height: 1.5; }
.about-img {
  background: var(--oxford); border-radius: 16px; padding: 32px;
  position: relative; overflow: hidden;
}
.about-img::before {
  content: '';
  position: absolute; bottom: -60px; right: -60px;
  width: 200px; height: 200px;
  background: radial-gradient(circle, rgba(210,180,140,0.12) 0%, transparent 65%);
}
.about-img-inner {
  display: flex; flex-direction: column; gap: 12px;
}
.about-step {
  display: flex; align-items: center; gap: 14px;
  background: rgba(255,255,255,0.06); border-radius: 10px;
  padding: 14px 16px;
}
.about-step-num {
  width: 32px; height: 32px; flex-shrink: 0;
  background: var(--tan); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.82rem; color: var(--oxford);
}
.about-step-text strong { display: block; color: #fff; font-size: 0.875rem; }
.about-step-text span { font-size: 0.78rem; color: rgba(255,255,255,0.5); }

/* MASALAH & SOLUSI */
.problem-section {
  background: var(--white); padding: 80px 48px;
  border-top: 1px solid var(--gray-200); border-bottom: 1px solid var(--gray-200);
  width: 100%; overflow: hidden;
}
.section-center { text-align: center; max-width: 600px; margin: 0 auto 48px; }
.section-center h2 { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--oxford); margin-bottom: 12px; }
.section-center p { color: var(--gray-500); font-size: 0.95rem; line-height: 1.7; }
.problem-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 20px; max-width: 900px; margin: 0 auto;
}
.problem-card {
  border-radius: 12px; padding: 28px 24px;
  border: 1px solid var(--gray-200); transition: all 0.22s;
}
.problem-card:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,33,71,0.08); }
.problem-card.before { background: #fef9f0; border-color: #f0d9a8; }
.problem-card.after  { background: #f0f7f2; border-color: #a8d4b4; }
.problem-card-label {
  font-size: 0.68rem; font-weight: 700; letter-spacing: 1px;
  text-transform: uppercase; margin-bottom: 16px;
  display: flex; align-items: center; gap: 6px;
}
.problem-card.before .problem-card-label { color: #b07d20; }
.problem-card.after  .problem-card-label { color: #2d7a4f; }
.problem-list { list-style: none; display: flex; flex-direction: column; gap: 10px; }
.problem-list li {
  display: flex; align-items: flex-start; gap: 10px;
  font-size: 0.85rem; line-height: 1.5;
}
.problem-card.before .problem-list li { color: #7a5a1a; }
.problem-card.after  .problem-list li { color: #1a5c35; }
.problem-list li::before { content: ''; flex-shrink: 0; margin-top: 6px; }
.problem-card.before .problem-list li::before {
  width: 8px; height: 8px; border-radius: 50%; background: #d4a030;
}
.problem-card.after .problem-list li::before {
  width: 8px; height: 8px; border-radius: 2px; background: #2d7a4f;
  transform: rotate(45deg);
}

/* PENGGUNA */
.users-section { padding: 80px 48px; max-width: 1140px; margin: 0 auto; width: 100%; overflow: hidden; }
.users-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.user-card {
  border-radius: 14px; overflow: hidden;
  border: 1px solid var(--gray-200); background: var(--white);
  transition: all 0.22s;
}
.user-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,33,71,0.1); }
.user-card-header { padding: 28px 24px 20px; text-align: center; }
.user-card.admin   .user-card-header { background: var(--oxford); }
.user-card.petugas .user-card-header { background: #1a5c35; }
.user-card.owner   .user-card-header { background: #5c3a1a; }
.user-avatar {
  width: 60px; height: 60px; border-radius: 50%;
  background: rgba(255,255,255,0.15); display: flex;
  align-items: center; justify-content: center;
  margin: 0 auto 12px; color: #fff;
}
.user-avatar svg { width: 26px; height: 26px; }
.user-card-name { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: #fff; font-weight: 600; }
.user-card-body { padding: 20px 24px; }
.user-card-desc { font-size: 0.82rem; color: var(--gray-500); line-height: 1.65; margin-bottom: 16px; }
.user-feature-list { list-style: none; display: flex; flex-direction: column; gap: 8px; }
.user-feature-list li {
  display: flex; align-items: center; gap: 8px;
  font-size: 0.82rem; color: var(--oxford);
}
.user-feature-list li svg { width: 14px; height: 14px; color: var(--tan-dark); flex-shrink: 0; }

/* CTA */
.cta-section {
  background: var(--oxford); padding: 80px 48px;
  text-align: center; position: relative; overflow: hidden;
}
.cta-section::before {
  content: ''; position: absolute; top: -80px; left: 50%;
  transform: translateX(-50%);
  width: 700px; height: 400px;
  background: radial-gradient(ellipse, rgba(210,180,140,0.1) 0%, transparent 65%);
}
.cta-section h2 { font-family: 'Playfair Display', serif; font-size: 2.4rem; color: #fff; margin-bottom: 14px; }
.cta-section h2 em { color: var(--tan); font-style: italic; }
.cta-section p { color: rgba(255,255,255,0.6); font-size: 0.95rem; margin-bottom: 36px; line-height: 1.7; max-width: 480px; margin-left: auto; margin-right: auto; margin-bottom: 36px; }

/* FOOTER */
.footer {
  background: #001030; padding: 32px 48px;
  display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
}
.footer-brand { display: flex; align-items: center; gap: 8px; }
.footer-brand-icon {
  width: 32px; height: 32px; background: rgba(210,180,140,0.15);
  border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--tan);
}
.footer-brand-icon svg { width: 16px; height: 16px; }
.footer-brand-name { font-family: 'Playfair Display', serif; color: var(--tan); font-size: 1rem; }
.footer-copy { font-size: 0.75rem; color: rgba(255,255,255,0.3); }

/* MODAL */
.modal-overlay {
  display: none; position: fixed;
  top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,15,40,0.78);
  z-index: 9999; backdrop-filter: blur(5px);
  align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.open { display: flex; animation: mFadeIn 0.2s ease; }
.modal-box {
  background: var(--white); border-radius: 20px;
  padding: 40px 36px; width: 100%; max-width: 400px;
  box-shadow: 0 24px 64px rgba(0,15,40,0.5);
  animation: mScaleIn 0.25s cubic-bezier(0.34,1.56,0.64,1);
  position: relative;
}
.modal-x {
  position: absolute; top: 16px; right: 16px;
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--gray-100); border: none; cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  color: var(--gray-500); transition: all 0.2s;
}
.modal-x:hover { background: var(--gray-200); color: var(--oxford); }
.modal-x svg { width: 15px; height: 15px; }
.modal-logo { text-align: center; margin-bottom: 28px; }
.modal-logo-icon {
  width: 54px; height: 54px; background: var(--oxford);
  border-radius: 14px; display: flex; align-items: center;
  justify-content: center; margin: 0 auto 12px;
}
.modal-logo-icon svg { width: 26px; height: 26px; color: var(--tan); }
.modal-logo h2 { font-family: 'Playfair Display', serif; font-size: 1.45rem; color: var(--oxford); margin-bottom: 4px; }
.modal-logo p { font-size: 0.78rem; color: var(--gray-500); }
.modal-error {
  background: var(--danger-bg); color: var(--danger);
  border: 1px solid rgba(192,57,43,0.2);
  border-radius: 8px; padding: 10px 14px;
  font-size: 0.82rem; margin-bottom: 16px;
  display: flex; align-items: center; gap: 8px;
}
.modal-error svg { width: 15px; height: 15px; flex-shrink: 0; }

@keyframes mFadeIn  { from { opacity: 0; } to { opacity: 1; } }
@keyframes mScaleIn { from { opacity: 0; transform: scale(0.93); } to { opacity: 1; transform: scale(1); } }

@media (max-width: 1024px) {
  .hero          { grid-template-columns: 1fr; padding: 60px 32px; gap: 40px; }
  .about-section { grid-template-columns: 1fr; padding: 60px 32px; gap: 40px; }
  .users-grid    { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 768px) {
  .hero          { padding: 60px 20px; gap: 32px; }
  .about-section { padding: 60px 20px; gap: 32px; }
  .users-grid    { grid-template-columns: 1fr; }
  .navbar        { padding: 0 16px; }
  .problem-section, .users-section, .cta-section { padding: 60px 20px; }
  .footer        { padding: 24px 20px; flex-direction: column; text-align: center; }
  .nav-links a:not(.btn-masuk) { display: none; }
  .hero h1       { font-size: 2rem; }
  .section-center h2 { font-size: 1.6rem; }
  .about-section h2  { font-size: 1.6rem; }
}
</style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
  <a href="<?= BASE_URL ?>/landing.php" class="nav-brand">
    <div class="nav-brand-icon"><i data-lucide="parking-square"></i></div>
    <span class="nav-brand-name">ParkWise</span>
  </a>
  <div class="nav-links">
    <a href="#tentang">Tentang</a>
    <a href="#pengguna">Pengguna</a>
    <a href="#cara-kerja">Cara Kerja</a>
    <button class="btn-masuk" onclick="openModal()">
      <i data-lucide="log-in"></i> Masuk ke Sistem
    </button>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-left">
    <div class="hero-tag"><i data-lucide="star"></i> Sistem Manajemen Parkir</div>
    <h1>Parkir Lebih Teratur,<br>Laporan Lebih <em>Jelas</em></h1>
    <p class="hero-desc">ParkWise hadir untuk menggantikan pencatatan parkir manual yang rawan kesalahan. Dengan sistem digital yang mudah digunakan, setiap transaksi tercatat rapi dan laporan tersedia kapan saja.</p>
    <div class="hero-actions">
      <button class="btn-hero-primary" onclick="openModal()">
        <i data-lucide="log-in"></i> Masuk ke Sistem
      </button>
      <a href="#tentang" class="btn-hero-outline">
        <i data-lucide="chevron-down"></i> Pelajari Lebih Lanjut
      </a>
    </div>
  </div>
  <div class="hero-right">
    <?php
    // Ambil data real dari database
    try {
      $pdo2 = getDB();

      // Stat hari ini
      $s1 = $pdo2->query("SELECT COUNT(*) FROM tb_transaksi WHERE DATE(waktu_masuk)=CURDATE()")->fetchColumn();
      $s2 = $pdo2->query("SELECT COUNT(*) FROM tb_transaksi WHERE DATE(waktu_keluar)=CURDATE() AND status='keluar'")->fetchColumn();
      $s3 = $pdo2->query("SELECT COUNT(*) FROM tb_transaksi WHERE status='masuk'")->fetchColumn();

      // 4 transaksi terbaru hari ini
      $trxLive = $pdo2->query(
        "SELECT k.plat_nomor, t.status, t.waktu_masuk, t.waktu_keluar
         FROM tb_transaksi t
         JOIN tb_kendaraan k ON t.id_kendaraan = k.id_kendaraan
         ORDER BY t.id_parkir DESC LIMIT 4"
      )->fetchAll();
    } catch(Exception $e) {
      $s1 = 0; $s2 = 0; $s3 = 0; $trxLive = [];
    }
    ?>
    <div class="hero-visual">
      <div class="hero-visual-title"><i data-lucide="radio"></i> Live — Data Parkir Hari Ini</div>
      <div class="mock-stat-row">
        <div class="mock-stat">
          <div class="mock-stat-val"><?= (int)$s1 ?></div>
          <div class="mock-stat-lbl">Masuk hari ini</div>
        </div>
        <div class="mock-stat">
          <div class="mock-stat-val"><?= (int)$s2 ?></div>
          <div class="mock-stat-lbl">Sudah keluar</div>
        </div>
        <div class="mock-stat">
          <div class="mock-stat-val"><?= (int)$s3 ?></div>
          <div class="mock-stat-lbl">Masih parkir</div>
        </div>
      </div>
      <?php if (empty($trxLive)): ?>
      <div class="mock-row" style="justify-content:center;">
        <span style="color:rgba(255,255,255,0.4);font-size:0.8rem;">Belum ada transaksi hari ini</span>
      </div>
      <?php else: foreach($trxLive as $tr): ?>
      <div class="mock-row">
        <span class="mock-plat"><?= htmlspecialchars($tr['plat_nomor']) ?></span>
        <span class="mock-badge <?= $tr['status'] ?>"><?= ucfirst($tr['status']) ?></span>
        <span class="mock-time">
          <?= date('H:i', strtotime($tr['status'] === 'keluar' ? $tr['waktu_keluar'] : $tr['waktu_masuk'])) ?>
        </span>
      </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</section>

<!-- TENTANG -->
<section id="tentang">
<div class="about-section">
  <div class="about-left">
    <div class="about-tag">Tentang ParkWise</div>
    <h2>Apa itu ParkWise?</h2>
    <p>ParkWise adalah aplikasi manajemen parkir berbasis web yang dirancang untuk memudahkan pengelolaan area parkir secara digital, menggantikan sistem pencatatan manual yang tidak efisien.</p>
    <p>Aplikasi ini memungkinkan petugas mencatat kendaraan masuk dan keluar dengan cepat, menghitung biaya secara otomatis, serta mencetak struk pembayaran — semuanya dalam satu sistem yang terintegrasi.</p>
    <div class="about-points">
      <div class="about-point">
        <div class="about-point-icon"><i data-lucide="zap"></i></div>
        <div class="about-point-text">
          <strong>Cepat & Efisien</strong>
          <span>Proses input kendaraan masuk dan keluar hanya butuh beberapa detik, tanpa perlu tulis manual.</span>
        </div>
      </div>
      <div class="about-point">
        <div class="about-point-icon"><i data-lucide="shield"></i></div>
        <div class="about-point-text">
          <strong>Akses Terstruktur</strong>
          <span>Setiap pengguna punya akses sesuai perannya — admin, petugas, atau owner — tidak bisa saling masuk.</span>
        </div>
      </div>
      <div class="about-point">
        <div class="about-point-icon"><i data-lucide="trending-up"></i></div>
        <div class="about-point-text">
          <strong>Laporan Real-Time</strong>
          <span>Owner bisa pantau pendapatan dan rekap transaksi kapan saja, lengkap dengan grafik dan filter waktu.</span>
        </div>
      </div>
    </div>
  </div>
  <div class="about-right">
    <div class="about-img" id="cara-kerja">
      <div style="font-size:0.7rem;color:var(--tan);font-weight:700;letter-spacing:1px;text-transform:uppercase;margin-bottom:20px;">Cara Kerja Sistem</div>
      <div class="about-img-inner">
        <div class="about-step">
          <div class="about-step-num">1</div>
          <div class="about-step-text">
            <strong>Kendaraan Masuk</strong>
            <span>Petugas input plat nomor, pilih jenis & area parkir. Sistem catat waktu masuk otomatis.</span>
          </div>
        </div>
        <div class="about-step">
          <div class="about-step-num">2</div>
          <div class="about-step-text">
            <strong>Hitung Biaya Otomatis</strong>
            <span>Saat kendaraan keluar, sistem hitung durasi & biaya berdasarkan tarif yang sudah diatur admin.</span>
          </div>
        </div>
        <div class="about-step">
          <div class="about-step-num">3</div>
          <div class="about-step-text">
            <strong>Bayar & Cetak Struk</strong>
            <span>Pilih metode bayar (Tunai atau QRIS), konfirmasi, lalu struk bisa langsung dicetak.</span>
          </div>
        </div>
        <div class="about-step">
          <div class="about-step-num">4</div>
          <div class="about-step-text">
            <strong>Laporan untuk Owner</strong>
            <span>Semua transaksi terekam. Owner bisa lihat rekap pendapatan harian, mingguan, atau bulanan.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</section>

<!-- MASALAH & SOLUSI -->
<section class="problem-section">
  <div class="section-center">
    <div class="about-tag">Kenapa ParkWise?</div>
    <h2>Dari Manual ke Digital</h2>
    <p>Pencatatan parkir manual penuh dengan risiko — mulai dari kesalahan hitung, data hilang, hingga laporan yang tidak akurat. ParkWise hadir sebagai solusinya.</p>
  </div>
  <div class="problem-grid">
    <div class="problem-card before">
      <div class="problem-card-label"><i data-lucide="x-circle" style="width:14px;height:14px;"></i> Tanpa ParkWise</div>
      <ul class="problem-list">
        <li>Pencatatan pakai buku tulis, rawan hilang dan rusak</li>
        <li>Hitung biaya manual, sering salah atau lambat</li>
        <li>Tidak ada struk, pelanggan tidak punya bukti bayar</li>
        <li>Laporan pendapatan sulit dibuat, butuh waktu lama</li>
        <li>Tidak tahu berapa slot parkir yang masih kosong</li>
        <li>Tidak ada jejak siapa yang jaga dan kapan</li>
      </ul>
    </div>
    <div class="problem-card after">
      <div class="problem-card-label"><i data-lucide="check-circle" style="width:14px;height:14px;"></i> Dengan ParkWise</div>
      <ul class="problem-list">
        <li>Semua transaksi tersimpan digital, aman dan terstruktur</li>
        <li>Biaya dihitung otomatis berdasarkan tarif yang sudah diatur</li>
        <li>Struk bisa langsung dicetak setiap kali transaksi selesai</li>
        <li>Laporan pendapatan tersedia real-time dengan grafik</li>
        <li>Status kapasitas area parkir terpantau langsung</li>
        <li>Log aktivitas semua pengguna tercatat lengkap</li>
      </ul>
    </div>
  </div>
</section>

<!-- PENGGUNA -->
<section class="users-section" id="pengguna">
  <div class="section-center">
    <div class="about-tag">Pengguna Sistem</div>
    <h2>Dirancang untuk 3 Peran</h2>
    <p>ParkWise membagi akses berdasarkan peran — setiap pengguna hanya melihat dan bisa melakukan hal yang relevan dengan tugasnya.</p>
  </div>
  <div class="users-grid">
    <div class="user-card admin">
      <div class="user-card-header">
        <div class="user-avatar"><i data-lucide="shield-check"></i></div>
        <div class="user-card-name">Admin</div>
      </div>
      <div class="user-card-body">
        <p class="user-card-desc">Admin bertanggung jawab atas konfigurasi dan pengelolaan seluruh data master di dalam sistem.</p>
        <ul class="user-feature-list">
          <li><i data-lucide="check"></i> Kelola akun pengguna (tambah, edit, hapus)</li>
          <li><i data-lucide="check"></i> Atur tarif parkir per jenis kendaraan</li>
          <li><i data-lucide="check"></i> Kelola area dan kapasitas parkir</li>
          <li><i data-lucide="check"></i> Lihat data semua kendaraan terdaftar</li>
          <li><i data-lucide="check"></i> Pantau log aktivitas seluruh pengguna</li>
        </ul>
      </div>
    </div>
    <div class="user-card petugas">
      <div class="user-card-header">
        <div class="user-avatar"><i data-lucide="user-check"></i></div>
        <div class="user-card-name">Petugas</div>
      </div>
      <div class="user-card-body">
        <p class="user-card-desc">Petugas adalah pengguna harian yang langsung menangani transaksi parkir di lapangan.</p>
        <ul class="user-feature-list">
          <li><i data-lucide="check"></i> Input kendaraan masuk dengan cepat</li>
          <li><i data-lucide="check"></i> Proses kendaraan keluar & hitung biaya</li>
          <li><i data-lucide="check"></i> Terima pembayaran tunai atau QRIS</li>
          <li><i data-lucide="check"></i> Cetak struk parkir otomatis</li>
          <li><i data-lucide="check"></i> Lihat riwayat transaksi shift-nya</li>
        </ul>
      </div>
    </div>
    <div class="user-card owner">
      <div class="user-card-header">
        <div class="user-avatar"><i data-lucide="briefcase"></i></div>
        <div class="user-card-name">Owner</div>
      </div>
      <div class="user-card-body">
        <p class="user-card-desc">Owner fokus pada pemantauan bisnis — melihat performa dan pendapatan parkir secara keseluruhan.</p>
        <ul class="user-feature-list">
          <li><i data-lucide="check"></i> Dashboard ringkasan pendapatan</li>
          <li><i data-lucide="check"></i> Rekap transaksi berdasarkan periode</li>
          <li><i data-lucide="check"></i> Filter per hari, minggu, atau bulan</li>
          <li><i data-lucide="check"></i> Lihat perbandingan pendapatan per jenis kendaraan</li>
          <li><i data-lucide="check"></i> Grafik tren pendapatan interaktif</li>
        </ul>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="cta-section">
  <h2>Siap Mengelola Parkir<br>dengan <em>Lebih Baik?</em></h2>
  <p>Masuk ke sistem sekarang dan rasakan kemudahan mengelola parkir secara digital. Tidak perlu instalasi tambahan.</p>
  <button class="btn-hero-primary" onclick="openModal()" style="margin: 0 auto;">
    <i data-lucide="log-in"></i> Masuk ke Sistem
  </button>
</section>

<!-- FOOTER -->
<footer class="footer">
  <div class="footer-brand">
    <div class="footer-brand-icon"><i data-lucide="parking-square"></i></div>
    <span class="footer-brand-name">ParkWise</span>
  </div>
  <span class="footer-copy">Sistem Manajemen Parkir Cerdas &copy; <?= date('Y') ?></span>
</footer>

<!-- MODAL LOGIN -->
<div class="modal-overlay" id="loginOverlay" onclick="handleOverlay(event)">
  <div class="modal-box">
    <button class="modal-x" onclick="closeModal()"><i data-lucide="x"></i></button>
    <div class="modal-logo">
      <div class="modal-logo-icon"><i data-lucide="parking-square"></i></div>
      <h2>Masuk ke ParkWise</h2>
      <p>Gunakan akun yang diberikan admin</p>
    </div>
    <?php if (!empty($_GET['error'])): ?>
    <div class="modal-error">
      <i data-lucide="alert-circle"></i>
      <?= $_GET['error'] == 2 ? 'Akun tidak aktif. Hubungi admin.' : 'Username atau password salah.' ?>
    </div>
    <?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/login.php">
      <div class="form-group">
        <label>Username</label>
        <div style="position:relative;">
          <i data-lucide="user" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--gray-500);pointer-events:none;"></i>
          <input type="text" name="username" class="form-control" style="padding-left:36px;" placeholder="Masukkan username" required>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div style="position:relative;">
          <i data-lucide="lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);width:15px;height:15px;color:var(--gray-500);pointer-events:none;"></i>
          <input type="password" name="password" id="pwInput" class="form-control" style="padding-left:36px;padding-right:40px;" placeholder="Masukkan password" required>
          <button type="button" onclick="togglePw()" style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--gray-500);">
            <i data-lucide="eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-block" style="padding:11px;margin-top:4px;">
        <i data-lucide="log-in"></i> Masuk
      </button>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  lucide.createIcons();

  <?php if (!empty($_GET['error'])): ?>
  openModal();
  <?php endif; ?>

  document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
      e.preventDefault();
      const t = document.querySelector(a.getAttribute('href'));
      if (t) t.scrollIntoView({ behavior: 'smooth' });
    });
  });
});

function openModal() {
  document.getElementById('loginOverlay').classList.add('open');
  document.body.style.overflow = 'hidden';
  setTimeout(() => document.querySelector('#loginOverlay input[name="username"]')?.focus(), 200);
}

function closeModal() {
  document.getElementById('loginOverlay').classList.remove('open');
  document.body.style.overflow = '';
}

function handleOverlay(e) {
  if (e.target === document.getElementById('loginOverlay')) closeModal();
}

function togglePw() {
  const p = document.getElementById('pwInput');
  const i = document.getElementById('eyeIcon');
  p.type = p.type === 'password' ? 'text' : 'password';
  i.setAttribute('data-lucide', p.type === 'password' ? 'eye' : 'eye-off');
  lucide.createIcons();
}

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') closeModal();
});
</script>
</body>
</html>