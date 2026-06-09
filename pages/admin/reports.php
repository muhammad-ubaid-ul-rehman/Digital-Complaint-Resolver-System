<?php
// ============================================================
//  DCRS — Reports (Admin)
//  File: pages/admin/reports.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$userModel      = new UserModel();

$stats         = $complaintModel->getStats();
$resolverStats = $userModel->resolverStats();
$allComplaints = $complaintModel->getAll([]);

// Category breakdown
$categories = [];
foreach ($allComplaints as $c) {
    $cat = $c['category'];
    if (!isset($categories[$cat])) {
        $categories[$cat] = ['total' => 0, 'resolved' => 0, 'pending' => 0];
    }
    $categories[$cat]['total']++;
    if ($c['status'] === 'Resolved') $categories[$cat]['resolved']++;
    if ($c['status'] === 'Pending')  $categories[$cat]['pending']++;
}
arsort($categories);

$total    = max(1, $stats['total'] ?? 1);
$resolved = $stats['resolved'] ?? 0;

renderLayout('Reports', 'Reports');
renderAlerts();
?>

<div class="page-header">
  <h2>📊 Reports & Analytics</h2>
  <p>System performance overview</p>
</div>

<!-- Summary Stats -->
<div class="stats-grid">
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;">📁</div>
    <div class="stat-body">
      <p class="stat-label">Total Complaints</p>
      <p class="stat-value"><?= $stats['total'] ?? 0 ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#fef3c7;">🔓</div>
    <div class="stat-body">
      <p class="stat-label">Open</p>
      <p class="stat-value"><?= ($stats['total'] ?? 0) - ($stats['resolved'] ?? 0) ?></p>
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
    <div class="stat-icon" style="background:#ede9fe;">📈</div>
    <div class="stat-body">
      <p class="stat-label">Resolution Rate</p>
      <p class="stat-value"><?= round(($resolved / $total) * 100) ?>%</p>
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

  <!-- Category Breakdown -->
  <div class="card">
    <h5 style="margin:0 0 16px;font-size:15px;font-weight:700;">Complaints by Category</h5>
    <?php foreach ($categories as $cat => $data): ?>
      <?php $pct = round(($data['total'] / $total) * 100); ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
          <span style="font-size:13px;color:var(--text-sec);font-weight:500;"><?= $cat ?></span>
          <div style="display:flex;gap:8px;align-items:center;">
            <span style="font-size:11px;color:var(--success);">✅ <?= $data['resolved'] ?></span>
            <span style="font-size:11px;color:var(--warning);">⏳ <?= $data['pending'] ?></span>
            <span style="font-size:12px;font-weight:700;color:var(--text);"><?= $data['total'] ?></span>
          </div>
        </div>
        <div class="progress">
          <div class="progress-bar bg-primary" style="width:<?= $pct ?>%;"></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Priority Breakdown -->
  <div class="card">
    <h5 style="margin:0 0 16px;font-size:15px;font-weight:700;">Priority Distribution</h5>
    <?php
    $priorityData = [
      'Critical' => ['color'=>'var(--danger)',       'bg'=>'#fee2e2'],
      'High'     => ['color'=>'var(--accent)',        'bg'=>'#ffedd5'],
      'Medium'   => ['color'=>'var(--warning)',       'bg'=>'#fef3c7'],
      'Low'      => ['color'=>'var(--success)',       'bg'=>'#d1fae5'],
    ];
    foreach ($priorityData as $pri => $style):
      $cnt = count(array_filter($allComplaints, fn($c) => $c['priority'] === $pri));
      $pct = round(($cnt / $total) * 100);
    ?>
      <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
        <div style="width:36px;height:36px;border-radius:8px;background:<?= $style['bg'] ?>;
                    display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:14px;">
          <?= $pri === 'Critical' ? '🔴' : ($pri === 'High' ? '🟠' : ($pri === 'Medium' ? '🟡' : '🟢')) ?>
        </div>
        <div style="flex:1;">
          <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span style="font-size:13px;font-weight:600;color:<?= $style['color'] ?>;"><?= $pri ?></span>
            <span style="font-size:13px;font-weight:700;"><?= $cnt ?> <small style="color:var(--text-muted);">(<?= $pct ?>%)</small></span>
          </div>
          <div class="progress">
            <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $style['color'] ?>;"></div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>

    <div style="padding-top:14px;border-top:1px solid var(--border);display:flex;justify-content:space-between;">
      <span style="font-size:13px;color:var(--text-sec);">Overall Resolution Rate</span>
      <span style="font-size:15px;font-weight:800;color:var(--success);">
        <?= round(($resolved / $total) * 100) ?>%
      </span>
    </div>
  </div>

</div>

<!-- Resolver Performance Table -->
<div class="card">
  <h5 style="margin:0 0 16px;font-size:15px;font-weight:700;">👥 Resolver Performance</h5>
  <div class="table-wrapper">
    <table class="dcrs-table">
      <thead>
        <tr>
          <th>Resolver</th>
          <th>Department</th>
          <th>Assigned</th>
          <th>Resolved</th>
          <th>Pending</th>
          <th>Avg Progress</th>
          <th>Resolution Rate</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($resolverStats as $r): ?>
          <?php
          $rate  = $r['resolution_rate'] ?? 0;
          $rColor = $rate >= 70 ? 'var(--success)' : ($rate >= 40 ? 'var(--warning)' : 'var(--danger)');
          ?>
          <tr>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:32px;height:32px;border-radius:50%;background:var(--primary-xlt);
                            color:var(--primary-light);display:flex;align-items:center;
                            justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;">
                  <?= strtoupper(substr($r['name'], 0, 2)) ?>
                </div>
                <span style="font-weight:600;font-size:13px;"><?= clean($r['name']) ?></span>
              </div>
            </td>
            <td style="font-size:12px;color:var(--text-sec);"><?= clean($r['department']) ?></td>
            <td style="font-weight:700;font-size:14px;"><?= $r['total_assigned'] ?></td>
            <td style="font-weight:700;font-size:14px;color:var(--success);"><?= $r['total_resolved'] ?></td>
            <td style="font-weight:700;font-size:14px;color:var(--warning);"><?= $r['total_pending'] ?></td>
            <td style="min-width:100px;">
              <div class="progress" style="margin-bottom:3px;">
                <div class="progress-bar bg-primary" style="width:<?= $r['avg_progress'] ?? 0 ?>%;"></div>
              </div>
              <small><?= $r['avg_progress'] ?? 0 ?>%</small>
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div class="progress" style="width:80px;margin-bottom:0;">
                  <div class="progress-bar" style="width:<?= $rate ?>%;background:<?= $rColor ?>;"></div>
                </div>
                <span style="font-size:13px;font-weight:700;color:<?= $rColor ?>;"><?= $rate ?>%</span>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Status Summary Table -->
<div class="card">
  <h5 style="margin:0 0 16px;font-size:15px;font-weight:700;">Status Summary</h5>
  <div class="table-wrapper">
    <table class="dcrs-table">
      <thead>
        <tr>
          <th>Status</th>
          <th>Count</th>
          <th>Percentage</th>
          <th>Visual</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $statusRows = [
          'Pending'     => ['color'=>'var(--text-sec)', 'key'=>'pending'],
          'Assigned'    => ['color'=>'var(--primary-light)', 'key'=>'assigned'],
          'In Progress' => ['color'=>'var(--warning)',   'key'=>'in_progress'],
          'Resolved'    => ['color'=>'var(--success)',   'key'=>'resolved'],
        ];
        foreach ($statusRows as $label => $info):
          $cnt = $stats[$info['key']] ?? 0;
          $pct = round(($cnt / $total) * 100);
        ?>
          <tr>
            <td><?= statusBadge($label) ?></td>
            <td style="font-weight:700;font-size:15px;"><?= $cnt ?></td>
            <td style="font-weight:600;color:<?= $info['color'] ?>;"><?= $pct ?>%</td>
            <td style="min-width:150px;">
              <div class="progress">
                <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $info['color'] ?>;"></div>
              </div>
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
