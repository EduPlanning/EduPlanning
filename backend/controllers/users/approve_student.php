<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole('administrateur');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Notification.php';
include_once '../../models/Historique.php';

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
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'approve_etudiant', json_encode(['id' => $student_id]));
    // Notify student
    $notif = new Notification($db);
    $notif->utilisateur_id = $student_id;
    $notif->message = 'Votre compte étudiant a été validé par un administrateur.';
    $notif->create();

    echo json_encode(['message' => 'Étudiant activé et notifié']);
} else {
    echo json_encode(['message' => 'Erreur lors de l\'activation']);
}
