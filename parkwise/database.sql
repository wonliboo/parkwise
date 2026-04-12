-- =========================================================
-- ParkWise - Sistem Manajemen Parkir Cerdas
-- Database Schema v1.0
-- =========================================================

CREATE DATABASE IF NOT EXISTS parkwise CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parkwise;

-- =========================================================
-- TABLE: tb_user
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_user (
    id_user    INT(11) NOT NULL AUTO_INCREMENT,
    nama_lengkap VARCHAR(50) NOT NULL,
    username   VARCHAR(50) NOT NULL UNIQUE,
    password   VARCHAR(100) NOT NULL,
    role       ENUM('admin','petugas','owner') NOT NULL DEFAULT 'petugas',
    status_aktif TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_user),
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLE: tb_area_parkir
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_area_parkir (
    id_area    INT(11) NOT NULL AUTO_INCREMENT,
    nama_area  VARCHAR(50) NOT NULL,
    kapasitas  INT(5) NOT NULL DEFAULT 0,
    terisi     INT(5) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_area),
    INDEX idx_nama_area (nama_area)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLE: tb_tarif
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_tarif (
    id_tarif        INT(11) NOT NULL AUTO_INCREMENT,
    jenis_kendaraan ENUM('motor','mobil','lainnya') NOT NULL,
    tarif_per_jam   DECIMAL(10,0) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_tarif),
    INDEX idx_jenis (jenis_kendaraan)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLE: tb_kendaraan
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_kendaraan (
    id_kendaraan    INT(11) NOT NULL AUTO_INCREMENT,
    plat_nomor      VARCHAR(15) NOT NULL,
    jenis_kendaraan VARCHAR(20) NOT NULL,
    warna           VARCHAR(20),
    pemilik         VARCHAR(100),
    id_user         INT(11),
    PRIMARY KEY (id_kendaraan),
    INDEX idx_plat (plat_nomor),
    CONSTRAINT fk_kendaraan_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLE: tb_transaksi
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_transaksi (
    id_parkir       INT(11) NOT NULL AUTO_INCREMENT,
    id_kendaraan    INT(11) NOT NULL,
    waktu_masuk     DATETIME NOT NULL,
    waktu_keluar    DATETIME,
    id_tarif        INT(11) NOT NULL,
    durasi_jam      INT(5),
    biaya_total     DECIMAL(10,0),
    metode_bayar    ENUM('tunai','qris') DEFAULT 'tunai',
    status          ENUM('masuk','keluar','') NOT NULL DEFAULT 'masuk',
    id_user         INT(11),
    id_area         INT(11),
    PRIMARY KEY (id_parkir),
    INDEX idx_status (status),
    INDEX idx_waktu_masuk (waktu_masuk),
    CONSTRAINT fk_trx_kendaraan FOREIGN KEY (id_kendaraan) REFERENCES tb_kendaraan(id_kendaraan),
    CONSTRAINT fk_trx_tarif     FOREIGN KEY (id_tarif)     REFERENCES tb_tarif(id_tarif),
    CONSTRAINT fk_trx_user      FOREIGN KEY (id_user)      REFERENCES tb_user(id_user) ON DELETE SET NULL,
    CONSTRAINT fk_trx_area      FOREIGN KEY (id_area)      REFERENCES tb_area_parkir(id_area) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- TABLE: tb_log_aktivitas
-- =========================================================
CREATE TABLE IF NOT EXISTS tb_log_aktivitas (
    id_log          INT(11) NOT NULL AUTO_INCREMENT,
    id_user         INT(11),
    aktivitas       VARCHAR(100) NOT NULL,
    waktu_aktivitas DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_log),
    INDEX idx_id_user (id_user),
    INDEX idx_waktu (waktu_aktivitas),
    CONSTRAINT fk_log_user FOREIGN KEY (id_user) REFERENCES tb_user(id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================
-- STORED PROCEDURES & FUNCTIONS
-- =========================================================

DELIMITER $$

-- Function: Hitung durasi jam
CREATE FUNCTION fn_hitung_durasi(p_masuk DATETIME, p_keluar DATETIME)
RETURNS INT(5)
DETERMINISTIC
BEGIN
    DECLARE selisih_menit INT;
    DECLARE durasi INT;
    SET selisih_menit = TIMESTAMPDIFF(MINUTE, p_masuk, p_keluar);
    SET durasi = CEIL(selisih_menit / 60);
    IF durasi < 1 THEN SET durasi = 1; END IF;
    RETURN durasi;
END$$

-- Function: Hitung biaya
CREATE FUNCTION fn_hitung_biaya(p_durasi INT, p_tarif_per_jam DECIMAL(10,0))
RETURNS DECIMAL(10,0)
DETERMINISTIC
BEGIN
    RETURN p_durasi * p_tarif_per_jam;
END$$

-- Procedure: Proses kendaraan keluar
CREATE PROCEDURE sp_kendaraan_keluar(
    IN p_id_parkir  INT,
    IN p_metode     ENUM('tunai','qris'),
    IN p_id_user    INT,
    OUT p_biaya     DECIMAL(10,0),
    OUT p_durasi    INT
)
BEGIN
    DECLARE v_masuk     DATETIME;
    DECLARE v_tarif     DECIMAL(10,0);
    DECLARE v_id_area   INT;
    DECLARE v_keluar    DATETIME DEFAULT NOW();

    SELECT t.waktu_masuk, tf.tarif_per_jam, t.id_area
    INTO   v_masuk, v_tarif, v_id_area
    FROM   tb_transaksi t
    JOIN   tb_tarif tf ON t.id_tarif = tf.id_tarif
    WHERE  t.id_parkir = p_id_parkir AND t.status = 'masuk'
    LIMIT  1;

    SET p_durasi = fn_hitung_durasi(v_masuk, v_keluar);
    SET p_biaya  = fn_hitung_biaya(p_durasi, v_tarif);

    UPDATE tb_transaksi
    SET    waktu_keluar = v_keluar,
           durasi_jam   = p_durasi,
           biaya_total  = p_biaya,
           metode_bayar = p_metode,
           status       = 'keluar'
    WHERE  id_parkir = p_id_parkir;

    IF v_id_area IS NOT NULL THEN
        UPDATE tb_area_parkir
        SET    terisi = GREATEST(terisi - 1, 0)
        WHERE  id_area = v_id_area;
    END IF;

    INSERT INTO tb_log_aktivitas (id_user, aktivitas)
    VALUES (p_id_user, CONCAT('Kendaraan keluar - ID Transaksi: ', p_id_parkir));
END$$

-- Procedure: Log aktivitas
CREATE PROCEDURE sp_log(IN p_id_user INT, IN p_aktivitas VARCHAR(100))
BEGIN
    INSERT INTO tb_log_aktivitas (id_user, aktivitas) VALUES (p_id_user, p_aktivitas);
END$$

DELIMITER ;

-- =========================================================
-- SEED DATA
-- =========================================================

-- Users (password = 'parkwise123' hashed)
INSERT INTO tb_user (nama_lengkap, username, password, role, status_aktif) VALUES
('Administrator', 'admin', 'Adm.26Secure', 'admin', 1),
('Petugas Satu',  'petugas1', 'Ops.01Park', 'petugas', 1),
('Owner Parkir',  'owner',   'Own.26Access', 'owner', 1);

-- Area Parkir
INSERT INTO tb_area_parkir (nama_area, kapasitas, terisi) VALUES
('Zona A - Motor',  50, 12),
('Zona B - Mobil',  30,  5),
('Zona C - VIP',    10,  2);

-- Tarif
INSERT INTO tb_tarif (jenis_kendaraan, tarif_per_jam) VALUES
('motor',    2000),
('mobil',    5000),
('lainnya',  3000);
