<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Notification.php';
include_once '../../models/Historique.php';

$database = new Database();
$db = $database->connect();
$data = json_decode(file_get_contents('php://input'));

if (!isset($data->nom, $data->prenom, $data->email, $data->mot_de_passe)) {
    echo json_encode(['message' => 'Données manquantes'], JSON_UNESCAPED_UNICODE);
    exit;
}

$teacherUid = (int) getCurrentUserId();
$teacherLookup = $db->prepare('SELECT id FROM enseignant WHERE utilisateur_id = :uid LIMIT 1');
$teacherLookup->bindParam(':uid', $teacherUid);
$teacherLookup->execute();
if ($teacherLookup->rowCount() === 0) {
    echo json_encode(['message' => 'Enseignant introuvable'], JSON_UNESCAPED_UNICODE);
    exit;
}

$nom = htmlspecialchars(strip_tags(trim($data->nom)));
$prenom = htmlspecialchars(strip_tags(trim($data->prenom)));
$email = filter_var(trim($data->email), FILTER_SANITIZE_EMAIL);
$motDePasse = (string) $data->mot_de_passe;
$groupeId = isset($data->groupe_id) && (int) $data->groupe_id > 0 ? (int) $data->groupe_id : null;

if (strlen($motDePasse) < 8) {
    echo json_encode(['message' => 'Le mot de passe doit contenir au moins 8 caractères.'], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($groupeId !== null) {
    $groupeLookup = $db->prepare('SELECT id FROM `groupe` WHERE id = :id LIMIT 1');
    $groupeLookup->bindParam(':id', $groupeId);
    $groupeLookup->execute();
    if ($groupeLookup->rowCount() === 0) {
        echo json_encode(['message' => 'Groupe introuvable'], JSON_UNESCAPED_UNICODE);
        exit;
    }
}

$query = 'INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, role, actif, groupe_id)
          VALUES (:nom, :prenom, :email, :mot_de_passe, "etudiant", 0, :groupe_id)';
$stmt = $db->prepare($query);
$hash = password_hash($motDePasse, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt->bindParam(':nom', $nom);
$stmt->bindParam(':prenom', $prenom);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':mot_de_passe', $hash);
$stmt->bindValue(':groupe_id', $groupeId, $groupeId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

try {
    if (!$stmt->execute()) {
        echo json_encode(['message' => 'Erreur lors de la création de l\'étudiant'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $newId = (int) $db->lastInsertId();

    $hist = new Historique($db);
    $hist->log($teacherUid, 'create_etudiant', json_encode([
        'id' => $newId,
        'email' => $email
    ], JSON_UNESCAPED_UNICODE));

    $teacherQuery = $db->prepare('SELECT nom, prenom FROM utilisateur WHERE id = :id LIMIT 1');
    $teacherQuery->bindParam(':id', $teacherUid);
    $teacherQuery->execute();
    $teacher = $teacherQuery->fetch(PDO::FETCH_ASSOC);
    $teacherName = $teacher ? ($teacher['prenom'] . ' ' . $teacher['nom']) : 'Un enseignant';

    $admins = $db->query('SELECT id FROM utilisateur WHERE role = "administrateur"');
    $notif = new Notification($db);
    while ($admin = $admins->fetch(PDO::FETCH_ASSOC)) {
        $notif->utilisateur_id = (int) $admin['id'];
        $notif->message = "Nouvel étudiant en attente de validation : {$nom} {$prenom} ({$email}), créé par {$teacherName}";
        $notif->create();
    }

    echo json_encode(['message' => 'Étudiant créé et en attente de validation', 'id' => $newId], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    $message = ((int) $e->getCode() === 23000) ? 'Cet email existe déjà.' : 'Erreur lors de la création de l\'étudiant';
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
}
?>
