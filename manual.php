<?php
require_once 'includes/config.php';
auth_check();
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>คู่มือการใช้งาน — <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
  .manual-hero {
    background: linear-gradient(120deg, #4f46e5 0%, #7c3aed 100%);
    padding: 2.5rem 2rem;
    border-radius: 14px;
    color: #fff;
    margin-bottom: 2rem;
  }
  .manual-hero h1 { font-size: 1.5rem; font-weight: 700; margin: 0; }
  .manual-hero p  { margin: .4rem 0 0; color: rgba(255,255,255,.7); font-size: .88rem; }

  .toc-card {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.25rem 1.5rem;
    position: sticky;
    top: 76px;
  }
  .toc-title {
    font-size: .68rem; font-weight: 700;
    text-transform: uppercase; letter-spacing: .8px;
    color: var(--muted); margin-bottom: .85rem;
  }
  .toc-card a {
    display: flex; align-items: center; gap: .5rem;
    font-size: .82rem; color: var(--muted);
    text-decoration: none; padding: .35rem 0;
    border-bottom: 1px solid #f1f5f9;
    transition: color .12s;
  }
  .toc-card a:last-child  { border-bottom: none; }
  .toc-card a:hover       { color: var(--primary); }
  .toc-card a i           { width: 16px; color: var(--primary); }

  /* Section */
  .manual-section {
    background: #fff;
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    margin-bottom: 1.5rem;
    scroll-margin-top: 80px;
  }
  .section-header {
    background: linear-gradient(120deg, #4f46e5 0%, #7c3aed 100%);
    padding: 1rem 1.5rem;
    display: flex; align-items: center; gap: .7rem;
  }
  .section-header i  { font-size: 1.1rem; color: rgba(255,255,255,.85); }
  .section-header h2 { margin: 0; font-size: .95rem; font-weight: 700; color: #fff; }
  .section-body      { padding: 1.5rem; }

  /* Step */
  .step {
    display: flex; gap: 1rem;
    margin-bottom: 1.25rem;
    padding-bottom: 1.25rem;
    border-bottom: 1px solid #f1f5f9;
  }
  .step:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
  .step-num {
    width: 28px; height: 28px; flex-shrink: 0;
    background: var(--primary); color: #fff;
    border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: .75rem; font-weight: 700;
    margin-top: 1px;
  }
  .step-content h3 {
    font-size: .88rem; font-weight: 700;
    color: var(--text); margin: 0 0 .3rem;
  }
  .step-content p, .step-content ul, .step-content ol {
    font-size: .84rem; color: #475569; margin: 0; line-height: 1.7;
  }
  .step-content ul, .step-content ol { padding-left: 1.2rem; }

  /* Tag/Badge inline */
  .tag {
    display: inline-flex; align-items: center; gap: .25rem;
    font-size: .72rem; font-weight: 600;
    padding: .15rem .55rem; border-radius: 5px;
    vertical-align: middle;
  }
  .tag-primary { background: var(--primary-light); color: var(--primary); }
  .tag-success { background: #f0fdf4; color: #15803d; }
  .tag-danger  { background: #fee2e2; color: #dc2626; }
  .tag-warning { background: #fffbeb; color: #d97706; }
  .tag-muted   { background: #f1f5f9; color: var(--muted); }

  /* Info/Note box */
  .note-box {
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-left: 4px solid #3b82f6;
    border-radius: 8px;
    padding: .85rem 1rem;
    font-size: .82rem;
    color: #1e40af;
    margin-top: .85rem;
  }
  .note-box i { margin-right: .35rem; }

  .warn-box {
    background: #fffbeb;
    border: 1px solid #fde68a;
    border-left: 4px solid #f59e0b;
    border-radius: 8px;
    padding: .85rem 1rem;
    font-size: .82rem;
    color: #92400e;
    margin-top: .85rem;
  }

  /* Print settings table */
  .print-table { font-size: .82rem; }
  .print-table th {
    background: #f8fafc; font-size: .72rem;
    font-weight: 700; text-transform: uppercase;
    letter-spacing: .5px; color: var(--muted);
  }

  /* Field reference */
  .field-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .75rem;
    margin-top: .5rem;
  }
  .field-item {
    background: #fafbfd;
    border: 1px solid var(--border);
    border-radius: 9px;
    padding: .75rem 1rem;
  }
  .field-name { font-size: .75rem; font-weight: 700; color: var(--text); margin-bottom: .2rem; }
  .field-desc { font-size: .78rem; color: var(--muted); }

  @media (max-width: 767px) {
    .field-grid { grid-template-columns: 1fr; }
    .toc-card   { display: none; }
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="app-navbar">
  <a href="index.php" class="brand">
    <span class="brand-icon"><i class="bi bi-arrow-left"></i></span>
    <div>
      <div><?= APP_NAME ?></div>
      <div class="subtitle">คู่มือการใช้งาน</div>
    </div>
  </a>
  <div class="d-flex align-items-center gap-2">
    <a href="logout.php" class="btn-nav btn-nav-clear" title="ออกจากระบบ"
       onclick="return confirm('ออกจากระบบ?')">
      <i class="bi bi-box-arrow-right"></i>
    </a>
  </div>
</nav>

<div class="container-fluid py-4" style="max-width:1100px">

  <!-- Hero -->
  <div class="manual-hero">
    <div class="d-flex align-items-center gap-3">
      <div style="width:52px;height:52px;background:rgba(255,255,255,.15);border-radius:14px;
                  display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0">
        <i class="bi bi-book-fill"></i>
      </div>
      <div>
        <h1><i class="bi bi-book me-2" style="font-size:1.2rem"></i>คู่มือการใช้งาน</h1>
        <p><?= APP_NAME ?> &bull; Ricoh IM C2010 &bull; Elephant No.A15 (50×80 mm)</p>
      </div>
    </div>
  </div>

  <div class="row g-4">

    <!-- TOC Sidebar -->
    <div class="col-md-3">
      <div class="toc-card">
        <div class="toc-title">สารบัญ</div>
        <a href="#sec-login"><i class="bi bi-lock"></i>การเข้าสู่ระบบ</a>
        <a href="#sec-main"><i class="bi bi-grid"></i>หน้าหลัก</a>
        <a href="#sec-search"><i class="bi bi-search"></i>การค้นหา</a>
        <a href="#sec-select"><i class="bi bi-check2-square"></i>การเลือกรายการ</a>
        <a href="#sec-add"><i class="bi bi-plus-circle"></i>เพิ่มรายชื่อ</a>
        <a href="#sec-edit"><i class="bi bi-pencil"></i>แก้ไขรายชื่อ</a>
        <a href="#sec-delete"><i class="bi bi-trash"></i>ลบรายชื่อ</a>
        <a href="#sec-print"><i class="bi bi-printer"></i>การพิมพ์ Label</a>
        <a href="#sec-settings"><i class="bi bi-gear"></i>ตั้งค่าการพิมพ์</a>
        <a href="#sec-logout"><i class="bi bi-box-arrow-right"></i>ออกจากระบบ</a>
      </div>
    </div>

    <!-- Content -->
    <div class="col-md-9">

      <!-- 1. Login -->
      <div class="manual-section" id="sec-login">
        <div class="section-header">
          <i class="bi bi-lock-fill"></i>
          <h2>1. การเข้าสู่ระบบ</h2>
        </div>
        <div class="section-body">
          <div class="step">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>เปิดเบราว์เซอร์และไปที่ URL ของระบบ</h3>
              <p>แนะนำให้ใช้ <strong>Google Chrome</strong> หรือ <strong>Microsoft Edge</strong> เพื่อการพิมพ์ที่แม่นยำที่สุด</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div class="step-content">
              <h3>กรอกชื่อผู้ใช้และรหัสผ่าน</h3>
              <p>ใส่ข้อมูล Login ที่ได้รับจากผู้ดูแลระบบ แล้วกดปุ่ม <span class="tag tag-primary"><i class="bi bi-box-arrow-in-right"></i> เข้าสู่ระบบ</span></p>
              <div class="note-box">
                <i class="bi bi-info-circle-fill"></i>
                ถ้าลืมรหัสผ่าน ให้ติดต่อผู้ดูแลระบบเพื่อแก้ไขไฟล์ <code>.env</code>
              </div>
            </div>
          </div>
          <div class="step">
            <div class="step-num">3</div>
            <div class="step-content">
              <h3>เข้าสู่หน้าหลักอัตโนมัติ</h3>
              <p>เมื่อ Login สำเร็จ ระบบจะพาไปยังหน้าหลักทันที</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 2. Main Page -->
      <div class="manual-section" id="sec-main">
        <div class="section-header">
          <i class="bi bi-grid-fill"></i>
          <h2>2. หน้าหลัก</h2>
        </div>
        <div class="section-body">
          <p class="mb-3" style="font-size:.84rem;color:#475569">หน้าหลักแสดงรายชื่อทั้งหมดในระบบ พร้อมสถิติสรุป และเครื่องมือจัดการรายชื่อ</p>

          <h3 style="font-size:.88rem;font-weight:700;margin-bottom:.75rem">แถบสถิติ (Stat Cards)</h3>
          <div class="row g-2 mb-4">
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3 text-center" style="background:#eff6ff;border:1px solid #bfdbfe">
                <i class="bi bi-people-fill text-primary mb-1" style="font-size:1.2rem"></i>
                <div style="font-size:.72rem;font-weight:700;color:#1e40af">รายชื่อทั้งหมด</div>
                <div style="font-size:.7rem;color:#3b82f6">จำนวน record ทั้งหมดในระบบ</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3 text-center" style="background:#fffbeb;border:1px solid #fde68a">
                <i class="bi bi-check2-square text-warning mb-1" style="font-size:1.2rem"></i>
                <div style="font-size:.72rem;font-weight:700;color:#92400e">เลือกพิมพ์</div>
                <div style="font-size:.7rem;color:#d97706">รายการที่ติ๊กเลือกไว้</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3 text-center" style="background:#f0fdfa;border:1px solid #99f6e4">
                <i class="bi bi-send-fill mb-1" style="font-size:1.2rem;color:#0d9488"></i>
                <div style="font-size:.72rem;font-weight:700;color:#134e4a">มีประเภทส่ง</div>
                <div style="font-size:.7rem;color:#0d9488">รายการที่ระบุ EMS/ลงทะเบียน ฯลฯ</div>
              </div>
            </div>
            <div class="col-6 col-md-3">
              <div class="p-3 rounded-3 text-center" style="background:#f5f3ff;border:1px solid #ddd6fe">
                <i class="bi bi-files mb-1" style="font-size:1.2rem;color:#7c3aed"></i>
                <div style="font-size:.72rem;font-weight:700;color:#4c1d95">หน้าทั้งหมด</div>
                <div style="font-size:.7rem;color:#7c3aed">จำนวนหน้า (20 รายการ/หน้า)</div>
              </div>
            </div>
          </div>

          <h3 style="font-size:.88rem;font-weight:700;margin-bottom:.75rem">ปุ่มใน Toolbar</h3>
          <div class="d-flex flex-column gap-2">
            <div class="step" style="margin-bottom:.75rem;padding-bottom:.75rem">
              <div class="flex-shrink-0"><span class="tag tag-primary"><i class="bi bi-check-all"></i> เลือกหน้านี้</span></div>
              <div class="step-content" style="margin-left:.5rem"><p>ติ๊กเลือกรายการทุกรายการในหน้าปัจจุบัน</p></div>
            </div>
            <div class="step" style="margin-bottom:.75rem;padding-bottom:.75rem">
              <div class="flex-shrink-0"><span class="tag tag-muted"><i class="bi bi-x-square"></i> ยกเลิก</span></div>
              <div class="step-content" style="margin-left:.5rem"><p>ยกเลิกการเลือกทุกรายการในหน้าปัจจุบัน</p></div>
            </div>
            <div class="step" style="margin-bottom:0;padding-bottom:0;border:none">
              <div class="flex-shrink-0"><span class="tag tag-success"><i class="bi bi-plus-lg"></i> เพิ่มรายชื่อ</span></div>
              <div class="step-content" style="margin-left:.5rem"><p>เปิด Modal เพื่อเพิ่มรายชื่อใหม่</p></div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3. Search -->
      <div class="manual-section" id="sec-search">
        <div class="section-header">
          <i class="bi bi-search"></i>
          <h2>3. การค้นหา</h2>
        </div>
        <div class="section-body">
          <div class="step">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>พิมพ์คำค้นหาในช่อง Search</h3>
              <p>ค้นหาได้จาก <strong>ชื่อบริษัท</strong>, <strong>ชื่อผู้ติดต่อ</strong>, หรือ <strong>ที่อยู่</strong></p>
              <div class="note-box mt-2">
                <i class="bi bi-lightning-fill"></i>
                <strong>คำค้นหาตั้งแต่ 3 ตัวอักษรขึ้นไป</strong> ใช้ Full-text Search (เร็วกว่า) — น้อยกว่า 3 ตัวใช้การค้นหาแบบปกติ
              </div>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div class="step-content">
              <h3>กดปุ่ม Search หรือ Enter</h3>
              <p>ผลลัพธ์จะแสดงเฉพาะรายการที่ตรงกับคำค้นหา พร้อมอัพเดต stat cards</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">3</div>
            <div class="step-content">
              <h3>ล้างการค้นหา</h3>
              <p>กดปุ่ม <span class="tag tag-muted"><i class="bi bi-x-lg"></i></span> ข้างช่อง Search เพื่อกลับไปแสดงรายการทั้งหมด</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 4. Select -->
      <div class="manual-section" id="sec-select">
        <div class="section-header">
          <i class="bi bi-check2-square"></i>
          <h2>4. การเลือกรายการเพื่อพิมพ์</h2>
        </div>
        <div class="section-body">
          <div class="step">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>ติ๊กช่อง Checkbox ที่คอลัมน์แรก</h3>
              <p>คลิกที่ช่อง ☐ หน้าแต่ละรายการ — แถวที่เลือกจะเปลี่ยนพื้นหลังเป็นสีเหลืองอ่อน</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div class="step-content">
              <h3>เลือกทั้งหน้าพร้อมกัน</h3>
              <p>กดปุ่ม <span class="tag tag-primary"><i class="bi bi-check-all"></i> เลือกหน้านี้</span> เพื่อติ๊กทุกรายการในหน้าปัจจุบันพร้อมกัน</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">3</div>
            <div class="step-content">
              <h3>ดูจำนวนที่เลือก</h3>
              <p>แถบสีเหลืองที่ด้านบนขวา (Badge) และแถบ Action Bar ที่ด้านล่างจะแสดงจำนวนรายการที่เลือกไว้ในขณะนั้น</p>
              <div class="note-box">
                <i class="bi bi-info-circle-fill"></i>
                การเลือกจะ<strong>ถูกจำ</strong>ไว้แม้จะเปลี่ยนหน้าหรือค้นหา — รายการที่เลือกไว้จะอยู่ครบจนกว่าจะกด "ล้างทั้งหมด"
              </div>
            </div>
          </div>
          <div class="step">
            <div class="step-num">4</div>
            <div class="step-content">
              <h3>ล้างการเลือกทั้งหมด</h3>
              <p>กดปุ่ม <span class="tag tag-muted"><i class="bi bi-x-circle"></i> ล้างทั้งหมด</span> ที่ Navbar หรือ Action Bar ด้านล่าง</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 5. Add -->
      <div class="manual-section" id="sec-add">
        <div class="section-header">
          <i class="bi bi-plus-circle-fill"></i>
          <h2>5. เพิ่มรายชื่อใหม่</h2>
        </div>
        <div class="section-body">
          <div class="step">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-success"><i class="bi bi-plus-lg"></i> เพิ่มรายชื่อ</span></h3>
              <p>หน้าต่าง Modal จะเปิดขึ้นมาทันที โดยไม่ต้องออกจากหน้าหลัก</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div class="step-content">
              <h3>กรอกข้อมูล</h3>
              <p>กรอกข้อมูลที่ต้องการ — ต้องกรอกอย่างน้อย <strong>ชื่อบริษัท</strong> หรือ <strong>ที่อยู่</strong> อย่างใดอย่างหนึ่ง</p>
              <div class="field-grid mt-2">
                <div class="field-item">
                  <div class="field-name"><i class="bi bi-building text-primary me-1"></i> ชื่อบริษัท *</div>
                  <div class="field-desc">ชื่อบริษัทหรือหน่วยงาน (ต้องกรอก)</div>
                </div>
                <div class="field-item">
                  <div class="field-name"><i class="bi bi-person text-primary me-1"></i> ชื่อผู้ติดต่อ</div>
                  <div class="field-desc">ชื่อบุคคลที่ต้องการส่งถึง</div>
                </div>
                <div class="field-item">
                  <div class="field-name"><i class="bi bi-briefcase text-primary me-1"></i> ตำแหน่ง</div>
                  <div class="field-desc">ตำแหน่งงานของผู้ติดต่อ</div>
                </div>
                <div class="field-item">
                  <div class="field-name"><i class="bi bi-geo-alt text-primary me-1"></i> ที่อยู่ *</div>
                  <div class="field-desc">ที่อยู่สำหรับจัดส่ง (ต้องกรอก)</div>
                </div>
                <div class="field-item">
                  <div class="field-name"><i class="bi bi-send text-primary me-1"></i> ประเภทจัดส่ง</div>
                  <div class="field-desc">EMS / ลงทะเบียน / พัสดุ / ไปรษณีย์ธรรมดา</div>
                </div>
                <div class="field-item">
                  <div class="field-name"><i class="bi bi-file-text text-primary me-1"></i> หมายเหตุ</div>
                  <div class="field-desc">BILLING NOTE / TAX INVOICE / RECEIPT / QUOTATION</div>
                </div>
              </div>
            </div>
          </div>
          <div class="step">
            <div class="step-num">3</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-primary"><i class="bi bi-save"></i> บันทึก</span></h3>
              <p>ระบบจะบันทึกและรีเฟรชหน้าเพื่อแสดงรายการใหม่ทันที</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 6. Edit -->
      <div class="manual-section" id="sec-edit">
        <div class="section-header">
          <i class="bi bi-pencil-fill"></i>
          <h2>6. แก้ไขรายชื่อ</h2>
        </div>
        <div class="section-body">
          <div class="step">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-primary"><i class="bi bi-pencil"></i></span> ในคอลัมน์ "จัดการ"</h3>
              <p>ปุ่มสีน้ำเงินด้านซ้ายสุดของคอลัมน์จัดการ จะเปิดหน้าแก้ไขรายชื่อนั้น</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div class="step-content">
              <h3>แก้ไขข้อมูลที่ต้องการ</h3>
              <p>ข้อมูลเดิมจะถูกโหลดมาแสดงในฟอร์มให้อัตโนมัติ แก้ไขได้ทุกช่อง</p>
              <div class="note-box">
                <i class="bi bi-info-circle-fill"></i>
                รายการที่ <strong>ไม่มีชื่อบริษัท</strong> จะแสดงช่องว่าง — พร้อมให้กรอกข้อมูลใหม่
              </div>
            </div>
          </div>
          <div class="step">
            <div class="step-num">3</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-primary"><i class="bi bi-save"></i> บันทึก</span></h3>
              <p>ระบบจะบันทึกข้อมูลและกลับไปหน้าหลักทันที</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">4</div>
            <div class="step-content">
              <h3>พิมพ์ Label จากหน้าแก้ไขได้ทันที</h3>
              <p>กดปุ่ม <span class="tag tag-danger"><i class="bi bi-printer"></i> พิมพ์ Label นี้</span> ที่มุมขวาล่างของฟอร์ม เพื่อพิมพ์รายการนั้นทันที</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 7. Delete -->
      <div class="manual-section" id="sec-delete">
        <div class="section-header">
          <i class="bi bi-trash-fill"></i>
          <h2>7. ลบรายชื่อ</h2>
        </div>
        <div class="section-body">
          <div class="step">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-muted"><i class="bi bi-trash"></i></span> ในคอลัมน์ "จัดการ"</h3>
              <p>ปุ่มสีเทาด้านขวาสุดของคอลัมน์จัดการ</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div class="step-content">
              <h3>ยืนยันการลบ</h3>
              <p>กล่องยืนยันจะถามว่า "ลบรายการนี้?" กด <strong>OK</strong> เพื่อยืนยัน หรือ <strong>Cancel</strong> เพื่อยกเลิก</p>
              <div class="warn-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>ข้อควรระวัง:</strong> การลบไม่สามารถเรียกคืนได้ กรุณาตรวจสอบก่อนกด OK
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 8. Print -->
      <div class="manual-section" id="sec-print">
        <div class="section-header">
          <i class="bi bi-printer-fill"></i>
          <h2>8. การพิมพ์ Label</h2>
        </div>
        <div class="section-body">

          <h3 style="font-size:.88rem;font-weight:700;margin-bottom:1rem">
            <span class="tag tag-primary me-1">วิธีที่ 1</span> พิมพ์หลายรายการพร้อมกัน
          </h3>
          <div class="step">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>เลือกรายการที่ต้องการ</h3>
              <p>ติ๊ก Checkbox หน้ารายการที่ต้องการพิมพ์ (เลือกได้หลายรายการข้ามหน้า)</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">2</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-primary"><i class="bi bi-printer"></i> พิมพ์ที่เลือก</span></h3>
              <p>กดที่ Navbar ด้านบน หรือ Action Bar ด้านล่าง — หน้าต่าง Preview จะเปิดใน Tab ใหม่</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">3</div>
            <div class="step-content">
              <h3>ตรวจสอบ Preview และกด <span class="tag tag-primary"><i class="bi bi-printer-fill"></i> พิมพ์</span></h3>
              <p>ตรวจดูว่า Label แสดงถูกต้อง จากนั้นกดปุ่มพิมพ์ หรือกด <kbd>Ctrl+P</kbd></p>
            </div>
          </div>

          <hr class="my-3">

          <h3 style="font-size:.88rem;font-weight:700;margin-bottom:1rem">
            <span class="tag tag-success me-1">วิธีที่ 2</span> พิมพ์รายการเดียว
          </h3>
          <div class="step" style="margin-bottom:0;padding-bottom:0;border:none">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-danger"><i class="bi bi-printer"></i></span> ในคอลัมน์จัดการ</h3>
              <p>ปุ่มพิมพ์สีแดงตรงกลาง — จะเปิด Preview ของรายการนั้นรายการเดียวทันที ไม่ต้องเลือกก่อน</p>
            </div>
          </div>
        </div>
      </div>

      <!-- 9. Print Settings -->
      <div class="manual-section" id="sec-settings">
        <div class="section-header">
          <i class="bi bi-gear-fill"></i>
          <h2>9. ตั้งค่าการพิมพ์ (Browser Print Dialog)</h2>
        </div>
        <div class="section-body">
          <p class="mb-3" style="font-size:.84rem;color:#475569">
            เมื่อกด <kbd>Ctrl+P</kbd> หรือปุ่มพิมพ์ ให้ตั้งค่าดังนี้ใน Browser Print Dialog:
          </p>

          <table class="table table-bordered print-table">
            <thead>
              <tr>
                <th>การตั้งค่า</th>
                <th>ค่าที่ต้องตั้ง</th>
                <th>หมายเหตุ</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><i class="bi bi-printer me-1 text-primary"></i>Printer</td>
                <td><strong>Ricoh IM C2010</strong></td>
                <td>เลือกเครื่องพิมพ์ที่ถูกต้อง</td>
              </tr>
              <tr>
                <td><i class="bi bi-file-earmark me-1 text-primary"></i>Paper Size</td>
                <td><strong>A4</strong> (210 × 297 mm)</td>
                <td>—</td>
              </tr>
              <tr>
                <td><i class="bi bi-phone me-1 text-primary"></i>Orientation</td>
                <td><strong>Portrait</strong> (แนวตั้ง)</td>
                <td>—</td>
              </tr>
              <tr>
                <td><i class="bi bi-layout-three-columns me-1 text-primary"></i>Margins</td>
                <td><strong>None</strong> (ไม่มี)</td>
                <td>ระบบจัดการ margin เอง</td>
              </tr>
              <tr>
                <td><i class="bi bi-zoom-in me-1 text-primary"></i>Scale</td>
                <td><strong>100%</strong></td>
                <td>ห้ามตั้ง Fit to Page</td>
              </tr>
              <tr>
                <td><i class="bi bi-layout-text-window me-1 text-primary"></i>Headers &amp; Footers</td>
                <td><strong>ปิด (Off)</strong></td>
                <td>ไม่ต้องการ URL/วันที่ใน Label</td>
              </tr>
            </tbody>
          </table>

          <div class="note-box">
            <i class="bi bi-lightbulb-fill"></i>
            <strong>เคล็ดลับ:</strong> ใช้ <strong>Google Chrome</strong> พิมพ์ได้แม่นยำที่สุด — ใน More Settings เลือก "Background graphics" เพื่อให้ badge สีพิมพ์ออกมาสวยงาม
          </div>

          <div class="warn-box">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <strong>Label Elephant No.A15:</strong> วางกระดาษสติกเกอร์ในถาดแบบ <strong>Portrait (แนวตั้ง)</strong>
            — ดวง Label จะอยู่ในแนวนอน (Landscape) บนแผ่น 2 คอลัมน์ × 4 แถว = 8 ดวง/แผ่น
          </div>
        </div>
      </div>

      <!-- 10. Logout -->
      <div class="manual-section" id="sec-logout">
        <div class="section-header">
          <i class="bi bi-box-arrow-right"></i>
          <h2>10. ออกจากระบบ</h2>
        </div>
        <div class="section-body">
          <div class="step" style="margin-bottom:0;padding-bottom:0;border:none">
            <div class="step-num">1</div>
            <div class="step-content">
              <h3>กดปุ่ม <span class="tag tag-muted"><i class="bi bi-box-arrow-right"></i></span> ที่มุมขวาบนของ Navbar</h3>
              <p>ยืนยันการออกจากระบบในกล่อง Confirm แล้วระบบจะพากลับไปหน้า Login ทันที</p>
              <div class="note-box mt-2">
                <i class="bi bi-shield-check-fill"></i>
                Session จะถูกยกเลิกทั้งหมดเมื่อออกจากระบบ — ข้อมูลที่เลือกไว้จะยังคงอยู่ในฐานข้อมูล
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer note -->
      <div class="text-center py-2" style="font-size:.75rem;color:var(--muted)">
        <?= APP_NAME ?> &bull; Elephant No.A15 (50×80 mm) &bull; Ricoh IM C2010
      </div>

    </div><!-- col -->
  </div><!-- row -->
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
