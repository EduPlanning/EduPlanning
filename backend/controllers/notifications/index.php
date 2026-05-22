<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Notification.php';

$database = new Database();
$db = $database->connect();
$notif = new Notification($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $notif->utilisateur_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $result = $notif->getByUser();
    $arr = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) $arr[] = $row;
    $unread = $notif->countUnread();
    echo json_encode(['notifications' => $arr, 'non_lues' => $unread]);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    if (isset($data->action) && $data->action === 'mark_read') {
        $notif->utilisateur_id = $data->user_id;
        $notif->markAllRead();
        echo json_encode(['message' => 'Notifications marquées comme lues']);
    }
}
