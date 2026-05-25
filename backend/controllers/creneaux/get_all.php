<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);

$filters = [];
if (isset($_GET['groupe_id'])) {
    $filters['groupe_id'] = (int) $_GET['groupe_id'];
}
if (isset($_GET['enseignant_id'])) {
    $filters['enseignant_id'] = (int) $_GET['enseignant_id'];
}
if (isset($_GET['salle_id'])) {
    $filters['salle_id'] = (int) $_GET['salle_id'];
}
if (isset($_GET['date_debut'])) {
    $filters['date_debut'] = $_GET['date_debut'];
}
if (isset($_GET['date_fin'])) {
    $filters['date_fin'] = $_GET['date_fin'];
}

$result = $creneau->getAll($filters);
$rows = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $rows[] = $row;
}

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
?>
