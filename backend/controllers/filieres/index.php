<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Filiere.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$filiere = new Filiere($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $filiere->getAll();
    $rows = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $rows[] = $row;
    }
    echo json_encode($rows, JSON_UNESCAPED_UNICODE);
    exit;
}

requireRole('administrateur');
$data = json_decode(file_get_contents('php://input'));

if (!isset($data->nom, $data->code)) {
    echo json_encode(['message' => 'Nom et code requis'], JSON_UNESCAPED_UNICODE);
    exit;
}

$filiere->nom = $data->nom;
$filiere->code = $data->code;

try {
    $success = false;
    $action = '';

    if (isset($data->id) && (int) $data->id > 0) {
        $filiere->id = (int) $data->id;
        $success = $filiere->update();
        $action = 'update_filiere';
    } else {
        $success = $filiere->create();
        $action = 'create_filiere';
    }

    if (!$success) {
        echo json_encode(['message' => 'Erreur lors de l\'enregistrement'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), $action, json_encode([
        'id' => (int) ($filiere->id ?? 0),
        'nom' => $filiere->nom,
        'code' => $filiere->code
    ], JSON_UNESCAPED_UNICODE));

    $verb = isset($data->id) && (int) $data->id > 0 ? 'mise à jour' : 'créée';
    echo json_encode(['message' => "Filière {$verb}", 'id' => (int) ($filiere->id ?? 0)], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    $message = ((int) $e->getCode() === 23000) ? 'Ce code filière existe déjà.' : 'Erreur lors de l\'enregistrement';
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
}
?>
