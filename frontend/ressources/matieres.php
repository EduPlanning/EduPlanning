<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Matières</h1>
        <p>Ajouter, modifier ou supprimer des matières.</p>
    </div>
    <div>
        <button class="btn btn-primary" id="btnNew"><i class='bx bx-plus'></i> Nouvelle matière</button>
    </div>
</div>

<div id="alertContainer"></div>
<div class="card">
    <div class="card-body">
        <table class="table full-width" id="tableMatieres">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Code</th>
                    <th>Volume horaire</th>
                    <th>Coefficient</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="matiereModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nouvelle matière</h3>
            <button class="modal-close" onclick="closeModal('matiereModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="matiereId">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" id="matiereNom" class="form-control">
            </div>
            <div class="form-group">
                <label>Code</label>
                <input type="text" id="matiereCode" class="form-control">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Volume horaire</label>
                    <input type="number" id="matiereVolume" class="form-control" min="0" value="0">
                </div>
                <div class="form-group">
                    <label>Coefficient</label>
                    <input type="number" id="matiereCoef" class="form-control" min="0" step="0.5" value="1">
                </div>
            </div>
            <div class="form-actions">
                <button class="btn btn-outline" onclick="closeModal('matiereModal')">Annuler</button>
                <button class="btn btn-primary" id="btnSaveMatiere">Enregistrer</button>
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

    const api = 'http://localhost/emploi_du_temps/backend/controllers/matieres';

    async function loadMatieres() {
        const res = await fetch(`${api}/index.php`);
        const matieres = await res.json();
        const tbody = document.querySelector('#tableMatieres tbody');
        tbody.innerHTML = '';
        matieres.forEach(m => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>${m.nom}</td>
            <td>${m.code}</td>
            <td>${m.volume_horaire}</td>
            <td>${m.coefficient}</td>
            <td class="text-right">
                <button class="btn btn-outline btn-sm" onclick="editMatiere(${m.id})"><i class='bx bx-edit'></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteMatiere(${m.id})"><i class='bx bx-trash'></i></button>
            </td>`;
            tbody.appendChild(row);
        });
    }

    function showAlert(type, message) {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `<div class="alert alert-${type}"><i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-x-circle'}"></i> ${message}</div>`;
    }

    function openMatiereModal() {
        document.getElementById('matiereId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouvelle matière';
        document.getElementById('matiereNom').value = '';
        document.getElementById('matiereCode').value = '';
        document.getElementById('matiereVolume').value = 0;
        document.getElementById('matiereCoef').value = 1;
        openModal('matiereModal');
    }

    function editMatiere(id) {
        fetch(`${api}/index.php`)
            .then(r => r.json())
            .then(matieres => {
                const matiere = matieres.find(x => x.id == id);
                if (!matiere) return;
                document.getElementById('matiereId').value = matiere.id;
                document.getElementById('modalTitle').textContent = 'Modifier la matière';
                document.getElementById('matiereNom').value = matiere.nom;
                document.getElementById('matiereCode').value = matiere.code;
                document.getElementById('matiereVolume').value = matiere.volume_horaire;
                document.getElementById('matiereCoef').value = matiere.coefficient;
                openModal('matiereModal');
            });
    }

    async function saveMatiere() {
        const id = document.getElementById('matiereId').value;
        const payload = {
            nom: document.getElementById('matiereNom').value.trim(),
            code: document.getElementById('matiereCode').value.trim(),
            volume_horaire: parseInt(document.getElementById('matiereVolume').value, 10),
            coefficient: parseFloat(document.getElementById('matiereCoef').value)
        };
        if (!payload.nom || !payload.code) {
            showAlert('danger', 'Le nom et le code sont requis.');
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
        closeModal('matiereModal');
        loadMatieres();
    }

    async function deleteMatiere(id) {
        if (!confirm('Supprimer cette matière ?')) return;
        const res = await fetch(`${api}/delete.php?id=${id}`);
        const data = await res.json();
        showAlert(data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        loadMatieres();
    }

    document.getElementById('btnNew').addEventListener('click', openMatiereModal);
    document.getElementById('btnSaveMatiere').addEventListener('click', saveMatiere);
    loadMatieres();
</script>