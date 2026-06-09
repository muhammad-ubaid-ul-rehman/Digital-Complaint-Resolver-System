<?php
// ============================================================
//  DCRS — All Complaints (Admin)
//  File: pages/admin/all_complaints.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$userModel      = new UserModel();

$filters = [
  'status'   => clean($_GET['status']   ?? ''),
  'priority' => clean($_GET['priority'] ?? ''),
  'category' => clean($_GET['category'] ?? ''),
  'search'   => clean($_GET['search']   ?? ''),
];
$filters     = array_filter($filters);
$complaints  = $complaintModel->getAll($filters);
$stats       = $complaintModel->getStats();
$resolvers   = $userModel->getResolvers();

renderLayout('All Complaints', 'All Complaints');
renderAlerts();
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
  <div>
    <h2>All Complaints</h2>
    <p><?= count($complaints) ?> of <?= $stats['total'] ?? 0 ?> total</p>
  </div>
  <a href="<?= APP_URL ?>/pages/admin/assign.php" class="btn btn-accent">
    👤 Go to Assign
  </a>
</div>

<!-- Filter Form -->
<div class="card" style="padding:1rem;">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
    <div style="flex:2;min-width:200px;">
      <input type="text" name="search" class="form-control"
             placeholder="🔍 Search title or code..."
             value="<?= $filters['search'] ?? '' ?>">
    </div>
    <div style="flex:1;min-width:140px;">
      <select name="status" class="form-control auto-filter">
        <option value="">All Status</option>
        <?php foreach (['Pending','Assigned','In Progress','Resolved','Closed'] as $s): ?>
          <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="flex:1;min-width:140px;">
      <select name="priority" class="form-control auto-filter">
        <option value="">All Priority</option>
        <?php foreach (['Low','Medium','High','Critical'] as $p): ?>
          <option value="<?= $p ?>" <?= ($filters['priority'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div style="flex:1;min-width:140px;">
      <select name="category" class="form-control auto-filter">
        <option value="">All Category</option>
        <?php foreach (['Academic','IT','Library','Cafeteria','Hostel','Transport','Finance','Other'] as $c): ?>
          <option value="<?= $c ?>" <?= ($filters['category'] ?? '') === $c ? 'selected' : '' ?>><?= $c ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Filter</button>
    <a href="<?= APP_URL ?>/pages/admin/all_complaints.php" class="btn btn-ghost">Clear</a>
  </form>
</div>

<!-- Table -->
<?php if (empty($complaints)): ?>
  <div class="card" style="text-align:center;padding:3rem;">
    <p style="font-size:2rem;margin-bottom:8px;">🔍</p>
    <p style="color:var(--text-muted);">No complaints match your filters.</p>
  </div>
<?php else: ?>
  <div class="card" style="padding:0;overflow:hidden;">
    <div class="table-wrapper">
      <table class="dcrs-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Title</th>
            <th>Student</th>
            <th>Resolver</th>
            <th>Category</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Progress</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($complaints as $c): ?>
            <tr>
              <td class="code"><?= $c['complaint_code'] ?></td>
              <td class="title-cell" style="max-width:200px;">
                <?= clean(substr($c['title'], 0, 40)) ?><?= strlen($c['title']) > 40 ? '...' : '' ?>
              </td>
              <td style="font-size:12px;"><?= clean($c['student_name']) ?></td>
              <td style="font-size:12px;color:var(--text-muted);">
                <?= $c['resolver_name'] ? clean($c['resolver_name']) : '<em>Unassigned</em>' ?>
              </td>
              <td><span style="font-size:11px;"><?= $c['category'] ?></span></td>
              <td><?= priorityBadge($c['priority']) ?></td>
              <td><?= statusBadge($c['status']) ?></td>
              <td style="min-width:90px;">
                <div class="progress" style="margin-bottom:2px;">
                  <div class="progress-bar bg-primary" style="width:<?= $c['progress'] ?>%;"></div>
                </div>
                <small style="font-size:10px;color:var(--text-muted);"><?= $c['progress'] ?>%</small>
              </td>
              <td style="font-size:11px;color:var(--text-muted);white-space:nowrap;">
                <?= substr($c['created_at'], 0, 10) ?>
              </td>
              <td>
                <div style="display:flex;gap:4px;">
                  <a href="<?= APP_URL ?>/pages/complaint_detail.php?id=<?= $c['complaint_id'] ?>"
                     class="btn btn-outline btn-sm" title="View">👁</a>
                  <?php if ($c['status'] === 'Pending'): ?>
                    <a href="<?= APP_URL ?>/pages/admin/assign.php?highlight=<?= $c['complaint_id'] ?>"
                       class="btn btn-accent btn-sm" title="Assign">👤</a>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php endif; ?>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
