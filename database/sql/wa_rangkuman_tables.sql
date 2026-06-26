-- =============================================================
-- WA RANGKUMAN - Database Tables
-- Tabel terbaru untuk fitur WA Rangkuman, Target, Log & History
-- =============================================================

-- Tabel Target Harian (input manual via Excel import)
CREATE TABLE IF NOT EXISTS `wa_rangkuman_targets` (
    `Id_Target`       INT AUTO_INCREMENT PRIMARY KEY,
    `Target_Date`     DATE NOT NULL,
    `Category_Group`  VARCHAR(50) NOT NULL COMMENT 'TRANSMISI, SUB ENGINE, LINE A, LINE B, SUB ASSY, MAIN LINE, INSPEKSI, MOCOL',
    `Category_Item`   VARCHAR(50) NOT NULL COMMENT 'SXG3 & SF, Transmisi, Sub Engine, Unit, Mocol, Line B, Sub Assy, Mainline, Inspeksi, Mower, Collector',
    `Target`          INT NOT NULL DEFAULT 0 COMMENT 'Target produksi harian',
    `Created_At`      DATETIME DEFAULT CURRENT_TIMESTAMP,
    `Updated_At`      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `wa_target_unique` (`Target_Date`, `Category_Group`, `Category_Item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Log Aktivitas Import/Export
CREATE TABLE IF NOT EXISTS `wa_rangkuman_logs` (
    `Id_Log`       INT AUTO_INCREMENT PRIMARY KEY,
    `Action_Type`  VARCHAR(20) NOT NULL COMMENT 'IMPORT, EXPORT, UPDATE, DELETE',
    `File_Name`    VARCHAR(255) DEFAULT NULL COMMENT 'Nama file Excel',
    `Total_Rows`   INT NOT NULL DEFAULT 0 COMMENT 'Jumlah baris yang diproses',
    `Month`        VARCHAR(7) DEFAULT NULL COMMENT 'Periode bulan YYYY-MM',
    `Created_By`   INT DEFAULT NULL COMMENT 'ID user yang melakukan aksi',
    `Created_At`   DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_log_month` (`Month`),
    INDEX `idx_log_action` (`Action_Type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel History Harian (snapshot WA Rangkuman per tanggal)
CREATE TABLE IF NOT EXISTS `wa_rangkuman_history` (
    `Id_History`     INT AUTO_INCREMENT PRIMARY KEY,
    `Log_Date`       DATE NOT NULL,
    `Category_Group` VARCHAR(50) NOT NULL,
    `Category_Item`  VARCHAR(50) NOT NULL,
    `Target`         INT NOT NULL DEFAULT 0 COMMENT 'Target (T)',
    `Actual`         INT NOT NULL DEFAULT 0 COMMENT 'Actual (A)',
    `Selisih`        INT NOT NULL DEFAULT 0 COMMENT 'Selisih (S) = A - T',
    `Grand_Total`    INT NOT NULL DEFAULT 0 COMMENT 'Grand Total (GT) dari scan log',
    `Koreksi`        TEXT DEFAULT NULL COMMENT 'Catatan koreksi manual',
    `Created_At`     DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `wa_history_unique` (`Log_Date`, `Category_Group`, `Category_Item`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================
-- VIEWS
-- =============================================================

-- View: Rekap target per bulan
CREATE OR REPLACE VIEW `v_wa_target_monthly` AS
SELECT
    `Category_Group`,
    `Category_Item`,
    DATE_FORMAT(`Target_Date`, '%Y-%m') AS `Month`,
    SUM(`Target`) AS `Total_Target`,
    COUNT(*) AS `Total_Days`,
    AVG(`Target`) AS `Avg_Target`
FROM `wa_rangkuman_targets`
GROUP BY `Category_Group`, `Category_Item`, DATE_FORMAT(`Target_Date`, '%Y-%m')
ORDER BY `Month` DESC, `Category_Group`, `Category_Item`;

-- View: Log ringkasan per bulan
CREATE OR REPLACE VIEW `v_wa_log_summary` AS
SELECT
    DATE_FORMAT(`Created_At`, '%Y-%m') AS `Month`,
    `Action_Type`,
    COUNT(*) AS `Total_Actions`,
    SUM(`Total_Rows`) AS `Total_Rows_Processed`
FROM `wa_rangkuman_logs`
GROUP BY DATE_FORMAT(`Created_At`, '%Y-%m'), `Action_Type`
ORDER BY `Month` DESC;

-- View: History terbaru per tanggal
CREATE OR REPLACE VIEW `v_wa_history_latest` AS
SELECT
    `Log_Date`,
    `Category_Group`,
    `Category_Item`,
    `Target`,
    `Actual`,
    `Selisih`,
    `Grand_Total`,
    `Koreksi`,
    `Created_At`
FROM `wa_rangkuman_history`
ORDER BY `Log_Date` DESC, `Category_Group`, `Category_Item`;

-- =============================================================
-- TRIGGERS (auto timestamp)
-- =============================================================
DELIMITER //

CREATE TRIGGER IF NOT EXISTS `trg_wa_target_insert`
BEFORE INSERT ON `wa_rangkuman_targets`
FOR EACH ROW
BEGIN
    SET NEW.Created_At = IFNULL(NEW.Created_At, NOW());
    SET NEW.Updated_At = IFNULL(NEW.Updated_At, NOW());
END//

CREATE TRIGGER IF NOT EXISTS `trg_wa_target_update`
BEFORE UPDATE ON `wa_rangkuman_targets`
FOR EACH ROW
BEGIN
    SET NEW.Updated_At = NOW();
END//

CREATE TRIGGER IF NOT EXISTS `trg_wa_log_insert`
BEFORE INSERT ON `wa_rangkuman_logs`
FOR EACH ROW
BEGIN
    SET NEW.Created_At = IFNULL(NEW.Created_At, NOW());
END//

CREATE TRIGGER IF NOT EXISTS `trg_wa_history_insert`
BEFORE INSERT ON `wa_rangkuman_history`
FOR EACH ROW
BEGIN
    SET NEW.Created_At = IFNULL(NEW.Created_At, NOW());
END//

DELIMITER ;
