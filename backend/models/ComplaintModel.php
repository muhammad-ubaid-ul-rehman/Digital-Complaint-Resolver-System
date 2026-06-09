<?php
// ============================================================
//  DCRS — Complaint Model
//  File: backend/models/ComplaintModel.php
// ============================================================

class ComplaintModel {
    private PDO $db;

    public function __construct() {
        $this->db = DB::get();
    }

    // ── Create new complaint ──────────────────────────────────
    public function create(array $data): int {
        $code = generateComplaintCode($this->db);
        $stmt = $this->db->prepare(
            "INSERT INTO complaints
               (complaint_code, student_id, title, description,
                category, priority, status)
             VALUES
               (:code, :student_id, :title, :description,
                :category, :priority, 'Pending')"
        );
        $stmt->execute([
            ':code'        => $code,
            ':student_id'  => $data['student_id'],
            ':title'       => clean($data['title']),
            ':description' => clean($data['description']),
            ':category'    => $data['category'],
            ':priority'    => $data['priority'] ?? 'Medium',
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ── Get single complaint (with student & resolver names) ──
    public function getById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT c.*,
                    s.name AS student_name, s.email AS student_email,
                    r.name AS resolver_name, r.department AS resolver_dept,
                    a.name AS assigned_by_name
             FROM complaints c
             JOIN users s ON s.user_id = c.student_id
             LEFT JOIN users r ON r.user_id = c.resolver_id
             LEFT JOIN users a ON a.user_id = c.assigned_by
             WHERE c.complaint_id = ?
             LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Get all complaints (admin) ────────────────────────────
    public function getAll(array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }
        if (!empty($filters['priority'])) {
            $where[] = 'c.priority = :priority';
            $params[':priority'] = $filters['priority'];
        }
        if (!empty($filters['category'])) {
            $where[] = 'c.category = :category';
            $params[':category'] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $where[] = '(c.title LIKE :search OR c.complaint_code LIKE :search)';
            $params[':search'] = '%' . $filters['search'] . '%';
        }

        $sql = "SELECT c.complaint_id, c.complaint_code, c.title,
                       c.category, c.priority, c.status, c.progress,
                       c.created_at, c.updated_at,
                       s.name AS student_name,
                       r.name AS resolver_name
                FROM complaints c
                JOIN users s ON s.user_id = c.student_id
                LEFT JOIN users r ON r.user_id = c.resolver_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY
                  FIELD(c.priority,'Critical','High','Medium','Low'),
                  FIELD(c.status,'Pending','Assigned','In Progress','Resolved','Closed'),
                  c.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Get complaints by student ─────────────────────────────
    public function getByStudent(int $studentId, array $filters = []): array {
        $filters['student_id'] = $studentId;
        $where = ['c.student_id = :student_id'];
        $params = [':student_id' => $studentId];

        if (!empty($filters['status'])) {
            $where[] = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }

        $stmt = $this->db->prepare(
            "SELECT c.complaint_id, c.complaint_code, c.title,
                    c.category, c.priority, c.status, c.progress,
                    c.remarks, c.created_at, c.updated_at,
                    r.name AS resolver_name
             FROM complaints c
             LEFT JOIN users r ON r.user_id = c.resolver_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY c.created_at DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Get complaints assigned to resolver ───────────────────
    public function getByResolver(int $resolverId, array $filters = []): array {
        $where = ['c.resolver_id = :rid'];
        $params = [':rid' => $resolverId];

        if (!empty($filters['status'])) {
            $where[] = 'c.status = :status';
            $params[':status'] = $filters['status'];
        }

        $stmt = $this->db->prepare(
            "SELECT c.complaint_id, c.complaint_code, c.title,
                    c.category, c.priority, c.status, c.progress,
                    c.remarks, c.created_at, c.updated_at,
                    s.name AS student_name
             FROM complaints c
             JOIN users s ON s.user_id = c.student_id
             WHERE " . implode(' AND ', $where) . "
             ORDER BY
               FIELD(c.priority,'Critical','High','Medium','Low'),
               c.created_at DESC"
        );
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // ── Assign complaint to resolver (first time, Pending only) ─
    public function assign(int $complaintId, int $resolverId, string $priority, int $adminId): bool {
        $stmt = $this->db->prepare(
            "UPDATE complaints
             SET resolver_id = :rid, priority = :priority,
                 status = 'Assigned', assigned_by = :admin
             WHERE complaint_id = :id"
        );
        return $stmt->execute([
            ':rid'      => $resolverId,
            ':priority' => $priority,
            ':admin'    => $adminId,
            ':id'       => $complaintId,
        ]);
    }

    // ── Reassign to different resolver (admin) ────────────────
    public function reassign(int $complaintId, int $resolverId, string $priority, int $adminId): bool {
        $stmt = $this->db->prepare(
            "UPDATE complaints
             SET resolver_id = :rid, priority = :priority, assigned_by = :admin
             WHERE complaint_id = :id AND status != 'Resolved'"
        );
        return $stmt->execute([
            ':rid'      => $resolverId,
            ':priority' => $priority,
            ':admin'    => $adminId,
            ':id'       => $complaintId,
        ]);
    }

    // ── Update status & progress (resolver) ──────────────────
    public function updateProgress(
        int $complaintId, string $status,
        int $progress, string $remarks, int $updatedBy
    ): bool {
        $resolvedAt = $status === 'Resolved' ? 'NOW()' : 'NULL';
        $stmt = $this->db->prepare(
            "UPDATE complaints
             SET status = :status, progress = :progress,
                 remarks = :remarks,
                 resolved_at = $resolvedAt
             WHERE complaint_id = :id"
        );
        $ok = $stmt->execute([
            ':status'   => $status,
            ':progress' => $progress,
            ':remarks'  => clean($remarks),
            ':id'       => $complaintId,
        ]);

        // Also add progress_update row
        if ($ok) {
            $this->addProgressUpdate($complaintId, $updatedBy, $remarks, $progress, null, $status);
        }
        return $ok;
    }

    // ── Add timeline entry ─────────────────────────────────────
    public function addProgressUpdate(
        int $complaintId, int $userId,
        string $text, int $progress,
        ?string $oldStatus, ?string $newStatus
    ): void {
        $stmt = $this->db->prepare(
            "INSERT INTO progress_updates
               (complaint_id, updated_by, update_text,
                progress_percentage, old_status, new_status)
             VALUES (?,?,?,?,?,?)"
        );
        $stmt->execute([
            $complaintId, $userId, clean($text),
            $progress, $oldStatus, $newStatus,
        ]);
    }

    // ── Get timeline for a complaint ──────────────────────────
    public function getTimeline(int $complaintId): array {
        $stmt = $this->db->prepare(
            "SELECT pu.*, u.name AS updated_by_name, u.role AS updater_role
             FROM progress_updates pu
             JOIN users u ON u.user_id = pu.updated_by
             WHERE pu.complaint_id = ?
             ORDER BY pu.created_at ASC"
        );
        $stmt->execute([$complaintId]);
        return $stmt->fetchAll();
    }

    // ── Get unassigned complaints ──────────────────────────────
    public function getUnassigned(): array {
        $stmt = $this->db->query(
            "SELECT c.complaint_id, c.complaint_code, c.title,
                    c.category, c.priority, c.created_at,
                    s.name AS student_name
             FROM complaints c
             JOIN users s ON s.user_id = c.student_id
             WHERE c.status = 'Pending'
             ORDER BY
               FIELD(c.priority,'Critical','High','Medium','Low'),
               c.created_at ASC"
        );
        return $stmt->fetchAll();
    }

    // ── Dashboard stats ───────────────────────────────────────
    public function getStats(?int $studentId = null, ?int $resolverId = null): array {
        $where  = '1=1';
        $params = [];

        if ($studentId) {
            $where = 'student_id = :uid';
            $params[':uid'] = $studentId;
        } elseif ($resolverId) {
            $where = 'resolver_id = :uid';
            $params[':uid'] = $resolverId;
        }

        $stmt = $this->db->prepare(
            "SELECT
               COUNT(*)                          AS total,
               SUM(status = 'Pending')           AS pending,
               SUM(status = 'Assigned')          AS assigned,
               SUM(status = 'In Progress')       AS in_progress,
               SUM(status = 'Resolved')          AS resolved,
               SUM(priority = 'Critical')        AS critical,
               SUM(priority = 'High')            AS high,
               ROUND(AVG(progress),0)            AS avg_progress
             FROM complaints WHERE $where"
        );
        $stmt->execute($params);
        return $stmt->fetch();
    }

    // ── Delete complaint (student, before assignment) ─────────
    public function delete(int $complaintId, int $studentId): bool {
        $stmt = $this->db->prepare(
            "DELETE FROM complaints
             WHERE complaint_id = ? AND student_id = ? AND status = 'Pending'"
        );
        return $stmt->execute([$complaintId, $studentId]);
    }
}
