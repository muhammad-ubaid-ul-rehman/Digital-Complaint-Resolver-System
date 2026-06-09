<?php
// ============================================================
//  DCRS — Bootstrap (include this at top of every PHP file)
//  File: backend/config/bootstrap.php
// ============================================================

// Error display: off in production, on in dev
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Autoload all config files
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/session.php';

// Autoload all models
$models = glob(__DIR__ . '/../models/*.php');
foreach ($models as $model) {
    require_once $model;
}

// Autoload all helpers
$helpers = glob(__DIR__ . '/../helpers/*.php');
foreach ($helpers as $helper) {
    require_once $helper;
}

// Start session
Session::start();
