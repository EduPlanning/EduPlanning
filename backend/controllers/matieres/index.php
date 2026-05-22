<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Matiere.php';

$database = new Database();
$db = $database->connect();
$matiere = new Matiere($db);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $result = $matiere->getAll();
    $arr = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) $arr[] = $row;
    echo json_encode($arr);

} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'));
    $matiere->nom            = $data->nom;
    $matiere->code           = $data->code;
    $matiere->volume_horaire = $data->volume_horaire ?? 0;
    $matiere->coefficient    = $data->coefficient ?? 1;

    if (isset($data->id)) {
        $matiere->id = $data->id;
        echo json_encode($matiere->update() ? ['message' => 'Matière mise à jour'] : ['message' => 'Erreur']);
    } else {
        echo json_encode($matiere->create() ? ['message' => 'Matière créée'] : ['message' => 'Erreur']);
    }
}
