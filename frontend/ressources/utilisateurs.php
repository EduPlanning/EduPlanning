<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Utilisateurs</h1>
        <p>Gestion des comptes, rôles et statuts.</p>
    </div>
    <div>
        <button class="btn btn-primary" id="btnNew"><i class='bx bx-plus'></i> Nouvel utilisateur</button>
    </div>
</div>

<div id="alertContainer"></div>
<div class="card">
    <div class="card-body">
        <table class="table full-width" id="tableUsers">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actif</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="userModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nouvel utilisateur</h3>
            <button class="modal-close" onclick="closeModal('userModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="userId">
            <div class="form-row">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" id="userNom" class="form-control">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" id="userPrenom" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" id="userEmail" class="form-control">
            </div>
            <div class="form-group" id="passwordGroup">
                <label>Mot de passe</label>
                <input type="password" id="userPassword" class="form-control">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Rôle</label>
                    <select id="userRole" class="form-control">
                        <option value="administrateur">Administrateur</option>
                        <option value="enseignant">Enseignant</option>
                        <option value="etudiant">Étudiant</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Actif</label>
                    <select id="userActif" class="form-control">
                        <option value="1">Oui</option>
                        <option value="0">Non</option>
                    </select>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-outline" onclick="closeModal('userModal')">Annuler</button>
                <button class="btn btn-primary" id="btnSaveUser">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php include('../inc/footer.php') ?>

<script>
    const user = getUser();
    if (!user) {
        location.replace('../users/login.php');
        return;
    }
    if (user.role !== 'administrateur') {
        location.replace('../dashboard/index.php');
        return;
    }

    const api = 'http://localhost/emploi_du_temps/backend/controllers/users';
    let users = [];

    async function loadUsers() {
        const res = await fetch(`${api}/get_all.php`);
        users = await res.json();
        const tbody = document.querySelector('#tableUsers tbody');
        tbody.innerHTML = '';
        users.forEach(u => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>${u.nom}</td>
            <td>${u.prenom}</td>
            <td>${u.email}</td>
            <td>${u.role}</td>
            <td>${u.actif == 1 ? 'Oui' : 'Non'}</td>
            <td class="text-right">
                <button class="btn btn-outline btn-sm" onclick="editUser(${u.id})"><i class='bx bx-edit'></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})"><i class='bx bx-trash'></i></button>
            </td>`;
            tbody.appendChild(row);
        });
    }

    function showAlert(type, message) {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `<div class="alert alert-${type}"><i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-x-circle'}"></i> ${message}</div>`;
    }

    function openUserModal() {
        document.getElementById('userId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouvel utilisateur';
        document.getElementById('userNom').value = '';
        document.getElementById('userPrenom').value = '';
        document.getElementById('userEmail').value = '';
        document.getElementById('userPassword').value = '';
        document.getElementById('userRole').value = 'etudiant';
        document.getElementById('userActif').value = 1;
        document.getElementById('passwordGroup').style.display = 'block';
        openModal('userModal');
    }

    function editUser(id) {
        const u = users.find(x => x.id == id);
        if (!u) return;
        document.getElementById('userId').value = u.id;
        document.getElementById('modalTitle').textContent = 'Modifier l\'utilisateur';
        document.getElementById('userNom').value = u.nom;
        document.getElementById('userPrenom').value = u.prenom;
        document.getElementById('userEmail').value = u.email;
        document.getElementById('userRole').value = u.role;
        document.getElementById('userActif').value = u.actif;
        document.getElementById('passwordGroup').style.display = 'none';
        openModal('userModal');
    }

    async function saveUser() {
        const id = document.getElementById('userId').value;
        const payload = {
            nom: document.getElementById('userNom').value.trim(),
            prenom: document.getElementById('userPrenom').value.trim(),
            email: document.getElementById('userEmail').value.trim(),
            role: document.getElementById('userRole').value,
            actif: parseInt(document.getElementById('userActif').value, 10)
        };
        if (!payload.nom || !payload.prenom || !payload.email) {
            showAlert('danger', 'Nom, prénom et email sont requis.');
            return;
        }
        if (!id) {
            const password = document.getElementById('userPassword').value;
            if (!password) {
                showAlert('danger', 'Le mot de passe est requis pour un nouvel utilisateur.');
                return;
            }
            payload.mot_de_passe = password;
            const res = await fetch(`${api}/register.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            showAlert(data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        } else {
            payload.id = id;
            const res = await fetch(`${api}/update.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            showAlert(data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        }
        closeModal('userModal');
        loadUsers();
    }

    async function deleteUser(id) {
        if (!confirm('Supprimer cet utilisateur ?')) return;
        const res = await fetch(`${api}/delete.php?id=${id}`);
        const data = await res.json();
        showAlert(data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        loadUsers();
    }

    document.getElementById('btnNew').addEventListener('click', openUserModal);
    document.getElementById('btnSaveUser').addEventListener('click', saveUser);
    loadUsers();
</script>