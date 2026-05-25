<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole('administrateur');

include_once '../../config/Database.php';

$database = new Database();
$db = $database->connect();

$q = $db->prepare('SELECT p.*, u.nom as auteur_nom, u.prenom as auteur_prenom FROM proposition p JOIN utilisateur u ON p.auteur_id = u.id ORDER BY p.cree_le DESC');
$q->execute();
$arr = [];
while ($row = $q->fetch(PDO::FETCH_ASSOC)) $arr[] = $row;
echo json_encode($arr);
