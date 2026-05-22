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

if (!isset($data->nom, $data->prenom, $data->email, $data->mot_de_passe)) {
    echo json_encode(['message' => 'Données manquantes']);
    exit;
}

$user->nom         = $data->nom;
$user->prenom      = $data->prenom;
$user->email       = $data->email;
$user->mot_de_passe = $data->mot_de_passe;
$user->role        = $data->role ?? 'etudiant';

if ($user->register()) {
    echo json_encode(['message' => 'Compte créé', 'id' => $user->id]);
} else {
    echo json_encode(['message' => 'Erreur lors de la création du compte']);
}
