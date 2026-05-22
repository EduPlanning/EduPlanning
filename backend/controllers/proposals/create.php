<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Notification.php';

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->auteur_id, $data->resource, $data->action, $data->payload)) {
    echo json_encode(['message' => 'Données manquantes']);
    exit;
}

$auteur_id = intval($data->auteur_id);
$resource = substr($data->resource, 0, 100);
$action = substr($data->action, 0, 20);
$cible_id = isset($data->cible_id) ? (int)$data->cible_id : null;
$payload = json_encode($data->payload);

$q = $db->prepare('INSERT INTO proposition (auteur_id, resource, action, cible_id, payload) VALUES (:auteur,:resource,:action,:cible,:payload)');
$q->bindParam(':auteur', $auteur_id);
$q->bindParam(':resource', $resource);
$q->bindParam(':action', $action);
$q->bindParam(':cible', $cible_id);
$q->bindParam(':payload', $payload);

if ($q->execute()) {
    $pid = $db->lastInsertId();
    // Notify admins
    $notif = new Notification($db);
    $r = $db->prepare('SELECT id, nom, prenom FROM utilisateur WHERE role = "administrateur"');
    $r->execute();
    $author = $db->prepare('SELECT nom, prenom FROM utilisateur WHERE id = :id LIMIT 1');
    $author->bindParam(':id', $auteur_id);
    $author->execute();
    $authorRow = $author->fetch(PDO::FETCH_ASSOC);
    $authorName = $authorRow ? ($authorRow['prenom'] . ' ' . $authorRow['nom']) : 'Un enseignant';
    while ($admin = $r->fetch(PDO::FETCH_ASSOC)) {
        $notif->utilisateur_id = $admin['id'];
        $notif->message = "Proposition #{$pid} : {$resource} {$action} proposée par {$authorName}";
        $notif->create();
    }

    echo json_encode(['message' => 'Proposition créée', 'id' => $pid]);
} else {
    echo json_encode(['message' => 'Erreur lors de la création']);
}
