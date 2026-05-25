<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth(); // any logged user can submit proposal (though UI blocks etudiants from edit pages)

include_once '../../config/Database.php';
include_once '../../models/Notification.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->resource, $data->action, $data->payload)) {
    echo json_encode(['message' => 'Données manquantes'], JSON_UNESCAPED_UNICODE);
    exit;
}

$allowedResources = ['creneau', 'groupe', 'matiere', 'salle'];
$allowedActions = ['create', 'update', 'delete'];

$resource = htmlspecialchars(strip_tags(substr((string) $data->resource, 0, 100)));
$action = htmlspecialchars(strip_tags(substr((string) $data->action, 0, 20)));
if (!in_array($resource, $allowedResources, true) || !in_array($action, $allowedActions, true)) {
    echo json_encode(['message' => 'Type de proposition invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

$authorId = (int) getCurrentUserId();
$cibleId = isset($data->cible_id) ? (int) $data->cible_id : null;
$payload = json_encode($data->payload, JSON_UNESCAPED_UNICODE);

$query = $db->prepare('INSERT INTO proposition (auteur_id, resource, action, cible_id, payload) VALUES (:auteur, :resource, :action, :cible, :payload)');
$query->bindParam(':auteur', $authorId);
$query->bindParam(':resource', $resource);
$query->bindParam(':action', $action);
$query->bindParam(':cible', $cibleId);
$query->bindParam(':payload', $payload);

if (!$query->execute()) {
    echo json_encode(['message' => 'Erreur lors de la création'], JSON_UNESCAPED_UNICODE);
    exit;
}

$proposalId = (int) $db->lastInsertId();
$notif = new Notification($db);
$admins = $db->query('SELECT id FROM utilisateur WHERE role = "administrateur"');
$authorQuery = $db->prepare('SELECT nom, prenom FROM utilisateur WHERE id = :id LIMIT 1');
$authorQuery->bindParam(':id', $authorId);
$authorQuery->execute();
$author = $authorQuery->fetch(PDO::FETCH_ASSOC);
$authorName = $author ? ($author['prenom'] . ' ' . $author['nom']) : 'Un enseignant';

while ($admin = $admins->fetch(PDO::FETCH_ASSOC)) {
    $notif->utilisateur_id = (int) $admin['id'];
    $notif->message = "Proposition #{$proposalId} : {$resource} {$action} proposée par {$authorName}";
    $notif->create();
}

$hist = new Historique($db);
$hist->log($authorId, 'create_proposal', json_encode([
    'id' => $proposalId,
    'resource' => $resource,
    'action' => $action
], JSON_UNESCAPED_UNICODE));

echo json_encode(['message' => 'Proposition créée', 'id' => $proposalId], JSON_UNESCAPED_UNICODE);
?>
