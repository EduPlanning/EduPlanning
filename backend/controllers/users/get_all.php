<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);

$role = isset($_GET['role']) ? $_GET['role'] : null;
$result = $user->getAll($role);
$num = $result->rowCount();

if ($num > 0) {
    $arr = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $arr[] = $row;
    }
    echo json_encode($arr);
} else {
    echo json_encode(['message' => 'Aucun utilisateur trouvé']);
}
