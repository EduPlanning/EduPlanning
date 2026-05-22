<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);

$filters = [];
if (isset($_GET['groupe_id']))     $filters['groupe_id']     = intval($_GET['groupe_id']);
if (isset($_GET['enseignant_id'])) $filters['enseignant_id'] = intval($_GET['enseignant_id']);
if (isset($_GET['salle_id']))      $filters['salle_id']      = intval($_GET['salle_id']);
if (isset($_GET['date_debut']))    $filters['date_debut']    = $_GET['date_debut'];
if (isset($_GET['date_fin']))      $filters['date_fin']      = $_GET['date_fin'];

$result = $creneau->getAll($filters);
$num    = $result->rowCount();

if ($num > 0) {
    $arr = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row;
    }
    echo json_encode($arr);
} else {
    echo json_encode(['message' => 'Aucun créneau trouvé']);
}
