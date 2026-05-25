<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Notification.php';

$database = new Database();
$db = $database->connect();
$notif = new Notification($db);
$notif->utilisateur_id = (int) getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $notif->getByUser();
    $rows = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }

    echo json_encode([
        'notifications' => $rows,
        'non_lues' => $notif->countUnread()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode(file_get_contents('php://input'));
if (($data->action ?? '') === 'mark_read') {
    $notif->markAllRead();
    echo json_encode(['message' => 'Notifications marquées comme lues'], JSON_UNESCAPED_UNICODE);
}
?>
