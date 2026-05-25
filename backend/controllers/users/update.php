<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole('administrateur');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Enseignant.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);
$enseignant = new Enseignant($db);

$data = json_decode(file_get_contents('php://input'));

if (!isset($data->id)) {
    echo json_encode(['message' => 'ID utilisateur requis'], JSON_UNESCAPED_UNICODE);
    exit;
}

$user->id = (int) $data->id;
$user->nom = $data->nom ?? null;
$user->prenom = $data->prenom ?? null;
$user->email = isset($data->email) ? filter_var(trim($data->email), FILTER_SANITIZE_EMAIL) : null;
$user->role = $data->role ?? null;
$user->actif = isset($data->actif) ? (int) $data->actif : 1;
$user->groupe_id = isset($data->groupe_id) && (int) $data->groupe_id > 0 ? (int) $data->groupe_id : null;

if (!$user->update()) {
    echo json_encode(['message' => 'Erreur lors de la mise à jour'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($user->role === 'enseignant') {
    $enseignant->ensureForUser((int) $user->id);
}

$hist = new Historique($db);
$hist->log(getCurrentUserId(), 'update_utilisateur', json_encode([
    'id' => (int) $user->id,
    'role' => $user->role
], JSON_UNESCAPED_UNICODE));

echo json_encode(['message' => 'Utilisateur mis à jour'], JSON_UNESCAPED_UNICODE);
?>
