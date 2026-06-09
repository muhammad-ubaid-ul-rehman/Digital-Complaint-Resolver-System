<?php
// ============================================================
//  DCRS — Assign & Prioritize (Admin)
//  File: pages/admin/assign.php
//  UPDATED: Shows assigned complaints too with Reassign option
// ============================================================

require_once __DIR__ . '/../../frontend/partials/layout.php';

$complaintModel = new ComplaintModel();
$userModel      = new UserModel();

$unassigned = $complaintModel->getUnassigned();
$resolvers  = $userModel->getResolvers();
$highlight  = (int)($_GET['highlight'] ?? 0);

// Get already-assigned (non-resolved) complaints for reassign tab
// getAll() returns resolver_name (not resolver_id), so filter by that
$allComplaints = $complaintModel->getAll([]);
$assigned = array_filter($allComplaints, fn($c) =>
    !in_array($c['status'], ['Resolved', 'Closed', 'Pending']) &&
    !empty($c['resolver_name'])
);

renderLayout('Assign & Prioritize', 'Assign & Prioritize');
renderAlerts();
?>

<div class="page-header">
  <h2>👤 Assign & Prioritize</h2>
  <p>
    <?= count($unassigned) ?> unassigned &nbsp;·&nbsp;
    <?= count($assigned) ?> can be reassigned
  </p>
</div>

<!-- Tab switcher -->
<div style="display:flex;gap:0;margin-bottom:20px;border:1px solid var(--border);
            border-radius:10px;overflow:hidden;max-width:400px;">
  <button id="tabUnassigned" onclick="switchTab('unassigned')"
          style="flex:1;padding:10px;border:none;cursor:pointer;font-weight:700;
                 font-size:13px;background:var(--primary);color:#fff;transition:background 0.15s;">
    ⏳ Unassigned (<?= count($unassigned) ?>)
  </button>
  <button id="tabAssigned" onclick="switchTab('assigned')"
          style="flex:1;padding:10px;border:none;cursor:pointer;font-weight:700;
                 font-size:13px;background:var(--surface);color:var(--text-sec);transition:background 0.15s;">
    🔄 Reassign (<?= count($assigned) ?>)
  </button>
</div>

<div class="grid-2" style="align-items:flex-start;">

  <!-- Left panel: complaint list -->
  <div>

    <!-- UNASSIGNED LIST -->
    <div id="listUnassigned">
      <?php if (empty($unassigned)): ?>
        <div class="card" style="text-align:center;padding:2.5rem;">
          <p style="font-size:2rem;margin-bottom:8px;">🎉</p>
          <p style="color:var(--text-muted);font-size:13px;">All complaints are assigned!</p>
        </div>
      <?php else: ?>
        <p style="font-size:11px;font-weight:700;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
          Click a complaint to assign it
        </p>
        <?php foreach ($unassigned as $c): ?>
          <div class="complaint-card" id="card-<?= $c['complaint_id'] ?>"
               onclick="selectComplaint(
                 <?= $c['complaint_id'] ?>,
                 '<?= addslashes($c['complaint_code']) ?>',
                 '<?= addslashes(substr($c['title'],0,60)) ?>',
                 '<?= $c['priority'] ?>',
                 false,
                 null
               )"
               style="cursor:pointer;">
            <div class="complaint-meta">
              <span class="complaint-code"><?= $c['complaint_code'] ?></span>
              <?= priorityBadge($c['priority']) ?>
              <span style="font-size:11px;color:var(--text-muted);">📂 <?= $c['category'] ?></span>
            </div>
            <p class="complaint-title"><?= clean($c['title']) ?></p>
            <div class="complaint-footer">
              <span>🎓 <?= clean($c['student_name']) ?></span>
              <span>📅 <?= $c['created_at'] ?></span>
              <a href="<?= APP_URL ?>/pages/complaint_detail.php?id=<?= $c['complaint_id'] ?>"
                 class="btn btn-ghost btn-sm" onclick="event.stopPropagation();">👁 View</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ASSIGNED/REASSIGN LIST -->
    <div id="listAssigned" style="display:none;">
      <?php if (empty($assigned)): ?>
        <div class="card" style="text-align:center;padding:2.5rem;">
          <p style="color:var(--text-muted);font-size:13px;">No complaints available for reassignment.</p>
        </div>
      <?php else: ?>
        <p style="font-size:11px;font-weight:700;color:var(--text-muted);
                  text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;">
          Click a complaint to reassign it to a different resolver
        </p>
        <?php foreach ($assigned as $c): ?>
          <div class="complaint-card" id="card-<?= $c['complaint_id'] ?>"
               onclick="selectComplaint(
                 <?= $c['complaint_id'] ?>,
                 '<?= addslashes($c['complaint_code']) ?>',
                 '<?= addslashes(substr($c['title'],0,60)) ?>',
                 '<?= $c['priority'] ?>',
                 true,
                 '<?= addslashes($c['resolver_name'] ?? 'Unassigned') ?>'
               )"
               style="cursor:pointer;">
            <div class="complaint-meta">
              <span class="complaint-code"><?= $c['complaint_code'] ?></span>
              <?= statusBadge($c['status']) ?>
              <?= priorityBadge($c['priority']) ?>
            </div>
            <p class="complaint-title"><?= clean($c['title']) ?></p>
            <div class="complaint-footer">
              <span>🎓 <?= clean($c['student_name']) ?></span>
              <span>👤 <?= clean($c['resolver_name'] ?? 'Unassigned') ?></span>
              <a href="<?= APP_URL ?>/pages/complaint_detail.php?id=<?= $c['complaint_id'] ?>"
                 class="btn btn-ghost btn-sm" onclick="event.stopPropagation();">👁 View</a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

  </div>

  <!-- Right: Assignment form (sticky) -->
  <div style="position:sticky;top:80px;">
    <div class="card" id="assignPanel">
      <h5 id="formTitle" style="margin:0 0 16px;font-size:15px;font-weight:700;">
        Assignment Form
      </h5>

      <!-- Placeholder -->
      <div id="noSelection" style="text-align:center;padding:1.5rem;color:var(--text-muted);">
        <p style="font-size:1.5rem;margin-bottom:8px;">👈</p>
        <p style="font-size:13px;">Click a complaint on the left to assign or reassign it.</p>
      </div>

      <!-- Form -->
      <div id="assignForm" style="display:none;">

        <!-- Current resolver notice (shown for reassign) -->
        <div id="currentResolverBox" style="display:none;background:#fff3cd;border-left:3px solid var(--warning);
             padding:8px 12px;border-radius:7px;margin-bottom:14px;">
          <p style="margin:0;font-size:12px;color:var(--warning);font-weight:700;">Current Resolver</p>
          <p style="margin:2px 0 0;font-size:13px;font-weight:600;" id="currentResolverName"></p>
        </div>

        <div id="selectedInfo" style="background:var(--bg);border-radius:8px;padding:10px 14px;margin-bottom:16px;">
          <p style="margin:0;font-size:11px;color:var(--text-muted);font-weight:700;" id="selectedCode"></p>
          <p style="margin:2px 0 0;font-size:13px;font-weight:600;color:var(--text);" id="selectedTitle"></p>
        </div>

        <form method="POST"
              action="<?= APP_URL ?>/backend/controllers/AdminController.php?action=assign">
          <input type="hidden" name="complaint_id" id="inputComplaintId">
          <input type="hidden" name="is_reassign"  id="inputIsReassign" value="0">

          <div class="form-group">
            <label class="form-label" id="resolverLabel">Assign to Resolver *</label>
            <select name="resolver_id" class="form-control" required id="resolverSelect">
              <option value="">— Select Resolver —</option>
              <?php foreach ($resolvers as $r): ?>
                <option value="<?= $r['user_id'] ?>">
                  <?= clean($r['name']) ?> — <?= clean($r['department']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Priority</label>
            <select name="priority" class="form-control" id="prioritySelect">
              <option value="Low">🟢 Low</option>
              <option value="Medium" selected>🟡 Medium</option>
              <option value="High">🟠 High</option>
              <option value="Critical">🔴 Critical</option>
            </select>
          </div>

          <div style="display:flex;gap:8px;">
            <button type="submit" id="submitBtn" class="btn btn-accent"
                    style="flex:1;justify-content:center;">
              ✅ Assign Complaint
            </button>
            <button type="button" class="btn btn-ghost" onclick="clearSelection()">✕</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Resolver workload -->
    <?php if (!empty($resolvers)): ?>
      <div class="card" style="margin-top:0;">
        <h5 style="margin:0 0 12px;font-size:12px;font-weight:700;color:var(--text-sec);
                   text-transform:uppercase;letter-spacing:0.5px;">Resolver Workload</h5>
        <?php
        // Use PDO directly so we have resolver_id available
        $pdo      = DB::get();
        $wlStmt   = $pdo->query(
            "SELECT resolver_id,
                    SUM(status IN ('Assigned','In Progress')) AS active_count,
                    SUM(status = 'Resolved')                  AS resolved_count
             FROM complaints
             WHERE resolver_id IS NOT NULL
             GROUP BY resolver_id"
        );
        $workload = [];
        foreach ($wlStmt->fetchAll() as $row) {
            $workload[$row['resolver_id']] = $row;
        }
        foreach ($resolvers as $r):
          $wl      = $workload[$r['user_id']] ?? ['active_count' => 0, 'resolved_count' => 0];
          $active   = (int)$wl['active_count'];
          $resolved = (int)$wl['resolved_count'];
        ?>
          <div style="display:flex;justify-content:space-between;align-items:center;
                      padding:8px 0;border-bottom:1px solid var(--border);">
            <div>
              <p style="margin:0;font-size:12px;font-weight:600;color:var(--text);">
                <?= clean($r['name']) ?>
              </p>
              <p style="margin:0;font-size:11px;color:var(--text-muted);"><?= clean($r['department']) ?></p>
            </div>
            <div style="text-align:right;">
              <span style="font-size:12px;font-weight:700;
                    color:<?= $active > 3 ? 'var(--danger)' : ($active > 1 ? 'var(--warning)' : 'var(--success)') ?>;">
                <?= $active ?> active
              </span>
              <p style="margin:0;font-size:10px;color:var(--text-muted);"><?= $resolved ?> resolved</p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</div>

<script>
let currentTab = 'unassigned';

function switchTab(tab) {
  currentTab = tab;
  const isUnassigned = tab === 'unassigned';

  document.getElementById('listUnassigned').style.display = isUnassigned ? 'block' : 'none';
  document.getElementById('listAssigned').style.display   = isUnassigned ? 'none'  : 'block';

  document.getElementById('tabUnassigned').style.background = isUnassigned ? 'var(--primary)' : 'var(--surface)';
  document.getElementById('tabUnassigned').style.color      = isUnassigned ? '#fff' : 'var(--text-sec)';
  document.getElementById('tabAssigned').style.background   = isUnassigned ? 'var(--surface)' : 'var(--primary)';
  document.getElementById('tabAssigned').style.color        = isUnassigned ? 'var(--text-sec)' : '#fff';

  clearSelection();
}

function selectComplaint(id, code, title, priority, isReassign, currentResolver) {
  // Highlight card
  document.querySelectorAll('.complaint-card').forEach(el => {
    el.style.borderColor = 'var(--border)';
    el.style.borderWidth = '1px';
  });
  const card = document.getElementById('card-' + id);
  if (card) { card.style.borderColor = 'var(--accent)'; card.style.borderWidth = '2px'; }

  // Fill inputs
  document.getElementById('inputComplaintId').value = id;
  document.getElementById('inputIsReassign').value  = isReassign ? '1' : '0';
  document.getElementById('selectedCode').textContent  = code;
  document.getElementById('selectedTitle').textContent = title;

  // Set priority
  const ps = document.getElementById('prioritySelect');
  if (ps) ps.value = priority;

  // Update form UI based on assign vs reassign
  const crBox = document.getElementById('currentResolverBox');
  const crName = document.getElementById('currentResolverName');
  const submitBtn = document.getElementById('submitBtn');
  const formTitle = document.getElementById('formTitle');
  const resolverLabel = document.getElementById('resolverLabel');

  if (isReassign) {
    crBox.style.display = 'block';
    crName.textContent  = currentResolver || 'Unknown';
    submitBtn.textContent = '🔄 Reassign Complaint';
    submitBtn.style.background = 'var(--warning)';
    formTitle.textContent = 'Reassign Complaint';
    resolverLabel.textContent = 'Reassign to Resolver *';
  } else {
    crBox.style.display = 'none';
    submitBtn.textContent = '✅ Assign Complaint';
    submitBtn.style.background = 'var(--accent)';
    formTitle.textContent = 'Assignment Form';
    resolverLabel.textContent = 'Assign to Resolver *';
  }

  // Show form
  document.getElementById('noSelection').style.display = 'none';
  document.getElementById('assignForm').style.display  = 'block';
}

function clearSelection() {
  document.querySelectorAll('.complaint-card').forEach(el => {
    el.style.borderColor = 'var(--border)';
    el.style.borderWidth = '1px';
  });
  document.getElementById('noSelection').style.display = 'block';
  document.getElementById('assignForm').style.display  = 'none';
}

// Auto-select if highlight param set
<?php if ($highlight): ?>
  document.addEventListener('DOMContentLoaded', () => {
    const card = document.getElementById('card-<?= $highlight ?>');
    if (card) { card.click(); card.scrollIntoView({ behavior:'smooth', block:'center' }); }
  });
<?php endif; ?>
</script>

<?php
echo "<script>window.DCRS_NOTIF_URL = '" . APP_URL . "/backend/controllers/NotificationController.php';</script>";
renderLayoutEnd();
?>