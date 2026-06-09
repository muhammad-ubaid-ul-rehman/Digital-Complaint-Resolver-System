<?php
// ============================================================
//  DCRS — Notifications Page (Student)
//  File: pages/student/notifications.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$notifModel = new NotificationModel();
$userId     = Session::userId();

// Mark all read when page visited
$notifModel->markAllRead($userId);

$notifications = $notifModel->getForUser($userId, 30);

renderLayout('Notifications', 'Notifications');
renderAlerts();

$iconMap = [
  'submitted' => '📝',
  'assigned'  => '👤',
  'updated'   => '🔄',
  'resolved'  => '✅',
  'closed'    => '🔒',
  'comment'   => '💬',
];
?>

<div class="page-header">
  <h2>🔔 Notifications</h2>
  <p><?= count($notifications) ?> notification(s)</p>
</div>

<div style="max-width:700px;">
  <?php if (empty($notifications)): ?>
    <div class="card" style="text-align:center;padding:3rem;">
      <p style="font-size:2.5rem;margin-bottom:10px;">🔕</p>
      <p style="color:var(--text-muted);">No notifications yet.</p>
    </div>
  <?php else: ?>
    <div class="card" style="padding:0;overflow:hidden;">
      <?php foreach ($notifications as $n): ?>
        <div style="display:flex;gap:14px;padding:14px 18px;border-bottom:1px solid var(--border);">
          <span style="font-size:22px;flex-shrink:0;margin-top:2px;">
            <?= $iconMap[$n['type']] ?? '🔔' ?>
          </span>
          <div style="flex:1;">
            <p style="margin:0;font-size:13px;color:var(--text);"><?= clean($n['message']) ?></p>
            <?php if ($n['complaint_code']): ?>
              <p style="margin:2px 0 0;">
                <a href="<?= APP_URL ?>/pages/complaint_detail.php?id=<?= $n['complaint_id'] ?>"
                   style="font-size:11px;color:var(--primary-light);font-weight:600;">
                  View <?= $n['complaint_code'] ?>
                </a>
              </p>
            <?php endif; ?>
            <p style="margin:3px 0 0;font-size:11px;color:var(--text-muted);">
              <?= $n['created_at'] ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
