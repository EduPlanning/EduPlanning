<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Enseignant.php';
include_once '../../models/Historique.php';
include_once '../../middleware/auth.php';

requireRole('administrateur');

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);
$enseignant = new Enseignant($db);

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->nom, $data->prenom, $data->email, $data->mot_de_passe)) {
    echo json_encode(['message' => 'Données manquantes'], JSON_UNESCAPED_UNICODE);
    exit;
}

$allowedRoles = ['administrateur', 'enseignant', 'etudiant'];
$role = in_array($data->role ?? 'etudiant', $allowedRoles, true) ? $data->role : 'etudiant';

$user->nom = $data->nom;
$user->prenom = $data->prenom;
$user->email = filter_var(trim($data->email), FILTER_SANITIZE_EMAIL);
$user->mot_de_passe = $data->mot_de_passe;
$user->role = $role;
$user->actif = isset($data->actif) ? (int) $data->actif : 1;
$user->groupe_id = isset($data->groupe_id) && (int) $data->groupe_id > 0 ? (int) $data->groupe_id : null;

try {
    if (!$user->register()) {
        echo json_encode(['message' => 'Erreur lors de la création de l\'utilisateur'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($role === 'enseignant') {
        $enseignant->ensureForUser((int) $user->id);
    }

    $historique = new Historique($db);
    $historique->log(getCurrentUserId(), 'create_utilisateur', json_encode([
        'id' => (int) $user->id,
        'role' => $role,
        'email' => $user->email
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['message' => 'Utilisateur créé', 'id' => (int) $user->id], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    $message = ((int) $e->getCode() === 23000) ? 'Cet email existe déjà.' : 'Erreur lors de la création de l\'utilisateur';
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
}
?>
