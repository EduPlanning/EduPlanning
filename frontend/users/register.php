<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../layout/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Inscription — EduPlanning</title>
</head>

<body class="auth-page">

    <div class="login-page">
        <div class="login-card" style="max-width:480px">
            <div class="login-logo">
                <i class='bx bx-calendar-check'></i>
                <h1>Créer un compte</h1>
                <p>Rejoignez EduPlanning</p>
            </div>

            <div id="alertContainer"></div>

            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" id="nom" class="form-control" placeholder="Elbouraqqady">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" id="prenom" class="form-control" placeholder="Nouhaila">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="email" class="form-control" placeholder="vous@ecole.ma">
            </div>
            <div class="form-group">
                <label>Mot de passe</label>
                <input type="password" id="password" class="form-control" placeholder="Min. 8 caractères">
            </div>
            <div class="form-group">
                <p>Les inscriptions publiques sont réservées aux étudiants. Si vous êtes enseignant, contactez un administrateur.</p>
            </div>

            <div class="login-actions">
                <button class="btn btn-primary" id="btnRegister">
                    <i class='bx bx-user-plus'></i> S'inscrire
                </button>
            </div>

            <div class="login-footer">
                Déjà un compte ? <a href="./login.php">Se connecter</a>
            </div>
        </div>
    </div>

    <script>
        const BASE = 'http://localhost/emploi_du_temps/backend/controllers';

        document.getElementById('btnRegister').addEventListener('click', async () => {
            const nom = document.getElementById('nom').value.trim();
            const prenom = document.getElementById('prenom').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const alertBox = document.getElementById('alertContainer');
            alertBox.innerHTML = '';

            if (!nom || !prenom || !email || !password) {
                alertBox.innerHTML = '<div class="alert alert-warning"><i class="bx bx-error"></i> Veuillez remplir tous les champs.</div>';
                return;
            }

            const btn = document.getElementById('btnRegister');
            btn.disabled = true;

            const res = await fetch(`${BASE}/users/register.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    nom,
                    prenom,
                    email,
                    mot_de_passe: password,
                    role: 'etudiant'
                })
            });
            const data = await res.json();

            if (data.message === 'Compte créé') {
                alertBox.innerHTML = '<div class="alert alert-success"><i class="bx bx-check-circle"></i> Compte créé ! Redirection…</div>';
                setTimeout(() => location.replace('./login.php'), 1500);
            } else {
                alertBox.innerHTML = `<div class="alert alert-danger"><i class="bx bx-x-circle"></i> ${data.message}</div>`;
                btn.disabled = false;
            }
        });
    </script>
</body>

</html>