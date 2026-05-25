<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../layout/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Connexion — EduPlanning</title>
</head>

<body class="auth-page">

    <div class="login-page">
        <div class="login-card" style="max-width:420px">
            <div class="login-logo">
                <i class='bx bx-lock-open'></i>
                <h1>Connexion</h1>
                <p>Accédez à EduPlanning</p>
            </div>

            <div id="alertContainer"></div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" id="email" class="form-control" placeholder="vous@ecole.ma">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" id="password" class="form-control" placeholder="Mot de passe">
            </div>

            <div class="login-actions">
                <button class="btn btn-primary" id="btnLogin"><i class='bx bx-log-in'></i> Se connecter</button>
            </div>
            <div style="text-align:center;margin-top:0.5rem">
                <a href="forgot_password.php" style="font-size:0.9rem">Mot de passe oublié ?</a>
            </div>

            <div class="login-footer">
                Pour obtenir un compte, contactez un administrateur.
            </div>
        </div>
    </div>

    <script>
        const BASE = 'http://localhost/emploi_du_temps/backend/controllers';

        async function showAlert(message, type = 'danger') {
            const box = document.getElementById('alertContainer');
            box.innerHTML = `<div class="alert alert-${type}"><i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-x-circle'}"></i> ${message}</div>`;
        }

        document.getElementById('btnLogin').addEventListener('click', async () => {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            if (!email || !password) {
                showAlert('Veuillez remplir tous les champs.', 'warning');
                return;
            }

            const btn = document.getElementById('btnLogin');
            btn.disabled = true;

            try {
                const res = await fetch(`${BASE}/users/login.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        email,
                        mot_de_passe: password
                    })
                });
                const data = await res.json();
                if (data.utilisateur) {
                    localStorage.setItem('user', JSON.stringify(data.utilisateur));
                    showAlert('Connexion réussie !', 'success');
                    setTimeout(() => location.replace('../dashboard/index.php'), 900);
                } else {
                    showAlert(data.message || 'Échec de la connexion.');
                    btn.disabled = false;
                }
            } catch (error) {
                showAlert('Erreur réseau, veuillez réessayer.');
                btn.disabled = false;
            }
        });
    </script>
</body>

</html>