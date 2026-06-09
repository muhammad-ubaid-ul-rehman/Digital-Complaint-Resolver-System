<?php
// ============================================================
//  DCRS — Complaint Controller (Student Actions)
//  File: backend/controllers/ComplaintController.php
//  FIX: myComplaints() and view() were void but returning values
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';

class ComplaintController {
    private ComplaintModel     $complaintModel;
    private NotificationModel  $notifModel;

    public function __construct() {
        $this->complaintModel = new ComplaintModel();
        $this->notifModel     = new NotificationModel();
    }

    // ── Submit new complaint (student) ────────────────────────
    public function submit(): void {
        Session::requireRole('student');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect(APP_URL . '/pages/student/submit_complaint.php');
        }

        $title       = trim($_POST['title']       ?? '');
        $description = trim($_POST['description'] ?? '');
        $category    = trim($_POST['category']    ?? '');
        $priority    = trim($_POST['priority']    ?? 'Medium');

        $allowed_categories = ['Academic','IT','Library','Cafeteria','Hostel','Transport','Finance','Other'];
        $allowed_priorities = ['Low','Medium','High','Critical'];

        if (empty($title) || empty($description) || empty($category)) {
            Session::flash('error', 'Title, description, and category are required.');
            redirect(APP_URL . '/pages/student/submit_complaint.php');
        }

        if (strlen($title) < 5 || strlen($title) > 255) {
            Session::flash('error', 'Title must be between 5 and 255 characters.');
            redirect(APP_URL . '/pages/student/submit_complaint.php');
        }

        if (!in_array($category, $allowed_categories)) {
            Session::flash('error', 'Invalid category selected.');
            redirect(APP_URL . '/pages/student/submit_complaint.php');
        }

        if (!in_array($priority, $allowed_priorities)) {
            $priority = 'Medium';
        }

        $studentId = Session::userId();

        $complaintId = $this->complaintModel->create([
            'student_id'  => $studentId,
            'title'       => $title,
            'description' => $description,
            'category'    => $category,
            'priority'    => $priority,
        ]);

        if ($complaintId) {
            $complaint = $this->complaintModel->getById($complaintId);

            $this->complaintModel->addProgressUpdate(
                $complaintId, $studentId,
                'Complaint submitted by student.',
                0, null, 'Pending'
            );

            $this->notifModel->onComplaintSubmitted(
                $studentId,
                $complaint['complaint_code'],
                $complaintId
            );

            Session::flash('success', 'Complaint submitted! Code: ' . $complaint['complaint_code']);
            redirect(APP_URL . '/pages/student/my_complaints.php');
        } else {
            Session::flash('error', 'Failed to submit complaint. Please try again.');
            redirect(APP_URL . '/pages/student/submit_complaint.php');
        }
    }

    // ── Delete complaint (student, pending only) ──────────────
    public function delete(): void {
        Session::requireRole('student');

        $complaintId = (int)($_POST['complaint_id'] ?? 0);

        if (!$complaintId) {
            Session::flash('error', 'Invalid complaint.');
            redirect(APP_URL . '/pages/student/my_complaints.php');
        }

        $deleted = $this->complaintModel->delete($complaintId, Session::userId());

        if ($deleted) {
            Session::flash('success', 'Complaint deleted successfully.');
        } else {
            Session::flash('error', 'Cannot delete — complaint may already be assigned.');
        }

        redirect(APP_URL . '/pages/student/my_complaints.php');
    }
}

// ── Route ─────────────────────────────────────────────────────
$ctrl   = new ComplaintController();
$action = $_GET['action'] ?? '';

match ($action) {
    'submit' => $ctrl->submit(),
    'delete' => $ctrl->delete(),
    default  => redirect(APP_URL . '/pages/student/my_complaints.php'),
};
