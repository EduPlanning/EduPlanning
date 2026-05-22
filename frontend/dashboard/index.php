<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Tableau de bord</h1>
        <p>Vue d'ensemble du système de gestion des emplois du temps</p>
    </div>
    <div id="adminActions" style="display:none">
        <a href="../planning/index.php" class="btn btn-primary">
            <i class='bx bx-plus'></i> Nouveau créneau
        </a>
    </div>
</div>

<div id="alertContainer"></div>

<!-- Stats Cards -->
<div class="stats-grid" id="statsGrid">
    <div class="stat-card">
        <div class="stat_icon blue"><i class='bx bx-user-voice'></i></div>
        <div class="stat_info">
            <p>Enseignants</p>
            <h3 id="statEnseignants">—</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon green"><i class='bx bx-group'></i></div>
        <div class="stat_info">
            <p>Étudiants</p>
            <h3 id="statEtudiants">—</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon orange"><i class='bx bx-building'></i></div>
        <div class="stat_info">
            <p>Salles actives</p>
            <h3 id="statSalles">—</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon blue"><i class='bx bx-calendar'></i></div>
        <div class="stat_info">
            <p>Créneaux total</p>
            <h3 id="statCreneaux">—</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon red"><i class='bx bx-error-alt'></i></div>
        <div class="stat_info">
            <p>Conflits détectés</p>
            <h3 id="statConflits">—</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon green"><i class='bx bx-collection'></i></div>
        <div class="stat_info">
            <p>Groupes</p>
            <h3 id="statGroupes">—</h3>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem" id="chartsRow">
    <div class="card">
        <div class="card-header">
            <h2><i class='bx bx-bar-chart-alt-2'></i> Occupation des salles (heures)</h2>
        </div>
        <div class="chart-container" id="occChart"></div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class='bx bx-info-circle'></i> Informations système</h2>
        </div>
        <div class="card-body">
            <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.875rem">
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Version</span>
                    <span style="font-weight:600">1.0 — PFE 2025-2026</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Établissement</span>
                    <span style="font-weight:600">ETEC</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Filière</span>
                    <span style="font-weight:600">Développement Informatique</span>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0">
                    <span style="color:var(--text-secondary)">Réalisé par</span>
                    <span style="font-weight:600">Nouhaila Elbouraqqady</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick actions for admin -->
<div class="card" id="quickActionsCard" style="display:none">
    <div class="card-header">
        <h2>Accès rapide — Administration</h2>
    </div>
    <div class="card-body">
        <div style="display:flex;flex-wrap:wrap;gap:.75rem">
            <a href="../ressources/utilisateurs.php" class="btn btn-outline"><i class='bx bx-user'></i> Gérer les utilisateurs</a>
            <a href="../ressources/salles.php" class="btn btn-outline"><i class='bx bx-building'></i> Gérer les salles</a>
            <a href="../ressources/matieres.php" class="btn btn-outline"><i class='bx bx-book'></i> Gérer les matières</a>
            <a href="../ressources/groupes.php" class="btn btn-outline"><i class='bx bx-group'></i> Gérer les groupes</a>
            <a href="../planning/index.php" class="btn btn-primary"><i class='bx bx-calendar-plus'></i> Gérer l'emploi du temps</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const user = getUser();
        if (!user) {
            location.replace('../users/login.php');
            return;
        }

        if (user.role === 'administrateur') {
            document.getElementById('adminActions').style.display = 'flex';
            document.getElementById('quickActionsCard').style.display = 'block';
        }

        loadStats();
    });

    async function loadStats() {
        try {
            const res = await fetch(`${BASE}/stats/stats.php`);
            const data = await res.json();

            document.getElementById('statEnseignants').textContent = data.nb_enseignants;
            document.getElementById('statEtudiants').textContent = data.nb_etudiants;
            document.getElementById('statSalles').textContent = data.nb_salles;
            document.getElementById('statCreneaux').textContent = data.nb_creneaux;
            document.getElementById('statConflits').textContent = data.nb_conflits;
            document.getElementById('statGroupes').textContent = data.nb_groupes;

            // Conflict card red highlight
            if (data.nb_conflits > 0) {
                const conflictCard = document.querySelector('#statsGrid .stat-card:nth-child(5)');
                if (conflictCard) {
                    conflictCard.style.borderColor = 'var(--danger)';
                    conflictCard.style.background = 'var(--danger-light)';
                }
            }

            drawOccupationChart(data.occupation_salles || []);
        } catch (e) {
            showAlert(document.getElementById('alertContainer'), 'danger', 'Erreur chargement des statistiques.');
        }
    }

    function drawOccupationChart(data) {
        const container = document.getElementById('occChart');
        if (!data.length) {
            container.innerHTML = '<p class="notif_empty">Aucune donnée</p>';
            return;
        }
        const maxH = Math.max(...data.map(d => parseFloat(d.heures_total) || 0), 1);
        container.innerHTML = '';

        data.slice(0, 6).forEach(d => {
            const h = parseFloat(d.heures_total) || 0;
            const pct = Math.round((h / maxH) * 100);
            const col = document.createElement('div');
            col.className = 'chart-bar-group';
            col.innerHTML = `
            <div class="chart-bar-value">${h.toFixed(1)}h</div>
            <div class="chart-bar-outer" style="flex:1;width:100%">
                <div class="chart-bar-inner" style="height:${pct}%"></div>
            </div>
            <div class="chart-bar-label">${d.salle}</div>`;
            container.appendChild(col);
        });
    }
</script>

<?php include('../inc/footer.php') ?>