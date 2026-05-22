<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Enseignant.php';

$database = new Database();
$db = $database->connect();
$enseignant = new Enseignant($db);

$result = $enseignant->getAll();
$arr = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $arr[] = $row;
}
echo json_encode($arr);
