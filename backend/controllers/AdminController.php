<?php
// ============================================================
//  DCRS — Admin Controller
//  File: backend/controllers/AdminController.php
//  UPDATED: addResolver(), reassign support, live notif trigger
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';

class AdminController {
    private ComplaintModel    $complaintModel;
    private UserModel         $userModel;
    private NotificationModel $notifModel;

    public function __construct() {
        $this->complaintModel = new ComplaintModel();
        $this->userModel      = new UserModel();
        $this->notifModel     = new NotificationModel();
    }

    // ── Assign OR Reassign complaint to resolver ──────────────
    public function assign(): void {
        Session::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/pages/admin/assign.php');
        }

        $complaintId = (int)($_POST['complaint_id'] ?? 0);
        $resolverId  = (int)($_POST['resolver_id']  ?? 0);
        $priority    = trim($_POST['priority'] ?? 'Medium');
        $isReassign  = !empty($_POST['is_reassign']);

        $allowed = ['Low','Medium','High','Critical'];
        if (!in_array($priority, $allowed)) $priority = 'Medium';

        if (!$complaintId || !$resolverId) {
            Session::flash('error', 'Complaint and resolver are required.');
            redirect(APP_URL . '/pages/admin/assign.php');
        }

        $complaint = $this->complaintModel->getById($complaintId);
        if (!$complaint) {
            Session::flash('error', 'Complaint not found.');
            redirect(APP_URL . '/pages/admin/assign.php');
        }

        $oldResolverId = $complaint['resolver_id'];
        $resolver      = $this->userModel->findById($resolverId);

        if ($isReassign) {
            // Reassign: update resolver + priority, keep current status unless Pending
            $ok = $this->complaintModel->reassign(
                $complaintId, $resolverId, $priority, Session::userId()
            );
        } else {
            $ok = $this->complaintModel->assign(
                $complaintId, $resolverId, $priority, Session::userId()
            );
        }

        if ($ok) {
            $action = $isReassign ? 'Reassigned' : 'Assigned';

            $this->complaintModel->addProgressUpdate(
                $complaintId,
                Session::userId(),
                $action . ' to ' . ($resolver['name'] ?? 'resolver') . ' with priority: ' . $priority,
                $complaint['progress'],
                $complaint['status'],
                $isReassign ? $complaint['status'] : 'Assigned'
            );

            // Notify new resolver
            $this->notifModel->send(
                $resolverId,
                "Complaint {$complaint['complaint_code']} has been " . strtolower($action) . " to you.",
                'assigned',
                $complaintId
            );

            // Notify student
            $this->notifModel->send(
                $complaint['student_id'],
                "Your complaint {$complaint['complaint_code']} has been " . strtolower($action) . " to a resolver.",
                'assigned',
                $complaintId
            );

            // If reassign, also notify OLD resolver they were removed
            if ($isReassign && $oldResolverId && $oldResolverId !== $resolverId) {
                $this->notifModel->send(
                    $oldResolverId,
                    "Complaint {$complaint['complaint_code']} has been reassigned to another resolver.",
                    'updated',
                    $complaintId
                );
            }

            $msg = $isReassign ? 'Complaint reassigned successfully!' : 'Complaint assigned successfully!';
            Session::flash('success', $complaint['complaint_code'] . ' — ' . $msg);
        } else {
            Session::flash('error', 'Operation failed. Please try again.');
        }

        redirect(APP_URL . '/pages/admin/assign.php');
    }

    // ── Add resolver account (admin only) ─────────────────────
    public function addResolver(): void {
        Session::requireRole('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/pages/admin/users.php');
        }

        $name       = trim($_POST['name']       ?? '');
        $email      = trim($_POST['email']      ?? '');
        $password   = trim($_POST['password']   ?? '');
        $department = trim($_POST['department'] ?? '');
        $phone      = trim($_POST['phone']      ?? '');

        if (empty($name) || empty($email) || empty($password)) {
            Session::flash('error', 'Name, email, and password are required.');
            redirect(APP_URL . '/pages/admin/users.php');
        }

        if (!isValidEmail($email)) {
            Session::flash('error', 'Invalid email format.');
            redirect(APP_URL . '/pages/admin/users.php');
        }

        if (strlen($password) < 6) {
            Session::flash('error', 'Password must be at least 6 characters.');
            redirect(APP_URL . '/pages/admin/users.php');
        }

        if ($this->userModel->emailExists($email)) {
            Session::flash('error', 'An account with this email already exists.');
            redirect(APP_URL . '/pages/admin/users.php');
        }

        $userId = $this->userModel->create([
            'name'       => $name,
            'email'      => $email,
            'password'   => $password,
            'role'       => 'resolver',
            'department' => $department,
            'phone'      => $phone,
        ]);

        if ($userId) {
            Session::flash('success', "Resolver account created for {$name}.");
        } else {
            Session::flash('error', 'Failed to create resolver account.');
        }

        redirect(APP_URL . '/pages/admin/users.php');
    }

    // ── Toggle user active/inactive ───────────────────────────
    public function toggleUser(): void {
        Session::requireRole('admin');

        $userId = (int)($_POST['user_id'] ?? 0);

        if (!$userId || $userId === Session::userId()) {
            Session::flash('error', 'Invalid operation.');
            redirect(APP_URL . '/pages/admin/users.php');
        }

        $pdo  = DB::get();
        $stmt = $pdo->prepare(
            "UPDATE users SET is_active = NOT is_active WHERE user_id = ?"
        );
        $stmt->execute([$userId]);

        Session::flash('success', 'User status updated.');
        redirect(APP_URL . '/pages/admin/users.php');
    }
}

// ── Route ─────────────────────────────────────────────────────
$ctrl   = new AdminController();
$action = $_GET['action'] ?? '';

match ($action) {
    'assign'       => $ctrl->assign(),
    'add_resolver' => $ctrl->addResolver(),
    'toggle_user'  => $ctrl->toggleUser(),
    default        => redirect(APP_URL . '/pages/admin/dashboard.php'),
};
