<?php
// ============================================================
//  DCRS — Register Page
//  File: register.php
// ============================================================

require_once __DIR__ . '/backend/config/bootstrap.php';

if (Session::isLoggedIn()) redirect(APP_URL . '/login.php');

$error   = Session::getFlash('error');
$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | DCRS Portal</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/frontend/css/style.css">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #1a3a5c 0%, #0d2240 55%, #1a1a3e 100%);
      display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    .auth-box { width: 100%; max-width: 460px; }
    .auth-brand { text-align: center; margin-bottom: 24px; }
    .auth-brand h1 { color: #fff; font-size: 24px; font-weight: 900; margin: 0 0 4px; }
    .auth-brand p  { color: rgba(255,255,255,0.55); font-size: 13px; margin: 0; }
    .auth-card { background: #fff; border-radius: 16px; padding: 2rem; }
    .auth-card h2 { font-size: 17px; font-weight: 800; margin: 0 0 18px; }
    .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .auth-footer { text-align: center; margin-top: 14px; font-size: 13px; color: var(--text-muted); }
    .auth-footer a { color: var(--primary-light); font-weight: 600; }
  </style>
</head>
<body>
<div class="auth-box">

  <div class="auth-brand">
    <h1>🛡 DCRS Portal</h1>
    <p>Create your student account</p>
  </div>

  <div class="auth-card">
    <h2>Student Registration</h2>

    <?php if ($error):   echo "<div class='alert alert-error'>❌ $error</div>"; endif; ?>
    <?php if ($success): echo "<div class='alert alert-success'>✅ $success</div>"; endif; ?>

    <form action="<?= APP_URL ?>/backend/controllers/AuthController.php?action=register"
          method="POST">

      <div class="form-group">
        <label class="form-label">Full Name *</label>
        <input type="text" name="name" class="form-control"
               placeholder="Ali Hassan" required
               value="<?= clean($_POST['name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">University Email *</label>
        <input type="email" name="email" class="form-control"
               placeholder="s20200012@university.edu" required
               value="<?= clean($_POST['email'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">Department</label>
        <select name="department" class="form-control">
          <option value="">Select department...</option>
          <?php foreach (['Computer Science','Electrical Engineering','Business Administration',
                          'Mechanical Engineering','Civil Engineering','Mathematics',
                          'Physics','Chemistry','Other'] as $dept): ?>
            <option value="<?= $dept ?>"><?= $dept ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Phone (optional)</label>
        <input type="text" name="phone" class="form-control"
               placeholder="03xx-xxxxxxx"
               value="<?= clean($_POST['phone'] ?? '') ?>">
      </div>

      <div class="two-col">
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control"
                 placeholder="Min. 6 chars" required>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password *</label>
          <input type="password" name="confirm" class="form-control"
                 placeholder="Repeat password" required>
        </div>
      </div>

      <button type="submit" class="btn btn-accent btn-lg"
              style="width:100%;justify-content:center;">
        Create Account
      </button>
    </form>

    <p class="auth-footer">
      Already have an account? <a href="<?= APP_URL ?>/login.php">Sign in</a>
    </p>
  </div>

</div>
</body>
</html>
