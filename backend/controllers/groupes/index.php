<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Groupe.php';

$database = new Database();
$db = $database->connect();
$groupe = new Groupe($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $groupe->getAll();
    $arr = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) $arr[] = $row;
    echo json_encode($arr);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    $groupe->nom        = $data->nom;
    $groupe->niveau     = $data->niveau;
    $groupe->filiere_id = $data->filiere_id;
    $groupe->capacite   = $data->capacite ?? 30;

    if (isset($data->id)) {
        $groupe->id = $data->id;
        echo json_encode($groupe->update() ? ['message' => 'Groupe mis à jour'] : ['message' => 'Erreur']);
    } else {
        echo json_encode($groupe->create() ? ['message' => 'Groupe créé'] : ['message' => 'Erreur']);
    }
}
