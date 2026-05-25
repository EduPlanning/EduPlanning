<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';
include_once '../../models/Notification.php';
include_once '../../models/Enseignant.php';
include_once '../../models/Historique.php';
include_once '../../utils/mailer.php';

function isValidDateValue($value)
{
    return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
}

function isValidTimeValue($value)
{
    return is_string($value) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $value) === 1;
}

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);
$data = json_decode(file_get_contents('php://input'));

if (!isset($data->date_cours, $data->heure_debut, $data->heure_fin, $data->matiere_id, $data->enseignant_id, $data->salle_id, $data->groupe_id)) {
    echo json_encode(['message' => 'Données manquantes'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isValidDateValue($data->date_cours) || !isValidTimeValue($data->heure_debut) || !isValidTimeValue($data->heure_fin) || strtotime($data->heure_fin) <= strtotime($data->heure_debut)) {
    echo json_encode(['message' => 'Date ou horaires invalides'], JSON_UNESCAPED_UNICODE);
    exit;
}

$matiereId = (int)($data->matiere_id ?? 0);
$enseignantId = (int)($data->enseignant_id ?? 0);
$salleId = (int)($data->salle_id ?? 0);
$groupeId = (int)($data->groupe_id ?? 0);
if ($matiereId <= 0 || $enseignantId <= 0 || $salleId <= 0 || $groupeId <= 0) {
    echo json_encode(['message' => 'Matière, enseignant, salle et groupe sont requis'], JSON_UNESCAPED_UNICODE);
    exit;
}

$creneau->id = 0;
$creneau->date_cours = $data->date_cours;
$creneau->heure_debut = strlen($data->heure_debut) === 5 ? $data->heure_debut . ':00' : $data->heure_debut;
$creneau->heure_fin = strlen($data->heure_fin) === 5 ? $data->heure_fin . ':00' : $data->heure_fin;
$creneau->matiere_id = $matiereId;
$creneau->enseignant_id = $enseignantId;
$creneau->salle_id = $salleId;
$creneau->groupe_id = $groupeId;
$creneau->type = $data->type ?? 'cours';
$creneau->recurrent = isset($data->recurrent) ? (int) $data->recurrent : 0;
$creneau->freq_recurrence = $data->freq_recurrence ?? null;
$creneau->date_fin_recurrence = $data->date_fin_recurrence ?? null;

$conflicts = $creneau->detecterConflits();
if (!empty($conflicts)) {
    echo json_encode(['message' => 'Conflit détecté', 'conflits' => $conflicts], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$creneau->create()) {
    echo json_encode(['message' => 'Erreur lors de la création'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Side effects (notifications, email log, history) must never break the JSON response to the client.
// Any exception here (e.g. FS permission on logs, historque FK, etc.) is swallowed so the creneau creation always succeeds for the UI.
try {
    $enseignant = new Enseignant($db);
    $enseignant->id = $creneau->enseignant_id;
    $row = $enseignant->getById()->fetch(PDO::FETCH_ASSOC);

    if ($row && isset($row['utilisateur_id'])) {
        $notif = new Notification($db);
        $notif->utilisateur_id = (int) $row['utilisateur_id'];
        $notif->message = "Nouveau créneau le {$creneau->date_cours} de {$creneau->heure_debut} à {$creneau->heure_fin}";
        $notif->create();

        $emailQuery = $db->prepare('SELECT email FROM utilisateur WHERE id = :uid');
        $emailQuery->bindParam(':uid', $row['utilisateur_id']);
        $emailQuery->execute();
        $email = $emailQuery->fetchColumn();
        if ($email) {
            sendEmail($email, 'Nouveau créneau planifié - EduPlanning', "Bonjour,\n\nUn nouveau créneau a été ajouté à votre planning :\nDate: {$creneau->date_cours}\nHeure: {$creneau->heure_debut} - {$creneau->heure_fin}\n\nVérifiez votre emploi du temps.");
        }
    }

    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'create_creneau', json_encode([
        'id' => (int) $creneau->id,
        'date' => $creneau->date_cours
    ], JSON_UNESCAPED_UNICODE));
} catch (Exception $e) {
    // Silently ignore side-effect failures; the main creneau was created successfully.
    // In production you could error_log($e->getMessage());
}

echo json_encode(['message' => 'Créneau créé', 'id' => (int) $creneau->id], JSON_UNESCAPED_UNICODE);
?>
