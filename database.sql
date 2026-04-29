-- ============================================
-- Label Print System - Database Schema
-- Migrated from Microsoft Access (LABEL_2010.mdb)
-- ============================================

CREATE DATABASE IF NOT EXISTS label_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE label_system;

CREATE TABLE IF NOT EXISTS label_billing (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    contact     VARCHAR(100)  DEFAULT NULL COMMENT 'ชื่อผู้ติดต่อ',
    position    VARCHAR(255)  DEFAULT NULL COMMENT 'ตำแหน่ง',
    company     VARCHAR(100)  DEFAULT NULL COMMENT 'ชื่อบริษัท',
    address     VARCHAR(255)  DEFAULT NULL COMMENT 'ที่อยู่',
    is_selected TINYINT(1)    DEFAULT 0   COMMENT 'เลือกพิมพ์ (0=ไม่เลือก, 1=เลือก)',
    ems         VARCHAR(50)   DEFAULT NULL COMMENT 'ประเภทจัดส่ง (EMS/ฯลฯ)',
    billing_note VARCHAR(255) DEFAULT NULL COMMENT 'หมายเหตุ (Billing Note / Tax Invoice)',
    field3      VARCHAR(255)  DEFAULT NULL,
    f10         VARCHAR(255)  DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_company (company),
    INDEX idx_ems (ems),
    INDEX idx_selected (is_selected),
    FULLTEXT INDEX ft_search (company, contact, address)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Import data from Access export (CSV)
-- Run after creating table:
-- LOAD DATA LOCAL INFILE 'label_billing.csv'
-- INTO TABLE label_billing
-- FIELDS TERMINATED BY ','
-- OPTIONALLY ENCLOSED BY '"'
-- LINES TERMINATED BY '\n'
-- IGNORE 1 ROWS
-- (id, contact, position, company, address, is_selected, ems, billing_note, field3, f10);
-- ============================================
