<?php
// ============================================================
//  DCRS — Student Dashboard
//  File: pages/student/dashboard.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$studentId      = Session::userId();
$stats          = $complaintModel->getStats($studentId);
$recent         = $complaintModel->getByStudent($studentId, []);
$recent         = array_slice($recent, 0, 5);

renderLayout('Dashboard', 'Dashboard');
renderAlerts();
?>

<div class="page-header">
  <h2>Welcome back, <?= clean(Session::name()) ?> 👋</h2>
  <p>Here's your complaint activity overview</p>
</div>

<!-- Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;">📁</div>
    <div class="stat-body">
      <p class="stat-label">Total Filed</p>
      <p class="stat-value"><?= $stats['total'] ?? 0 ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fef3c7;">⏳</div>
    <div class="stat-body">
      <p class="stat-label">Pending</p>
      <p class="stat-value"><?= $stats['pending'] ?? 0 ?></p>
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
    </div>
  </div>
</div>

<!-- Recent + Quick Actions -->
<div class="grid-2">

  <!-- Recent Complaints -->
  <div class="card">
    <div class="card-header-row">
      <h5>Recent Complaints</h5>
      <a href="<?= APP_URL ?>/pages/student/my_complaints.php" class="btn btn-ghost btn-sm">View All</a>
    </div>

    <?php if (empty($recent)): ?>
      <div style="text-align:center;padding:2rem;color:var(--text-muted);">
        <p style="font-size:2rem;margin-bottom:8px;">📭</p>
        <p>No complaints yet.</p>
        <a href="<?= APP_URL ?>/pages/student/submit_complaint.php" class="btn btn-accent btn-sm" style="margin-top:8px;">Submit First Complaint</a>
      </div>
    <?php else: ?>
      <?php foreach ($recent as $c): ?>
        <div style="padding:10px 0;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
          <div>
            <p style="margin:0;font-size:13px;font-weight:600;color:var(--text);">
              <?= clean($c['title']) ?>
            </p>
            <p style="margin:0;font-size:11px;color:var(--text-muted);">
              <?= $c['complaint_code'] ?> · <?= $c['created_at'] ?>
            </p>
          </div>
          <?= statusBadge($c['status']) ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- Quick Actions + Progress -->
  <div>
    <div class="card" style="margin-bottom:14px;">
      <h5 style="margin:0 0 14px;font-size:15px;font-weight:700;">Quick Actions</h5>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <a href="<?= APP_URL ?>/pages/student/submit_complaint.php" class="btn btn-accent">
          📝 Submit New Complaint
        </a>
        <a href="<?= APP_URL ?>/pages/student/my_complaints.php?status=Pending" class="btn btn-outline">
          ⏳ View Pending Complaints
        </a>
        <a href="<?= APP_URL ?>/pages/student/my_complaints.php?status=Resolved" class="btn btn-ghost">
          ✅ View Resolved Complaints
        </a>
      </div>
    </div>

    <div class="card">
      <h5 style="margin:0 0 14px;font-size:15px;font-weight:700;">Status Breakdown</h5>
      <?php
      $statusMap = [
        'Pending'     => ['label'=>'Pending',     'color'=>'var(--warning)'],
        'Assigned'    => ['label'=>'Assigned',    'color'=>'var(--primary-light)'],
        'in_progress' => ['label'=>'In Progress', 'color'=>'var(--accent)'],
        'Resolved'    => ['label'=>'Resolved',    'color'=>'var(--success)'],
      ];
      $statKeys = [
        'Pending'     => $stats['pending']     ?? 0,
        'Assigned'    => $stats['assigned']    ?? 0,
        'in_progress' => $stats['in_progress'] ?? 0,
        'Resolved'    => $stats['resolved']    ?? 0,
      ];
      $total = max(1, $stats['total'] ?? 1);
      foreach ($statusMap as $key => $info):
        $count = $statKeys[$key];
        $pct   = round(($count / $total) * 100);
      ?>
        <div style="margin-bottom:12px;">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="font-size:12px;color:var(--text-sec);"><?= $info['label'] ?></span>
            <span style="font-size:12px;font-weight:700;color:<?= $info['color'] ?>;"><?= $count ?></span>
          </div>
          <div class="progress">
            <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $info['color'] ?>;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</div>

<?php
// Set notification URL for JS
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
