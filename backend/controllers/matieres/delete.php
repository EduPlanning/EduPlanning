<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: DELETE, GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Matiere.php';

$database = new Database();
$db = $database->connect();
$matiere = new Matiere($db);

$matiere->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($matiere->delete()) {
    echo json_encode(['message' => 'Matière supprimée']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
