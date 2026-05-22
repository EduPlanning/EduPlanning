<?php
// Frontend root entrypoint.
// Redirect to the default dashboard page.

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$script = $_SERVER['SCRIPT_NAME'];
$base = rtrim(str_replace('index.php', '', $script), '/\\');
$path = trim(substr($uri, strlen($base)), '/');

if ($path === '' || $path === 'index.php') {
    header('Location: dashboard/index.php');
    exit;
}

http_response_code(404);
echo '<h1>404 Not Found</h1>';
echo '<p>Le chemin demandé n\'existe pas.</p>';