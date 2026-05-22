<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);

$data = json_decode(file_get_contents('php://input'));

if (!isset($data->id)) {
    echo json_encode(['message' => 'ID utilisateur requis']);
    exit;
}

$user->id = $data->id;
$user->nom = $data->nom ?? null;
$user->prenom = $data->prenom ?? null;
$user->email = $data->email ?? null;
$user->role = $data->role ?? null;
$user->actif = isset($data->actif) ? intval($data->actif) : 1;

if ($user->update()) {
    echo json_encode(['message' => 'Utilisateur mis à jour']);
} else {
    echo json_encode(['message' => 'Erreur lors de la mise à jour']);
}
