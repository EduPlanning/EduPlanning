<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Salle.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$salle = new Salle($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $salle->getAll();
    $rows = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
}

requireRole(['enseignant', 'administrateur']);
$data = json_decode(file_get_contents('php://input'));
if (!isset($data->nom)) {
    echo json_encode(['message' => 'Nom de salle requis'], JSON_UNESCAPED_UNICODE);
    exit;
}

$salle->nom = $data->nom;
$salle->capacite = isset($data->capacite) ? max(1, (int) $data->capacite) : 30;
$salle->equipements = $data->equipements ?? '';
$salle->disponible = isset($data->disponible) ? (int) $data->disponible : 1;

$success = false;
$action = '';
if (isset($data->id) && (int) $data->id > 0) {
    $salle->id = (int) $data->id;
    $success = $salle->update();
    $action = 'update_salle';
} else {
    $success = $salle->create();
    $action = 'create_salle';
}

if ($success) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), $action, json_encode([
        'id' => (int) ($salle->id ?? 0),
        'nom' => $salle->nom
    ], JSON_UNESCAPED_UNICODE));
}

$message = $success ? ['message' => 'Salle enregistrée', 'id' => (int) ($salle->id ?? 0)] : ['message' => 'Erreur'];
echo json_encode($message, JSON_UNESCAPED_UNICODE);
?>
