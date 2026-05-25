<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Créer un étudiant</h1>
        <p>Le compte sera créé inactif puis validé par un administrateur.</p>
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
            <input type="password" id="password" class="form-control" placeholder="Minimum 8 caractères">
        </div>
        <div class="form-group">
            <label>Groupe</label>
            <select id="groupeSelect" class="form-control">
                <option value="">- Aucun -</option>
            </select>
        </div>

        <div style="display:flex;gap:.5rem;margin-top:1rem">
            <button class="btn btn-primary" id="btnCreate">Créer (en attente)</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const user = getUser();
        if (!user) return location.replace('../users/login.php');
        if (user.role !== 'enseignant') {
            document.body.innerHTML = '<div style="padding:2rem">Accès refusé</div>';
            return;
        }

        await loadGroupes();
        document.getElementById('btnCreate').addEventListener('click', createStudent);
    });

    async function loadGroupes() {
        const res = await fetch(`${BASE}/groupes/index.php`);
        const groupes = await res.json();
        const select = document.getElementById('groupeSelect');
        select.innerHTML = '<option value="">- Aucun -</option>';
        if (Array.isArray(groupes)) {
            groupes.forEach((groupe) => {
                const option = document.createElement('option');
                option.value = groupe.id;
                option.textContent = groupe.nom;
                select.appendChild(option);
            });
        }
    }

    async function createStudent() {
        const nom = document.getElementById('nom').value.trim();
        const prenom = document.getElementById('prenom').value.trim();
        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;
        const groupe_id = document.getElementById('groupeSelect').value || null;
        const alertBox = document.getElementById('alertContainer');
        alertBox.innerHTML = '';

        if (!nom || !prenom || !email || !password) {
            showAlert(alertBox, 'warning', 'Remplissez tous les champs.');
            return;
        }

        if (password.length < 8) {
            showAlert(alertBox, 'warning', 'Le mot de passe doit contenir au moins 8 caractères.');
            return;
        }

        const btn = document.getElementById('btnCreate');
        btn.disabled = true;

        try {
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
                    groupe_id
                })
            });
            const data = await res.json();
            if (data.id) {
                showAlert(alertBox, 'success', 'Étudiant créé, en attente de validation.');
                document.getElementById('nom').value = '';
                document.getElementById('prenom').value = '';
                document.getElementById('email').value = '';
                document.getElementById('password').value = '';
                document.getElementById('groupeSelect').value = '';
            } else {
                showAlert(alertBox, 'danger', data.message || 'Erreur');
            }
        } catch (e) {
            showAlert(alertBox, 'danger', 'Erreur réseau.');
        }

        btn.disabled = false;
    }
</script>

<?php include('../inc/footer.php') ?>
