<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Salle.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$salle = new Salle($db);

$salle->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($salle->delete()) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'delete_salle', json_encode(['id' => $salle->id]));
    echo json_encode(['message' => 'Salle supprimée']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
