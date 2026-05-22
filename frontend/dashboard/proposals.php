<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Propositions en attente</h1>
        <p>Propositions soumises par les enseignants (créations/modifications/suppressions)</p>
    </div>
</div>

<div id="alertContainer"></div>

<div class="card">
    <div class="card-body">
        <table class="table" id="propTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Auteur</th>
                    <th>Resource</th>
                    <th>Action</th>
                    <th>Payload</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="7" class="notif_empty">Chargement…</td>
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
        loadProps();
    });

    async function loadProps() {
        const tbody = document.querySelector('#propTable tbody');
        tbody.innerHTML = '<tr><td colspan="7" class="notif_empty">Chargement…</td></tr>';
        try {
            const res = await fetch(`${BASE}/proposals/list.php`);
            const data = await res.json();
            if (!Array.isArray(data) || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="7" class="notif_empty">Aucune proposition</td></tr>';
                return;
            }
            tbody.innerHTML = '';
            data.forEach(p => {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td>${p.id}</td><td>${p.auteur_prenom} ${p.auteur_nom}</td><td>${p.resource}</td><td>${p.action}</td>
                    <td><pre style="white-space:pre-wrap;max-width:300px">${p.payload}</pre></td><td>${p.status}</td>
                    <td>${p.status==='pending'?`<button class="btn btn-sm" onclick="decide(${p.id},'approved', this)">Approuver</button> <button class="btn btn-sm" onclick="decide(${p.id},'rejected', this)">Refuser</button>`:''}</td>`;
                tbody.appendChild(tr);
            });
        } catch (e) {
            showAlert(document.getElementById('alertContainer'), 'danger', 'Erreur chargement');
        }
    }

    async function decide(id, status, btn) {
        btn.disabled = true;
        try {
            const res = await fetch(`${BASE}/proposals/approve.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    proposal_id: id,
                    status
                })
            });
            const data = await res.json();
            showAlert(document.getElementById('alertContainer'), 'success', data.message || 'Mise à jour');
            loadProps();
        } catch (e) {
            showAlert(document.getElementById('alertContainer'), 'danger', 'Erreur réseau');
            btn.disabled = false;
        }
    }
</script>

<?php include('../inc/footer.php') ?>