<?php
// ============================================
// login.php - Authentication Page
// ============================================
require_once 'includes/config.php';

if (!empty($_SESSION['logged_in'])) {
    header('Location: index.php'); exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify()) {
        $error = 'คำขอไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
    } elseif (auth_login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '')) {
        header('Location: index.php'); exit;
    } else {
        $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>เข้าสู่ระบบ — <?= APP_NAME ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="css/style.css">
<style>
  body {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--bg);
  }
  .login-wrap { width: 100%; max-width: 400px; padding: 1rem; }

  .login-header {
    background: linear-gradient(120deg, #4f46e5 0%, #7c3aed 100%);
    padding: 2rem 1.5rem 1.75rem;
    text-align: center;
    border-radius: var(--radius) var(--radius) 0 0;
  }
  .login-icon {
    width: 56px; height: 56px;
    background: rgba(255,255,255,.18);
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-bottom: .75rem;
  }
  .input-group-text {
    background: #f8fafc;
    border-color: var(--border);
    color: var(--muted);
  }
  .input-group .form-control {
    border-left: none;
  }
  .input-group .form-control:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(99,102,241,.12);
  }
  .input-group:focus-within .input-group-text {
    border-color: #818cf8;
  }
</style>
</head>
<body>

<div class="login-wrap">
  <div class="main-card">

    <div class="login-header">
      <div class="login-icon">
        <i class="bi bi-printer-fill text-white"></i>
      </div>
      <div class="text-white fw-bold fs-5 mb-1"><?= APP_NAME ?></div>
      <div style="color:rgba(255,255,255,.6);font-size:.75rem">
        Ricoh IM C2010 &bull; Label 50&times;80 mm
      </div>
    </div>

    <div class="card-body p-4">

      <?php if ($error): ?>
      <div class="alert alert-danger py-2 mb-3 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-circle-fill"></i>
        <?= htmlspecialchars($error) ?>
      </div>
      <?php endif; ?>

      <form method="post" autocomplete="on">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="mb-3">
          <label class="form-label">ชื่อผู้ใช้</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" name="username" class="form-control"
                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                   autocomplete="username" autofocus>
          </div>
        </div>

        <div class="mb-4">
          <label class="form-label">รหัสผ่าน</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" class="form-control"
                   autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
          <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
        </button>
      </form>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
