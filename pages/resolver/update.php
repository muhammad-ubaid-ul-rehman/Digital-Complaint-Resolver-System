<?php
// ============================================================
//  DCRS — Update Complaint Progress (Resolver)
//  File: pages/resolver/update.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

Session::requireRole('resolver');

$complaintModel = new ComplaintModel();
$complaintId    = (int)($_GET['id'] ?? 0);

if (!$complaintId) {
    Session::flash('error', 'Invalid complaint ID.');
    redirect(APP_URL . '/pages/resolver/assigned.php');
}

$complaint = $complaintModel->getById($complaintId);

if (!$complaint || $complaint['resolver_id'] !== Session::userId()) {
    Session::flash('error', 'Complaint not found or not assigned to you.');
    redirect(APP_URL . '/pages/resolver/assigned.php');
}

if ($complaint['status'] === 'Resolved') {
    Session::flash('error', 'This complaint is already resolved.');
    redirect(APP_URL . '/pages/resolver/assigned.php');
}

$timeline = $complaintModel->getTimeline($complaintId);

renderLayout('Update Progress', 'Assigned to Me');
renderAlerts();
?>

<div class="page-header">
  <h2>✏️ Update Complaint Progress</h2>
  <p><?= $complaint['complaint_code'] ?> — <?= clean($complaint['title']) ?></p>
</div>

<div class="grid-2" style="align-items:flex-start;">

  <!-- Left: Update Form -->
  <div>
    <div class="card">
      <h5 style="margin:0 0 16px;font-size:15px;font-weight:700;">Update Progress</h5>

      <!-- Complaint Info -->
      <div style="background:var(--bg);border-radius:8px;padding:12px;margin-bottom:18px;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
          <span class="badge badge-primary"><?= $complaint['complaint_code'] ?></span>
          <?= priorityBadge($complaint['priority']) ?>
          <?= statusBadge($complaint['status']) ?>
        </div>
        <p style="margin:0;font-size:14px;font-weight:600;color:var(--text);">
          <?= clean($complaint['title']) ?>
        </p>
        <p style="margin:4px 0 0;font-size:12px;color:var(--text-muted);">
          🎓 <?= clean($complaint['student_name']) ?> · 📂 <?= $complaint['category'] ?>
        </p>
      </div>

      <form method="POST"
            action="<?= APP_URL ?>/backend/controllers/ResolverController.php?action=update_progress">
        <input type="hidden" name="complaint_id" value="<?= $complaint['complaint_id'] ?>">

        <!-- Status -->
        <div class="form-group">
          <label class="form-label">New Status *</label>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
            <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid var(--border);
                          border-radius:8px;cursor:pointer;transition:border-color 0.15s;"
                   id="lbl-inprogress">
              <input type="radio" name="status" value="In Progress" required
                     onchange="document.getElementById('lbl-inprogress').style.borderColor='var(--warning)';
                               document.getElementById('lbl-resolved').style.borderColor='var(--border)';">
              <span>🔄 In Progress</span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid var(--border);
                          border-radius:8px;cursor:pointer;transition:border-color 0.15s;"
                   id="lbl-resolved">
              <input type="radio" name="status" value="Resolved"
                     onchange="document.getElementById('lbl-resolved').style.borderColor='var(--success)';
                               document.getElementById('lbl-inprogress').style.borderColor='var(--border)';
                               document.getElementById('progressSlider').value=100;
                               document.getElementById('progressLabel').textContent='100%';
                               document.getElementById('progressBar').style.width='100%';">
              <span>✅ Resolved</span>
            </label>
          </div>
        </div>

        <!-- Progress Slider -->
        <div class="form-group">
          <label class="form-label">
            Progress:
            <span id="progressLabel" style="color:var(--primary-light);font-weight:800;">
              <?= $complaint['progress'] ?>%
            </span>
          </label>
          <input type="range" name="progress" id="progressSlider"
                 class="progress-slider"
                 min="0" max="100" step="5"
                 value="<?= $complaint['progress'] ?>"
                 data-label="progressLabel"
                 data-bar="progressBar"
                 style="width:100%;accent-color:var(--primary-light);">
          <div class="progress" style="margin-top:6px;">
            <div class="progress-bar bg-primary" id="progressBar"
                 style="width:<?= $complaint['progress'] ?>%;"></div>
          </div>
        </div>

        <!-- Remarks -->
        <div class="form-group">
          <label class="form-label">Remarks / Update Notes *</label>
          <textarea name="remarks" class="form-control" rows="4" required
                    placeholder="Describe the steps taken, current status, and next actions..."><?= clean($complaint['remarks'] ?? '') ?></textarea>
          <small style="color:var(--text-muted);font-size:11px;">Required — will be visible to student</small>
        </div>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button type="submit" class="btn btn-accent btn-lg">
            💾 Save Update
          </button>
          <a href="<?= APP_URL ?>/pages/resolver/assigned.php" class="btn btn-ghost btn-lg">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- Right: Info + Timeline -->
  <div>
    <!-- Current State -->
    <div class="card" style="margin-bottom:14px;">
      <h5 style="margin:0 0 14px;font-size:14px;font-weight:700;">Current State</h5>

      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
          <span style="font-size:13px;color:var(--text-sec);">Current Progress</span>
          <span style="font-size:14px;font-weight:800;color:var(--primary-light);">
            <?= $complaint['progress'] ?>%
          </span>
        </div>
        <div class="progress" style="height:10px;">
          <div class="progress-bar bg-primary" style="width:<?= $complaint['progress'] ?>%;"></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;font-size:12px;">
        <div>
          <p style="margin:0;color:var(--text-muted);">Submitted</p>
          <p style="margin:0;font-weight:600;"><?= $complaint['created_at'] ?></p>
        </div>
        <div>
          <p style="margin:0;color:var(--text-muted);">Last Updated</p>
          <p style="margin:0;font-weight:600;"><?= $complaint['updated_at'] ?></p>
        </div>
        <div>
          <p style="margin:0;color:var(--text-muted);">Student</p>
          <p style="margin:0;font-weight:600;"><?= clean($complaint['student_name']) ?></p>
        </div>
        <div>
          <p style="margin:0;color:var(--text-muted);">Category</p>
          <p style="margin:0;font-weight:600;"><?= $complaint['category'] ?></p>
        </div>
      </div>

      <?php if ($complaint['description']): ?>
        <div style="margin-top:14px;padding-top:14px;border-top:1px solid var(--border);">
          <p style="margin:0 0 6px;font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;">
            Description
          </p>
          <p style="margin:0;font-size:13px;color:var(--text-sec);line-height:1.6;">
            <?= clean($complaint['description']) ?>
          </p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Timeline -->
    <div class="card">
      <h5 style="margin:0 0 16px;font-size:14px;font-weight:700;">Activity Timeline</h5>
      <?php if (empty($timeline)): ?>
        <p style="color:var(--text-muted);font-size:13px;">No activity yet.</p>
      <?php else: ?>
        <ul class="timeline">
          <?php foreach (array_reverse($timeline) as $i => $t): ?>
            <li class="timeline-item">
              <div class="timeline-dot"
                   style="background:<?= $i === 0 ? 'var(--accent)' : 'var(--primary-light)' ?>;"></div>
              <div class="timeline-body">
                <p><?= clean($t['update_text']) ?></p>
                <small>
                  <?= $t['updated_by_name'] ?> ·
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

</div>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
