<?php
// backend/controllers/users/forgot_password.php — Password reset request (CDC §4.1)
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');

include_once '../../config/Database.php';
include_once '../../utils/mailer.php';

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->email)) {
    echo json_encode(['message' => 'Email requis']);
    exit;
}
$email = filter_var($data->email, FILTER_SANITIZE_EMAIL);

// Check user exists
$stmt = $db->prepare('SELECT id, prenom, nom FROM utilisateur WHERE email = :e AND actif=1 LIMIT 1');
$stmt->bindParam(':e', $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    // Don't reveal if exists
    echo json_encode(['message' => 'Si le compte existe, un email a été envoyé.']);
    exit;
}

// Generate token
$token = bin2hex(random_bytes(16));
$expiry = date('Y-m-d H:i:s', time() + 3600); // 1h

// Store in simple json file (for demo; prod: dedicated table)
$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}
$resetFile = $logDir . '/reset_tokens.json';
$tokens = [];
if (file_exists($resetFile)) $tokens = json_decode(file_get_contents($resetFile), true) ?: [];
$tokens[$token] = ['email' => $email, 'expiry' => $expiry, 'used' => false];
file_put_contents($resetFile, json_encode($tokens));

$resetLink = "http://localhost/emploi_du_temps/frontend/users/reset_password.php?token=$token";
$body = "Bonjour {$user['prenom']},\n\nPour réinitialiser votre mot de passe EduPlanning, cliquez : $resetLink\n\nValable 1 heure.\n\nSi vous n'avez pas demandé, ignorez.";
sendEmail($email, 'Réinitialisation mot de passe - EduPlanning', $body);

echo json_encode(['message' => 'Si le compte existe, un email a été envoyé avec le lien de réinitialisation.']);
?>
