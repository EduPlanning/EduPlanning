<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Groupe.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$groupe = new Groupe($db);

$groupe->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($groupe->delete()) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'delete_groupe', json_encode(['id' => $groupe->id]));
    echo json_encode(['message' => 'Groupe supprimé']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
