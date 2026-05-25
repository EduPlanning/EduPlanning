<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);

$creneau->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($creneau->delete()) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'delete_creneau', json_encode(['id' => $creneau->id]));
    echo json_encode(['message' => 'Créneau supprimé']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
