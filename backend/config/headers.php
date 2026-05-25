<?php
/*
 * backend/config/headers.php — Security & CORS headers (FIXED)
 *
 * Fix 1: Accept any localhost origin (127.0.0.1, ::1, custom ports)
 *         so fetch() with credentials:'include' is not rejected.
 * Fix 2: Access-Control-Allow-Credentials must be 'true' (string) for
 *         browsers to honour credentials:'include' on the JS side.
 * Fix 3: Handle OPTIONS preflight before any other output.
 */

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

/* Allow any localhost / 127.0.0.1 variant (port-agnostic) */
$isLocalhost = (
    $origin !== '' && (
        preg_match('#^https?://(localhost|127\.0\.0\.1)(:\d+)?$#', $origin) ||
        preg_match('#^https?://\[::1\](:\d+)?$#', $origin)
    )
);

if ($isLocalhost) {
    /* Reflect the exact origin so the browser accepts the response */
    header("Access-Control-Allow-Origin: $origin");
} else {
    /* Fallback for same-origin requests (no Origin header) */
    header('Access-Control-Allow-Origin: http://localhost');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');   /* ← required for credentials:'include' */
header('Access-Control-Max-Age: 86400');            /* cache preflight 24 h */
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

/* Handle preflight first — before any session_start() or DB calls */
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}