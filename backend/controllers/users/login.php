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

if (!isset($data->email, $data->mot_de_passe)) {
    echo json_encode(['message' => 'Email et mot de passe requis']);
    exit;
}

$user->email = $data->email;
$result = $user->login();

if ($result->rowCount() > 0) {
    $row = $result->fetch(PDO::FETCH_ASSOC);
    if (password_verify($data->mot_de_passe, $row['mot_de_passe'])) {
        unset($row['mot_de_passe']); // never send hash to client
        echo json_encode(['message' => 'Connexion réussie', 'utilisateur' => $row]);
    } else {
        echo json_encode(['message' => 'Mot de passe incorrect']);
    }
} else {
    echo json_encode(['message' => 'Utilisateur introuvable']);
}
