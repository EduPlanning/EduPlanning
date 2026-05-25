<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../layout/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <title>Réinitialiser mot de passe — EduPlanning</title>
</head>
<body class="auth-page">
    <div class="login-page">
        <div class="login-card" style="max-width:420px">
            <div class="login-logo">
                <i class='bx bx-lock-reset'></i>
                <h1>Réinitialiser</h1>
            </div>
            <div id="alertContainer"></div>
            <div class="form-group">
                <label>Nouveau mot de passe</label>
                <input type="password" id="newpass" class="form-control" minlength="6">
            </div>
            <div class="login-actions">
                <button class="btn btn-primary" id="btnReset">Réinitialiser</button>
            </div>
            <div class="login-footer"><a href="login.php">Connexion</a></div>
        </div>
    </div>
    <script>
        const BASE = 'http://localhost/emploi_du_temps/backend/controllers';
        function showAlert(m, t='danger') { document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${t}">${m}</div>`; }
        const params = new URLSearchParams(location.search);
        const token = params.get('token');
        if (!token) showAlert('Token manquant dans l\'URL', 'danger');
        document.getElementById('btnReset').addEventListener('click', async () => {
            const np = document.getElementById('newpass').value;
            if (!np || np.length < 6) return showAlert('Mot de passe trop court (min 6)');
            const res = await fetch(`${BASE}/users/reset_password.php`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({token, new_password: np})});
            const d = await res.json();
            showAlert(d.message, d.message.includes('succès') ? 'success' : 'danger');
        });
    </script>
</body>
</html>