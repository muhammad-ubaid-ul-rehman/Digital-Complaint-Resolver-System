<?php
// ============================================================
//  DCRS — Admin Dashboard
//  File: pages/admin/dashboard.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$userModel      = new UserModel();

$stats         = $complaintModel->getStats();
$unassigned    = $complaintModel->getUnassigned();
$recent        = $complaintModel->getAll([]);
$recent        = array_slice($recent, 0, 6);
$resolverStats = $userModel->resolverStats();

renderLayout('Dashboard', 'Dashboard');
renderAlerts();

$total    = max(1, $stats['total'] ?? 1);
$resolved = $stats['resolved'] ?? 0;
$rateStr  = round(($resolved / $total) * 100) . '%';
?>

<div class="page-header">
  <h2>Admin Dashboard</h2>
  <p>System-wide complaint overview</p>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;">📁</div>
    <div class="stat-body">
      <p class="stat-label">Total</p>
      <p class="stat-value"><?= $stats['total'] ?? 0 ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fef3c7;">⏳</div>
    <div class="stat-body">
      <p class="stat-label">Pending</p>
      <p class="stat-value"><?= $stats['pending'] ?? 0 ?></p>
      <p class="stat-sub">Need assignment</p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#ffedd5;">🔄</div>
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
      <p class="stat-sub"><?= $rateStr ?> rate</p>
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

  <!-- Unassigned complaints -->
  <div class="card">
    <div class="card-header-row">
      <h5>⚠️ Needs Assignment (<?= count($unassigned) ?>)</h5>
      <a href="<?= APP_URL ?>/pages/admin/assign.php" class="btn btn-accent btn-sm">Assign All</a>
    </div>
    <?php if (empty($unassigned)): ?>
      <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:1rem;">
        ✅ All complaints are assigned!
      </p>
    <?php else: ?>
      <?php foreach (array_slice($unassigned, 0, 5) as $c): ?>
        <div style="padding:9px 0;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
          <div>
            <p style="margin:0;font-size:13px;font-weight:600;"><?= clean($c['title']) ?></p>
            <p style="margin:0;font-size:11px;color:var(--text-muted);">
              <?= $c['complaint_code'] ?> · <?= clean($c['student_name']) ?>
            </p>
          </div>
          <?= priorityBadge($c['priority']) ?>
        </div>
      <?php endforeach; ?>
      <?php if (count($unassigned) > 5): ?>
        <p style="margin:8px 0 0;font-size:12px;color:var(--text-muted);">
          +<?= count($unassigned) - 5 ?> more...
          <a href="<?= APP_URL ?>/pages/admin/assign.php">View all</a>
        </p>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <!-- Resolver performance -->
  <div class="card">
    <div class="card-header-row">
      <h5>👥 Resolver Performance</h5>
      <a href="<?= APP_URL ?>/pages/admin/reports.php" class="btn btn-ghost btn-sm">Full Report</a>
    </div>
    <?php foreach ($resolverStats as $r): ?>
      <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid var(--border);">
        <div style="width:34px;height:34px;border-radius:50%;background:var(--primary-xlt);
                    color:var(--primary-light);display:flex;align-items:center;justify-content:center;
                    font-size:12px;font-weight:700;flex-shrink:0;">
          <?= strtoupper(substr($r['name'], 0, 2)) ?>
        </div>
        <div style="flex:1;min-width:0;">
          <p style="margin:0;font-size:13px;font-weight:600;"><?= clean($r['name']) ?></p>
          <div style="margin-top:4px;">
            <div class="progress">
              <div class="progress-bar bg-success" style="width:<?= $r['resolution_rate'] ?? 0 ?>%;"></div>
            </div>
          </div>
        </div>
        <div style="text-align:right;flex-shrink:0;">
          <p style="margin:0;font-size:13px;font-weight:700;color:var(--success);">
            <?= $r['total_resolved'] ?>/<?= $r['total_assigned'] ?>
          </p>
          <p style="margin:0;font-size:11px;color:var(--text-muted);"><?= $r['resolution_rate'] ?? 0 ?>%</p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

</div>

<!-- Recent complaints table -->
<div class="card">
  <div class="card-header-row">
    <h5>Recent Complaints</h5>
    <a href="<?= APP_URL ?>/pages/admin/all_complaints.php" class="btn btn-ghost btn-sm">View All</a>
  </div>
  <div class="table-wrapper">
    <table class="dcrs-table">
      <thead>
        <tr>
          <th>Code</th>
          <th>Title</th>
          <th>Student</th>
          <th>Category</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Progress</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent as $c): ?>
          <tr>
            <td class="code"><?= $c['complaint_code'] ?></td>
            <td class="title-cell">
              <?= clean(substr($c['title'], 0, 45)) ?><?= strlen($c['title']) > 45 ? '...' : '' ?>
            </td>
            <td><?= clean($c['student_name']) ?></td>
            <td><?= $c['category'] ?></td>
            <td><?= priorityBadge($c['priority']) ?></td>
            <td><?= statusBadge($c['status']) ?></td>
            <td style="min-width:100px;">
              <div class="progress">
                <div class="progress-bar bg-primary" style="width:<?= $c['progress'] ?>%;"></div>
              </div>
              <small><?= $c['progress'] ?>%</small>
            </td>
            <td>
              <a href="<?= APP_URL ?>/pages/complaint_detail.php?id=<?= $c['complaint_id'] ?>"
                 class="btn btn-outline btn-sm">View</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
