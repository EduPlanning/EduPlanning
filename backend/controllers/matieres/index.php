<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Matiere.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$matiere = new Matiere($db);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $result = $matiere->getAll();
    $rows = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
}

requireRole(['enseignant', 'administrateur']);
$data = json_decode(file_get_contents('php://input'));
if (!isset($data->nom, $data->code)) {
    echo json_encode(['message' => 'Nom et code requis'], JSON_UNESCAPED_UNICODE);
    exit;
}

$matiere->nom = $data->nom;
$matiere->code = $data->code;
$matiere->volume_horaire = isset($data->volume_horaire) ? max(0, (int) $data->volume_horaire) : 0;
$matiere->coefficient = isset($data->coefficient) ? max(0, (float) $data->coefficient) : 1;

$success = false;
$action = '';
if (isset($data->id) && (int) $data->id > 0) {
    $matiere->id = (int) $data->id;
    $success = $matiere->update();
    $action = 'update_matiere';
} else {
    $success = $matiere->create();
    $action = 'create_matiere';
}

if ($success) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), $action, json_encode([
        'id' => (int) ($matiere->id ?? 0),
        'code' => $matiere->code
    ], JSON_UNESCAPED_UNICODE));
}

$message = $success ? ['message' => 'Matière enregistrée', 'id' => (int) ($matiere->id ?? 0)] : ['message' => 'Erreur'];
echo json_encode($message, JSON_UNESCAPED_UNICODE);
?>
