<?php
// ============================================================
//  DCRS — Database Configuration
//  File: backend/config/database.php
// ============================================================

define('DB_HOST',     'localhost');
define('DB_NAME',     'dcrs_db');
define('DB_USER',     'root');
define('DB_PASS',     '');          // Change for production
define('DB_CHARSET',  'utf8mb4');

define('APP_NAME',    'DCRS Portal');
define('APP_URL',     'http://localhost/dcrs');
define('APP_VERSION', '1.0.0');

// Session settings
define('SESSION_LIFETIME', 3600);   // 1 hour
define('SESSION_NAME',     'DCRS_SESSION');

// Password settings
define('BCRYPT_COST', 10);
