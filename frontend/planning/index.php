<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Emploi du Temps</h1>
        <p>Vue hebdomadaire — Lundi au Vendredi</p>
    </div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <button class="btn btn-outline" id="btnExportPDF"><i class='bx bx-file-pdf'></i> PDF</button>
        <button class="btn btn-primary" id="btnAddCreneau" style="display:none"><i class='bx bx-plus'></i>
            Ajouter</button>
    </div>
</div>

<!-- Filters & Navigation -->
<div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem 1.25rem">
        <div class="planning-toolbar">
            <div class="planning-filters">
                <select id="filterGroupe" class="form-control" style="width:auto">
                    <option value="">— Tous les groupes —</option>
                </select>
                <select id="filterEnseignant" class="form-control" style="width:auto">
                    <option value="">— Tous les enseignants —</option>
                </select>
                <select id="filterSalle" class="form-control" style="width:auto">
                    <option value="">— Toutes les salles —</option>
                </select>
            </div>
            <div class="planning-nav">
                <button id="prevWeek"><i class='bx bx-chevron-left'></i></button>
                <h3 id="weekLabel">Chargement…</h3>
                <button id="nextWeek"><i class='bx bx-chevron-right'></i></button>
                <button class="btn btn-outline btn-sm" id="todayBtn">Aujourd'hui</button>
            </div>
        </div>
    </div>
</div>

<div id="alertContainer"></div>

<!-- Calendar Grid -->
<div class="card">
    <div id="calendarContainer"></div>
</div>

<!-- Add/Edit Creneau Modal -->
<div class="modal-overlay" id="creneauModal">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modalTitle">Nouveau créneau</h3>
            <button class="modal-close" onclick="closeModal('creneauModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body">
            <div id="conflictAlert" style="display:none" class="alert alert-danger">
                <i class='bx bx-error'></i>
                <span id="conflictMsg"></span>
            </div>
            <input type="hidden" id="creneauId">
            <div class="form-row">
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" id="dateCours" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Type</label>
                    <select id="typeCours" class="form-control">
                        <option value="cours">Cours</option>
                        <option value="td">TD</option>
                        <option value="tp">TP</option>
                        <option value="examen">Examen</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Heure début</label>
                    <input type="time" id="heureDebut" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Heure fin</label>
                    <input type="time" id="heureFin" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label>Matière</label>
                <select id="matiereId" class="form-control"></select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Enseignant</label>
                    <select id="enseignantId" class="form-control"></select>
                </div>
                <div class="form-group">
                    <label>Salle</label>
                    <select id="salleId" class="form-control"></select>
                </div>
            </div>
            <div class="form-group">
                <label>Groupe</label>
                <select id="groupeId" class="form-control"></select>
            </div>
            <div class="form-group">
                <label>Récurrence</label>
                <select id="recurrent" class="form-control" onchange="toggleRecurrence()">
                    <option value="0">Aucune (séance unique)</option>
                    <option value="1">Hebdomadaire</option>
                </select>
            </div>
            <div id="recurrenceOptions" style="display:none" class="form-group">
                <label>Jusqu'au</label>
                <input type="date" id="dateFinRecurrence" class="form-control">
            </div>
            <div class="form-actions">
                <button class="btn btn-outline" onclick="closeModal('creneauModal')">Annuler</button>
                <button class="btn btn-primary" id="btnSaveCreneau">
                    <i class='bx bx-save'></i> Enregistrer
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Détail du créneau</h3>
            <button class="modal-close" onclick="closeModal('detailModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" id="detailBody"></div>
    </div>
</div>

<script>
    const DAYS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    const HOURS = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
    let currentMonday = getMonday(new Date());
    let allCreneaux = [];
    let isAdmin = false;
    let allMatieres = [],
        allEnseignants = [],
        allSalles = [],
        allGroupes = [];

    document.addEventListener('DOMContentLoaded', async () => {
        const user = getUser();
        if (!user) {
            location.replace('../users/login.php');
            return;
        }

        isAdmin = user.role === 'administrateur';
        const isTeacher = user.role === 'enseignant';
        if (isAdmin || isTeacher) document.getElementById('btnAddCreneau').style.display = 'flex';

        await Promise.all([loadFilters(), loadResources()]);
        renderWeek();
    });

    // ---- Navigation ----
    document.getElementById('prevWeek').addEventListener('click', () => {
        currentMonday = addDays(currentMonday, -7);
        renderWeek();
    });
    document.getElementById('nextWeek').addEventListener('click', () => {
        currentMonday = addDays(currentMonday, 7);
        renderWeek();
    });
    document.getElementById('todayBtn').addEventListener('click', () => {
        currentMonday = getMonday(new Date());
        renderWeek();
    });
    document.getElementById('btnAddCreneau').addEventListener('click', () => {
        document.getElementById('creneauId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouveau créneau';
        document.getElementById('conflictAlert').style.display = 'none';
        document.getElementById('dateCours').value = toISO(currentMonday);
        openModal('creneauModal');
    });

    // Filter change
    ['filterGroupe', 'filterEnseignant', 'filterSalle'].forEach(id => {
        document.getElementById(id).addEventListener('change', renderWeek);
    });

    // ---- Load resources for selectors ----
    async function loadResources() {
        const [m, e, s, g] = await Promise.all([
            fetch(`${BASE}/matieres/index.php`).then(r => r.json()),
            fetch(`${BASE}/enseignants/index.php`).then(r => r.json()),
            fetch(`${BASE}/salles/index.php`).then(r => r.json()),
            fetch(`${BASE}/groupes/index.php`).then(r => r.json())
        ]);
        allMatieres = Array.isArray(m) ? m : [];
        allEnseignants = Array.isArray(e) ? e : [];
        allSalles = Array.isArray(s) ? s : [];
        allGroupes = Array.isArray(g) ? g : [];

        fillSelect('matiereId', allMatieres, 'id', 'nom');
        fillSelect('enseignantId', allEnseignants, 'id', u => u.prenom + ' ' + u.nom);
        fillSelect('salleId', allSalles, 'id', 'nom');
        fillSelect('groupeId', allGroupes, 'id', 'nom');
    }

    async function loadFilters() {
        const [e, s, g] = await Promise.all([
            fetch(`${BASE}/enseignants/index.php`).then(r => r.json()),
            fetch(`${BASE}/salles/index.php`).then(r => r.json()),
            fetch(`${BASE}/groupes/index.php`).then(r => r.json())
        ]);
        fillSelect('filterEnseignant', Array.isArray(e) ? e : [], 'id', u => u.prenom + ' ' + u.nom, true);
        fillSelect('filterSalle', Array.isArray(s) ? s : [], 'id', 'nom', true);
        fillSelect('filterGroupe', Array.isArray(g) ? g : [], 'id', 'nom', true);

        // Auto-filter for enseignant/etudiant role
        const user = getUser();
        if (user?.role === 'enseignant') {
            // TODO: map user.id to enseignant.id — for now skip
        }
    }

    function fillSelect(id, items, valKey, labelFn, keepDefault = false) {
        const sel = document.getElementById(id);
        if (!sel) return;
        const current = sel.value;
        const defaultOpt = keepDefault ? sel.options[0]?.outerHTML : '';
        sel.innerHTML = defaultOpt;
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item[valKey];
            opt.textContent = typeof labelFn === 'function' ? labelFn(item) : item[labelFn];
            sel.appendChild(opt);
        });
        if (current) sel.value = current;
    }

    // ---- Render Week ----
    async function renderWeek() {
        const label = document.getElementById('weekLabel');
        const friday = addDays(currentMonday, 4);
        label.textContent = `${formatDateFR(currentMonday)} – ${formatDateFR(friday)}`;

        // Build query params
        const params = new URLSearchParams({
            date_debut: toISO(currentMonday),
            date_fin: toISO(friday)
        });
        const g = document.getElementById('filterGroupe').value;
        const en = document.getElementById('filterEnseignant').value;
        const s = document.getElementById('filterSalle').value;
        if (g) params.set('groupe_id', g);
        if (en) params.set('enseignant_id', en);
        if (s) params.set('salle_id', s);

        const res = await fetch(`${BASE}/creneaux/get_all.php?${params}`);
        const data = await res.json();
        allCreneaux = Array.isArray(data) ? data : [];

        buildGrid();
    }

    function buildGrid() {
        const container = document.getElementById('calendarContainer');
        const today = toISO(new Date());

        let html = '<div class="week-grid">';
        // Headers
        html += '<div class="wg-col-header"></div>';
        for (let d = 0; d < 5; d++) {
            const day = addDays(currentMonday, d);
            const iso = toISO(day);
            const isToday = iso === today;
            html += `<div class="wg-col-header ${isToday?'today':''}">
            ${DAYS[d]}<br><small>${formatDateFR(day)}</small>
        </div>`;
        }
        // Time rows
        HOURS.forEach((hour, hi) => {
            html += `<div class="wg-time-slot">${hour}</div>`;
            for (let d = 0; d < 5; d++) {
                const day = addDays(currentMonday, d);
                const iso = toISO(day);
                // Find creneaux for this day+hour slot
                const creneaux = allCreneaux.filter(c => {
                    const ch = c.heure_debut?.substring(0, 5);
                    return c.date_cours === iso && ch === hour;
                });
                let cellHtml = '';
                creneaux.forEach(c => {
                    const typeClass = `type-${c.type || 'cours'}`;
                    cellHtml += `
                    <div class="wg-creneau ${typeClass}" onclick="showDetail(${c.id})"
                         data-id="${c.id}">
                        <div class="wg-title">${c.matiere_nom || ''} <span class="badge badge-gray" style="font-size:.65rem">${c.type?.toUpperCase()}</span></div>
                        <div class="wg-sub">${c.enseignant_nom || ''}</div>
                        <div class="wg-sub">${c.salle_nom||''} · ${c.groupe_nom||''}</div>
                        <div class="wg-sub">${c.heure_debut?.substring(0,5)}–${c.heure_fin?.substring(0,5)}</div>
                    </div>`;
                });
                if (isAdmin || isTeacher) {
                    cellHtml += `<div class="wg-add-btn" style="opacity:0;position:absolute;inset:0;cursor:pointer"
                    onclick="prefillAdd('${iso}','${hour}')"></div>`;
                }
                html += `<div class="wg-cell" style="position:relative">${cellHtml}</div>`;
            }
        });
        html += '</div>';
        container.innerHTML = html;
    }

    function prefillAdd(date, hour) {
        document.getElementById('creneauId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouveau créneau';
        document.getElementById('conflictAlert').style.display = 'none';
        document.getElementById('dateCours').value = date;
        document.getElementById('heureDebut').value = hour;
        // auto-add 1h
        const [hh, mm] = hour.split(':').map(Number);
        document.getElementById('heureFin').value = `${String(hh+1).padStart(2,'0')}:${String(mm).padStart(2,'0')}`;
        openModal('creneauModal');
    }

    function showDetail(id) {
        const c = allCreneaux.find(x => x.id == id);
        if (!c) return;
        const body = document.getElementById('detailBody');
        body.innerHTML = `
        <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.9rem">
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-secondary)">Matière</span>
                <strong>${c.matiere_nom}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-secondary)">Enseignant</span>
                <strong>${c.enseignant_nom}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-secondary)">Salle</span>
                <strong>${c.salle_nom}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-secondary)">Groupe</span>
                <strong>${c.groupe_nom}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-secondary)">Date</span>
                <strong>${c.date_cours}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                <span style="color:var(--text-secondary)">Horaire</span>
                <strong>${c.heure_debut?.substring(0,5)} – ${c.heure_fin?.substring(0,5)}</strong>
            </div>
            <div style="display:flex;justify-content:space-between;padding:.5rem 0">
                <span style="color:var(--text-secondary)">Type</span>
                ${typeBadge(c.type)}
            </div>
        </div>
        ${(isAdmin || (user.role === 'enseignant')) ? `<div class="form-actions" style="margin-top:1rem">
            <button class="btn btn-outline" onclick="editCreneau(${c.id})"><i class='bx bx-edit'></i> Modifier</button>
            <button class="btn btn-danger" onclick="deleteCreneau(${c.id})"><i class='bx bx-trash'></i> Supprimer</button>
        </div>` : ''}
    `;
        openModal('detailModal');
    }

    function editCreneau(id) {
        const c = allCreneaux.find(x => x.id == id);
        if (!c) return;
        closeModal('detailModal');
        document.getElementById('creneauId').value = c.id;
        document.getElementById('modalTitle').textContent = 'Modifier le créneau';
        document.getElementById('dateCours').value = c.date_cours;
        document.getElementById('heureDebut').value = c.heure_debut?.substring(0, 5);
        document.getElementById('heureFin').value = c.heure_fin?.substring(0, 5);
        document.getElementById('typeCours').value = c.type;
        // selects will populate from allCreneaux ids when resources are loaded
        openModal('creneauModal');
    }

    async function deleteCreneau(id) {
        if (!confirm('Supprimer ce créneau ?')) return;
        const user = getUser();
        if (user.role === 'administrateur' || user.role === 'enseignant') {
            const res = await fetch(`${BASE}/creneaux/delete.php?id=${id}`);
            const data = await res.json();
            closeModal('detailModal');
            showAlert(document.getElementById('alertContainer'), data.message === 'Créneau supprimé' ? 'success' : 'danger', data.message);
            renderWeek();
        } else {
            // submit proposal
            const prop = {
                auteur_id: user.id,
                resource: 'creneau',
                action: 'delete',
                cible_id: id,
                payload: {}
            };
            const r = await fetch(`${BASE}/proposals/create.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(prop)
            });
            const d = await r.json();
            closeModal('detailModal');
            showAlert(document.getElementById('alertContainer'), 'success', d.message || 'Proposition envoyée');
        }
    }

    // ---- Save Creneau ----
    document.getElementById('btnSaveCreneau').addEventListener('click', async () => {
        const id = document.getElementById('creneauId').value;
        const date_cours = document.getElementById('dateCours').value;
        const heure_debut = document.getElementById('heureDebut').value;
        const heure_fin = document.getElementById('heureFin').value;
        const matiere_id = document.getElementById('matiereId').value;
        const enseignant_id = document.getElementById('enseignantId').value;
        const salle_id = document.getElementById('salleId').value;
        const groupe_id = document.getElementById('groupeId').value;
        const type = document.getElementById('typeCours').value;
        const recurrent = document.getElementById('recurrent').value;
        const date_fin_rec = document.getElementById('dateFinRecurrence').value;

        document.getElementById('conflictAlert').style.display = 'none';

        const payload = {
            date_cours,
            heure_debut,
            heure_fin,
            matiere_id,
            enseignant_id,
            salle_id,
            groupe_id,
            type,
            recurrent,
            freq_recurrence: recurrent == '1' ? 'hebdomadaire' : null,
            date_fin_recurrence: date_fin_rec || null
        };
        if (id) payload.id = id;

        const user = getUser();
        let data = {};
        if (user.role === 'administrateur' || user.role === 'enseignant') {
            const url = id ? `${BASE}/creneaux/update.php` : `${BASE}/creneaux/add.php`;
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            data = await res.json();
        } else {
            // submit proposal for creation/update
            const prop = {
                auteur_id: user.id,
                resource: 'creneau',
                action: id ? 'update' : 'create',
                cible_id: id || null,
                payload
            };
            const r = await fetch(`${BASE}/proposals/create.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(prop)
            });
            data = await r.json();
        }

        if (data.conflits) {
            const names = {
                enseignant: 'enseignant',
                salle: 'salle',
                groupe: 'groupe'
            };
            document.getElementById('conflictMsg').textContent =
                'Conflit détecté avec : ' + data.conflits.map(c => names[c]).join(', ');
            document.getElementById('conflictAlert').style.display = 'flex';
            return;
        }

        closeModal('creneauModal');
        showAlert(document.getElementById('alertContainer'), 'success', data.message);
        renderWeek();
    });

    function toggleRecurrence() {
        const v = document.getElementById('recurrent').value;
        document.getElementById('recurrenceOptions').style.display = v == '1' ? 'block' : 'none';
    }

    // ---- Export PDF (print) ----
    document.getElementById('btnExportPDF').addEventListener('click', () => window.print());

    // ---- Date helpers ----
    function getMonday(d) {
        const day = d.getDay();
        const diff = d.getDate() - day + (day == 0 ? -6 : 1);
        return new Date(d.setDate(diff));
    }

    function addDays(d, n) {
        const r = new Date(d);
        r.setDate(r.getDate() + n);
        return r;
    }

    function toISO(d) {
        return d.toISOString().split('T')[0];
    }

    function formatDateFR(d) {
        return d.toLocaleDateString('fr-MA', {
            day: '2-digit',
            month: 'short'
        });
    }
</script>

<?php include('../inc/footer.php') ?>