<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Groupes</h1>
        <p>Gestion des groupes et de leurs filières.</p>
    </div>
    <div>
        <button class="btn btn-primary" id="btnNew"><i class='bx bx-plus'></i> Nouveau groupe</button>
    </div>
</div>

<div id="alertContainer"></div>
<div class="card">
    <div class="card-body">
        <table class="table full-width" id="tableGroupes">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Niveau</th>
                    <th>Filière</th>
                    <th>Capacité</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="groupeModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nouveau groupe</h3>
            <button class="modal-close" onclick="closeModal('groupeModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="groupeId">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" id="groupeNom" class="form-control">
            </div>
            <div class="form-group">
                <label>Niveau</label>
                <input type="text" id="groupeNiveau" class="form-control" placeholder="Ex. Technicien Spécialisé 1ère année">
            </div>
            <div class="form-group">
                <label>Filière</label>
                <select id="groupeFiliere" class="form-control"></select>
            </div>
            <div class="form-group">
                <label>Capacité</label>
                <input type="number" id="groupeCapacite" class="form-control" min="1" value="30">
            </div>
            <div class="form-actions">
                <button class="btn btn-outline" onclick="closeModal('groupeModal')">Annuler</button>
                <button class="btn btn-primary" id="btnSaveGroupe">Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<?php include('../inc/footer.php') ?>

<script>
    const user = getUser();
    if (!user) {
        location.replace('../users/login.php');
    }

    const api = 'http://localhost/emploi_du_temps/backend/controllers/groupes';
    const filiereApi = 'http://localhost/emploi_du_temps/backend/controllers/filieres';
    let filieres = [];

    async function loadGroupes() {
        const [res, filRes] = await Promise.all([
            fetch(`${api}/index.php`),
            fetch(`${filiereApi}/index.php`)
        ]);
        const groupes = await res.json();
        filieres = await filRes.json();
        const tbody = document.querySelector('#tableGroupes tbody');
        tbody.innerHTML = '';
        groupes.forEach(g => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>${g.nom}</td>
            <td>${g.niveau}</td>
            <td>${g.filiere_nom || '—'}</td>
            <td>${g.capacite}</td>
            <td class="text-right">
                <button class="btn btn-outline btn-sm" onclick="editGroupe(${g.id})"><i class='bx bx-edit'></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteGroupe(${g.id})"><i class='bx bx-trash'></i></button>
            </td>`;
            tbody.appendChild(row);
        });
        fillFilieres();
    }

    function fillFilieres() {
        const select = document.getElementById('groupeFiliere');
        select.innerHTML = '<option value="">Sélectionner une filière</option>';
        filieres.forEach(f => {
            const option = document.createElement('option');
            option.value = f.id;
            option.textContent = `${f.nom} (${f.code})`;
            select.appendChild(option);
        });
    }

    function showAlert(type, message) {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `<div class="alert alert-${type}"><i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-x-circle'}"></i> ${message}</div>`;
    }

    function openGroupeModal() {
        document.getElementById('groupeId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouveau groupe';
        document.getElementById('groupeNom').value = '';
        document.getElementById('groupeNiveau').value = '';
        document.getElementById('groupeFiliere').value = '';
        document.getElementById('groupeCapacite').value = 30;
        openModal('groupeModal');
    }

    function editGroupe(id) {
        fetch(`${api}/index.php`)
            .then(r => r.json())
            .then(groupes => {
                const groupe = groupes.find(x => x.id == id);
                if (!groupe) return;
                document.getElementById('groupeId').value = groupe.id;
                document.getElementById('modalTitle').textContent = 'Modifier le groupe';
                document.getElementById('groupeNom').value = groupe.nom;
                document.getElementById('groupeNiveau').value = groupe.niveau;
                document.getElementById('groupeFiliere').value = groupe.filiere_id || '';
                document.getElementById('groupeCapacite').value = groupe.capacite;
                openModal('groupeModal');
            });
    }

    async function saveGroupe() {
        const id = document.getElementById('groupeId').value;
        const payload = {
            nom: document.getElementById('groupeNom').value.trim(),
            niveau: document.getElementById('groupeNiveau').value.trim(),
            filiere_id: document.getElementById('groupeFiliere').value,
            capacite: parseInt(document.getElementById('groupeCapacite').value, 10)
        };
        if (!payload.nom || !payload.niveau || !payload.filiere_id) {
            showAlert('danger', 'Le nom, le niveau et la filière sont requis.');
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
        showAlert(data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        closeModal('groupeModal');
        loadGroupes();
    }

    async function deleteGroupe(id) {
        if (!confirm('Supprimer ce groupe ?')) return;
        const res = await fetch(`${api}/delete.php?id=${id}`);
        const data = await res.json();
        showAlert(data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        loadGroupes();
    }

    document.getElementById('btnNew').addEventListener('click', openGroupeModal);
    document.getElementById('btnSaveGroupe').addEventListener('click', saveGroupe);
    loadGroupes();
</script>