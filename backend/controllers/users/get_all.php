<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole('administrateur');

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
