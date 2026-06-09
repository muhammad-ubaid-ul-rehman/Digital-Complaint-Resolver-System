<?php
// ============================================================
//  DCRS — Assigned Complaints (Resolver)
//  File: pages/resolver/assigned.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$resolverId     = Session::userId();

$filterStatus = clean($_GET['status'] ?? '');
$filters      = $filterStatus ? ['status' => $filterStatus] : [];

$complaints = $complaintModel->getByResolver($resolverId, $filters);
$stats      = $complaintModel->getStats(null, $resolverId);

renderLayout('Assigned to Me', 'Assigned to Me');
renderAlerts();
?>

<div class="page-header">
  <h2>✅ Assigned to Me</h2>
  <p><?= count($complaints) ?> complaint(s) found</p>
</div>

<!-- Filter -->
<form method="GET" style="margin-bottom:16px;">
  <div class="filter-bar">
    <select name="status" class="form-control auto-filter" style="max-width:200px;">
      <option value="">All Status</option>
      <?php foreach (['Assigned','In Progress','Resolved'] as $s): ?>
        <option value="<?= $s ?>" <?= $filterStatus === $s ? 'selected' : '' ?>><?= $s ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-outline btn-sm">Filter</button>
    <?php if ($filterStatus): ?>
      <a href="<?= APP_URL ?>/pages/resolver/assigned.php" class="btn btn-ghost btn-sm">Clear</a>
    <?php endif; ?>
  </div>
</form>

<!-- Stats mini row -->
<div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;">
  <?php foreach (['assigned'=>['Assigned','var(--primary-light)'],'in_progress'=>['In Progress','var(--warning)'],'resolved'=>['Resolved','var(--success)']] as $key => [$label, $color]): ?>
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:8px;
                padding:8px 16px;display:flex;align-items:center;gap:8px;">
      <span style="font-size:18px;font-weight:800;color:<?= $color ?>;"><?= $stats[$key] ?? 0 ?></span>
      <span style="font-size:12px;color:var(--text-muted);"><?= $label ?></span>
    </div>
  <?php endforeach; ?>
</div>

<?php if (empty($complaints)): ?>
  <div class="card" style="text-align:center;padding:3rem;">
    <p style="font-size:2.5rem;margin-bottom:10px;">📭</p>
    <p style="color:var(--text-muted);">
      <?= $filterStatus ? "No $filterStatus complaints." : "No complaints assigned to you yet." ?>
    </p>
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

      <!-- Progress -->
      <div style="margin-bottom:10px;">
        <?php
        $barColor = match($c['priority']) {
          'Critical' => 'danger', 'High' => 'warning', 'Low' => 'success', default => 'primary'
        };
        ?>
        <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
          <span style="font-size:11px;color:var(--text-muted);">Progress</span>
          <span style="font-size:11px;font-weight:700;"><?= $c['progress'] ?>%</span>
        </div>
        <div class="progress">
          <div class="progress-bar bg-<?= $barColor ?>" style="width:<?= $c['progress'] ?>%;"></div>
        </div>
      </div>

      <?php if ($c['remarks']): ?>
        <div style="background:#f0f9f5;border-left:3px solid var(--success);
                    padding:7px 12px;border-radius:6px;margin-bottom:10px;">
          <p style="margin:0;font-size:12px;color:var(--text-sec);">
            <strong>Last remark:</strong> <?= clean($c['remarks']) ?>
          </p>
        </div>
      <?php endif; ?>

      <div class="complaint-footer">
        <span>🎓 <?= clean($c['student_name']) ?></span>
        <span>📅 <?= $c['created_at'] ?></span>
        <span>🔄 <?= $c['updated_at'] ?></span>
        <div style="margin-left:auto;display:flex;gap:8px;">
          <a href="<?= APP_URL ?>/pages/complaint_detail.php?id=<?= $c['complaint_id'] ?>"
             class="btn btn-outline btn-sm">👁 View</a>
          <?php if ($c['status'] !== 'Resolved'): ?>
            <a href="<?= APP_URL ?>/pages/resolver/update.php?id=<?= $c['complaint_id'] ?>"
               class="btn btn-accent btn-sm">✏️ Update</a>
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
