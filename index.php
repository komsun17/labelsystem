<?php
// ============================================
// index.php - Label Print System (Main Page)
// Ricoh IM C2010 | Label A15 (100x150mm)
// ============================================
require_once 'includes/config.php';
auth_check();

$db = getDB();

// ── Search & Pagination ──────────────────────────────
$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = ITEMS_PER_PAGE;
$offset  = ($page - 1) * $limit;

$where  = "WHERE (company != '' OR contact != '' OR address != '')";
$params = [];

if ($search !== '') {
    if (mb_strlen($search) >= 3) {
        // FULLTEXT boolean mode — prefix each word with * for partial matching
        $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
        $ft    = implode(' ', array_map(
            fn($t) => preg_replace('/[+\-><()~*"@]/', '', $t) . '*',
            $terms
        ));
        $where .= " AND MATCH(company, contact, address) AGAINST (:ft IN BOOLEAN MODE)";
        $params[':ft'] = $ft;
    } else {
        $where  .= " AND (company LIKE :s OR contact LIKE :s2 OR address LIKE :s3)";
        $params[':s']  = "%$search%";
        $params[':s2'] = "%$search%";
        $params[':s3'] = "%$search%";
    }
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
    <div style="width:1px;height:20px;background:rgba(255,255,255,.2)"></div>
    <a href="logout.php" class="btn-nav btn-nav-clear" title="ออกจากระบบ"
       onclick="return confirm('ออกจากระบบ?')">
      <i class="bi bi-box-arrow-right"></i>
      <span class="d-none d-md-inline"><?= htmlspecialchars($_SESSION['login_user'] ?? '') ?></span>
    </a>
  </div>
</nav>

<!-- Sticky Action Bar -->
<div id="actionBar" class="action-bar" style="display:none">
  <span class="text-white fs-6">
    <i class="bi bi-check2-square me-2 text-warning"></i>
    เลือก <strong id="actionCount">0</strong> รายการ
  </span>
  <div class="d-flex gap-2">
    <a href="print_preview.php" target="_blank"
       class="btn btn-warning btn-sm fw-bold px-3">
      <i class="bi bi-printer-fill me-1"></i> พิมพ์ที่เลือก
    </a>
    <form method="post" class="d-inline"
          onsubmit="return confirm('ล้างรายการที่เลือกทั้งหมด?')">
      <input type="hidden" name="csrf_token"
             value="<?= htmlspecialchars(csrf_token()) ?>">
      <button type="submit" name="clear_all" value="1"
              class="btn btn-outline-light btn-sm px-3">
        <i class="bi bi-x-circle me-1"></i> ล้างทั้งหมด
      </button>
    </form>
  </div>
</div>

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
        <button class="btn-toolbar btn btn-success"
                data-bs-toggle="modal" data-bs-target="#addModal">
          <i class="bi bi-plus-lg me-1"></i>เพิ่มรายชื่อ
        </button>
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
        body: JSON.stringify({ action: 'toggle', id: hotData[row].id, val: newVal ? 1 : 0, csrf: CSRF_TOKEN })
      }).catch(() => console.error('toggle failed'));
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
  }).then(r => r.json()).then(d => {
    if (d.ok) location.reload();
    else alert('ลบไม่สำเร็จ: ' + (d.error ?? 'unknown error'));
  }).catch(() => alert('เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง'));
});

// ── Select all / none on page ────────────────────────
function selectAllPage(checked) {
  const changes = hotData.map((_, i) => [i, 'selected', checked]);
  hot.setDataAtRowProp(changes, 'external');
  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'toggle_many', ids: hotData.map(r => r.id), val: checked ? 1 : 0, csrf: CSRF_TOKEN })
  }).then(() => updateBadge()).catch(() => console.error('toggle_many failed'));
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
    updateActionBar(d.count);
  });
}

// ── Sticky Action Bar ────────────────────────────────
function updateActionBar(count) {
  const bar = document.getElementById('actionBar');
  const cnt = document.getElementById('actionCount');
  if (!bar) return;
  if (count > 0) {
    cnt.textContent = count;
    bar.style.display = 'flex';
    document.body.classList.add('has-action-bar');
  } else {
    bar.style.display = 'none';
    document.body.classList.remove('has-action-bar');
  }
}

// ── Initial state on page load ───────────────────────
updateActionBar(<?= (int)$selectedCount ?>);
// ── Add Modal ────────────────────────────────────────
function submitAddForm() {
  const form  = document.getElementById('addForm');
  const btn   = document.getElementById('addSubmitBtn');
  const err   = document.getElementById('addError');
  const company = form.company.value.trim();
  const address = form.address.value.trim();

  err.classList.add('d-none');

  if (!company && !address) {
    err.textContent = 'กรุณากรอกชื่อบริษัท หรือที่อยู่อย่างน้อยหนึ่งอย่าง';
    err.classList.remove('d-none');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก...';

  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action:       'add',
      csrf:         CSRF_TOKEN,
      company:      company,
      contact:      form.contact.value.trim(),
      position:     form.position.value.trim(),
      address:      address,
      ems:          form.ems.value,
      billing_note: form.billing_note.value,
    })
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) { location.reload(); return; }
    err.textContent = d.error ?? 'เกิดข้อผิดพลาด';
    err.classList.remove('d-none');
  })
  .catch(() => {
    err.textContent = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง';
    err.classList.remove('d-none');
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-save me-1"></i>บันทึก';
  });
}

document.getElementById('addModal').addEventListener('hidden.bs.modal', () => {
  document.getElementById('addForm').reset();
  document.getElementById('addError').classList.add('d-none');
});
</script>

<!-- ── Add Modal ──────────────────────────────────── -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:14px;overflow:hidden;border:none">

      <div class="modal-header border-0 py-3 px-4"
           style="background:linear-gradient(120deg,#4f46e5 0%,#7c3aed 100%)">
        <h5 class="modal-title text-white fw-bold" id="addModalLabel">
          <i class="bi bi-plus-circle me-2"></i>เพิ่มรายชื่อใหม่
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body p-4">
        <div id="addError" class="alert alert-danger py-2 d-none"></div>
        <form id="addForm" onsubmit="return false">

          <div class="mb-3">
            <label class="form-label">ชื่อบริษัท / Company <span class="text-danger">*</span></label>
            <input type="text" name="company" class="form-control" autofocus>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label">ชื่อผู้ติดต่อ / Contact</label>
              <input type="text" name="contact" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">ตำแหน่ง / Position</label>
              <input type="text" name="position" class="form-control">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">ที่อยู่ / Address <span class="text-danger">*</span></label>
            <textarea name="address" class="form-control" rows="3"></textarea>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">ประเภทจัดส่ง / EMS</label>
              <select name="ems" class="form-select">
                <option value="">-- ไม่ระบุ --</option>
                <option>EMS</option>
                <option>ลงทะเบียน</option>
                <option>พัสดุ</option>
                <option>ไปรษณีย์ธรรมดา</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">หมายเหตุ / Note</label>
              <select name="billing_note" class="form-select">
                <option value="">-- ไม่ระบุ --</option>
                <option>BILLING NOTE</option>
                <option>TAX INVOICE</option>
                <option>RECEIPT</option>
                <option>QUOTATION</option>
              </select>
            </div>
          </div>

        </form>
      </div>

      <div class="modal-footer border-top px-4 py-3" style="background:#fafbfd">
        <button type="button" class="btn btn-outline-secondary px-4"
                data-bs-dismiss="modal">ยกเลิก</button>
        <button type="button" id="addSubmitBtn" class="btn btn-primary px-4"
                onclick="submitAddForm()">
          <i class="bi bi-save me-1"></i>บันทึก
        </button>
      </div>

    </div>
  </div>
</div>

</body>
</html>
