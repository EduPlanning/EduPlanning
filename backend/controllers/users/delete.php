<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole('administrateur');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);

$user->id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user->id < 1) {
    echo json_encode(['message' => 'ID invalide']);
    exit;
}

if ($user->delete()) {
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'delete_utilisateur', json_encode(['id' => $user->id]));
    echo json_encode(['message' => 'Utilisateur supprimé']);
} else {
    echo json_encode(['message' => 'Erreur lors de la suppression']);
}
