<?php
// ============================================================
//  DCRS — Submit Complaint Page
//  File: pages/student/submit_complaint.php
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

renderLayout('Submit Complaint', 'Submit Complaint');
renderAlerts();
?>

<div class="page-header">
  <h2>📝 Submit a Complaint</h2>
  <p>Fill in all fields. The more detail you provide, the faster your complaint gets resolved.</p>
</div>

<div style="max-width:640px;">
  <div class="card">
    <form action="<?= APP_URL ?>/backend/controllers/ComplaintController.php?action=submit"
          method="POST">

      <!-- Title -->
      <div class="form-group">
        <label class="form-label" for="title">Complaint Title *</label>
        <input type="text" id="title" name="title" class="form-control"
               placeholder="e.g. Wi-Fi not working in Block C"
               minlength="5" maxlength="255" required
               value="<?= clean($_POST['title'] ?? '') ?>">
        <small style="color:var(--text-muted);font-size:11px;">5–255 characters</small>
      </div>

      <!-- Category + Priority -->
      <div class="grid-2">
        <div class="form-group">
          <label class="form-label" for="category">Category *</label>
          <select id="category" name="category" class="form-control" required>
            <option value="">— Select Category —</option>
            <?php foreach (['Academic','IT','Library','Cafeteria','Hostel','Transport','Finance','Other'] as $cat): ?>
              <option value="<?= $cat ?>" <?= (($_POST['category'] ?? '') === $cat) ? 'selected' : '' ?>>
                <?= $cat ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" for="priority">Priority *</label>
          <select id="priority" name="priority" class="form-control" required>
            <?php foreach (['Low'=>'🟢 Low','Medium'=>'🟡 Medium','High'=>'🟠 High','Critical'=>'🔴 Critical'] as $val => $label): ?>
              <option value="<?= $val ?>" <?= (($_POST['priority'] ?? 'Medium') === $val) ? 'selected' : '' ?>>
                <?= $label ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <!-- Description -->
      <div class="form-group">
        <label class="form-label" for="description">Detailed Description *</label>
        <textarea id="description" name="description" class="form-control"
                  placeholder="Describe the issue in detail. Include:&#10;- When did it start?&#10;- Where exactly (block, room, department)?&#10;- What impact is it having?&#10;- Any previous attempts to resolve it?"
                  rows="6" required><?= clean($_POST['description'] ?? '') ?></textarea>
      </div>

      <!-- Priority info box -->
      <div class="alert alert-info" style="margin-bottom:18px;">
        💡 <strong>Priority Guide:</strong>
        Low = Minor inconvenience &nbsp;|&nbsp;
        Medium = Affects daily work &nbsp;|&nbsp;
        High = Serious disruption &nbsp;|&nbsp;
        Critical = Urgent / emergency
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <button type="submit" class="btn btn-accent btn-lg">
          📤 Submit Complaint
        </button>
        <a href="<?= APP_URL ?>/pages/student/my_complaints.php" class="btn btn-ghost btn-lg">
          Cancel
        </a>
      </div>

    </form>
  </div>
</div>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>
