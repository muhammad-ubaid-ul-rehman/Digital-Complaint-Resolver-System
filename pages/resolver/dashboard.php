<?php
// ============================================================
//  DCRS — Resolver Dashboard
//  File: pages/resolver/dashboard.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$resolverId     = Session::userId();
$stats          = $complaintModel->getStats(null, $resolverId);
$assigned       = $complaintModel->getByResolver($resolverId, []);
$pending        = array_filter($assigned, fn($c) => $c['status'] !== 'Resolved');
$recent         = array_slice($assigned, 0, 5);

renderLayout('Dashboard', 'Dashboard');
renderAlerts();
?>

<div class="page-header">
  <h2>Welcome, <?= clean(Session::name()) ?> 👋</h2>
  <p>Your assigned complaints overview</p>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;">📋</div>
    <div class="stat-body">
      <p class="stat-label">Total Assigned</p>
      <p class="stat-value"><?= $stats['total'] ?? 0 ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fef3c7;">🔄</div>
    <div class="stat-body">
      <p class="stat-label">In Progress</p>
      <p class="stat-value"><?= ($stats['assigned'] ?? 0) + ($stats['in_progress'] ?? 0) ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#d1fae5;">✅</div>
    <div class="stat-body">
      <p class="stat-label">Resolved</p>
      <p class="stat-value"><?= $stats['resolved'] ?? 0 ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fee2e2;">🔴</div>
    <div class="stat-body">
      <p class="stat-label">Critical</p>
      <p class="stat-value"><?= $stats['critical'] ?? 0 ?></p>
    </div>
  </div>
</div>

<div class="grid-2">

  <!-- Pending Work -->
  <div class="card">
    <div class="card-header-row">
      <h5>⚠️ Pending Work (<?= count($pending) ?>)</h5>
      <a href="<?= APP_URL ?>/pages/resolver/assigned.php" class="btn btn-ghost btn-sm">View All</a>
    </div>

    <?php if (empty($pending)): ?>
      <div style="text-align:center;padding:1.5rem;color:var(--text-muted);">
        <p style="font-size:1.5rem;margin-bottom:6px;">🎉</p>
        <p style="font-size:13px;">No pending complaints. Great work!</p>
      </div>
    <?php else: ?>
      <?php foreach (array_slice($pending, 0, 5) as $c): ?>
        <div style="padding:10px 0;border-bottom:1px solid var(--border);">
          <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:6px;">
            <div style="flex:1;min-width:0;">
              <p style="margin:0;font-size:13px;font-weight:600;color:var(--text);">
                <?= clean(substr($c['title'], 0, 50)) ?>...
              </p>
              <p style="margin:0;font-size:11px;color:var(--text-muted);">
                <?= $c['complaint_code'] ?> · 🎓 <?= clean($c['student_name']) ?>
              </p>
            </div>
            <div style="display:flex;gap:4px;flex-shrink:0;margin-left:8px;">
              <?= priorityBadge($c['priority']) ?>
            </div>
          </div>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="flex:1;">
              <div class="progress">
                <div class="progress-bar bg-<?= $c['priority'] === 'Critical' ? 'danger' : ($c['priority'] === 'High' ? 'warning' : 'primary') ?>"
                     style="width:<?= $c['progress'] ?>%;"></div>
              </div>
            </div>
            <span style="font-size:11px;color:var(--text-muted);flex-shrink:0;"><?= $c['progress'] ?>%</span>
            <a href="<?= APP_URL ?>/pages/resolver/update.php?id=<?= $c['complaint_id'] ?>"
               class="btn btn-accent btn-sm" style="flex-shrink:0;">Update</a>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Performance Summary -->
  <div>
    <div class="card" style="margin-bottom:14px;">
      <h5 style="margin:0 0 16px;font-size:15px;font-weight:700;">My Performance</h5>
      <?php
      $total   = max(1, $stats['total'] ?? 1);
      $resRate = round((($stats['resolved'] ?? 0) / $total) * 100);
      $avgProg = $stats['avg_progress'] ?? 0;
      ?>
      <div style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <span style="font-size:13px;color:var(--text-sec);">Resolution Rate</span>
          <span style="font-size:14px;font-weight:800;color:var(--success);"><?= $resRate ?>%</span>
        </div>
        <div class="progress" style="height:10px;">
          <div class="progress-bar bg-success" style="width:<?= $resRate ?>%;"></div>
        </div>
      </div>
      <div style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <span style="font-size:13px;color:var(--text-sec);">Avg Progress</span>
          <span style="font-size:14px;font-weight:800;color:var(--primary-light);"><?= $avgProg ?>%</span>
        </div>
        <div class="progress" style="height:10px;">
          <div class="progress-bar bg-primary" style="width:<?= $avgProg ?>%;"></div>
        </div>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding-top:10px;border-top:1px solid var(--border);">
        <div style="text-align:center;">
          <p style="margin:0;font-size:22px;font-weight:800;color:var(--text);"><?= $stats['resolved'] ?? 0 ?></p>
          <p style="margin:0;font-size:11px;color:var(--text-muted);">Resolved</p>
        </div>
        <div style="text-align:center;">
          <p style="margin:0;font-size:22px;font-weight:800;color:var(--warning);"><?= count($pending) ?></p>
          <p style="margin:0;font-size:11px;color:var(--text-muted);">Pending</p>
        </div>
      </div>
    </div>

    <div class="card">
      <h5 style="margin:0 0 14px;font-size:15px;font-weight:700;">Quick Actions</h5>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <a href="<?= APP_URL ?>/pages/resolver/assigned.php" class="btn btn-primary">
          📋 View All Assigned
        </a>
        <a href="<?= APP_URL ?>/pages/resolver/assigned.php?status=In+Progress" class="btn btn-outline">
          🔄 In Progress Only
        </a>
        <a href="<?= APP_URL ?>/pages/resolver/assigned.php?status=Resolved" class="btn btn-ghost">
          ✅ Resolved Complaints
        </a>
      </div>
    </div>
  </div>

</div>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
