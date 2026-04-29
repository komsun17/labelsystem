# Label Print System — Project Guidelines

## Project Overview

PHP Web Application สำหรับพิมพ์ Label จ่าหน้าซอง — Migrate จาก Microsoft Access (LABEL_2010.mdb)
เครื่องพิมพ์: **Ricoh IM C2010** | กระดาษ Label: **A15 (100×150mm)**

## Architecture

```
labelsystem/
├── index.php              ← หน้าหลัก (รายชื่อ + ค้นหา + checkbox เลือก)
├── edit.php               ← เพิ่ม / แก้ไข รายชื่อ (ใช้ทั้ง add และ edit)
├── print_preview.php      ← Preview + Print labels (selected หรือ single)
├── print_single.php       ← Redirect ไป print_preview.php?mode=single
├── api.php                ← AJAX endpoint (toggle is_selected)
├── import_access.php      ← CLI migration script (ใช้ครั้งเดียว)
├── database.sql           ← MySQL schema
├── label_billing.csv      ← ข้อมูล export จาก Access
└── includes/
    └── config.php         ← DB config (PDO) + App constants
```

## Stack & Conventions

- **PHP**: ไม่มี Framework — Plain PHP + PDO
- **Database**: MySQL 8 / MariaDB, charset `utf8mb4_unicode_ci`
- **Frontend**: Bootstrap 5.3 + Bootstrap Icons (CDN)
- **No build tool**: ไม่มี npm / webpack — inline `<style>` และ CDN เท่านั้น
- **DB Connection**: ใช้ `getDB()` จาก `includes/config.php` (singleton PDO)
- **Security**: Prepared statements ทุก query, `htmlspecialchars()` ทุก output

## Database

ตาราง: `label_billing`

| Column | Type | หมายเหตุ |
|---|---|---|
| `id` | INT AUTO_INCREMENT | PK |
| `contact` | VARCHAR(100) | ชื่อผู้ติดต่อ |
| `position` | VARCHAR(255) | ตำแหน่ง |
| `company` | VARCHAR(100) | ชื่อบริษัท |
| `address` | VARCHAR(255) | ที่อยู่ |
| `is_selected` | TINYINT(1) | 0=ไม่เลือก, 1=เลือกพิมพ์ |
| `ems` | VARCHAR(50) | EMS / ลงทะเบียน / พัสดุ |
| `billing_note` | VARCHAR(255) | BILLING NOTE / TAX INVOICE |
| `field3`, `f10` | VARCHAR(255) | สำรอง (จาก Access) |

## Key Patterns

- **Checkbox selection**: ใช้ `is_selected` column เก็บ state ใน DB, sync ด้วย AJAX ผ่าน `api.php`
- **Print layout**: `@media print` + `@page { size: 100mm 150mm; margin: 0; }` ต่อ label 1 ใบ
- **Pagination**: ใช้ `ITEMS_PER_PAGE` constant จาก config.php (default: 20)
- **Search**: LIKE query บน `company`, `contact`, `address`
- **Delete**: GET request `?del=<id>` (known security issue — ใช้ POST form ถ้า refactor)

## Known Bugs (ต้องแก้ไข)

1. ปุ่ม "เพิ่มรายชื่อ" ใน `index.php` ชี้ไป `add.php` (ไม่มีไฟล์นี้) → ควรเป็น `edit.php`
2. `clear_all` handler อยู่หลัง HTML output → ต้องย้ายขึ้นก่อน `<!DOCTYPE html>`
3. ข้อมูลผู้ส่ง (FROM) ใน `print_preview.php` ยังเป็น placeholder

## Build & Run

```bash
# 1. สร้าง database
mysql -u root -p < database.sql

# 2. แก้ไข credentials
# includes/config.php → DB_USER, DB_PASS

# 3. import ข้อมูลจาก Access
php import_access.php

# 4. รันด้วย PHP built-in server (development)
php -S localhost:8080
```
