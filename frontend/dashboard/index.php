<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Tableau de bord</h1>
        <p>Vue d'ensemble du système de gestion des emplois du temps</p>
        <p id="userRoleLabel" class="text-muted" style="margin-top:.5rem"></p>
    </div>
</div>

<div id="alertContainer"></div>

<div class="stats-grid" id="statsGrid">
    <div class="stat-card">
        <div class="stat_icon blue"><i class='bx bx-user-voice'></i></div>
        <div class="stat_info">
            <p>Enseignants</p>
            <h3 id="statEnseignants">-</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon green"><i class='bx bx-group'></i></div>
        <div class="stat_info">
            <p>Étudiants</p>
            <h3 id="statEtudiants">-</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon orange"><i class='bx bx-building'></i></div>
        <div class="stat_info">
            <p>Salles</p>
            <h3 id="statSalles">-</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon blue"><i class='bx bx-calendar'></i></div>
        <div class="stat_info">
            <p>Créneaux</p>
            <h3 id="statCreneaux">-</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon red"><i class='bx bx-error-alt'></i></div>
        <div class="stat_info">
            <p>Conflits</p>
            <h3 id="statConflits">-</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon green"><i class='bx bx-collection'></i></div>
        <div class="stat_info">
            <p>Groupes</p>
            <h3 id="statGroupes">-</h3>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat_icon blue"><i class='bx bx-sitemap'></i></div>
        <div class="stat_info">
            <p>Filières</p>
            <h3 id="statFilieres">-</h3>
        </div>
    </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem" id="chartsRow">
    <div class="card">
        <div class="card-header">
            <h2><i class='bx bx-bar-chart-alt-2'></i> Occupation des salles (heures)</h2>
        </div>
        <div class="chart-container" id="occChart"></div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2><i class='bx bx-bar-chart-square'></i> Répartition horaire par filière</h2>
        </div>
        <div class="chart-container" id="filiereChart"></div>
    </div>
</div>

<div class="card" id="quickActionsCard">
    <div class="card-header">
        <h2>Accès rapide</h2>
    </div>
    <div class="card-body">
        <div style="display:flex;flex-wrap:wrap;gap:.75rem" id="quickActions"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const user = getUser();
        if (!user) {
            location.replace('../users/login.php');
            return;
        }

        const roleNames = {
            administrateur: 'Administrateur',
            enseignant: 'Enseignant',
            etudiant: 'Étudiant'
        };
        document.getElementById('userRoleLabel').textContent = `Connecté en tant que : ${roleNames[user.role] || user.role}`;
        renderQuickActions(user.role);
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
            document.getElementById('statFilieres').textContent = data.nb_filieres;

            if (data.nb_conflits > 0) {
                const conflictCard = document.querySelector('#statsGrid .stat-card:nth-child(5)');
                if (conflictCard) {
                    conflictCard.style.borderColor = 'var(--danger)';
                    conflictCard.style.background = 'var(--danger-light)';
                }
            }

            drawBarChart(document.getElementById('occChart'), data.occupation_salles || [], 'salle');
            drawBarChart(document.getElementById('filiereChart'), data.repartition_filieres || [], 'filiere');
        } catch (e) {
            showAlert(document.getElementById('alertContainer'), 'danger', 'Erreur chargement des statistiques.');
        }
    }

    function drawBarChart(container, data, labelKey) {
        if (!Array.isArray(data) || data.length === 0) {
            container.innerHTML = '<p class="notif_empty">Aucune donnée</p>';
            return;
        }

        const maxValue = Math.max(...data.map((item) => parseFloat(item.heures_total) || 0), 1);
        container.innerHTML = '';

        data.slice(0, 6).forEach((item) => {
            const value = parseFloat(item.heures_total) || 0;
            const pct = Math.round((value / maxValue) * 100);
            const col = document.createElement('div');
            col.className = 'chart-bar-group';
            col.innerHTML = `
                <div class="chart-bar-value">${value.toFixed(1)}h</div>
                <div class="chart-bar-outer" style="flex:1;width:100%">
                    <div class="chart-bar-inner" style="height:${pct}%"></div>
                </div>
                <div class="chart-bar-label">${escapeHtml(item[labelKey] || '-')}</div>`;
            container.appendChild(col);
        });
    }

    function renderQuickActions(role) {
        const container = document.getElementById('quickActions');
        const actions = [];

        if (role === 'administrateur') {
            actions.push(
                { href: '../ressources/utilisateurs.php', label: 'Utilisateurs', icon: 'bx-user' },
                { href: '../ressources/filieres.php', label: 'Filières', icon: 'bx-sitemap' },
                { href: '../dashboard/validate_requests.php', label: 'Validation étudiants', icon: 'bx-check-shield' },
                { href: '../dashboard/proposals.php', label: 'Propositions', icon: 'bx-send' },
                { href: '../planning/index.php', label: 'Consulter planning', icon: 'bx-calendar' }
            );
        } else if (role === 'enseignant') {
            actions.push(
                { href: '../planning/index.php', label: 'Gérer planning', icon: 'bx-calendar-plus' },
                { href: '../ressources/groupes.php', label: 'Groupes', icon: 'bx-group' },
                { href: '../ressources/matieres.php', label: 'Matières', icon: 'bx-book' },
                { href: '../ressources/salles.php', label: 'Salles', icon: 'bx-building' },
                { href: '../enseignants/create_student.php', label: 'Créer étudiant', icon: 'bx-user-plus' }
            );
        } else {
            actions.push(
                { href: '../planning/index.php', label: 'Voir planning', icon: 'bx-calendar' },
                { href: '../notifications/index.php', label: 'Notifications', icon: 'bx-bell' }
            );
        }

        container.innerHTML = actions.map((action) => `
            <a href="${action.href}" class="btn btn-outline">
                <i class='bx ${action.icon}'></i> ${escapeHtml(action.label)}
            </a>`).join('');
    }
</script>

<?php include('../inc/footer.php') ?>
