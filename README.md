# Label Print System - PHP Web Application
## Migrated from Microsoft Access (LABEL_2010.mdb)
### Ricoh IM C2010 | Label A15 (100×150mm)

---

## โครงสร้างไฟล์

```
label_system/
├── index.php              ← หน้าหลัก (รายชื่อ + ค้นหา + เลือก)
├── edit.php               ← เพิ่ม / แก้ไข รายชื่อ
├── print_preview.php      ← Preview และ Print Labels
├── print_single.php       ← Print Label รายการเดียว
├── api.php                ← AJAX Endpoint (checkbox toggle)
├── import_access.php      ← Script import ข้อมูลจาก Access (ครั้งเดียว)
├── database.sql           ← SQL สร้าง Database
├── label_billing.csv      ← ข้อมูล Export จาก Access (วางไฟล์ตรงนี้)
└── includes/
    └── config.php         ← Database Configuration
```

---

## ขั้นตอนติดตั้ง

### 1. สร้าง Database

```sql
mysql -u root -p < database.sql
```

### 2. แก้ไข config.php

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('DB_NAME', 'label_system');
```

### 3. Export ข้อมูลจาก Access

```bash
# ติดตั้ง mdbtools (Linux)
sudo apt install mdbtools

# Export ข้อมูล
mdb-export LABEL_2010.mdb 'LABEL - BILLING' > label_billing.csv

# วางไฟล์ label_billing.csv ใน folder label_system/
```

### 4. Import ข้อมูลเข้า MySQL

```bash
php import_access.php
```

### 5. แก้ไขข้อมูล Sender ใน print_preview.php

เปิดไฟล์ `print_preview.php` หาบรรทัดนี้และแก้ข้อมูลบริษัทของคุณ:

```php
<!-- FROM (Sender) — แก้ไขชื่อ/ที่อยู่บริษัทคุณตรงนี้ -->
<div class="label-from">
  <div class="from-title">จาก / FROM:</div>
  <div>บริษัท ของคุณ จำกัด</div>
  ...
```

---

## การตั้งค่า Printer (Ricoh IM C2010)

### ตั้งค่า Paper Size ใน Browser

เมื่อกด **พิมพ์ (Ctrl+P)**:

| Setting        | ค่าที่ตั้ง         |
|----------------|-------------------|
| Printer        | Ricoh IM C2010    |
| Paper Size     | Custom: 100×150mm |
| Margins        | None (ไม่มี)       |
| Scale          | 100%              |
| Headers/Footers| ปิด (Off)         |

### สร้าง Custom Paper Size ใน Windows

1. Control Panel → Devices and Printers
2. คลิกขวา Ricoh IM C2010 → Printing Preferences
3. Tab: Paper → Custom Size
4. กรอก: Width = 100mm, Height = 150mm
5. ตั้งชื่อ: "Label A15"
6. Save

---

## Features

- ✅ ค้นหา บริษัท / ชื่อ / ที่อยู่
- ✅ เลือกหลายรายการแล้วพิมพ์พร้อมกัน
- ✅ Preview ก่อนพิมพ์ (Ricoh IM C2010)
- ✅ พิมพ์ Label รายการเดียว
- ✅ เพิ่ม / แก้ไข / ลบ รายชื่อ
- ✅ รองรับ EMS / TAX INVOICE / BILLING NOTE
- ✅ Label ขนาด 100×150mm (A15)
- ✅ รองรับ TH/EN ทั้งสองภาษา

---

## Requirements

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- Web Server: Apache / Nginx
- Browser: Chrome / Edge (แนะนำสำหรับ Print)
