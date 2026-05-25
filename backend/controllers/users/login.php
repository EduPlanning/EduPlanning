<?php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');

include_once '../../config/Database.php';
include_once '../../models/Utilisateur.php';
include_once '../../middleware/auth.php';

$database = new Database();
$db = $database->connect();
$user = new Utilisateur($db);

$data = json_decode(file_get_contents('php://input'));

if (!isset($data->email, $data->mot_de_passe)) {
    echo json_encode(['message' => 'Email et mot de passe requis'], JSON_UNESCAPED_UNICODE);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}
$logf = $logDir . '/login_attempts.log';
@touch($logf);

$now = time();
$window = 300;
$max = 5;
$lines = file($logf, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
$recent = array_filter($lines, function ($line) use ($now, $window, $ip) {
    [$ts, $storedIp] = explode('|', $line . '|');
    return ($now - (int) $ts < $window) && $storedIp === $ip;
});

if (count($recent) >= $max) {
    echo json_encode(['message' => 'Trop de tentatives de connexion. Réessayez dans 5 minutes.'], JSON_UNESCAPED_UNICODE);
    exit;
}

$user->email = filter_var(trim($data->email), FILTER_SANITIZE_EMAIL);
$result = $user->login();

if ($result->rowCount() === 0) {
    file_put_contents($logf, "$now|$ip\n", FILE_APPEND | LOCK_EX);
    echo json_encode(['message' => 'Utilisateur introuvable'], JSON_UNESCAPED_UNICODE);
    exit;
}

$row = $result->fetch(PDO::FETCH_ASSOC);
if (!password_verify($data->mot_de_passe, $row['mot_de_passe'])) {
    file_put_contents($logf, "$now|$ip\n", FILE_APPEND | LOCK_EX);
    echo json_encode(['message' => 'Mot de passe incorrect'], JSON_UNESCAPED_UNICODE);
    exit;
}

session_regenerate_id(true);
unset($row['mot_de_passe']);
$_SESSION['user'] = $row;

echo json_encode([
    'message' => 'Connexion réussie',
    'utilisateur' => $row
], JSON_UNESCAPED_UNICODE);
