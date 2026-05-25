<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Filières</h1>
        <p>Gestion administrative des filières.</p>
    </div>
    <div>
        <button class="btn btn-primary" id="btnNew"><i class='bx bx-plus'></i> Nouvelle filière</button>
    </div>
</div>

<div id="alertContainer"></div>
<div class="card">
    <div class="card-body">
        <table class="table full-width" id="tableFilieres">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Code</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="filiereModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nouvelle filière</h3>
            <button class="modal-close" onclick="closeModal('filiereModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="filiereId">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" id="filiereNom" class="form-control">
            </div>
            <div class="form-group">
                <label>Code</label>
                <input type="text" id="filiereCode" class="form-control">
            </div>
            <div class="form-actions">
                <button class="btn btn-outline" onclick="closeModal('filiereModal')">Annuler</button>
                <button class="btn btn-primary" id="btnSaveFiliere">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php include('../inc/footer.php') ?>

<script>
    const currentUser = getUser();
    if (!currentUser) {
        location.replace('../users/login.php');
    }
    if (currentUser?.role !== 'administrateur') {
        alert('Accès réservé aux administrateurs.');
        location.replace('../dashboard/index.php');
    }

    const api = 'http://localhost/emploi_du_temps/backend/controllers/filieres';
    let filieres = [];

    async function loadFilieres() {
        const res = await fetch(`${api}/index.php`);
        filieres = await res.json();
        const tbody = document.querySelector('#tableFilieres tbody');
        tbody.innerHTML = '';
        filieres.forEach((filiere) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${escapeHtml(filiere.nom)}</td>
                <td>${escapeHtml(filiere.code)}</td>
                <td class="text-right">
                    <button class="btn btn-outline btn-sm" onclick="editFiliere(${filiere.id})"><i class='bx bx-edit'></i></button>
                    <button class="btn btn-danger btn-sm" onclick="deleteFiliere(${filiere.id})"><i class='bx bx-trash'></i></button>
                </td>`;
            tbody.appendChild(row);
        });
    }

    function openFiliereModal() {
        document.getElementById('filiereId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouvelle filière';
        document.getElementById('filiereNom').value = '';
        document.getElementById('filiereCode').value = '';
        openModal('filiereModal');
    }

    function editFiliere(id) {
        const filiere = filieres.find((item) => item.id == id);
        if (!filiere) return;
        document.getElementById('filiereId').value = filiere.id;
        document.getElementById('modalTitle').textContent = 'Modifier la filière';
        document.getElementById('filiereNom').value = filiere.nom;
        document.getElementById('filiereCode').value = filiere.code;
        openModal('filiereModal');
    }

    async function saveFiliere() {
        const id = document.getElementById('filiereId').value;
        const payload = {
            nom: document.getElementById('filiereNom').value.trim(),
            code: document.getElementById('filiereCode').value.trim()
        };

        if (!payload.nom || !payload.code) {
            showAlert(document.getElementById('alertContainer'), 'danger', 'Nom et code requis.');
            return;
        }

        if (id) payload.id = id;

        const res = await fetch(`${api}/index.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        showAlert(document.getElementById('alertContainer'), data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        closeModal('filiereModal');
        loadFilieres();
    }

    async function deleteFiliere(id) {
        if (!confirm('Supprimer cette filière ?')) return;
        const res = await fetch(`${api}/delete.php?id=${id}`);
        const data = await res.json();
        showAlert(document.getElementById('alertContainer'), data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        loadFilieres();
    }

    document.getElementById('btnNew').addEventListener('click', openFiliereModal);
    document.getElementById('btnSaveFiliere').addEventListener('click', saveFiliere);
    loadFilieres();
</script>
