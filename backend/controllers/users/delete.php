<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: DELETE, GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);

$user->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user->id < 1) {
    echo json_encode(['message' => 'ID invalide']);
    exit;
}

if ($user->delete()) {
    echo json_encode(['message' => 'Utilisateur supprimé']);
} else {
    echo json_encode(['message' => 'Erreur suppression']);
}
