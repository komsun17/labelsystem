<?php
// ============================================
// print_preview.php — Print Labels (Selected)
// Elephant No.A15 | 50×80mm | 12 ดวง/แผ่น
// ============================================
require_once 'includes/config.php';
auth_check();

$db   = getDB();
$mode = $_GET['mode'] ?? 'selected';
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

$totalSheets = ceil(count($labels) / LABELS_PER_SHEET);
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<title>พิมพ์ Label — <?= count($labels) ?> รายการ</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/print.css">
</head>
<body>

<!-- Toolbar (screen only) -->
<div class="toolbar">
  <div class="toolbar-brand">
    <i class="bi bi-printer-fill"></i>
    Label Print System
  </div>
  <span class="toolbar-badge"><?= count($labels) ?> รายการ &nbsp;/&nbsp; <?= $totalSheets ?> แผ่น</span>
  <div class="toolbar-sep"></div>
  <span class="toolbar-info">
    Elephant No.A15 &nbsp;|&nbsp; 50×80 mm &nbsp;|&nbsp; <?= LABELS_PER_SHEET ?> ดวง/แผ่น &nbsp;|&nbsp; A4
  </span>
  <button class="btn-toolbar btn-print" onclick="window.print()">
    <i class="bi bi-printer-fill"></i> พิมพ์
  </button>
  <button class="btn-toolbar btn-close-tb" onclick="window.close()">
    <i class="bi bi-x-lg"></i> ปิด
  </button>
</div>

<!-- Sheets -->
<div class="sheets-container">
<?php
$sheets = array_chunk($labels, LABELS_PER_SHEET);
foreach ($sheets as $si => $sheetLabels):
    // Pad to full grid (4 cols × 3 rows)
    while (count($sheetLabels) < LABELS_PER_SHEET) $sheetLabels[] = null;
?>

<div class="sheet-wrap">
  <div class="sheet-label">แผ่นที่ <?= $si + 1 ?> / <?= $totalSheets ?></div>
  <div class="sheet">
    <?php foreach ($sheetLabels as $i => $r): ?>

    <div class="label-cell<?= $r ? '' : ' label-empty' ?>">
      <?php if ($r): ?>

      <span class="label-number"><?= ($si * LABELS_PER_SHEET) + $i + 1 ?></span>

      <!-- TO -->
      <div class="label-to-block">
        <span class="label-to-dot"></span>
        <span class="label-to-title">ถึง &nbsp;/&nbsp; TO</span>
      </div>

      <!-- Company -->
      <?php if (!empty($r['company'])): ?>
      <div class="label-company"><?= htmlspecialchars($r['company']) ?></div>
      <?php endif; ?>

      <!-- Contact + Position (inline, saves vertical space) -->
      <?php if (!empty($r['contact']) || !empty($r['position'])): ?>
      <div class="label-contact-row">
        <?= htmlspecialchars($r['contact']) ?>
        <?php if (!empty($r['contact']) && !empty($r['position'])): ?>
          <span class="label-pos"> &mdash; <?= htmlspecialchars($r['position']) ?></span>
        <?php elseif (!empty($r['position'])): ?>
          <span class="label-pos"><?= htmlspecialchars($r['position']) ?></span>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <!-- Address -->
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
  </div><!-- .sheet -->
</div><!-- .sheet-wrap -->

<?php endforeach; ?>
</div><!-- .sheets-container -->

</body>
</html>
