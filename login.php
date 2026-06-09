<?php
// ============================================================
//  DCRS — Login Page
//  File: login.php  (place in project root: laragon/www/dcrs/)
// ============================================================

require_once __DIR__ . '/backend/config/bootstrap.php';

// Redirect if already logged in
if (Session::isLoggedIn()) {
    $role = Session::role();
    $map  = [
        'admin'    => APP_URL . '/pages/admin/dashboard.php',
        'resolver' => APP_URL . '/pages/resolver/dashboard.php',
        'student'  => APP_URL . '/pages/student/dashboard.php',
    ];
    redirect($map[$role] ?? APP_URL . '/login.php');
}

$error   = Session::getFlash('error');
$success = Session::getFlash('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | DCRS Portal</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/frontend/css/style.css">
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(135deg, #1a3a5c 0%, #0d2240 55%, #1a1a3e 100%);
      display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    .auth-box {
      width: 100%; max-width: 420px;
    }
    .auth-brand {
      text-align: center; margin-bottom: 28px;
    }
    .auth-brand .logo {
      width: 64px; height: 64px; border-radius: 16px;
      background: rgba(255,255,255,0.1);
      border: 2px solid rgba(255,255,255,0.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 30px; margin: 0 auto 14px;
    }
    .auth-brand h1 { color: #fff; font-size: 26px; font-weight: 900; margin: 0 0 5px; }
    .auth-brand p  { color: rgba(255,255,255,0.55); font-size: 13px; margin: 0; }
    .auth-card {
      background: #fff; border-radius: 16px; padding: 2rem;
    }
    .auth-card h2 { font-size: 17px; font-weight: 800; color: var(--text); margin: 0 0 18px; }
    .demo-accounts { margin-top: 20px; }
    .demo-accounts p { font-size: 11px; color: var(--text-muted); text-align: center; margin-bottom: 10px; }
    .demo-grid { display: flex; gap: 8px; }
    .demo-btn {
      flex: 1; padding: 8px 6px; border-radius: 8px; cursor: pointer;
      text-align: center; border: 2px solid; background: transparent;
      transition: background 0.15s;
    }
    .demo-btn .demo-icon { font-size: 18px; display: block; margin-bottom: 2px; }
    .demo-btn .demo-label { font-size: 11px; font-weight: 700; display: block; }
    .demo-btn.student { border-color: #dbeafe; color: #1d4ed8; }
    .demo-btn.student:hover { background: #dbeafe; }
    .demo-btn.admin    { border-color: #fee2e2; color: #991b1b; }
    .demo-btn.admin:hover    { background: #fee2e2; }
    .demo-btn.resolver { border-color: #d1fae5; color: #065f46; }
    .demo-btn.resolver:hover { background: #d1fae5; }
    .auth-footer { text-align: center; margin-top: 14px; font-size: 13px; color: var(--text-muted); }
    .auth-footer a { color: var(--primary-light); font-weight: 600; }
  </style>
</head>
<body>

<div class="auth-box">

  <div class="auth-brand">
    <div class="logo">🛡</div>
    <h1>DCRS Portal</h1>
    <p>Digital Complaint Resolver System</p>
  </div>

  <div class="auth-card">
    <h2>Sign in to your account</h2>

    <?php if ($error):   echo "<div class='alert alert-error'>❌ $error</div>"; endif; ?>
    <?php if ($success): echo "<div class='alert alert-success'>✅ $success</div>"; endif; ?>

    <form action="<?= APP_URL ?>/backend/controllers/AuthController.php?action=login"
          method="POST">

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input type="email" id="email" name="email" class="form-control"
               placeholder="your@university.edu" required autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input type="password" id="password" name="password" class="form-control"
               placeholder="••••••••" required autocomplete="current-password">
      </div>

      <button type="submit" class="btn btn-primary btn-lg"
              style="width:100%;justify-content:center;margin-top:4px;">
        Sign In →
      </button>
    </form>

    <div class="demo-accounts">
      <p>— Quick Demo Access —</p>
      <div class="demo-grid">
        <button class="demo-btn student" onclick="fillDemo('ali@university.edu')">
          <span class="demo-icon">🎓</span>
          <span class="demo-label">Student</span>
        </button>
        <button class="demo-btn admin" onclick="fillDemo('admin@university.edu')">
          <span class="demo-icon">🛡</span>
          <span class="demo-label">Admin</span>
        </button>
        <button class="demo-btn resolver" onclick="fillDemo('fatima@university.edu')">
          <span class="demo-icon">🔧</span>
          <span class="demo-label">Resolver</span>
        </button>
      </div>
    </div>

    <p class="auth-footer">
      Don't have an account? <a href="<?= APP_URL ?>/register.php">Register here</a>
    </p>
  </div>

</div>

<script>
function fillDemo(email) {
  document.getElementById('email').value    = email;
  document.getElementById('password').value = 'password';
}
</script>
</body>
</html>
