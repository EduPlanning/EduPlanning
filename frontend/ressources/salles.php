<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Salles</h1>
        <p>Gérez les salles et équipements disponibles.</p>
    </div>
    <div>
        <button class="btn btn-primary" id="btnNew"><i class='bx bx-plus'></i> Nouvelle salle</button>
    </div>
</div>

<div id="alertContainer"></div>
<div class="card">
    <div class="card-body">
        <table class="table full-width" id="tableSalles">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Capacité</th>
                    <th>Équipements</th>
                    <th>Disponible</th>
                    <th></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="salleModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nouvelle salle</h3>
            <button class="modal-close" onclick="closeModal('salleModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="salleId">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" id="salleNom" class="form-control">
            </div>
            <div class="form-group">
                <label>Capacité</label>
                <input type="number" id="salleCapacite" class="form-control" min="1" value="30">
            </div>
            <div class="form-group">
                <label>Équipements</label>
                <textarea id="salleEquipements" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Disponible</label>
                <select id="salleDisponible" class="form-control">
                    <option value="1">Oui</option>
                    <option value="0">Non</option>
                </select>
            </div>
            <div class="form-actions">
                <button class="btn btn-outline" onclick="closeModal('salleModal')">Annuler</button>
                <button class="btn btn-primary" id="btnSaveSalle">Enregistrer</button>
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

    const api = 'http://localhost/emploi_du_temps/backend/controllers/salles';

    async function loadSalles() {
        const res = await fetch(`${api}/index.php`);
        const salles = await res.json();
        const tbody = document.querySelector('#tableSalles tbody');
        tbody.innerHTML = '';
        salles.forEach(s => {
            const row = document.createElement('tr');
            row.innerHTML = `
            <td>${s.nom}</td>
            <td>${s.capacite}</td>
            <td>${s.equipements || '—'}</td>
            <td>${s.disponible == 1 ? 'Oui' : 'Non'}</td>
            <td class="text-right">
                <button class="btn btn-outline btn-sm" onclick="editSalle(${s.id})"><i class='bx bx-edit'></i></button>
                <button class="btn btn-danger btn-sm" onclick="deleteSalle(${s.id})"><i class='bx bx-trash'></i></button>
            </td>`;
            tbody.appendChild(row);
        });
    }

    function showAlert(type, message) {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `<div class="alert alert-${type}"><i class="bx ${type === 'success' ? 'bx-check-circle' : 'bx-x-circle'}"></i> ${message}</div>`;
    }

    function openSalleModal() {
        document.getElementById('salleId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouvelle salle';
        document.getElementById('salleNom').value = '';
        document.getElementById('salleCapacite').value = 30;
        document.getElementById('salleEquipements').value = '';
        document.getElementById('salleDisponible').value = 1;
        openModal('salleModal');
    }

    function editSalle(id) {
        fetch(`${api}/index.php`)
            .then(r => r.json())
            .then(salles => {
                const salle = salles.find(x => x.id == id);
                if (!salle) return;
                document.getElementById('salleId').value = salle.id;
                document.getElementById('modalTitle').textContent = 'Modifier la salle';
                document.getElementById('salleNom').value = salle.nom;
                document.getElementById('salleCapacite').value = salle.capacite;
                document.getElementById('salleEquipements').value = salle.equipements;
                document.getElementById('salleDisponible').value = salle.disponible;
                openModal('salleModal');
            });
    }

    async function saveSalle() {
        const id = document.getElementById('salleId').value;
        const payload = {
            nom: document.getElementById('salleNom').value.trim(),
            capacite: parseInt(document.getElementById('salleCapacite').value, 10),
            equipements: document.getElementById('salleEquipements').value.trim(),
            disponible: parseInt(document.getElementById('salleDisponible').value, 10)
        };
        if (!payload.nom) {
            showAlert('danger', 'Le nom de la salle est requis.');
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
        showAlert(data.message === 'Salle créée' || data.message === 'Salle mise à jour' ? 'success' : 'danger', data.message);
        closeModal('salleModal');
        loadSalles();
    }

    async function deleteSalle(id) {
        if (!confirm('Supprimer cette salle ?')) return;
        const res = await fetch(`${api}/delete.php?id=${id}`);
        const data = await res.json();
        showAlert(data.message === 'Salle supprimée' ? 'success' : 'danger', data.message);
        loadSalles();
    }

    document.getElementById('btnNew').addEventListener('click', openSalleModal);
    document.getElementById('btnSaveSalle').addEventListener('click', saveSalle);
    loadSalles();
</script>