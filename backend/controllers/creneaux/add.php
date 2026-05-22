<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';
include_once '../../models/Notification.php';
include_once '../../models/Utilisateur.php';

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);

$data = json_decode(file_get_contents('php://input'));

if (!isset($data->date_cours, $data->heure_debut, $data->heure_fin,
           $data->matiere_id, $data->enseignant_id, $data->salle_id, $data->groupe_id)) {
    echo json_encode(['message' => 'Données manquantes']);
    exit;
}

$creneau->date_cours          = $data->date_cours;
$creneau->heure_debut         = $data->heure_debut;
$creneau->heure_fin           = $data->heure_fin;
$creneau->matiere_id          = $data->matiere_id;
$creneau->enseignant_id       = $data->enseignant_id;
$creneau->salle_id            = $data->salle_id;
$creneau->groupe_id           = $data->groupe_id;
$creneau->type                = $data->type ?? 'cours';
$creneau->recurrent           = $data->recurrent ?? 0;
$creneau->freq_recurrence     = $data->freq_recurrence ?? null;
$creneau->date_fin_recurrence = $data->date_fin_recurrence ?? null;
$creneau->id                  = 0; // new record

// Check conflicts first
$conflicts = $creneau->detecterConflits();
if (!empty($conflicts)) {
    echo json_encode([
        'message'   => 'Conflit détecté',
        'conflits'  => $conflicts
    ]);
    exit;
}

if ($creneau->create()) {
    // Notify affected users (students in group + teacher)
    $notif = new Notification($db);

    // Notify enseignant
    $notif->utilisateur_id = $data->enseignant_id; // link via utilisateur_id via enseignant table
    $notif->message = "Nouveau créneau le {$data->date_cours} de {$data->heure_debut} à {$data->heure_fin}";
    $notif->create();

    echo json_encode(['message' => 'Créneau créé', 'id' => $creneau->id]);
} else {
    echo json_encode(['message' => 'Erreur lors de la création']);
}
