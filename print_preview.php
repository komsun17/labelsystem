<?php
// ============================================
// print_preview.php - Print Labels (Selected)
// Ricoh IM C2010 | Label A15 (50x80mm) 8 ดวง/แผ่น
// ============================================
require_once 'includes/config.php';

$db   = getDB();
$mode = $_GET['mode'] ?? 'selected';  // selected | single
$id   = (int)($_GET['id'] ?? 0);

if ($mode === 'single' && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM label_billing WHERE id=?");
    $stmt->execute([$id]);
    $labels = $stmt->fetchAll();
} else {
    $labels = $db->query("SELECT * FROM label_billing WHERE is_selected=1 ORDER BY company ASC")->fetchAll();
}

if (empty($labels)) {
    echo '<script>alert("ไม่มีรายการที่เลือก"); history.back();</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>พิมพ์ Label - <?= count($labels) ?> รายการ</title>
<link rel="stylesheet" href="css/print.css">
</head>
<body>

<!-- Toolbar (screen only) -->
<div class="toolbar">
  <span style="font-size:16px;font-weight:700">
    🖨️ Preview Label — <?= count($labels) ?> รายการ
  </span>
  <button class="btn-print" onclick="window.print()">
    🖨️ พิมพ์ (Ctrl+P)
  </button>
  <button class="btn-close" onclick="window.close()">✕ ปิด</button>
  <span style="font-size:12px;opacity:.7">Ricoh IM C2010 | Label A15 (50×80mm) | 8 ดวง/แผ่น</span>
</div>

<!-- Labels: 8 ดวง/แผ่น, 2 คอลัมน์ × 4 แถว -->
<div class="sheets-container">
<?php
$sheets = array_chunk($labels, 8);
foreach ($sheets as $si => $sheetLabels):
    while (count($sheetLabels) < 8) $sheetLabels[] = null; // pad empty cells
?>
<div class="sheet">
  <?php foreach ($sheetLabels as $i => $r): ?>
  <div class="label-cell<?= $r ? '' : ' label-empty' ?>">
    <?php if ($r): ?>
    <span class="label-number">#<?= ($si * 8) + $i + 1 ?></span>

    <!-- TO -->
    <div class="label-to-title">ถึง / TO :</div>

    <?php if (!empty($r['company'])): ?>
    <div class="label-company"><?= htmlspecialchars($r['company']) ?></div>
    <?php endif; ?>

    <?php if (!empty($r['contact'])): ?>
    <div class="label-contact">
      <?= htmlspecialchars($r['contact']) ?>
      <?php if (!empty($r['position'])): ?>
        <span class="label-pos"> — <?= htmlspecialchars($r['position']) ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($r['address'])): ?>
    <div class="label-address"><?= nl2br(htmlspecialchars($r['address'])) ?></div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="label-footer">
      <?php if (!empty($r['ems'])): ?>
        <span class="badge-ems"><?= htmlspecialchars($r['ems']) ?></span>
      <?php else: ?>
        <span></span>
      <?php endif; ?>
      <?php if (!empty($r['billing_note'])): ?>
        <span class="badge-note"><?= htmlspecialchars($r['billing_note']) ?></span>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endforeach; ?>
</div>
<?php endforeach; ?>
</div>

</body>
</html>
