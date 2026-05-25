<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../layout/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <title>Mot de passe oublié — EduPlanning</title>
</head>
<body class="auth-page">
    <div class="login-page">
        <div class="login-card" style="max-width:420px">
            <div class="login-logo">
                <i class='bx bx-key'></i>
                <h1>Mot de passe oublié</h1>
                <p>Recevez un lien de réinitialisation</p>
            </div>
            <div id="alertContainer"></div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="email" class="form-control" placeholder="vous@ecole.ma">
            </div>
            <div class="login-actions">
                <button class="btn btn-primary" id="btnForgot"><i class='bx bx-send'></i> Envoyer le lien</button>
            </div>
            <div class="login-footer">
                <a href="login.php">Retour à la connexion</a>
            </div>
        </div>
    </div>
    <script>
        const BASE = 'http://localhost/emploi_du_temps/backend/controllers';
        function showAlert(m, t='danger') {
            document.getElementById('alertContainer').innerHTML = `<div class="alert alert-${t}">${m}</div>`;
        }
        document.getElementById('btnForgot').addEventListener('click', async () => {
            const email = document.getElementById('email').value.trim();
            if (!email) return showAlert('Email requis');
            const res = await fetch(`${BASE}/users/forgot_password.php`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({email})});
            const d = await res.json();
            showAlert(d.message, 'success');
        });
    </script>
</body>
</html>