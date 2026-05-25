<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');
include_once '../../middleware/auth.php';
requireRole(['enseignant', 'administrateur']);

include_once '../../config/Database.php';
include_once '../../models/Historique.php';

function normalizeDateInput($value)
{
    return is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : null;
}

$database = new Database();
$db = $database->connect();
$payload = json_decode(file_get_contents('php://input'));

$sourceStart = normalizeDateInput($payload->source_start ?? $_GET['source_start'] ?? null);
$sourceEnd = normalizeDateInput($payload->source_end ?? $_GET['source_end'] ?? null);
$targetStart = normalizeDateInput($payload->target_start ?? $_GET['target_start'] ?? null);
$offsetDays = isset($payload->offset) ? (int) $payload->offset : (isset($_GET['offset']) ? (int) $_GET['offset'] : null);

if ($sourceStart && $sourceEnd && $targetStart) {
    $offsetDays = (int) round((strtotime($targetStart) - strtotime($sourceStart)) / 86400);
}

if ($offsetDays === null) {
    $offsetDays = 180;
}

$selectSql = 'SELECT * FROM creneau';
$params = [];
if ($sourceStart && $sourceEnd) {
    $selectSql .= ' WHERE date_cours BETWEEN :source_start AND :source_end';
    $params[':source_start'] = $sourceStart;
    $params[':source_end'] = $sourceEnd;
}

$select = $db->prepare($selectSql);
foreach ($params as $key => $value) {
    $select->bindValue($key, $value);
}
$select->execute();
$rows = $select->fetchAll(PDO::FETCH_ASSOC);

if (!$rows) {
    echo json_encode(['message' => 'Aucun créneau à dupliquer', 'count' => 0], JSON_UNESCAPED_UNICODE);
    exit;
}

$insert = $db->prepare('INSERT INTO creneau (date_cours, heure_debut, heure_fin, matiere_id, enseignant_id, salle_id, groupe_id, type, recurrent, freq_recurrence, date_fin_recurrence)
                        VALUES (:date_cours, :heure_debut, :heure_fin, :matiere_id, :enseignant_id, :salle_id, :groupe_id, :type, :recurrent, :freq_recurrence, :date_fin_recurrence)');

$db->beginTransaction();
try {
    $count = 0;
    foreach ($rows as $row) {
        $newDate = date('Y-m-d', strtotime($row['date_cours'] . " {$offsetDays} days"));
        $newRecurrenceEnd = null;
        if (!empty($row['date_fin_recurrence'])) {
            $newRecurrenceEnd = date('Y-m-d', strtotime($row['date_fin_recurrence'] . " {$offsetDays} days"));
        }

        $insert->execute([
            ':date_cours' => $newDate,
            ':heure_debut' => $row['heure_debut'],
            ':heure_fin' => $row['heure_fin'],
            ':matiere_id' => $row['matiere_id'],
            ':enseignant_id' => $row['enseignant_id'],
            ':salle_id' => $row['salle_id'],
            ':groupe_id' => $row['groupe_id'],
            ':type' => $row['type'],
            ':recurrent' => $row['recurrent'],
            ':freq_recurrence' => $row['freq_recurrence'],
            ':date_fin_recurrence' => $newRecurrenceEnd
        ]);
        $count++;
    }

    $db->commit();
    $hist = new Historique($db);
    $hist->log(getCurrentUserId(), 'duplicate_semestre', json_encode([
        'count' => $count,
        'offset_days' => $offsetDays,
        'source_start' => $sourceStart,
        'source_end' => $sourceEnd,
        'target_start' => $targetStart
    ], JSON_UNESCAPED_UNICODE));

    echo json_encode(['message' => "{$count} créneaux dupliqués", 'count' => $count], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['message' => 'Erreur lors de la duplication'], JSON_UNESCAPED_UNICODE);
}
?>
