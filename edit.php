<?php
// ============================================
// edit.php - Add / Edit Customer Record
// ============================================
require_once 'includes/config.php';

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
<nav class="navbar navbar-dark" style="background:#2c3e50;">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <i class="bi bi-arrow-left me-2"></i><?= APP_NAME ?>
    </a>
  </div>
</nav>

<div class="container py-4" style="max-width:700px">
  <div class="card">
    <div class="card-header bg-white border-0 pt-4 pb-0">
      <h5 class="fw-bold">
        <i class="bi bi-<?= $record['id'] ? 'pencil-square' : 'plus-circle' ?> me-2 text-primary"></i>
        <?= $record['id'] ? 'แก้ไขรายชื่อ' : 'เพิ่มรายชื่อใหม่' ?>
      </h5>
    </div>
    <div class="card-body">

      <?php foreach ($errors as $e): ?>
      <div class="alert alert-danger py-2"><?= htmlspecialchars($e) ?></div>
      <?php endforeach; ?>

      <form method="post">
        <div class="mb-3">
          <label class="form-label fw-semibold">ชื่อบริษัท / Company <span class="text-danger">*</span></label>
          <input type="text" name="company" class="form-control"
                 value="<?= htmlspecialchars($_POST['company'] ?? $record['company']) ?>"
                 placeholder="THAI MIYAKE FORGING CO., LTD.">
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">ชื่อผู้ติดต่อ / Contact</label>
            <input type="text" name="contact" class="form-control"
                   value="<?= htmlspecialchars($_POST['contact'] ?? $record['contact']) ?>"
                   placeholder="ACCOUNTING DEPARTMENT">
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">ตำแหน่ง / Position</label>
            <input type="text" name="position" class="form-control"
                   value="<?= htmlspecialchars($_POST['position'] ?? $record['position']) ?>">
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">ที่อยู่ / Address</label>
          <textarea name="address" class="form-control" rows="3"
                    placeholder="เลขที่ ถนน ตำบล/แขวง อำเภอ/เขต จังหวัด รหัสไปรษณีย์"><?= htmlspecialchars($_POST['address'] ?? $record['address']) ?></textarea>
        </div>

        <div class="row g-3 mb-4">
          <div class="col-md-6">
            <label class="form-label fw-semibold">ประเภทจัดส่ง / EMS</label>
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
            <label class="form-label fw-semibold">หมายเหตุ / Note</label>
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

        <div class="d-flex gap-2">
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
