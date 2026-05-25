<?php
include_once '../../config/headers.php';
include_once '../../middleware/auth.php';
requireAuth();

include_once '../../config/Database.php';
include_once '../../models/Creneau.php';

function formatIcalDate($date, $time)
{
    $time = strlen($time) === 5 ? $time . ':00' : $time;
    return date('Ymd\THis', strtotime($date . ' ' . $time));
}

$database = new Database();
$db = $database->connect();
$creneau = new Creneau($db);

$filters = [];
foreach (['groupe_id', 'enseignant_id', 'salle_id'] as $key) {
    if (isset($_GET[$key])) {
        $filters[$key] = (int) $_GET[$key];
    }
}
if (isset($_GET['date_debut'])) {
    $filters['date_debut'] = $_GET['date_debut'];
}
if (isset($_GET['date_fin'])) {
    $filters['date_fin'] = $_GET['date_fin'];
}

$result = $creneau->getAll($filters);
$events = [];
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    $summary = trim(($row['matiere_nom'] ?? 'Cours') . ' - ' . strtoupper($row['type'] ?? 'cours'));
    $description = "Enseignant: " . ($row['enseignant_nom'] ?? '') . "\\nGroupe: " . ($row['groupe_nom'] ?? '');
    $location = $row['salle_nom'] ?? '';
    $events[] = [
        'uid' => 'creneau-' . $row['id'] . '@eduplanning.local',
        'dtstart' => formatIcalDate($row['date_cours'], $row['heure_debut']),
        'dtend' => formatIcalDate($row['date_cours'], $row['heure_fin']),
        'summary' => addcslashes($summary, ",;\\"),
        'description' => addcslashes($description, ",;\\"),
        'location' => addcslashes($location, ",;\\")
    ];
}

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="planning.ics"');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//EduPlanning//PFE 2025-2026//FR\r\n";
echo "CALSCALE:GREGORIAN\r\n";
echo "METHOD:PUBLISH\r\n";
foreach ($events as $event) {
    echo "BEGIN:VEVENT\r\n";
    echo "UID:{$event['uid']}\r\n";
    echo "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
    echo "DTSTART:{$event['dtstart']}\r\n";
    echo "DTEND:{$event['dtend']}\r\n";
    echo "SUMMARY:{$event['summary']}\r\n";
    echo "DESCRIPTION:{$event['description']}\r\n";
    echo "LOCATION:{$event['location']}\r\n";
    echo "END:VEVENT\r\n";
}
echo "END:VCALENDAR\r\n";
?>
