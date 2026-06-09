<?php
// ============================================================
//  DCRS — Resolver Controller
//  File: backend/controllers/ResolverController.php
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';

class ResolverController {
    private ComplaintModel    $complaintModel;
    private NotificationModel $notifModel;

    public function __construct() {
        $this->complaintModel = new ComplaintModel();
        $this->notifModel     = new NotificationModel();
    }

    // ── Update complaint progress ─────────────────────────────
    public function updateProgress(): void {
        Session::requireRole('resolver');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/pages/resolver/assigned.php');
        }

        $complaintId = (int)($_POST['complaint_id'] ?? 0);
        $status      = trim($_POST['status']        ?? '');
        $progress    = (int)($_POST['progress']      ?? 0);
        $remarks     = trim($_POST['remarks']        ?? '');

        $allowedStatus = ['In Progress', 'Resolved'];

        // Validate
        if (!$complaintId) {
            Session::flash('error', 'Invalid complaint.');
            redirect(APP_URL . '/pages/resolver/assigned.php');
        }

        if (!in_array($status, $allowedStatus)) {
            Session::flash('error', 'Invalid status.');
            redirect(APP_URL . '/pages/resolver/assigned.php');
        }

        $progress = max(0, min(100, $progress));  // clamp 0-100

        if (empty($remarks)) {
            Session::flash('error', 'Remarks are required when updating progress.');
            redirect(APP_URL . '/pages/resolver/update.php?id=' . $complaintId);
        }

        // Ownership check
        $complaint = $this->complaintModel->getById($complaintId);
        if (!$complaint || $complaint['resolver_id'] !== Session::userId()) {
            Session::flash('error', 'You are not authorized to update this complaint.');
            redirect(APP_URL . '/pages/resolver/assigned.php');
        }

        if ($complaint['status'] === 'Resolved') {
            Session::flash('error', 'This complaint is already resolved.');
            redirect(APP_URL . '/pages/resolver/assigned.php');
        }

        // Force progress to 100 when resolving
        if ($status === 'Resolved') $progress = 100;

        $ok = $this->complaintModel->updateProgress(
            $complaintId, $status, $progress, $remarks, Session::userId()
        );

        if ($ok) {
            // Notify student
            if ($status === 'Resolved') {
                $this->notifModel->onComplaintResolved(
                    $complaint['student_id'],
                    $complaint['complaint_code'],
                    $complaintId
                );
            } else {
                $this->notifModel->onProgressUpdated(
                    $complaint['student_id'],
                    $complaint['complaint_code'],
                    $status,
                    $complaintId
                );
            }

            Session::flash('success',
                $status === 'Resolved'
                    ? 'Complaint marked as Resolved! ✅'
                    : 'Progress updated successfully.'
            );
        } else {
            Session::flash('error', 'Update failed. Please try again.');
        }

        redirect(APP_URL . '/pages/resolver/assigned.php');
    }

    // ── Get assigned complaints data ──────────────────────────
    public function assignedData(): array {
        Session::requireRole('resolver');

        $resolverId = Session::userId();
        $status     = $_GET['status'] ?? '';

        $filters = [];
        if (!empty($status)) $filters['status'] = $status;

        $complaints = $this->complaintModel->getByResolver($resolverId, $filters);
        $stats      = $this->complaintModel->getStats(null, $resolverId);

        return compact('complaints', 'stats');
    }

    // ── Get update form data ──────────────────────────────────
    public function updateFormData(int $complaintId): ?array {
        Session::requireRole('resolver');

        $complaint = $this->complaintModel->getById($complaintId);

        if (!$complaint || $complaint['resolver_id'] !== Session::userId()) {
            return null;
        }

        $timeline = $this->complaintModel->getTimeline($complaintId);

        return compact('complaint', 'timeline');
    }
}

// ── Route ─────────────────────────────────────────────────────
$ctrl   = new ResolverController();
$action = $_GET['action'] ?? '';

match ($action) {
    'update_progress' => $ctrl->updateProgress(),
    default           => redirect(APP_URL . '/pages/resolver/assigned.php'),
};
