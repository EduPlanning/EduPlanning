<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../models/Creneau.php';
include_once '../../models/Salle.php';
include_once '../../models/Groupe.php';

$database = new Database();
$db = $database->connect();

$user    = new Utilisateur($db);
$creneau = new Creneau($db);
$salle   = new Salle($db);
$groupe  = new Groupe($db);

// Occupation salles
$occ = $creneau->occupationSalles();
$occupation = [];
while ($row = $occ->fetch(PDO::FETCH_ASSOC)) {
    $occupation[] = $row;
}

echo json_encode([
    'nb_enseignants' => $user->countByRole('enseignant'),
    'nb_etudiants'   => $user->countByRole('etudiant'),
    'nb_salles'      => $salle->count(),
    'nb_groupes'     => $groupe->count(),
    'nb_creneaux'    => $creneau->countAll(),
    'nb_conflits'    => $creneau->countConflits(),
    'occupation_salles' => $occupation
]);
