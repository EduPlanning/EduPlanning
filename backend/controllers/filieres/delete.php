<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole('administrateur');

include_once '../../config/Database.php';
include_once '../../models/Filiere.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$filiere = new Filiere($db);

$filiere->id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($filiere->id < 1) {
    echo json_encode(['message' => 'ID invalide'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$filiere->delete()) {
    echo json_encode(['message' => 'Erreur lors de la suppression'], JSON_UNESCAPED_UNICODE);
    exit;
}

$hist = new Historique($db);
$hist->log(getCurrentUserId(), 'delete_filiere', json_encode(['id' => $filiere->id], JSON_UNESCAPED_UNICODE));

echo json_encode(['message' => 'Filière supprimée'], JSON_UNESCAPED_UNICODE);
?>
