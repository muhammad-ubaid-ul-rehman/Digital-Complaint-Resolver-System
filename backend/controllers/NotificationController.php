<?php
// ============================================================
//  DCRS — Notification Controller (AJAX)
//  File: backend/controllers/NotificationController.php
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

Session::requireLogin();

$notifModel = new NotificationModel();
$userId     = Session::userId();
$action     = $_GET['action'] ?? '';

switch ($action) {

    case 'get':
        $notifs = $notifModel->getForUser($userId, 15);
        $count  = $notifModel->unreadCount($userId);
        echo json_encode([
            'success'       => true,
            'notifications' => $notifs,
            'unread_count'  => $count,
        ]);
        break;

    case 'mark_all_read':
        $notifModel->markAllRead($userId);
        echo json_encode(['success' => true, 'unread_count' => 0]);
        break;

    case 'mark_read':
        $notifId = (int)($_POST['notification_id'] ?? 0);
        if ($notifId) {
            $notifModel->markRead($notifId, $userId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
        break;

    case 'count':
        echo json_encode([
            'success'      => true,
            'unread_count' => $notifModel->unreadCount($userId),
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
