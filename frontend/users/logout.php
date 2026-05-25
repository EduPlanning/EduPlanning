<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Déconnexion…</title>
</head>

<body>
    <script>
    /* Step 1 — clear client-side state immediately */
    localStorage.removeItem('user');
    sessionStorage.clear();

    /* Step 2 — tell the server to destroy the PHP session.
     * credentials:'include' is required so the session cookie
     * is sent with the request (same fix as login). */
    fetch(
        window.location.origin + '/emploi_du_temps/backend/controllers/users/logout.php', {
            method: 'POST',
            credentials: 'include'
        }
    ).finally(() => {
        /* Redirect regardless of fetch success/failure */
        location.replace(window.location.origin + '/emploi_du_temps/frontend/users/login.php');
    });
    </script>
</body>

</html>