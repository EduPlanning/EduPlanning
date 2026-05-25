<?php
// backend/exports/xlsx.php — Excel/CSV export (CDC §4.4) - outputs .csv for compatibility (no PhpSpreadsheet)
include_once '../config/headers.php';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="emploi_du_temps.csv"');

include_once '../config/Database.php';
include_once '../middleware/auth.php';
requireAuth();

$database = new Database();
$db = $database->connect();

$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

echo "\xEF\xBB\xBF"; // BOM for Excel UTF8
echo "Date,Heure Debut,Heure Fin,Matiere,Enseignant,Salle,Groupe,Type\r\n";

$stmt = $db->prepare("SELECT c.date_cours, c.heure_debut, c.heure_fin, m.nom as matiere, CONCAT(u.prenom,' ',u.nom) as ens, s.nom as salle, g.nom as groupe, c.type 
  FROM creneau c 
  LEFT JOIN matiere m ON c.matiere_id = m.id 
  LEFT JOIN enseignant e ON c.enseignant_id = e.id 
  LEFT JOIN utilisateur u ON e.utilisateur_id = u.id 
  LEFT JOIN salle s ON c.salle_id = s.id 
  LEFT JOIN groupe g ON c.groupe_id = g.id 
  WHERE c.date_cours BETWEEN :s AND :e ORDER BY c.date_cours, c.heure_debut");
$stmt->execute([':s' => $start, ':e' => $end]);

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $fields = [$row['date_cours'], $row['heure_debut'], $row['heure_fin'], $row['matiere']??'', $row['ens']??'', $row['salle']??'', $row['groupe']??'', $row['type']??''];
    echo implode(',', array_map(function($f){ return '"' . str_replace('"','""',$f) . '"'; }, $fields)) . "\r\n";
}
?>