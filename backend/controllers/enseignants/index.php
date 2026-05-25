<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

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
