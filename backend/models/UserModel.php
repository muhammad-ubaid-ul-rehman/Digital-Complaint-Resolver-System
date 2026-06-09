<?php
// ============================================================
//  DCRS — User Model
//  File: backend/models/UserModel.php
// ============================================================

class UserModel {
    private PDO $db;

    public function __construct() {
        $this->db = DB::get();
    }

    // ── Find by email ─────────────────────────────────────────
    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Find by ID ────────────────────────────────────────────
    public function findById(int $id): ?array {
        $stmt = $this->db->prepare(
            "SELECT user_id, name, email, role, department, phone, created_at
             FROM users WHERE user_id = ? AND is_active = 1 LIMIT 1"
        );
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Register new user ─────────────────────────────────────
    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO users (name, email, password, role, department, phone)
             VALUES (:name, :email, :password, :role, :department, :phone)"
        );
        $stmt->execute([
            ':name'       => clean($data['name']),
            ':email'      => strtolower(trim($data['email'])),
            ':password'   => password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            ':role'       => $data['role'] ?? 'student',
            ':department' => $data['department'] ?? null,
            ':phone'      => $data['phone'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    // ── Verify password ───────────────────────────────────────
    public function verifyPassword(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }

    // ── Get all resolvers (for admin assign dropdown) ─────────
    public function getResolvers(): array {
        $stmt = $this->db->query(
            "SELECT user_id, name, department
             FROM users WHERE role = 'resolver' AND is_active = 1
             ORDER BY name ASC"
        );
        return $stmt->fetchAll();
    }

    // ── Get all users (admin panel) ───────────────────────────
    public function getAll(): array {
        $stmt = $this->db->query(
            "SELECT user_id, name, email, role, department, phone,
                    is_active, created_at
             FROM users ORDER BY role, name"
        );
        return $stmt->fetchAll();
    }

    // ── Update profile ────────────────────────────────────────
    public function updateProfile(int $userId, array $data): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET name = :name, department = :dept, phone = :phone
             WHERE user_id = :id"
        );
        return $stmt->execute([
            ':name' => clean($data['name']),
            ':dept' => $data['department'] ?? null,
            ':phone'=> $data['phone'] ?? null,
            ':id'   => $userId,
        ]);
    }

    // ── Change password ───────────────────────────────────────
    public function changePassword(int $userId, string $newPassword): bool {
        $stmt = $this->db->prepare(
            "UPDATE users SET password = ? WHERE user_id = ?"
        );
        return $stmt->execute([
            password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => BCRYPT_COST]),
            $userId,
        ]);
    }

    // ── Email exists check ────────────────────────────────────
    public function emailExists(string $email): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM users WHERE email = ?"
        );
        $stmt->execute([strtolower(trim($email))]);
        return (int)$stmt->fetchColumn() > 0;
    }

    // ── Resolver stats (for reports) ─────────────────────────
    public function resolverStats(): array {
        $stmt = $this->db->query(
            "SELECT u.user_id, u.name, u.department,
                    COUNT(c.complaint_id)                                  AS total_assigned,
                    SUM(c.status = 'Resolved')                             AS total_resolved,
                    SUM(c.status IN ('Assigned','In Progress'))            AS total_pending,
                    ROUND(AVG(c.progress),0)                               AS avg_progress,
                    ROUND(
                      SUM(c.status='Resolved') / NULLIF(COUNT(c.complaint_id),0) * 100
                    ,0)                                                    AS resolution_rate
             FROM users u
             LEFT JOIN complaints c ON c.resolver_id = u.user_id
             WHERE u.role = 'resolver'
             GROUP BY u.user_id, u.name, u.department
             ORDER BY total_resolved DESC"
        );
        return $stmt->fetchAll();
    }
}
