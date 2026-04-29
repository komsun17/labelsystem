<?php
// ============================================
// index.php - Label Print System (Main Page)
// Ricoh IM C2010 | Label A15 (100x150mm)
// ============================================
require_once 'includes/config.php';

$db = getDB();

// ── Search & Pagination ──────────────────────────────
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = ITEMS_PER_PAGE;
$offset  = ($page - 1) * $limit;

$where  = "WHERE (company != '' OR contact != '' OR address != '')";
$params = [];

if ($search !== '') {
    $where  .= " AND (company LIKE :s OR contact LIKE :s2 OR address LIKE :s3)";
    $params[':s']  = "%$search%";
    $params[':s2'] = "%$search%";
    $params[':s3'] = "%$search%";
}

$total = $db->prepare("SELECT COUNT(*) FROM label_billing $where");
$total->execute($params);
$totalRows  = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalRows / $limit));

$stmt = $db->prepare("SELECT * FROM label_billing $where ORDER BY company ASC LIMIT :lim OFFSET :off");
foreach ($params as $k => $v) $stmt->bindValue($k, $v);
$stmt->bindValue(':lim',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':off',  $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll();

// ── Count selected ───────────────────────────────────
$selectedCount = (int)$db->query("SELECT COUNT(*) FROM label_billing WHERE is_selected=1")->fetchColumn();

// ── Handle clear_all (POST + CSRF) ──────────────────
if (isset($_POST['clear_all'])) {
    if (csrf_verify()) {
        $db->exec("UPDATE label_billing SET is_selected=0");
    }
    header('Location: index.php'); exit;
}

// ── Handle delete (POST + CSRF) ─────────────────────
if (isset($_POST['del'])) {
    if (csrf_verify()) {
        $db->prepare("DELETE FROM label_billing WHERE id=?")->execute([(int)$_POST['del']]);
    }
    header("Location: index.php?page=$page&search=" . urlencode($search)); exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark" style="background:#2c3e50;">
  <div class="container-fluid">
    <span class="navbar-brand"><i class="bi bi-printer-fill me-2"></i><?= APP_NAME ?></span>
    <div class="d-flex align-items-center gap-3">
      <span class="text-white-50 small">Ricoh IM C2010 | Label A15</span>
      <?php if ($selectedCount > 0): ?>
      <a href="print_preview.php" target="_blank"
         class="btn btn-sm btn-print-sel">
        <i class="bi bi-printer me-1"></i>พิมพ์ที่เลือก (<?= $selectedCount ?>)
      </a>
      <?php endif; ?>
      <form method="post" class="d-inline"
            onsubmit="return confirm('ล้างรายการที่เลือกทั้งหมด?')">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <button type="submit" name="clear_all" value="1" class="btn btn-sm btn-outline-light">
          <i class="bi bi-x-circle me-1"></i>ล้างทั้งหมด
        </button>
      </form>
    </div>
  </div>
</nav>



<div class="container-fluid py-4">

  <!-- Summary Cards -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <div class="fs-2 fw-bold text-primary"><?= number_format($totalRows) ?></div>
        <div class="text-muted small">รายชื่อทั้งหมด</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <div class="fs-2 fw-bold text-warning"><?= number_format($selectedCount) ?></div>
        <div class="text-muted small">เลือกพิมพ์</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <?php $emsCount = (int)$db->query("SELECT COUNT(*) FROM label_billing WHERE ems='EMS'")->fetchColumn(); ?>
        <div class="fs-2 fw-bold text-info"><?= number_format($emsCount) ?></div>
        <div class="text-muted small">ส่ง EMS</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card p-3 text-center">
        <div class="fs-2 fw-bold text-success"><?= $totalPages ?></div>
        <div class="text-muted small">หน้าทั้งหมด</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">

      <!-- Toolbar -->
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <form method="get" class="d-flex search-box gap-2">
          <input type="text" name="search" class="form-control form-control-sm"
                 placeholder="ค้นหา บริษัท / ชื่อ / ที่อยู่..."
                 value="<?= htmlspecialchars($search) ?>">
          <button class="btn btn-sm btn-primary"><i class="bi bi-search"></i></button>
          <?php if ($search): ?>
          <a href="index.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-x"></i></a>
          <?php endif; ?>
        </form>
        <div class="d-flex gap-2">
          <button onclick="selectAllPage(true)"  class="btn btn-sm btn-outline-primary">
            <i class="bi bi-check-all"></i> เลือกหน้านี้
          </button>
          <button onclick="selectAllPage(false)" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-x-square"></i> ยกเลิกหน้านี้
          </button>
          <a href="edit.php" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg"></i> เพิ่มรายชื่อ
          </a>
        </div>
      </div>

      <!-- Table -->
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="mainTable">
          <thead>
            <tr>
              <th width="40"><input type="checkbox" id="checkAll" class="form-check-input"></th>
              <th>บริษัท / ลูกค้า</th>
              <th>ผู้ติดต่อ / ตำแหน่ง</th>
              <th>ที่อยู่</th>
              <th>EMS</th>
              <th>หมายเหตุ</th>
              <th width="120">จัดการ</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($rows as $r): ?>
          <tr class="<?= $r['is_selected'] ? 'selected-row' : '' ?>" id="row-<?= $r['id'] ?>">
            <td>
              <input type="checkbox" class="form-check-input row-check"
                     data-id="<?= $r['id'] ?>"
                     <?= $r['is_selected'] ? 'checked' : '' ?>>
            </td>
            <td>
              <div class="fw-semibold"><?= htmlspecialchars($r['company'] ?? '') ?></div>
            </td>
            <td>
              <div><?= htmlspecialchars($r['contact'] ?? '') ?></div>
              <?php if ($r['position']): ?>
              <div class="text-muted small"><?= htmlspecialchars($r['position']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <div class="text-muted small" style="max-width:280px">
                <?= htmlspecialchars($r['address'] ?? '') ?>
              </div>
            </td>
            <td>
              <?php if ($r['ems']): ?>
              <span class="badge badge-ems"><?= htmlspecialchars($r['ems']) ?></span>
              <?php else: ?>
              <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="text-muted small"><?= htmlspecialchars($r['billing_note'] ?? '') ?></span>
            </td>
            <td>
              <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-xs btn-outline-primary btn-sm py-0 px-2">
                <i class="bi bi-pencil"></i>
              </a>
              <a href="print_single.php?id=<?= $r['id'] ?>" target="_blank"
                 class="btn btn-xs btn-outline-danger btn-sm py-0 px-2">
                <i class="bi bi-printer"></i>
              </a>
              <form method="post" class="d-inline"
                    onsubmit="return confirm('ลบรายการนี้?')">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="del" value="<?= $r['id'] ?>">
                <button type="submit" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
      <nav class="mt-3">
        <ul class="pagination pagination-sm justify-content-center">
          <?php for ($p = 1; $p <= $totalPages; $p++): ?>
          <li class="page-item <?= $p == $page ? 'active' : '' ?>">
            <a class="page-link" href="?page=<?= $p ?>&search=<?= urlencode($search) ?>"><?= $p ?></a>
          </li>
          <?php endfor; ?>
        </ul>
      </nav>
      <?php endif; ?>

    </div>
  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>
