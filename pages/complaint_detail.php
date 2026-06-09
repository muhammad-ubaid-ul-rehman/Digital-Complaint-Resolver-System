<?php
// ============================================================
//  DCRS — Complaint Detail (Shared - All Roles)
//  File: pages/complaint_detail.php
// ============================================================

require_once __DIR__ . '/../frontend/partials/layout.php';

Session::requireLogin();

$complaintModel = new ComplaintModel();
$complaintId    = (int)($_GET['id'] ?? 0);

if (!$complaintId) {
    Session::flash('error', 'Invalid complaint ID.');
    redirect(APP_URL . '/login.php');
}

$complaint = $complaintModel->getById($complaintId);

if (!$complaint) {
    Session::flash('error', 'Complaint not found.');
    redirect(APP_URL . '/login.php');
}

// Access control
$role = Session::role();
$uid  = Session::userId();

if ($role === 'student'   && (int)$complaint['student_id']  !== $uid) redirect(APP_URL . '/unauthorized.php');
if ($role === 'resolver'  && (int)$complaint['resolver_id'] !== $uid) redirect(APP_URL . '/unauthorized.php');

$timeline = $complaintModel->getTimeline($complaintId);

// Back URL per role
$backUrl = match($role) {
    'admin'    => APP_URL . '/pages/admin/all_complaints.php',
    'resolver' => APP_URL . '/pages/resolver/assigned.php',
    default    => APP_URL . '/pages/student/my_complaints.php',
};

renderLayout('Complaint Detail', match($role) {
    'admin'    => 'All Complaints',
    'resolver' => 'Assigned to Me',
    default    => 'My Complaints',
});
renderAlerts();

$barColor = match($complaint['priority']) {
    'Critical' => 'danger', 'High' => 'warning', 'Low' => 'success', default => 'primary'
};
?>

<!-- Back button -->
<div style="margin-bottom:18px;">
  <a href="<?= $backUrl ?>" class="btn btn-ghost btn-sm">← Back</a>
</div>

<div class="grid-2" style="align-items:flex-start;">

  <!-- Left: Main detail -->
  <div>
    <div class="card" style="margin-bottom:14px;">

      <!-- Header -->
      <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div>
          <p style="margin:0;font-size:11px;color:var(--text-muted);font-weight:700;letter-spacing:0.5px;">
            <?= $complaint['complaint_code'] ?>
          </p>
          <h3 style="margin:4px 0 0;font-size:18px;font-weight:800;color:var(--text);">
            <?= clean($complaint['title']) ?>
          </h3>
        </div>
        <div style="display:flex;gap:6px;flex-wrap:wrap;">
          <?= statusBadge($complaint['status']) ?>
          <?= priorityBadge($complaint['priority']) ?>
        </div>
      </div>

      <!-- Meta info -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;
                  background:var(--bg);border-radius:8px;padding:12px;">
        <div>
          <p style="margin:0;font-size:11px;color:var(--text-muted);">Category</p>
          <p style="margin:2px 0 0;font-size:13px;font-weight:600;">📂 <?= $complaint['category'] ?></p>
        </div>
        <div>
          <p style="margin:0;font-size:11px;color:var(--text-muted);">Submitted By</p>
          <p style="margin:2px 0 0;font-size:13px;font-weight:600;">🎓 <?= clean($complaint['student_name']) ?></p>
        </div>
        <div>
          <p style="margin:0;font-size:11px;color:var(--text-muted);">Date Submitted</p>
          <p style="margin:2px 0 0;font-size:13px;font-weight:600;">📅 <?= $complaint['created_at'] ?></p>
        </div>
        <div>
          <p style="margin:0;font-size:11px;color:var(--text-muted);">Last Updated</p>
          <p style="margin:2px 0 0;font-size:13px;font-weight:600;">🔄 <?= $complaint['updated_at'] ?></p>
        </div>
        <?php if ($complaint['resolver_name']): ?>
          <div>
            <p style="margin:0;font-size:11px;color:var(--text-muted);">Assigned Resolver</p>
            <p style="margin:2px 0 0;font-size:13px;font-weight:600;">👤 <?= clean($complaint['resolver_name']) ?></p>
          </div>
          <div>
            <p style="margin:0;font-size:11px;color:var(--text-muted);">Resolver Dept</p>
            <p style="margin:2px 0 0;font-size:13px;font-weight:600;">🏢 <?= clean($complaint['resolver_dept'] ?? '—') ?></p>
          </div>
        <?php endif; ?>
        <?php if ($complaint['resolved_at']): ?>
          <div style="grid-column:1/-1;">
            <p style="margin:0;font-size:11px;color:var(--text-muted);">Resolved At</p>
            <p style="margin:2px 0 0;font-size:13px;font-weight:600;color:var(--success);">✅ <?= $complaint['resolved_at'] ?></p>
          </div>
        <?php endif; ?>
      </div>

      <!-- Description -->
      <div style="margin-bottom:16px;">
        <p style="margin:0 0 8px;font-size:12px;font-weight:700;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:0.5px;">Description</p>
        <p style="margin:0;font-size:14px;color:var(--text-sec);line-height:1.7;">
          <?= nl2br(clean($complaint['description'])) ?>
        </p>
      </div>

      <!-- Progress -->
      <div style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <p style="margin:0;font-size:12px;font-weight:700;color:var(--text-muted);
                    text-transform:uppercase;letter-spacing:0.5px;">Progress</p>
          <span style="font-size:14px;font-weight:800;color:var(--text);"><?= $complaint['progress'] ?>%</span>
        </div>
        <div class="progress" style="height:10px;">
          <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $complaint['progress'] ?>%;"></div>
        </div>
      </div>

      <!-- Remarks -->
      <?php if ($complaint['remarks']): ?>
        <div style="background:#f0f9f5;border-left:4px solid var(--success);
                    padding:12px 16px;border-radius:8px;">
          <p style="margin:0 0 6px;font-size:12px;font-weight:700;color:var(--success);
                    text-transform:uppercase;letter-spacing:0.5px;">Resolver Remarks</p>
          <p style="margin:0;font-size:14px;color:var(--text-sec);line-height:1.6;">
            <?= nl2br(clean($complaint['remarks'])) ?>
          </p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Action buttons per role -->
    <?php if ($role === 'admin' && $complaint['status'] === 'Pending'): ?>
      <div class="card">
        <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:var(--text-sec);">Admin Actions</p>
        <a href="<?= APP_URL ?>/pages/admin/assign.php?highlight=<?= $complaint['complaint_id'] ?>"
           class="btn btn-accent">👤 Assign this Complaint</a>
      </div>
    <?php endif; ?>

    <?php if ($role === 'resolver' && $complaint['status'] !== 'Resolved'): ?>
      <div class="card">
        <p style="margin:0 0 10px;font-size:13px;font-weight:700;color:var(--text-sec);">Update this Complaint</p>
        <a href="<?= APP_URL ?>/pages/resolver/update.php?id=<?= $complaint['complaint_id'] ?>"
           class="btn btn-accent">✏️ Update Progress</a>
      </div>
    <?php endif; ?>
  </div>

  <!-- Right: Timeline -->
  <div class="card">
    <h5 style="margin:0 0 18px;font-size:15px;font-weight:700;">Activity Timeline</h5>

    <?php if (empty($timeline)): ?>
      <p style="color:var(--text-muted);font-size:13px;">No activity recorded yet.</p>
    <?php else: ?>
      <ul class="timeline">
        <?php foreach (array_reverse($timeline) as $i => $t): ?>
          <li class="timeline-item">
            <div class="timeline-dot"
                 style="background:<?= $i === 0 ? 'var(--accent)' : 'var(--primary-light)' ?>;
                        width:<?= $i === 0 ? '14px' : '10px' ?>;
                        height:<?= $i === 0 ? '14px' : '10px' ?>;"></div>
            <div class="timeline-body">
              <p style="font-size:13px;<?= $i === 0 ? 'font-weight:700;' : '' ?>">
                <?= clean($t['update_text']) ?>
              </p>
              <?php if ($t['old_status'] && $t['new_status']): ?>
                <p style="margin:2px 0;font-size:11px;">
                  <span style="background:var(--bg);padding:1px 7px;border-radius:10px;color:var(--text-muted);">
                    <?= $t['old_status'] ?>
                  </span>
                  → 
                  <span style="background:var(--primary-xlt);padding:1px 7px;border-radius:10px;color:var(--primary-light);">
                    <?= $t['new_status'] ?>
                  </span>
                </p>
              <?php endif; ?>
              <small style="color:var(--text-muted);">
                👤 <?= clean($t['updated_by_name']) ?>
                (<?= $t['updater_role'] ?>) ·
                <?= $t['progress_percentage'] ?>% ·
                <?= $t['created_at'] ?>
              </small>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

</div>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
