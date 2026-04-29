/* ============================================
   app.js — Main Application Script
   Label Print System
   ============================================ */

// Toggle single checkbox → update DB via AJAX
document.querySelectorAll('.row-check').forEach(cb => {
  cb.addEventListener('change', function () {
    const id  = this.dataset.id;
    const val = this.checked ? 1 : 0;
    fetch('api.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'toggle', id, val })
    });
    document.getElementById('row-' + id)
            .classList.toggle('selected-row', this.checked);
    updateBadge();
  });
});

// Check-all on current page
document.getElementById('checkAll').addEventListener('change', function () {
  selectAllPage(this.checked);
});

function selectAllPage(checked) {
  const ids = [];
  document.querySelectorAll('.row-check').forEach(cb => {
    cb.checked = checked;
    ids.push(parseInt(cb.dataset.id));
    document.getElementById('row-' + cb.dataset.id)
            .classList.toggle('selected-row', checked);
  });
  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'toggle_many', ids, val: checked ? 1 : 0 })
  }).then(() => location.reload());
}

function updateBadge() {
  fetch('api.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'count' })
  })
    .then(r => r.json())
    .then(data => {
      const badge = document.getElementById('selectedBadge');
      if (badge) {
        badge.textContent = data.count + ' รายการ';
        badge.style.display = data.count > 0 ? '' : 'none';
      }
      const statSel = document.getElementById('statSelected');
      if (statSel) statSel.textContent = data.count.toLocaleString();
    });
}
