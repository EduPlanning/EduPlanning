<?php
function bootstrapSession()
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    session_name('eduplanning_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);
    session_start();
}

function denyJson($statusCode, $message)
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

bootstrapSession();

function requireAuth()
{
    if (!isset($_SESSION['user']) || empty($_SESSION['user']['id'])) {
        denyJson(401, 'Non authentifié. Veuillez vous connecter.');
    }
}

function requireRole($allowedRoles)
{
    requireAuth();

    $allowed = is_array($allowedRoles) ? $allowedRoles : [$allowedRoles];
    $role = $_SESSION['user']['role'] ?? '';

    if (!in_array($role, $allowed, true)) {
        denyJson(403, 'Accès refusé pour ce rôle.');
    }
}

function getCurrentUser()
{
    bootstrapSession();
    return $_SESSION['user'] ?? null;
}

function getCurrentUserId()
{
    $user = getCurrentUser();
    return $user['id'] ?? null;
}
?>
