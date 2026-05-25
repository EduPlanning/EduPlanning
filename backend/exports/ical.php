<?php
// backend/exports/ical.php — iCal export (CDC §4.4)
include_once '../config/headers.php';
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="emploi_du_temps.ics"');

include_once '../config/Database.php';
include_once '../middleware/auth.php';
requireAuth();

$database = new Database();
$db = $database->connect();

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$stmt = $db->prepare("SELECT c.*, m.nom as matiere, u.prenom, u.nom as enseignant_nom, s.nom as salle, g.nom as groupe 
  FROM creneau c 
  LEFT JOIN matiere m ON c.matiere_id=m.id 
  LEFT JOIN enseignant e ON c.enseignant_id=e.id 
  LEFT JOIN utilisateur u ON e.utilisateur_id=u.id 
  LEFT JOIN salle s ON c.salle_id=s.id 
  LEFT JOIN groupe g ON c.groupe_id=g.id 
  WHERE c.date_cours BETWEEN :s AND :e ORDER BY c.date_cours, c.heure_debut");
$stmt->execute([':s'=>$start, ':e'=>$end]);

echo "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//EduPlanning//FR\r\nCALSCALE:GREGORIAN\r\nMETHOD:PUBLISH\r\n";
while ($c = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $dtstart = str_replace('-', '', $c['date_cours']) . 'T' . str_replace(':', '', $c['heure_debut']) . '00';
    $dtend = str_replace('-', '', $c['date_cours']) . 'T' . str_replace(':', '', $c['heure_fin']) . '00';
    $summary = ($c['matiere'] ?? 'Cours') . ' - ' . ($c['groupe'] ?? '');
    $desc = ($c['prenom']??'') . ' ' . ($c['enseignant_nom']??'') . ' @ ' . ($c['salle']??'');
    $uid = 'edt-' . $c['id'] . '@eduplanning';
    echo "BEGIN:VEVENT\r\nUID:$uid\r\nDTSTART:$dtstart\r\nDTEND:$dtend\r\nSUMMARY:$summary\r\nDESCRIPTION:$desc\r\nLOCATION:" . ($c['salle']??'') . "\r\nEND:VEVENT\r\n";
}
echo "END:VCALENDAR\r\n";
?>