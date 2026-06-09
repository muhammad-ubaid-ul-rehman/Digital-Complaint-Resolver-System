<?php
// ============================================================
//  DCRS — Users Management (Admin)
//  File: pages/admin/users.php
//  UPDATED: Add Resolver form included
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$userModel = new UserModel();
$users     = $userModel->getAll();

$students  = array_filter($users, fn($u) => $u['role'] === 'student');
$resolvers = array_filter($users, fn($u) => $u['role'] === 'resolver');
$admins    = array_filter($users, fn($u) => $u['role'] === 'admin');

renderLayout('Users', 'Users');
renderAlerts();
?>

<div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:10px;">
  <div>
    <h2>👥 System Users</h2>
    <p><?= count($users) ?> registered users</p>
  </div>
  <!-- Trigger Add Resolver Modal -->
  <button class="btn btn-accent" data-modal="addResolverModal">
    ➕ Add Resolver
  </button>
</div>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);max-width:500px;margin-bottom:22px;">
  <div class="stat-card">
    <div class="stat-icon" style="background:#dbeafe;">🎓</div>
    <div class="stat-body">
      <p class="stat-label">Students</p>
      <p class="stat-value"><?= count($students) ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#d1fae5;">🔧</div>
    <div class="stat-body">
      <p class="stat-label">Resolvers</p>
      <p class="stat-value"><?= count($resolvers) ?></p>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon" style="background:#ffedd5;">🛡</div>
    <div class="stat-body">
      <p class="stat-label">Admins</p>
      <p class="stat-value"><?= count($admins) ?></p>
    </div>
  </div>
</div>

<!-- Users Table -->
<div class="card" style="padding:0;overflow:hidden;">
  <div class="table-wrapper">
    <table class="dcrs-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Email</th>
          <th>Department</th>
          <th>Role</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <?php
          $roleColors = [
            'admin'    => ['bg'=>'#ffedd5','color'=>'var(--accent)'],
            'resolver' => ['bg'=>'#d1fae5','color'=>'var(--success)'],
            'student'  => ['bg'=>'#dbeafe','color'=>'var(--primary-light)'],
          ];
          $rc       = $roleColors[$u['role']] ?? $roleColors['student'];
          $initials = strtoupper(implode('', array_map(fn($w) => $w[0], explode(' ', trim($u['name'])))));
          $initials = substr($initials, 0, 2);
          ?>
          <tr style="<?= !$u['is_active'] ? 'opacity:0.5;' : '' ?>">
            <td style="font-size:11px;color:var(--text-muted);"><?= $u['user_id'] ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <div style="width:34px;height:34px;border-radius:50%;background:<?= $rc['bg'] ?>;
                            color:<?= $rc['color'] ?>;display:flex;align-items:center;
                            justify-content:center;font-size:12px;font-weight:700;flex-shrink:0;">
                  <?= $initials ?>
                </div>
                <span style="font-weight:600;font-size:13px;"><?= clean($u['name']) ?></span>
              </div>
            </td>
            <td style="font-size:12px;color:var(--text-sec);"><?= clean($u['email']) ?></td>
            <td style="font-size:12px;color:var(--text-sec);"><?= clean($u['department'] ?? '—') ?></td>
            <td>
              <span class="badge" style="background:<?= $rc['bg'] ?>;color:<?= $rc['color'] ?>;">
                <?= ucfirst($u['role']) ?>
              </span>
            </td>
            <td>
              <?php if ($u['is_active']): ?>
                <span class="badge badge-success">Active</span>
              <?php else: ?>
                <span class="badge badge-secondary">Inactive</span>
              <?php endif; ?>
            </td>
            <td style="font-size:11px;color:var(--text-muted);">
              <?= substr($u['created_at'], 0, 10) ?>
            </td>
            <td>
              <?php if ($u['user_id'] !== Session::userId()): ?>
                <form method="POST"
                      action="<?= APP_URL ?>/backend/controllers/AdminController.php?action=toggle_user"
                      style="display:inline;">
                  <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                  <button type="submit"
                          class="btn btn-sm <?= $u['is_active'] ? 'btn-danger' : 'btn-success' ?>"
                          data-confirm="<?= $u['is_active'] ? 'Deactivate this user?' : 'Activate this user?' ?>">
                    <?= $u['is_active'] ? '🔒 Deactivate' : '🔓 Activate' ?>
                  </button>
                </form>
              <?php else: ?>
                <span style="font-size:11px;color:var(--text-muted);">You</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- ── Add Resolver Modal ─────────────────────────────────── -->
<div class="modal-overlay" id="addResolverModal">
  <div class="modal-box" style="max-width:480px;">
    <div class="modal-header">
      <h5>➕ Add New Resolver</h5>
      <button class="modal-close" type="button">✕</button>
    </div>
    <div class="modal-body">
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:18px;">
        Create a resolver account. They will be able to login and handle assigned complaints.
      </p>
      <form method="POST"
            action="<?= APP_URL ?>/backend/controllers/AdminController.php?action=add_resolver">

        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control"
                 placeholder="e.g. Dr. Ahmed Raza" required>
        </div>

        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-control"
                 placeholder="resolver@university.edu" required>
        </div>

        <div class="form-group">
          <label class="form-label">Department</label>
          <select name="department" class="form-control">
            <option value="">— Select Department —</option>
            <?php foreach (['IT & Services','Academic Affairs','Administration',
                            'Maintenance','Library','Finance','Transport','Hostel Management','Other'] as $dept): ?>
              <option value="<?= $dept ?>"><?= $dept ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label">Phone (optional)</label>
          <input type="text" name="phone" class="form-control" placeholder="03xx-xxxxxxx">
        </div>

        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-control"
                 placeholder="Min. 6 characters" required minlength="6">
        </div>

        <div style="display:flex;gap:10px;margin-top:4px;">
          <button type="submit" class="btn btn-accent" style="flex:1;justify-content:center;">
            ✅ Create Resolver
          </button>
          <button type="button" class="modal-close btn btn-ghost">
            Cancel
          </button>
        </div>

      </form>
    </div>
  </div>
</div>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
