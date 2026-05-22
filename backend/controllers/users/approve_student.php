<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Notification.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->student_id)) {
    echo json_encode(['message' => 'student_id requis']);
    exit;
}

$student_id = intval($data->student_id);

// Activate student
$q = $db->prepare('UPDATE utilisateur SET actif = 1 WHERE id = :id');
$q->bindParam(':id', $student_id);
if ($q->execute()) {
    // Notify student
    $notif = new Notification($db);
    $notif->utilisateur_id = $student_id;
    $notif->message = 'Votre compte étudiant a été validé par un administrateur.';
    $notif->create();

    echo json_encode(['message' => 'Étudiant activé et notifié']);
} else {
    echo json_encode(['message' => 'Erreur lors de l\'activation']);
}
