<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';
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

if (!isset($data->id, $data->date_cours, $data->heure_debut, $data->heure_fin, $data->matiere_id, $data->enseignant_id, $data->salle_id, $data->groupe_id)) {
    echo json_encode(['message' => 'Données manquantes'], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isValidDateValue($data->date_cours) || !isValidTimeValue($data->heure_debut) || !isValidTimeValue($data->heure_fin) || strtotime($data->heure_fin) <= strtotime($data->heure_debut)) {
    echo json_encode(['message' => 'Date ou horaires invalides'], JSON_UNESCAPED_UNICODE);
    exit;
}

$creneau->id = (int) $data->id;
$creneau->date_cours = $data->date_cours;
$creneau->heure_debut = strlen($data->heure_debut) === 5 ? $data->heure_debut . ':00' : $data->heure_debut;
$creneau->heure_fin = strlen($data->heure_fin) === 5 ? $data->heure_fin . ':00' : $data->heure_fin;
$creneau->matiere_id = (int) $data->matiere_id;
$creneau->enseignant_id = (int) $data->enseignant_id;
$creneau->salle_id = (int) $data->salle_id;
$creneau->groupe_id = (int) $data->groupe_id;
$creneau->type = $data->type ?? 'cours';
$creneau->recurrent = isset($data->recurrent) ? (int) $data->recurrent : 0;
$creneau->freq_recurrence = $data->freq_recurrence ?? null;
$creneau->date_fin_recurrence = $data->date_fin_recurrence ?? null;

$conflicts = $creneau->detecterConflits();
if (!empty($conflicts)) {
    echo json_encode(['message' => 'Conflit détecté', 'conflits' => $conflicts], JSON_UNESCAPED_UNICODE);
    exit;
}

if (!$creneau->update()) {
    echo json_encode(['message' => 'Erreur lors de la mise à jour'], JSON_UNESCAPED_UNICODE);
    exit;
}

$hist = new Historique($db);
$hist->log(getCurrentUserId(), 'update_creneau', json_encode(['id' => (int) $creneau->id], JSON_UNESCAPED_UNICODE));

$emailQuery = $db->prepare('SELECT u.email FROM utilisateur u JOIN enseignant e ON e.utilisateur_id = u.id WHERE e.id = :eid LIMIT 1');
$emailQuery->bindParam(':eid', $creneau->enseignant_id);
$emailQuery->execute();
$email = $emailQuery->fetchColumn();
if ($email) {
    sendEmail($email, 'Créneau modifié - EduPlanning', "Un créneau a été mis à jour dans votre planning (ID {$creneau->id}). Vérifiez les détails.");
}

echo json_encode(['message' => 'Créneau mis à jour'], JSON_UNESCAPED_UNICODE);
?>
