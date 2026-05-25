<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Matiere.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$matiere = new Matiere($db);

$matiere->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($matiere->delete()) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'delete_matiere', json_encode(['id' => $matiere->id]));
    echo json_encode(['message' => 'Matière supprimée']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
