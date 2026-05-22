<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Salle.php';

$database = new Database();
$db = $database->connect();
$salle = new Salle($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $salle->getAll();
    $arr = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) $arr[] = $row;
    echo json_encode($arr);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    $salle->nom         = $data->nom;
    $salle->capacite    = $data->capacite ?? 30;
    $salle->equipements = $data->equipements ?? '';
    $salle->disponible  = $data->disponible ?? 1;

    if (isset($data->id)) {
        $salle->id = $data->id;
        echo json_encode($salle->update()
            ? ['message' => 'Salle mise à jour']
            : ['message' => 'Erreur']);
    } else {
        echo json_encode($salle->create()
            ? ['message' => 'Salle créée']
            : ['message' => 'Erreur']);
    }
}
