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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable@14/dist/handsontable.full.min.css">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="app-navbar">
  <a href="index.php" class="brand">
    <span class="brand-icon"><i class="bi bi-printer-fill"></i></span>
    <div>
      <div><?= APP_NAME ?></div>
      <div class="subtitle">Ricoh IM C2010 &bull; Label 50&times;80 mm</div>
    </div>
  </a>
  <div class="d-flex align-items-center gap-2">
    <span id="selectedBadge" class="badge rounded-pill bg-warning text-dark" style="font-size:.72rem;<?= $selectedCount == 0 ? 'display:none' : '' ?>">
      <?= $selectedCount ?> รายการ
    </span>
    <a href="print_preview.php" target="_blank" class="btn-nav btn-nav-print">
      <i class="bi bi-printer"></i> พิมพ์ที่เลือก
    </a>
    <form method="post" class="d-inline"
          onsubmit="return confirm('ล้างรายการที่เลือกทั้งหมด?')">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
      <button type="submit" name="clear_all" value="1" class="btn-nav btn-nav-clear">
        <i class="bi bi-x-circle"></i> ล้างทั้งหมด
      </button>
    </form>
  </div>
</nav>



<div class="container-fluid py-4">

  <!-- Stat Cards -->
  <?php $emsCount = (int)$db->query("SELECT COUNT(*) FROM label_billing WHERE ems != ''")->fetchColumn(); ?>
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
        <div>
          <div class="stat-value"><?= number_format($totalRows) ?></div>
          <div class="stat-label">รายชื่อทั้งหมด</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon amber"><i class="bi bi-check2-square"></i></div>
        <div>
          <div class="stat-value" id="statSelected"><?= number_format($selectedCount) ?></div>
          <div class="stat-label">เลือกพิมพ์</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon teal"><i class="bi bi-send-fill"></i></div>
        <div>
          <div class="stat-value"><?= number_format($emsCount) ?></div>
          <div class="stat-label">มีประเภทส่ง</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-card">
        <div class="stat-icon violet"><i class="bi bi-files"></i></div>
        <div>
          <div class="stat-value"><?= $totalPages ?></div>
          <div class="stat-label">หน้าทั้งหมด</div>
        </div>
      </div>
    </div>
  </div>

  <div class="main-card">
    <!-- Toolbar -->
    <div class="main-card-toolbar">
      <form method="get" class="d-flex align-items-center gap-2">
        <div class="search-wrap">
          <input type="text" name="search" class="form-control"
                 placeholder="ค้นหา บริษัท / ชื่อ / ที่อยู่..."
                 value="<?= htmlspecialchars($search) ?>">
          <button class="btn-search"><i class="bi bi-search"></i></button>
        </div>
        <?php if ($search): ?>
        <a href="index.php" class="btn-clear-search"><i class="bi bi-x-lg"></i></a>
        <?php endif; ?>
      </form>
      <div class="d-flex gap-2 flex-wrap">
        <button onclick="selectAllPage(true)"  class="btn-toolbar btn btn-outline-primary">
          <i class="bi bi-check-all"></i> &nbsp;&nbsp;เลือกหน้านี้
        </button>
        <button onclick="selectAllPage(false)" class="btn-toolbar btn btn-outline-secondary">
          <i class="bi bi-x-square"></i> &nbsp;&nbsp;ยกเลิก
        </button>
        <a href="edit.php" class="btn-toolbar btn btn-success">
          <i class="bi bi-plus-lg me-1"></i>เพิ่มรายชื่อ
        </a>
      </div>
    </div>

      <!-- Handsontable -->
      <div id="hot-container"></div>

      <!-- Pagination -->
      <?php if ($totalPages > 1):
        $qs   = $search !== '' ? '&search=' . urlencode($search) : '';
        $prev = max(1, $page - 1);
        $next = min($totalPages, $page + 1);

        /* build page-number list with ellipsis */
        $pages = [];
        for ($p = 1; $p <= $totalPages; $p++) {
            if ($p === 1 || $p === $totalPages
                || ($p >= $page - 2 && $p <= $page + 2)) {
                $pages[] = $p;
            } elseif (end($pages) !== '…') {
                $pages[] = '…';
            }
        }
      ?>
      <div class="px-3 py-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="background:#fafbfd">
        <span class="co-detail">
          แสดง <?= number_format(min($offset+1,$totalRows)) ?>–<?= number_format(min($offset+$limit,$totalRows)) ?>
          จาก <strong><?= number_format($totalRows) ?></strong> รายการ
          &nbsp;|&nbsp; หน้า <?= $page ?> / <?= $totalPages ?>
        </span>
        <nav>
          <ul class="pagination pagination-sm mb-0">

            <!-- First -->
            <li class="page-item <?= $page === 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=1<?= $qs ?>" title="หน้าแรก">
                <i class="bi bi-chevron-double-left"></i>
              </a>
            </li>
            <!-- Prev -->
            <li class="page-item <?= $page === 1 ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $prev ?><?= $qs ?>" title="ก่อนหน้า">
                <i class="bi bi-chevron-left"></i>
              </a>
            </li>

            <?php foreach ($pages as $p): ?>
              <?php if ($p === '…'): ?>
              <li class="page-item disabled">
                <span class="page-link px-2" style="min-width:auto">&hellip;</span>
              </li>
              <?php else: ?>
              <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $p ?><?= $qs ?>"><?= $p ?></a>
              </li>
              <?php endif; ?>
            <?php endforeach; ?>

            <!-- Next -->
            <li class="page-item <?= $page === $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $next ?><?= $qs ?>" title="ถัดไป">
                <i class="bi bi-chevron-right"></i>
              </a>
            </li>
            <!-- Last -->
            <li class="page-item <?= $page === $totalPages ? 'disabled' : '' ?>">
              <a class="page-link" href="?page=<?= $totalPages ?><?= $qs ?>" title="หน้าสุดท้าย">
                <i class="bi bi-chevron-double-right"></i>
              </a>
            </li>

          </ul>
        </nav>
      </div>
      <?php endif; ?>

  </div>
</div>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/handsontable@14/dist/handsontable.full.min.js"></script>
<script>
// ── PHP data → JS ────────────────────────────────────
const tableData  = <?= json_encode(array_values($rows), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
const CSRF_TOKEN = '<?= csrf_token() ?>';

// ── helpers ──────────────────────────────────────────
const esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');

function getInitials(name) {
  if (!name) return 'N/A';
  const w = name.trim().split(/\s+/).filter(Boolean);
  return w.length >= 2
    ? (w[0][0] + w[1][0]).toUpperCase()
    : name.trim().substring(0, 2).toUpperCase();
}

// ── HOT source data ──────────────────────────────────
const hotData = tableData.map(r => ({
  selected:     r.is_selected == 1,
  company:      r.company      || '',
  contact:      r.contact      || '',
  position:     r.position     || '',
  address:      r.address      || '',
  ems:          r.ems          || '',
  billing_note: r.billing_note || '',
  id:           r.id
}));

// ── Custom renderers ─────────────────────────────────
function companyRenderer(hot, TD, row) {
  const d = hotData[row];
  if (!d) return TD;
  TD.innerHTML = `<div class="d-flex align-items-center gap-2 py-1">
    <span class="co-avatar">${esc(getInitials(d.company))}</span>
    <span class="co-name">${esc(d.company)}</span>
  </div>`;
  return TD;
}

function contactRenderer(hot, TD, row) {
  const d = hotData[row];
  if (!d) return TD;
  TD.innerHTML = `<div class="co-name">${esc(d.contact)}</div>
    ${d.position ? `<div class="co-detail">${esc(d.position)}</div>` : ''}`;
  return TD;
}

function addressRenderer(hot, TD, row) {
  const d = hotData[row];
  if (!d) return TD;
  TD.innerHTML = `<div class="co-detail hot-addr">${esc(d.address)}</div>`;
  return TD;
}

function emsRenderer(hot, TD, row, col, prop, value) {
  TD.innerHTML = value
    ? `<span class="tag-ems">${esc(value)}</span>`
    : `<span class="co-detail">—</span>`;
  return TD;
}

function noteRenderer(hot, TD, row, col, prop, value) {
  TD.innerHTML = value
    ? `<span class="tag-billing">${esc(value)}</span>`
    : `<span class="co-detail">—</span>`;
  return TD;
}

function actionsRenderer(hot, TD, row, col, prop, value) {
  TD.innerHTML = `
    <div class="d-flex gap-1">
      <a href="edit.php?id=${value}" class="btn-act btn-act-edit" title="แก้ไข"><i class="bi bi-pencil"></i></a>
      <a href="print_single.php?id=${value}" target="_blank" class="btn-act btn-act-print" title="พิมพ์"><i class="bi bi-printer"></i></a>
      <button class="btn-act btn-act-delete" data-action="delete" data-id="${value}" title="ลบ"><i class="bi bi-trash"></i></button>
    </div>`;
  return TD;
}

// ── Init Handsontable ────────────────────────────────
const hot = new Handsontable(document.getElementById('hot-container'), {
  data:          hotData,
  licenseKey:    'non-commercial-and-evaluation',
  height:        'auto',
  rowHeaders:    false,
  colHeaders:    ['', 'บริษัท / ลูกค้า', 'ผู้ติดต่อ', 'ที่อยู่', 'ประเภท', 'หมายเหตุ', 'จัดการ'],
  rowHeights:    52,
  columns: [
    { data: 'selected',     type: 'checkbox',             width: 44 },
    { data: 'company',      renderer: companyRenderer,    readOnly: true, width: 315 },
    { data: 'contact',      renderer: contactRenderer,    readOnly: true, width: 200 },
    { data: 'address',      renderer: addressRenderer,    readOnly: true, width: 400 },
    { data: 'ems',          renderer: emsRenderer,        readOnly: true, width: 100 },
    { data: 'billing_note', renderer: noteRenderer,       readOnly: true, width: 130 },
    { data: 'id',           renderer: actionsRenderer,    readOnly: true, width: 30 },
  ],
  stretchH:               'last',
  manualColumnResize:     true,
  contextMenu:            false,
  copyPaste:              false,
  outsideClickDeselects:  true,
  cells(row) {
    return hotData[row]?.selected ? { className: 'hot-row-sel' } : {};
  },
  afterChange(changes, source) {
    if (!changes || source === 'external') return;
    changes.forEach(([row, prop, , newVal]) => {
      if (prop !== 'selected') return;
      fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', id: hotData[row].id, val: newVal ? 1 : 0 })
      });
      updateBadge();
    });
  },
});

// ── Delete via event delegation ──────────────────────
document.getElementById('hot-container').addEventListener('click', e => {
  const btn = e.target.closest('[data-action="delete"]');
  if (!btn) return;
  if (!confirm('ลบรายการนี้?')) return;
  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'delete', id: +btn.dataset.id, csrf: CSRF_TOKEN })
  }).then(r => r.json()).then(d => { if (d.ok) location.reload(); });
});

// ── Select all / none on page ────────────────────────
function selectAllPage(checked) {
  const changes = hotData.map((_, i) => [i, 'selected', checked]);
  hot.setDataAtRowProp(changes, 'external');
  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'toggle_many', ids: hotData.map(r => r.id), val: checked ? 1 : 0 })
  }).then(() => updateBadge());
}

// ── Sync badge + stat card ───────────────────────────
function updateBadge() {
  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'count' })
  })
  .then(r => r.json())
  .then(d => {
    const badge = document.getElementById('selectedBadge');
    if (badge) { badge.textContent = d.count + ' รายการ'; badge.style.display = d.count > 0 ? '' : 'none'; }
    const stat = document.getElementById('statSelected');
    if (stat) stat.textContent = d.count.toLocaleString();
  });
}
</script>
</body>
</html>
