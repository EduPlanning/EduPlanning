<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: DELETE, GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Groupe.php';

$database = new Database();
$db = $database->connect();
$groupe = new Groupe($db);

$groupe->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($groupe->delete()) {
    echo json_encode(['message' => 'Groupe supprimé']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
