<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Créer un étudiant</h1>
        <p>Créez un compte étudiant (sera en attente de validation par un administrateur)</p>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card" style="max-width:720px">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" id="nom" class="form-control">
            </div>
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" id="prenom" class="form-control">
            </div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" id="email" class="form-control">
        </div>
        <div class="form-group">
            <label>Mot de passe</label>
            <input type="password" id="password" class="form-control" placeholder="Min. 8 caractères">
        </div>
        <div class="form-group">
            <label>Groupe</label>
            <select id="groupeSelect" class="form-control">
                <option value="">— Aucun —</option>
            </select>
        </div>

        <div style="display:flex;gap:.5rem;margin-top:1rem">
            <button class="btn btn-primary" id="btnCreate">Créer (en attente)</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const user = getUser();
        if (!user) return location.replace('../users/login.php');
        if (user.role !== 'enseignant' && user.role !== 'administrateur') {
            document.body.innerHTML = '<div style="padding:2rem">Accès refusé</div>';
            return;
        }

        document.getElementById('btnCreate').addEventListener('click', async () => {
            const nom = document.getElementById('nom').value.trim();
            const prenom = document.getElementById('prenom').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            const alertBox = document.getElementById('alertContainer');
            alertBox.innerHTML = '';

            if (!nom || !prenom || !email || !password) {
                showAlert(alertBox, 'warning', 'Remplissez tous les champs.');
                return;
            }

            const btn = document.getElementById('btnCreate');
            btn.disabled = true;

            try {
                // load groupes
                const gres = await fetch(`${BASE}/groupes/index.php`);
                const groupes = await gres.json();
                const sel = document.getElementById('groupeSelect');
                if (Array.isArray(groupes)) {
                    groupes.forEach(g => {
                        const opt = document.createElement('option');
                        opt.value = g.id;
                        opt.textContent = g.nom;
                        sel.appendChild(opt);
                    });
                }

                const res = await fetch(`${BASE}/users/create_student.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nom,
                        prenom,
                        email,
                        mot_de_passe: password,
                        enseignant_utilisateur_id: user.id,
                        groupe_id: document.getElementById('groupeSelect').value || null
                    })
                });
                const data = await res.json();
                if (data.id) {
                    showAlert(alertBox, 'success', 'Étudiant créé, en attente de validation.');
                    document.getElementById('nom').value = '';
                    document.getElementById('prenom').value = '';
                    document.getElementById('email').value = '';
                    document.getElementById('password').value = '';
                } else {
                    showAlert(alertBox, 'danger', data.message || 'Erreur');
                }
            } catch (e) {
                showAlert(alertBox, 'danger', 'Erreur réseau.');
            }
            btn.disabled = false;
        });
    });
</script>

<?php include('../inc/footer.php') ?>