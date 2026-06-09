<?php
// ============================================================
//  DCRS — Unauthorized Page
//  File: unauthorized.php
// ============================================================

require_once __DIR__ . '/backend/config/bootstrap.php';

$backUrl = APP_URL . '/index.php';
if (Session::isLoggedIn()) {
    $map = [
        'admin'    => APP_URL . '/pages/admin/dashboard.php',
        'resolver' => APP_URL . '/pages/resolver/dashboard.php',
        'student'  => APP_URL . '/pages/student/dashboard.php',
    ];
    $backUrl = $map[Session::role()] ?? $backUrl;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Unauthorized | DCRS</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/frontend/css/style.css">
  <style>
    body {
      min-height:100vh;display:flex;align-items:center;
      justify-content:center;background:var(--bg);
    }
  </style>
</head>
<body>
  <div style="text-align:center;padding:2rem;">
    <p style="font-size:4rem;margin-bottom:12px;">🚫</p>
    <h1 style="font-size:24px;font-weight:900;margin:0 0 8px;">Access Denied</h1>
    <p style="color:var(--text-muted);margin-bottom:24px;">
      You don't have permission to access this page.
    </p>
    <a href="<?= $backUrl ?>" class="btn btn-primary">← Go to Dashboard</a>
  </div>
</body>
</html>
