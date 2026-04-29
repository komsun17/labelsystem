---
name: label-system
description: "Label Print System skill — ใช้เมื่อ: เพิ่มฟีเจอร์, แก้บัค, ปรับ layout การพิมพ์ label, ตั้งค่าข้อมูลผู้ส่ง (FROM), แก้ไขระบบ CRUD รายชื่อ, ปรับ print layout สำหรับ Ricoh IM C2010, แก้ security issues (CSRF/delete), เพิ่ม login/authentication, import ข้อมูลจาก Access CSV. Use for: label printing, print preview, address label, sender info, pagination, search, checkbox selection."
---

# Label Print System — Skill

## โครงสร้าง Project

```
labelsystem/
├── index.php           ← หน้าหลัก (ตาราง + ค้นหา + checkbox)
├── edit.php            ← เพิ่ม/แก้ไข รายชื่อ (id=0 = เพิ่มใหม่)
├── print_preview.php   ← Preview + พิมพ์ label (mode=selected|single)
├── print_single.php    ← Redirect → print_preview.php?mode=single&id=X
├── api.php             ← AJAX: toggle, toggle_many, count
├── import_access.php   ← CLI: import CSV จาก Access (ใช้ครั้งเดียว)
├── database.sql        ← MySQL schema (table: label_billing)
└── includes/config.php ← getDB() PDO singleton + constants
```

## Stack

- Plain PHP + PDO (ไม่มี framework)
- MySQL 8 / MariaDB — charset `utf8mb4_unicode_ci`
- Bootstrap 5.3 + Bootstrap Icons (CDN เท่านั้น)
- ไม่มี npm / build tool — ใช้ inline `<style>` หรือ CDN

## Known Bugs (ต้องแก้ก่อน)

1. **`index.php` บรรทัด `<a href="add.php">`** → ต้องเปลี่ยนเป็น `edit.php`
2. **`clear_all` redirect** → handler อยู่หลัง HTML output แล้ว → ย้ายขึ้นก่อน `<!DOCTYPE html>`
3. **ข้อมูล FROM ใน `print_preview.php`** → ยังเป็น placeholder `บริษัท ของคุณ จำกัด`
4. **Delete ใช้ GET request** → CSRF risk → ควรเปลี่ยนเป็น POST form

## Security Rules

- ทุก query ต้องใช้ Prepared Statements เสมอ
- ทุก output ต้อง `htmlspecialchars()` ก่อนแสดงผล
- Form ที่แก้ไขข้อมูลต้องใช้ POST method
- Delete / destructive action ต้องใช้ POST + CSRF token

## Database Pattern

```php
$db = getDB();  // จาก includes/config.php

// Query แบบ prepared
$stmt = $db->prepare("SELECT * FROM label_billing WHERE id=?");
$stmt->execute([$id]);
$row = $stmt->fetch();

// INSERT
$db->prepare("INSERT INTO label_billing (company,contact,address) VALUES(?,?,?)")
   ->execute([$company, $contact, $address]);
```

## Print Layout Rules

- กระดาษ: **100mm × 150mm** (Label A15)
- ต้องมี `@page { size: 100mm 150mm; margin: 0; }`
- แต่ละ label ต้องมี `page-break-after: always` (ยกเว้น label สุดท้าย)
- Font: TH Sarabun New / Tahoma / sans-serif
- Toolbar (ปุ่มพิมพ์) ต้อง `display: none !important` ใน `@media print`

## API Endpoints (api.php)

```json
// Toggle รายการเดียว
POST /api.php  { "action": "toggle",      "id": 1,          "val": 1 }

// Toggle หลายรายการ
POST /api.php  { "action": "toggle_many", "ids": [1,2,3],   "val": 1 }

// นับจำนวนที่เลือก
POST /api.php  { "action": "count" }
```

## Procedure — เพิ่มฟีเจอร์ใหม่

1. อ่าน `includes/config.php` ก่อนเสมอ เพื่อดู constants และ DB schema
2. ใช้ `getDB()` เพื่อเชื่อมต่อ DB
3. Prepared statements ทุก query
4. `htmlspecialchars()` ทุก echo
5. Bootstrap 5.3 class สำหรับ UI
6. ทดสอบ print layout ด้วย `@media print` preview ในเบราว์เซอร์

## Procedure — แก้ไข Print Layout

1. เปิด `print_preview.php`
2. แก้ไข section `/* ── Label card ── */` สำหรับขนาด/spacing
3. แก้ข้อมูล FROM ในส่วน `<!-- FROM (Sender) -->`
4. ทดสอบด้วย browser print preview (Ctrl+P) ก่อน print จริง
5. ตรวจสอบว่า `@page { size: 100mm 150mm; }` ยังอยู่ครบ

## Procedure — Setup & Run

```bash
# 1. สร้าง DB
mysql -u root -p < database.sql

# 2. แก้ credentials ใน includes/config.php
#    DB_USER, DB_PASS

# 3. Import ข้อมูลจาก Access CSV
php import_access.php

# 4. รันบน local
php -S localhost:8080
```
