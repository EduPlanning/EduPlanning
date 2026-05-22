<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: DELETE, GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);

$creneau->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($creneau->delete()) {
    echo json_encode(['message' => 'Créneau supprimé']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
