<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Notification.php';
include_once '../../models/Enseignant.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);

$data = json_decode(file_get_contents('php://input'));

if (!isset($data->nom, $data->prenom, $data->email, $data->mot_de_passe, $data->enseignant_utilisateur_id)) {
    echo json_encode(['message' => 'Données manquantes']);
    exit;
}

// Verify enseignant exists
$ensModel = new Enseignant($db);
$ensModel->id = intval($data->enseignant_id ?? 0);
$okEnseignant = false;
if (!empty($data->enseignant_utilisateur_id)) {
    // try to find enseignant by utilisateur_id
    $q = $db->prepare('SELECT id, utilisateur_id FROM enseignant WHERE utilisateur_id = :uid LIMIT 1');
    $q->bindParam(':uid', $data->enseignant_utilisateur_id);
    $q->execute();
    if ($q->rowCount() > 0) $okEnseignant = true;
}

if (!$okEnseignant) {
    echo json_encode(['message' => 'Enseignant introuvable']);
    exit;
}

$user->nom = $data->nom;
$user->prenom = $data->prenom;
$user->email = $data->email;
$user->mot_de_passe = $data->mot_de_passe;
$user->role = 'etudiant';

// create student as inactive (requires admin validation)
$groupe_id = isset($data->groupe_id) && intval($data->groupe_id) > 0 ? intval($data->groupe_id) : null;

// if groupe_id provided, verify it exists
if ($groupe_id) {
    $qg = $db->prepare('SELECT id FROM `groupe` WHERE id = :id LIMIT 1');
    $qg->bindParam(':id', $groupe_id);
    $qg->execute();
    if ($qg->rowCount() === 0) {
        echo json_encode(['message' => 'Groupe introuvable']);
        exit;
    }
}

$query = 'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, actif, groupe_id) VALUES (:nom,:prenom,:email,:mot_de_passe,:role,0,:groupe_id)';
$stmt = $db->prepare($query);
$nom = htmlspecialchars(strip_tags($user->nom));
$prenom = htmlspecialchars(strip_tags($user->prenom));
$email = htmlspecialchars(strip_tags($user->email));
$hash = password_hash($user->mot_de_passe, PASSWORD_BCRYPT, ['cost' => 12]);
$role = $user->role;
$stmt->bindParam(':nom', $nom);
$stmt->bindParam(':prenom', $prenom);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':mot_de_passe', $hash);
$stmt->bindParam(':role', $role);
// bind groupe id (nullable)
$stmt->bindValue(':groupe_id', $groupe_id, $groupe_id === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

if ($stmt->execute()) {
    $newId = $db->lastInsertId();

    // Notify all administrators
    $q = $db->prepare('SELECT id, nom, prenom FROM utilisateur WHERE role = "administrateur"');
    $q->execute();
    $notifModel = new Notification($db);
    $teacherUid = intval($data->enseignant_utilisateur_id);
    $teacherRow = null;
    $qt = $db->prepare('SELECT nom, prenom FROM utilisateur WHERE id = :id LIMIT 1');
    $qt->bindParam(':id', $teacherUid);
    $qt->execute();
    if ($qt->rowCount() > 0) $teacherRow = $qt->fetch(PDO::FETCH_ASSOC);

    while ($admin = $q->fetch(PDO::FETCH_ASSOC)) {
        $notifModel->utilisateur_id = $admin['id'];
        $tname = $teacherRow ? ($teacherRow['prenom'] . ' ' . $teacherRow['nom']) : 'Un enseignant';
        $notifModel->message = "Nouvel étudiant en attente de validation : {$nom} {$prenom} ({$email}), créé par {$tname}";
        $notifModel->create();
    }

    echo json_encode(['message' => 'Étudiant créé et en attente de validation', 'id' => $newId]);
} else {
    echo json_encode(['message' => 'Erreur lors de la création de l\'étudiant']);
}
