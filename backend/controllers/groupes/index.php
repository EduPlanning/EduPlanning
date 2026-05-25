<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Groupe.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$groupe = new Groupe($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $groupe->getAll();
    $rows = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
}

requireRole(['enseignant', 'administrateur']);
$data = json_decode(file_get_contents('php://input'));
if (!isset($data->nom, $data->niveau, $data->filiere_id)) {
    echo json_encode(['message' => 'Nom, niveau et filière requis'], JSON_UNESCAPED_UNICODE);
    exit;
}

$groupe->nom = $data->nom;
$groupe->niveau = $data->niveau;
$groupe->filiere_id = (int) $data->filiere_id;
$groupe->capacite = isset($data->capacite) ? max(1, (int) $data->capacite) : 30;

$success = false;
$action = '';
if (isset($data->id) && (int) $data->id > 0) {
    $groupe->id = (int) $data->id;
    $success = $groupe->update();
    $action = 'update_groupe';
} else {
    $success = $groupe->create();
    $action = 'create_groupe';
}

if ($success) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), $action, json_encode([
        'id' => (int) ($groupe->id ?? 0),
        'nom' => $groupe->nom
    ], JSON_UNESCAPED_UNICODE));
}

$message = $success ? ['message' => 'Groupe enregistré', 'id' => (int) ($groupe->id ?? 0)] : ['message' => 'Erreur'];
echo json_encode($message, JSON_UNESCAPED_UNICODE);
?>
