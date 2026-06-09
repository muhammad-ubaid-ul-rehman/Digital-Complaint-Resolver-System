<?php
// ============================================================
//  DCRS — Layout Partials
//  File: frontend/partials/layout.php
//  Usage: include this file, then call layout functions
// ============================================================

require_once __DIR__ . '/../../backend/config/bootstrap.php';

// ── Build nav items per role ──────────────────────────────────
function getNavItems(): array {
    $role = Session::role();
    $base = APP_URL . '/pages';

    $nav = [
        'student' => [
            ['icon'=>'🏠','label'=>'Dashboard',         'url'=> $base.'/student/dashboard.php'],
            ['icon'=>'📝','label'=>'Submit Complaint',  'url'=> $base.'/student/submit_complaint.php'],
            ['icon'=>'📋','label'=>'My Complaints',     'url'=> $base.'/student/my_complaints.php'],
            ['icon'=>'🔔','label'=>'Notifications',     'url'=> $base.'/student/notifications.php'],
        ],
        'admin' => [
            ['icon'=>'🏠','label'=>'Dashboard',         'url'=> $base.'/admin/dashboard.php'],
            ['icon'=>'📁','label'=>'All Complaints',    'url'=> $base.'/admin/all_complaints.php'],
            ['icon'=>'👤','label'=>'Assign & Prioritize','url'=> $base.'/admin/assign.php'],
            ['icon'=>'📊','label'=>'Reports',           'url'=> $base.'/admin/reports.php'],
            ['icon'=>'👥','label'=>'Users',             'url'=> $base.'/admin/users.php'],
        ],
        'resolver' => [
            ['icon'=>'🏠','label'=>'Dashboard',         'url'=> $base.'/resolver/dashboard.php'],
            ['icon'=>'✅','label'=>'Assigned to Me',    'url'=> $base.'/resolver/assigned.php'],
        ],
    ];

    return $nav[$role] ?? [];
}

// ── Render HTML head ──────────────────────────────────────────
function renderHead(string $pageTitle = 'DCRS'): void {
    $appName = APP_NAME;
    $cssPath = APP_URL . '/frontend/css/style.css';
    echo <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{$pageTitle} | {$appName}</title>
      <link rel="stylesheet" href="{$cssPath}">
    </head>
    HTML;
}

// ── Render sidebar + header open tags ─────────────────────────
function renderLayout(string $pageTitle, string $activePage): void {
    Session::requireLogin();
    $user      = Session::get('name');
    $role      = Session::role();
    $navItems  = getNavItems();
    $notifModel= new NotificationModel();
    $unread    = $notifModel->unreadCount(Session::userId());
    $initials  = strtoupper(implode('', array_map(fn($w)=>$w[0], explode(' ', trim($user)))));
    $initials  = substr($initials, 0, 2);
    $logoutUrl = APP_URL . '/backend/controllers/AuthController.php?action=logout';

    renderHead($pageTitle);

    echo <<<HTML
    <body>
    <div class="app-wrapper">

      <!-- SIDEBAR -->
      <aside class="sidebar">
        <div class="sidebar-brand">
          <div class="brand-icon">🛡</div>
          <div class="brand-text">
            <h1>DCRS</h1>
            <span>University Portal</span>
          </div>
        </div>
        <div class="sidebar-user">
          <div class="avatar">{$initials}</div>
          <div class="user-info">
            <p>{$user}</p>
            <span>{$role}</span>
          </div>
        </div>
        <nav class="sidebar-nav">
    HTML;

    foreach ($navItems as $item) {
        $activeClass = ($item['label'] === $activePage) ? ' active' : '';
        echo <<<HTML
          <a href="{$item['url']}" class="{$activeClass}">
            <span class="nav-icon">{$item['icon']}</span>
            <span>{$item['label']}</span>
          </a>
        HTML;
    }

    echo <<<HTML
        </nav>
        <div class="sidebar-footer">
          <a href="{$logoutUrl}" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center;">
            🚪 Logout
          </a>
        </div>
      </aside>

      <!-- MAIN CONTENT -->
      <div class="main-content">

        <!-- TOP HEADER -->
        <header class="top-header">
          <div class="header-breadcrumb">
            <span style="text-transform:capitalize">{$role}</span> /
            <span>{$pageTitle}</span>
          </div>
          <div class="header-actions">

            <!-- Notification Bell -->
            <div class="notif-wrapper">
              <button class="notif-btn" id="notifBtn" title="Notifications">
                🔔
                <span class="notif-badge" id="notifCount" style="display:{'.$unread>0?'flex':'none'.'}">
                  {$unread}
                </span>
              </button>
              <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                  <h6>Notifications</h6>
                  <button class="btn btn-ghost btn-sm" id="markAllRead">Mark all read</button>
                </div>
                <div class="notif-list" id="notifList">
                  <div style="padding:1rem;text-align:center;color:var(--text-muted);font-size:13px;">
                    Loading...
                  </div>
                </div>
              </div>
            </div>

            <!-- User info -->
            <div class="d-flex align-center gap-8">
              <div style="width:30px;height:30px;border-radius:50%;background:var(--primary-light);
                          color:#fff;display:flex;align-items:center;justify-content:center;
                          font-size:12px;font-weight:700;">{$initials}</div>
              <span style="font-size:13px;font-weight:600;">{$user}</span>
            </div>

          </div>
        </header>

        <!-- PAGE CONTENT -->
        <main class="page-content">
    HTML;
}

// ── Render flash alerts ───────────────────────────────────────
function renderAlerts(): void {
    $success = Session::getFlash('success');
    $error   = Session::getFlash('error');
    $warning = Session::getFlash('warning');

    if ($success) echo "<div class='alert alert-success'>✅ {$success}</div>";
    if ($error)   echo "<div class='alert alert-error'>❌ {$error}</div>";
    if ($warning) echo "<div class='alert alert-warning'>⚠️ {$warning}</div>";
}

// ── Close layout ──────────────────────────────────────────────
function renderLayoutEnd(): void {
    $jsPath = APP_URL . '/frontend/js/app.js';
    echo <<<HTML
        </main>
      </div><!-- .main-content -->
    </div><!-- .app-wrapper -->
    <script src="{$jsPath}"></script>
    </body>
    </html>
    HTML;
}
