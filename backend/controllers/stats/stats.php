<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Creneau.php';
include_once '../../models/Salle.php';
include_once '../../models/Groupe.php';
include_once '../../models/Filiere.php';

$database = new Database();
$db = $database->connect();

$user = new Utilisateur($db);
$creneau = new Creneau($db);
$salle = new Salle($db);
$groupe = new Groupe($db);
$filiere = new Filiere($db);

$occupation = [];
$occ = $creneau->occupationSalles();
while ($row = $occ->fetch(PDO::FETCH_ASSOC)) {
    $occupation[] = $row;
}

$repartitionFilieres = [];
$rep = $creneau->repartitionFilieres();
while ($row = $rep->fetch(PDO::FETCH_ASSOC)) {
    $repartitionFilieres[] = $row;
}

echo json_encode([
    'nb_enseignants' => $user->countByRole('enseignant'),
    'nb_etudiants' => $user->countByRole('etudiant'),
    'nb_salles' => $salle->count(),
    'nb_groupes' => $groupe->count(),
    'nb_filieres' => $filiere->count(),
    'nb_creneaux' => $creneau->countAll(),
    'nb_conflits' => $creneau->countConflits(),
    'occupation_salles' => $occupation,
    'repartition_filieres' => $repartitionFilieres
], JSON_UNESCAPED_UNICODE);
?>
