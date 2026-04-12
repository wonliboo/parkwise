<?php
/**
 * ParkWise - Konfigurasi Aplikasi
 * Input  : Konstanta konfigurasi
 * Proses : Koneksi database, session, helper functions
 * Output : Objek $pdo siap pakai di seluruh aplikasi
 */

define('APP_NAME',    'ParkWise');
define('APP_VERSION', '1.0.0');
define('APP_TAGLINE', 'Sistem Manajemen Parkir Cerdas');

// --- Base URL otomatis (support subfolder, misal /parkwise/) ---
(function () {
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $appRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $base    = substr($appRoot, strlen($docRoot));
    define('BASE_URL', rtrim($base, '/'));
})();

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'parkwise');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// --- Session ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- PDO Connection ---
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die(json_encode(['error' => 'Koneksi database gagal: ' . $e->getMessage()]));
        }
    }
    return $pdo;
}

// --- Auth Helpers ---
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireRole(array $roles): void {
    requireLogin();
    if (!in_array($_SESSION['role'] ?? '', $roles, true)) {
        // Redirect ke dashboard role sendiri, bukan tampil error mentah
        header('Location: ' . getDashboardUrl() . '?akses=ditolak');
        exit;
    }
}

function currentRole(): string {
    return $_SESSION['role'] ?? '';
}

function currentUserId(): int {
    return (int)($_SESSION['user_id'] ?? 0);
}

function currentUserName(): string {
    return htmlspecialchars($_SESSION['nama_lengkap'] ?? '', ENT_QUOTES);
}

// --- Log Aktivitas ---
function logAktivitas(string $aktivitas): void {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare('CALL sp_log(:uid, :akt)');
        $stmt->execute([':uid' => currentUserId(), ':akt' => $aktivitas]);
    } catch (Exception $e) {
        // silent fail on log
    }
}

// --- Flash Message ---
function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// --- Format Rupiah ---
function formatRupiah(float $nominal): string {
    return 'Rp ' . number_format($nominal, 0, ',', '.');
}

// --- Sanitize Input ---
function clean(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// --- Redirect ---
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

// --- Get Dashboard URL by Role ---
function getDashboardUrl(): string {
    $role = currentRole();
    $map = [
        'admin'   => BASE_URL . '/modules/admin/dashboard.php',
        'petugas' => BASE_URL . '/modules/petugas/dashboard.php',
        'owner'   => BASE_URL . '/modules/owner/dashboard.php',
    ];
    return $map[$role] ?? (BASE_URL . '/login.php');
}

$pdo = getDB();
