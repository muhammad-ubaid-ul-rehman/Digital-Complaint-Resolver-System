<?php
// ============================================================
//  DCRS — Helper Functions
//  File: backend/helpers/helpers.php
// ============================================================

// ── JSON response (for API endpoints) ───────────────────────
function jsonResponse(bool $success, string $message, array $data = [], int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data,
    ]);
    exit;
}

// ── Redirect ─────────────────────────────────────────────────
function redirect(string $url): never {
    header("Location: $url");
    exit;
}

// ── Sanitize input ───────────────────────────────────────────
function clean(mixed $data): string {
    return htmlspecialchars(strip_tags(trim((string)$data)), ENT_QUOTES, 'UTF-8');
}

// ── Generate complaint code  CMP-0001 ───────────────────────
function generateComplaintCode(PDO $pdo): string {
    $stmt = $pdo->query("SELECT COUNT(*) AS total FROM complaints");
    $row  = $stmt->fetch();
    $next = (int)$row['total'] + 1;
    return 'CMP-' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

// ── Time ago ─────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $ago  = new DateTime($datetime);
    $diff = $now->diff($ago);

    if ($diff->y > 0) return $diff->y . ' year'   . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month'  . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day'    . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour'   . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

// ── Priority badge HTML ──────────────────────────────────────
function priorityBadge(string $priority): string {
    $map = [
        'Low'      => 'success',
        'Medium'   => 'warning',
        'High'     => 'danger',
        'Critical' => 'dark-red',
    ];
    $cls = $map[$priority] ?? 'secondary';
    return "<span class=\"badge badge-{$cls}\">{$priority}</span>";
}

// ── Status badge HTML ────────────────────────────────────────
function statusBadge(string $status): string {
    $map = [
        'Pending'     => 'secondary',
        'Assigned'    => 'primary',
        'In Progress' => 'warning',
        'Resolved'    => 'success',
        'Closed'      => 'dark',
    ];
    $cls = $map[$status] ?? 'secondary';
    return "<span class=\"badge badge-{$cls}\">{$status}</span>";
}

// ── Progress bar HTML ────────────────────────────────────────
function progressBar(int $pct, string $color = 'primary'): string {
    return <<<HTML
    <div class="progress" style="height:8px;">
      <div class="progress-bar bg-{$color}" role="progressbar"
           style="width:{$pct}%;" aria-valuenow="{$pct}"
           aria-valuemin="0" aria-valuemax="100">{$pct}%</div>
    </div>
    HTML;
}

// ── Validate email ───────────────────────────────────────────
function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// ── Get unread notification count for current user ───────────
function unreadCount(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0"
    );
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}
