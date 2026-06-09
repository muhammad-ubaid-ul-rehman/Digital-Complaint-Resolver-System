<?php
// ============================================================
//  DCRS — Root Index Router
//  File: index.php  (place in laragon/www/dcrs/)
// ============================================================

require_once __DIR__ . '/backend/config/bootstrap.php';

if (!Session::isLoggedIn()) {
    redirect(APP_URL . '/login.php');
}

$role = Session::role();

$map = [
    'admin'    => APP_URL . '/pages/admin/dashboard.php',
    'resolver' => APP_URL . '/pages/resolver/dashboard.php',
    'student'  => APP_URL . '/pages/student/dashboard.php',
];

redirect($map[$role] ?? APP_URL . '/login.php');
