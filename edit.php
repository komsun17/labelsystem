<?php
// ============================================
// edit.php - Add / Edit Customer Record
// ============================================
require_once 'includes/config.php';
auth_check();

$db = getDB();
$id = (int)($_GET['id'] ?? 0);

// ── Load existing record ─────────────────────
$record = [
    'id' => 0, 'contact' => '', 'position' => '',
    'company' => '', 'address' => '', 'ems' => '',
    'billing_note' => '', 'is_selected' => 0
];

if ($id > 0) {
    $stmt = $db->prepare("SELECT * FROM label_billing WHERE id=?");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if ($found) $record = $found;
    else { header('Location: index.php'); exit; }
}

// ── Handle Save ──────────────────────────────
$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        http_response_code(403);
        die('Invalid CSRF token');
    }

    $company      = trim($_POST['company']      ?? '');
    $contact      = trim($_POST['contact']      ?? '');
    $position     = trim($_POST['position']     ?? '');
    $address      = trim($_POST['address']      ?? '');
    $ems          = trim($_POST['ems']          ?? '');
    $billing_note = trim($_POST['billing_note'] ?? '');

    if (empty($company) && empty($address)) {
        $errors[] = 'กรุณากรอกชื่อบริษัท หรือที่อยู่อย่างน้อยหนึ่งอย่าง';
    }

    if (empty($errors)) {
        if ($id > 0) {
            $db->prepare("UPDATE label_billing SET company=?,contact=?,position=?,address=?,ems=?,billing_note=? WHERE id=?")
               ->execute([$company, $contact, $position, $address, $ems, $billing_note, $id]);
        } else {
            $db->prepare("INSERT INTO label_billing (company,contact,position,address,ems,billing_note) VALUES(?,?,?,?,?,?)")
               ->execute([$company, $contact, $position, $address, $ems, $billing_note]);
            $id = (int)$db->lastInsertId();
        }
        header('Location: index.php'); exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $record['id'] ? 'แก้ไขรายชื่อ' : 'เพิ่มรายชื่อ' ?> - <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<nav class="app-navbar">
  <a href="index.php" class="brand">
    <span class="brand-icon"><i class="bi bi-arrow-left"></i></span>
    <div>
      <div><?= APP_NAME ?></div>
      <div class="subtitle"><?= $record['id'] ? 'แก้ไขรายชื่อ' : 'เพิ่มรายชื่อใหม่' ?></div>
    </div>
  </a>
  <a href="logout.php" class="btn-nav btn-nav-clear" title="ออกจากระบบ"
     onclick="return confirm('ออกจากระบบ?')">
    <i class="bi bi-box-arrow-right"></i>
  </a>
</nav>

<div class="container py-4" style="max-width:700px">
  <div class="card main-card">
    <div class="edit-header">
      <div class="d-flex align-items-center justify-content-between">
        <h5><i class="bi bi-<?= $record['id'] ? 'pencil-square' : 'plus-circle' ?> me-2"></i><?= $record['id'] ? 'แก้ไขรายชื่อ' : 'เพิ่มรายชื่อใหม่' ?></h5>
        <a href="index.php" class="back-link"><i class="bi bi-x-lg"></i></a>
      </div>
    </div>
    <div class="card-body p-4">

      <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
      <?php endforeach; ?>

      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="mb-3">
          <label class="form-label">ชื่อบริษัท / Company <span class="text-danger">*</span></label>
          <input type="text" name="company" class="form-control"
                 value="<?= htmlspecialchars($_POST['company'] ?? $record['company']) ?>"
                 placeholder="THAI MIYAKE FORGING CO., LTD.">
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label">ชื่อผู้ติดต่อ / Contact</label>
            <input type="text" name="contact" class="form-control"
                   value="<?= htmlspecialchars($_POST['contact'] ?? $record['contact']) ?>"
                   placeholder="ACCOUNTING DEPARTMENT">
          </div>
          <div class="col-md-6">
            <label class="form-label">ตำแหน่ง / Position</label>
            <input type="text" name="position" class="form-control"
                   value="<?= htmlspecialchars($_POST['position'] ?? $record['position']) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">ที่อยู่ / Address</label>
          <textarea name="address" class="form-control" rows="3"
                    placeholder="เลขที่ ถนน ตำบล/แขวง อำเภอ/เขต จังหวัด รหัสไปรษณีย์"><?= htmlspecialchars($_POST['address'] ?? $record['address']) ?></textarea>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label">ประเภทจัดส่ง / EMS</label>
            <select name="ems" class="form-select">
              <option value="">-- ไม่ระบุ --</option>
              <?php foreach (['EMS','ลงทะเบียน','พัสดุ','ไปรษณีย์ธรรมดา'] as $opt): ?>
              <option value="<?= $opt ?>" <?= (($_POST['ems'] ?? $record['ems']) === $opt) ? 'selected' : '' ?>>
                <?= $opt ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">หมายเหตุ / Note</label>
            <select name="billing_note" class="form-select">
              <option value="">-- ไม่ระบุ --</option>
              <?php foreach (['BILLING NOTE','TAX INVOICE','RECEIPT','QUOTATION'] as $opt): ?>
              <option value="<?= $opt ?>" <?= (($_POST['billing_note'] ?? $record['billing_note']) === $opt) ? 'selected' : '' ?>>
                <?= $opt ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="d-flex gap-2 pt-2 border-top">
          <button type="submit" class="btn btn-primary px-4">
            <i class="bi bi-save me-1"></i>บันทึก
          </button>
          <a href="index.php" class="btn btn-outline-secondary px-4">ยกเลิก</a>
          <?php if ($id > 0): ?>
          <a href="print_single.php?id=<?= $id ?>" target="_blank"
             class="btn btn-outline-danger ms-auto">
            <i class="bi bi-printer me-1"></i>พิมพ์ Label นี้
          </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
