<?php
// ============================================================
//  DCRS — My Complaints (Student)
//  File: pages/student/my_complaints.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$studentId      = Session::userId();

$filterStatus = clean($_GET['status'] ?? '');
$filters      = $filterStatus ? ['status' => $filterStatus] : [];

$complaints   = $complaintModel->getByStudent($studentId, $filters);
$stats        = $complaintModel->getStats($studentId);

renderLayout('My Complaints', 'My Complaints');
renderAlerts();
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
  <div>
    <h2>My Complaints</h2>
    <p><?= count($complaints) ?> complaint(s) found</p>
  </div>
  <a href="<?= APP_URL ?>/pages/student/submit_complaint.php" class="btn btn-accent">
    + New Complaint
  </a>
</div>

<!-- Filter Bar -->
<form method="GET" style="margin-bottom:16px;">
  <div class="filter-bar">
    <select name="status" class="form-control auto-filter" style="max-width:200px;">
      <option value="">All Status</option>
      <?php foreach (['Pending','Assigned','In Progress','Resolved','Closed'] as $s): ?>
        <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    <?php if ($filterStatus): ?>
      <a href="<?= APP_URL ?>/pages/student/my_complaints.php" class="btn btn-ghost btn-sm">Clear</a>
    <?php endif; ?>
  </div>
</form>

<!-- Complaints List -->
<?php if (empty($complaints)): ?>
  <div class="card" style="text-align:center;padding:3rem;">
    <p style="font-size:2.5rem;margin-bottom:10px;">📭</p>
    <p style="color:var(--text-muted);margin-bottom:16px;">
      <?= $filterStatus ? "No $filterStatus complaints found." : "You haven't submitted any complaints yet." ?>
    </p>
    <a href="<?= APP_URL ?>/pages/student/submit_complaint.php" class="btn btn-accent">
      Submit Your First Complaint
    </a>
  </div>

<?php else: ?>
  <?php foreach ($complaints as $c): ?>
    <div class="complaint-card">

      <div class="complaint-meta">
        <span class="complaint-code"><?= $c['complaint_code'] ?></span>
        <?= statusBadge($c['status']) ?>
        <?= priorityBadge($c['priority']) ?>
        <span style="font-size:11px;color:var(--text-muted);">📂 <?= $c['category'] ?></span>
      </div>

      <p class="complaint-title"><?= clean($c['title']) ?></p>

      <!-- Progress bar -->
      <div style="margin-bottom:10px;">
        <?php
        $barColor = match($c['priority']) {
          'Critical' => 'danger',
          'High'     => 'warning',
          'Low'      => 'success',
          default    => 'primary',
        };
        ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
          <span style="font-size:11px;color:var(--text-muted);">Progress</span>
          <span style="font-size:11px;font-weight:700;color:var(--text-sec);"><?= $c['progress'] ?>%</span>
        </div>
        <div class="progress">
          <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $c['progress'] ?>%;"></div>
        </div>
      </div>

      <?php if ($c['remarks']): ?>
        <div style="background:#f0f9f5;border-left:3px solid var(--success);padding:8px 12px;border-radius:6px;margin-bottom:10px;">
          <p style="margin:0;font-size:12px;font-weight:700;color:var(--success);margin-bottom:2px;">Resolver Remarks</p>
          <p style="margin:0;font-size:13px;color:var(--text-sec);"><?= clean($c['remarks']) ?></p>
        </div>
      <?php endif; ?>

      <div class="complaint-footer">
        <span>📅 Submitted: <?= $c['created_at'] ?></span>
        <?php if ($c['resolver_name']): ?>
          <span>👤 Resolver: <?= clean($c['resolver_name']) ?></span>
        <?php endif; ?>
        <span>🔄 Updated: <?= $c['updated_at'] ?></span>

        <div style="margin-left:auto;display:flex;gap:8px;">
          <a href="<?= APP_URL ?>/pages/complaint_detail.php?id=<?= $c['complaint_id'] ?>"
             class="btn btn-outline btn-sm">
            👁 View Details
          </a>
          <?php if ($c['status'] === 'Pending'): ?>
            <form method="POST"
                  action="<?= APP_URL ?>/backend/controllers/ComplaintController.php?action=delete"
                  style="display:inline;">
              <input type="hidden" name="complaint_id" value="<?= $c['complaint_id'] ?>">
              <button type="submit" class="btn btn-danger btn-sm"
                      data-confirm="Are you sure you want to delete this complaint?">
                🗑 Delete
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>

    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
