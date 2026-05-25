<?php
// backend/controllers/users/reset_password.php
include_once '../../config/headers.php';
header('Content-Type: application/json; charset=utf-8');

include_once '../../config/Database.php';

$database = new Database();
$db = $database->connect();

$data = json_decode(file_get_contents('php://input'));
if (!isset($data->token, $data->new_password)) {
    echo json_encode(['message' => 'Token et nouveau mot de passe requis']);
    exit;
}
$token = $data->token;
$newPass = $data->new_password;

$logDir = __DIR__ . '/../../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0775, true);
}
$resetFile = $logDir . '/reset_tokens.json';
if (!file_exists($resetFile)) {
    echo json_encode(['message' => 'Token invalide ou expiré']);
    exit;
}
$tokens = json_decode(file_get_contents($resetFile), true) ?: [];
if (!isset($tokens[$token]) || $tokens[$token]['used'] || strtotime($tokens[$token]['expiry']) < time()) {
    echo json_encode(['message' => 'Token invalide ou expiré']);
    exit;
}
$email = $tokens[$token]['email'];

// Update password
$hash = password_hash($newPass, PASSWORD_BCRYPT, ['cost' => 12]);
$up = $db->prepare('UPDATE utilisateur SET mot_de_passe = :h WHERE email = :e');
$up->bindParam(':h', $hash);
$up->bindParam(':e', $email);
if ($up->execute()) {
    $tokens[$token]['used'] = true;
    file_put_contents($resetFile, json_encode($tokens));
    echo json_encode(['message' => 'Mot de passe réinitialisé avec succès. Vous pouvez vous connecter.']);
} else {
    echo json_encode(['message' => 'Erreur lors de la mise à jour']);
}
?>
