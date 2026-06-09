<?php
// ============================================================
//  DCRS — Notification Model
//  File: backend/models/NotificationModel.php
// ============================================================

class NotificationModel {
    private PDO $db;

    public function __construct() {
        $this->db = DB::get();
    }

    // ── Send a notification ───────────────────────────────────
    public function send(int $userId, string $message, string $type = 'updated', ?int $complaintId = null): void {
        $stmt = $this->db->prepare(
            "INSERT INTO notifications (user_id, complaint_id, type, message)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $complaintId, $type, $message]);
    }

    // ── Send to multiple users ────────────────────────────────
    public function sendBulk(array $userIds, string $message, string $type = 'updated', ?int $complaintId = null): void {
        foreach ($userIds as $uid) {
            $this->send((int)$uid, $message, $type, $complaintId);
        }
    }

    // ── Get notifications for a user ──────────────────────────
    public function getForUser(int $userId, int $limit = 20): array {
        $stmt = $this->db->prepare(
            "SELECT n.*, c.complaint_code, c.title AS complaint_title
             FROM notifications n
             LEFT JOIN complaints c ON c.complaint_id = n.complaint_id
             WHERE n.user_id = ?
             ORDER BY n.created_at DESC
             LIMIT ?"
        );
        $stmt->execute([$userId, $limit]);
        return $stmt->fetchAll();
    }

    // ── Unread count ──────────────────────────────────────────
    public function unreadCount(int $userId): int {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    // ── Mark all read ──────────────────────────────────────────
    public function markAllRead(int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
    }

    // ── Mark one read ──────────────────────────────────────────
    public function markRead(int $notifId, int $userId): void {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET is_read = 1
             WHERE notification_id = ? AND user_id = ?"
        );
        $stmt->execute([$notifId, $userId]);
    }

    // ── Notification events (call these from controllers) ─────

    public function onComplaintSubmitted(int $studentId, string $code, int $complaintId): void {
        // Notify all admins
        $admins = $this->db->query(
            "SELECT user_id FROM users WHERE role = 'admin' AND is_active = 1"
        )->fetchAll(PDO::FETCH_COLUMN);

        $this->sendBulk(
            $admins,
            "New complaint {$code} submitted by a student.",
            'submitted',
            $complaintId
        );
    }

    public function onComplaintAssigned(int $resolverId, int $studentId, string $code, int $complaintId): void {
        $this->send($resolverId, "Complaint {$code} has been assigned to you.", 'assigned', $complaintId);
        $this->send($studentId, "Your complaint {$code} has been assigned to a resolver.", 'assigned', $complaintId);
    }

    public function onProgressUpdated(int $studentId, string $code, string $status, int $complaintId): void {
        $this->send($studentId, "Complaint {$code} status updated to: {$status}.", 'updated', $complaintId);
    }

    public function onComplaintResolved(int $studentId, string $code, int $complaintId): void {
        $this->send($studentId, "Your complaint {$code} has been resolved! ✅", 'resolved', $complaintId);
    }
}
