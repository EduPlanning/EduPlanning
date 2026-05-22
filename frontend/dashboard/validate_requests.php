<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Demandes en attente</h1>
        <p>Liste des étudiants créés par les enseignants en attente de validation</p>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card">
    <div class="card-body">
        <table class="table" id="pendingTable">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Groupe</th>
                    <th>Créé le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" class="notif_empty">Chargement…</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const user = getUser();
        if (!user) return location.replace('../users/login.php');
        if (user.role !== 'administrateur') {
            document.body.innerHTML = '<div style="padding:2rem">Accès refusé</div>';
            return;
        }
        loadPending();
    });

    async function loadPending() {
        const tbody = document.querySelector('#pendingTable tbody');
        tbody.innerHTML = '<tr><td colspan="5" class="notif_empty">Chargement…</td></tr>';
        try {
            const [gres, rres] = await Promise.all([
                fetch(`${BASE}/groupes/index.php`),
                fetch(`${BASE}/users/get_all.php?role=etudiant`)
            ]);
            const groupes = await gres.json();
            const data = await rres.json();
            const groupeMap = {};
            if (Array.isArray(groupes)) groupes.forEach(g => groupeMap[g.id] = g.nom);
            const pending = Array.isArray(data) ? data.filter(u => parseInt(u.actif) === 0) : [];
            if (!pending.length) {
                tbody.innerHTML = '<tr><td colspan="5" class="notif_empty">Aucune demande en attente</td></tr>';
                return;
            }
            tbody.innerHTML = '';
            pending.forEach(u => {
                const tr = document.createElement('tr');
                const gname = u.groupe_id ? (groupeMap[u.groupe_id] || '—') : '—';
                tr.innerHTML = `<td>${u.nom}</td><td>${u.prenom}</td><td>${u.email}</td><td>${gname}</td><td>${u.cree_le}</td>
                    <td><button class="btn btn-sm" onclick="approve(${u.id}, this)">Approuver</button></td>`;
                tbody.appendChild(tr);
            });
        } catch (e) {
            showAlert(document.getElementById('alertContainer'), 'danger', 'Erreur chargement.');
        }
    }

    async function approve(id, btn) {
        btn.disabled = true;
        try {
            const res = await fetch(`${BASE}/users/approve_student.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    student_id: id
                })
            });
            const data = await res.json();
            if (data.message) showAlert(document.getElementById('alertContainer'), 'success', data.message);
            loadPending();
        } catch (e) {
            showAlert(document.getElementById('alertContainer'), 'danger', 'Erreur réseau.');
            btn.disabled = false;
        }
    }
</script>

<?php include('../inc/footer.php') ?>