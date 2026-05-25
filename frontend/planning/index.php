<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Emploi du Temps</h1>
        <p>Consultation du planning avec vues journalière, hebdomadaire et mensuelle.</p>
    </div>
    <div class="view-switch">
        <button class="btn btn-outline" id="btnViewDay">Jour</button>
        <button class="btn btn-outline" id="btnViewWeek">Semaine</button>
        <button class="btn btn-outline" id="btnViewMonth">Mois</button>
        <button class="btn btn-outline" id="btnExportPDF"><i class='bx bx-file-pdf'></i> PDF</button>
        <button class="btn btn-outline" id="btnExportExcel"><i class='bx bx-spreadsheet'></i> Excel</button>
        <button class="btn btn-outline" id="btnExportICS"><i class='bx bx-calendar-event'></i> iCal</button>
        <button class="btn btn-outline" id="btnDuplicateSemester"><i class='bx bx-copy'></i> Dupliquer semestre</button>
        <button class="btn btn-primary" id="btnAddCreneau"><i class='bx bx-plus'></i> Ajouter</button>
    </div>
</div>

<div class="card" style="margin-bottom:1rem">
    <div class="card-body" style="padding:.75rem 1.25rem">
        <div class="planning-toolbar">
            <div class="planning-filters">
                <select id="filterGroupe" class="form-control" style="width:auto">
                    <option value="">- Tous les groupes -</option>
                </select>
                <select id="filterEnseignant" class="form-control" style="width:auto">
                    <option value="">- Tous les enseignants -</option>
                </select>
                <select id="filterSalle" class="form-control" style="width:auto">
                    <option value="">- Toutes les salles -</option>
                </select>
            </div>
            <div class="planning-nav">
                <button id="prevPeriod"><i class='bx bx-chevron-left'></i></button>
                <h3 id="periodLabel">Chargement...</h3>
                <button id="nextPeriod"><i class='bx bx-chevron-right'></i></button>
                <button class="btn btn-outline btn-sm" id="todayBtn">Aujourd'hui</button>
            </div>
        </div>
    </div>
</div>

<div id="alertContainer"></div>
<div id="readOnlyNote"></div>

<div class="card">
    <div id="calendarContainer"></div>
</div>

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
                <select id="recurrent" class="form-control">
                    <option value="0">Aucune</option>
                    <option value="1">Hebdomadaire</option>
                </select>
            </div>
            <div id="recurrenceOptions" style="display:none" class="form-group">
                <label>Jusqu'au</label>
                <input type="date" id="dateFinRecurrence" class="form-control">
            </div>
            <div class="form-actions">
                <button class="btn btn-outline" onclick="closeModal('creneauModal')">Annuler</button>
                <button class="btn btn-primary" id="btnSaveCreneau"><i class='bx bx-save'></i> Enregistrer</button>
            </div>
        </div>
    </div>
</div>

<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Détail du créneau</h3>
            <button class="modal-close" onclick="closeModal('detailModal')"><i class='bx bx-x'></i></button>
        </div>
        <div class="modal-body" id="detailBody"></div>
    </div>
</div>

<?php include('../inc/footer.php') ?>

<script>
    const HOURS = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
    const DAYS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi'];
    const MONTH_DAYS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    let currentUser = null;
    let currentView = 'week';
    let currentDate = startOfDay(new Date());
    let canManagePlanning = false;
    let allCreneaux = [];
    let allMatieres = [];
    let allEnseignants = [];
    let allSalles = [];
    let allGroupes = [];

    document.addEventListener('DOMContentLoaded', async () => {
        currentUser = getUser();
        if (!currentUser) {
            location.replace('../users/login.php');
            return;
        }

        canManagePlanning = currentUser.role === 'enseignant';
        if (!canManagePlanning) {
            document.getElementById('btnAddCreneau').style.display = 'none';
            document.getElementById('btnDuplicateSemester').style.display = 'none';
            const note = currentUser.role === 'administrateur'
                ? 'Lecture seule pour les administrateurs. La planification directe est réservée aux enseignants.'
                : 'Lecture seule pour les étudiants.';
            document.getElementById('readOnlyNote').innerHTML = `<div class="alert alert-info">${escapeHtml(note)}</div>`;
        }

        bindEvents();
        await Promise.all([loadFilters(), loadResources()]);
        renderCurrentView();
    });

    function bindEvents() {
        document.getElementById('btnViewDay').addEventListener('click', () => setView('day'));
        document.getElementById('btnViewWeek').addEventListener('click', () => setView('week'));
        document.getElementById('btnViewMonth').addEventListener('click', () => setView('month'));
        document.getElementById('prevPeriod').addEventListener('click', prevPeriod);
        document.getElementById('nextPeriod').addEventListener('click', nextPeriod);
        document.getElementById('todayBtn').addEventListener('click', () => {
            currentDate = startOfDay(new Date());
            renderCurrentView();
        });
        document.getElementById('btnAddCreneau').addEventListener('click', () => prefillAdd(toISO(currentDate), '08:00'));
        document.getElementById('btnDuplicateSemester').addEventListener('click', duplicateSemester);
        document.getElementById('btnSaveCreneau').addEventListener('click', saveCreneau);
        document.getElementById('btnExportPDF').addEventListener('click', () => window.print());
        document.getElementById('btnExportExcel').addEventListener('click', () => exportPlanning('xlsx'));
        document.getElementById('btnExportICS').addEventListener('click', () => exportPlanning('ical'));
        document.getElementById('recurrent').addEventListener('change', toggleRecurrence);

        ['filterGroupe', 'filterEnseignant', 'filterSalle'].forEach((id) => {
            document.getElementById(id).addEventListener('change', renderCurrentView);
        });
    }

    async function loadResources() {
        const [matieres, enseignants, salles, groupes] = await Promise.all([
            fetch(`${BASE}/matieres/index.php`).then((res) => res.json()),
            fetch(`${BASE}/enseignants/index.php`).then((res) => res.json()),
            fetch(`${BASE}/salles/index.php`).then((res) => res.json()),
            fetch(`${BASE}/groupes/index.php`).then((res) => res.json())
        ]);

        allMatieres = Array.isArray(matieres) ? matieres : [];
        allEnseignants = Array.isArray(enseignants) ? enseignants : [];
        allSalles = Array.isArray(salles) ? salles : [];
        allGroupes = Array.isArray(groupes) ? groupes : [];

        fillSelect('matiereId', allMatieres, 'id', 'nom');
        fillSelect('enseignantId', allEnseignants, 'id', (item) => `${item.prenom} ${item.nom}`);
        fillSelect('salleId', allSalles, 'id', 'nom');
        fillSelect('groupeId', allGroupes, 'id', 'nom');
    }

    async function loadFilters() {
        const [enseignants, salles, groupes] = await Promise.all([
            fetch(`${BASE}/enseignants/index.php`).then((res) => res.json()),
            fetch(`${BASE}/salles/index.php`).then((res) => res.json()),
            fetch(`${BASE}/groupes/index.php`).then((res) => res.json())
        ]);

        fillSelect('filterEnseignant', Array.isArray(enseignants) ? enseignants : [], 'id', (item) => `${item.prenom} ${item.nom}`, true);
        fillSelect('filterSalle', Array.isArray(salles) ? salles : [], 'id', 'nom', true);
        fillSelect('filterGroupe', Array.isArray(groupes) ? groupes : [], 'id', 'nom', true);

        if (currentUser.role === 'enseignant') {
            const me = enseignants.find((item) => item.utilisateur_id == currentUser.id);
            if (me) {
                document.getElementById('filterEnseignant').value = me.id;
            }
        }
    }

    function fillSelect(id, items, valueKey, labelKey, keepDefault = false) {
        const select = document.getElementById(id);
        if (!select) return;
        const currentValue = select.value;
        const defaultOption = keepDefault ? select.options[0]?.outerHTML : '';
        select.innerHTML = defaultOption;
        items.forEach((item) => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.textContent = typeof labelKey === 'function' ? labelKey(item) : item[labelKey];
            select.appendChild(option);
        });
        if (currentValue) {
            select.value = currentValue;
        }
    }

    function setView(view) {
        currentView = view;
        updateViewButtons();
        renderCurrentView();
    }

    function updateViewButtons() {
        document.getElementById('btnViewDay').classList.toggle('is-active', currentView === 'day');
        document.getElementById('btnViewWeek').classList.toggle('is-active', currentView === 'week');
        document.getElementById('btnViewMonth').classList.toggle('is-active', currentView === 'month');
    }

    async function renderCurrentView() {
        updateViewButtons();
        const range = getCurrentRange();
        updatePeriodLabel(range);
        await fetchCreneaux(range);

        if (currentView === 'day') {
            buildTimeGrid([range.start], true);
        } else if (currentView === 'week') {
            const days = [];
            let cursor = new Date(range.start);
            while (cursor <= range.end) {
                days.push(new Date(cursor));
                cursor = addDays(cursor, 1);
            }
            buildTimeGrid(days, false);
        } else {
            buildMonthGrid(range);
        }
    }

    function getCurrentRange() {
        if (currentView === 'day') {
            return { start: startOfDay(currentDate), end: startOfDay(currentDate) };
        }

        if (currentView === 'month') {
            const start = startOfMonth(currentDate);
            return { start, end: endOfMonth(currentDate) };
        }

        const start = startOfWeek(currentDate);
        return { start, end: addDays(start, 4) };
    }

    async function fetchCreneaux(range) {
        const params = buildQueryParams(range);
        const res = await fetch(`${BASE}/creneaux/get_all.php?${params}`);
        const data = await res.json();
        allCreneaux = Array.isArray(data) ? data : [];
    }

    function buildQueryParams(range) {
        const params = new URLSearchParams({
            date_debut: toISO(range.start),
            date_fin: toISO(range.end)
        });

        const groupe = document.getElementById('filterGroupe').value;
        const enseignant = document.getElementById('filterEnseignant').value;
        const salle = document.getElementById('filterSalle').value;
        if (groupe) params.set('groupe_id', groupe);
        if (enseignant) params.set('enseignant_id', enseignant);
        if (salle) params.set('salle_id', salle);
        return params;
    }

    function updatePeriodLabel(range) {
        const label = document.getElementById('periodLabel');
        if (currentView === 'day') {
            label.textContent = formatDateLong(range.start);
            return;
        }
        if (currentView === 'month') {
            label.textContent = range.start.toLocaleDateString('fr-MA', { month: 'long', year: 'numeric' });
            return;
        }
        label.textContent = `${formatDateFR(range.start)} - ${formatDateFR(range.end)}`;
    }

    function prevPeriod() {
        if (currentView === 'day') {
            currentDate = addDays(currentDate, -1);
        } else if (currentView === 'month') {
            currentDate = startOfMonth(addMonths(currentDate, -1));
        } else {
            currentDate = addDays(currentDate, -7);
        }
        renderCurrentView();
    }

    function nextPeriod() {
        if (currentView === 'day') {
            currentDate = addDays(currentDate, 1);
        } else if (currentView === 'month') {
            currentDate = startOfMonth(addMonths(currentDate, 1));
        } else {
            currentDate = addDays(currentDate, 7);
        }
        renderCurrentView();
    }

    function buildTimeGrid(days, singleDay) {
        const container = document.getElementById('calendarContainer');
        const todayIso = toISO(new Date());
        let html = `<div class="week-grid ${singleDay ? 'day-grid' : ''}">`;
        html += '<div class="wg-col-header"></div>';

        days.forEach((day, index) => {
            const iso = toISO(day);
            const title = singleDay ? formatDateLong(day) : `${DAYS[index]}<br><small>${formatDateFR(day)}</small>`;
            html += `<div class="wg-col-header ${iso === todayIso ? 'today' : ''}">${title}</div>`;
        });

        HOURS.forEach((hour) => {
            html += `<div class="wg-time-slot">${hour}</div>`;
            days.forEach((day) => {
                const iso = toISO(day);
                const creneaux = getCreneauxForSlot(iso, hour);
                let cellHtml = '';

                creneaux.forEach((creneau) => {
                    cellHtml += renderSlotCard(creneau);
                });

                if (canManagePlanning) {
                    cellHtml += `<button class="wg-inline-add" onclick="prefillAdd('${iso}', '${hour}')">+</button>`;
                }

                html += `<div class="wg-cell" data-date="${iso}" data-hour="${hour}">${cellHtml}</div>`;
            });
        });

        html += '</div>';
        container.innerHTML = html;
        if (canManagePlanning) {
            enableDragAndDrop();
        }
    }

    function buildMonthGrid(range) {
        const container = document.getElementById('calendarContainer');
        const monthStart = startOfMonth(currentDate);
        const monthEnd = endOfMonth(currentDate);
        const gridStart = startOfWeek(monthStart, true);
        const gridEnd = endOfWeek(monthEnd);
        const todayIso = toISO(new Date());

        let html = '<div class="month-grid">';
        MONTH_DAYS.forEach((dayLabel) => {
            html += `<div class="month-header">${dayLabel}</div>`;
        });

        let cursor = new Date(gridStart);
        while (cursor <= gridEnd) {
            const iso = toISO(cursor);
            const isCurrentMonth = cursor.getMonth() === currentDate.getMonth();
            const events = allCreneaux.filter((item) => item.date_cours === iso);
            html += `<div class="month-cell ${isCurrentMonth ? '' : 'is-other-month'} ${iso === todayIso ? 'is-today' : ''}">
                <div class="month-cell-head">
                    <span class="month-day-number">${cursor.getDate()}</span>
                    ${canManagePlanning ? `<button class="wg-inline-add month-add" onclick="prefillAdd('${iso}', '08:00')">+</button>` : ''}
                </div>
                <div class="month-events">`;
            events.slice(0, 4).forEach((event) => {
                html += `<button class="month-event type-${escapeHtml(event.type || 'cours')}" onclick="showDetail(${event.id})">
                    <span>${escapeHtml((event.heure_debut || '').substring(0, 5))}</span>
                    <strong>${escapeHtml(event.matiere_nom || 'Cours')}</strong>
                </button>`;
            });
            if (events.length > 4) {
                html += `<div class="month-more">+${events.length - 4} autres</div>`;
            }
            html += '</div></div>';
            cursor = addDays(cursor, 1);
        }

        html += '</div>';
        container.innerHTML = html;
    }

    function getCreneauxForSlot(dateIso, hour) {
        return allCreneaux.filter((item) => item.date_cours === dateIso && (item.heure_debut || '').substring(0, 5) === hour);
    }

    function renderSlotCard(creneau) {
        return `<div class="wg-creneau type-${escapeHtml(creneau.type || 'cours')}" data-id="${creneau.id}" onclick="showDetail(${creneau.id})">
            <div class="wg-title">${escapeHtml(creneau.matiere_nom || '')} <span class="badge badge-gray" style="font-size:.65rem">${escapeHtml((creneau.type || '').toUpperCase())}</span></div>
            <div class="wg-sub">${escapeHtml(creneau.enseignant_nom || '')}</div>
            <div class="wg-sub">${escapeHtml(creneau.salle_nom || '')} · ${escapeHtml(creneau.groupe_nom || '')}</div>
            <div class="wg-sub">${escapeHtml((creneau.heure_debut || '').substring(0, 5))} - ${escapeHtml((creneau.heure_fin || '').substring(0, 5))}</div>
        </div>`;
    }

    function enableDragAndDrop() {
        document.querySelectorAll('.wg-creneau').forEach((element) => {
            element.draggable = true;
            element.addEventListener('dragstart', (event) => {
                event.dataTransfer.setData('text/plain', element.dataset.id);
                event.dataTransfer.effectAllowed = 'move';
            });
        });

        document.querySelectorAll('.wg-cell').forEach((cell) => {
            cell.addEventListener('dragover', (event) => {
                event.preventDefault();
                cell.classList.add('is-drop-target');
            });
            cell.addEventListener('dragleave', () => cell.classList.remove('is-drop-target'));
            cell.addEventListener('drop', async (event) => {
                event.preventDefault();
                cell.classList.remove('is-drop-target');

                const id = event.dataTransfer.getData('text/plain');
                const creneau = allCreneaux.find((item) => String(item.id) === id);
                if (!creneau) return;

                const newDate = cell.dataset.date;
                const newHour = cell.dataset.hour;
                const duration = getDurationMinutes(creneau.heure_debut, creneau.heure_fin);
                const newEnd = addMinutesToTime(newHour, duration);

                const payload = {
                    id: creneau.id,
                    date_cours: newDate,
                    heure_debut: newHour,
                    heure_fin: newEnd,
                    matiere_id: creneau.matiere_id,
                    enseignant_id: creneau.enseignant_id,
                    salle_id: creneau.salle_id,
                    groupe_id: creneau.groupe_id,
                    type: creneau.type,
                    recurrent: creneau.recurrent || 0,
                    freq_recurrence: creneau.freq_recurrence,
                    date_fin_recurrence: creneau.date_fin_recurrence
                };

                const res = await fetch(`${BASE}/creneaux/update.php`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();
                if (data.conflits) {
                    showAlert(document.getElementById('alertContainer'), 'danger', data.message || 'Conflit détecté');
                    return;
                }
                showAlert(document.getElementById('alertContainer'), 'success', data.message || 'Créneau déplacé');
                renderCurrentView();
            });
        });
    }

    function prefillAdd(date, hour) {
        if (!canManagePlanning) return;
        document.getElementById('creneauId').value = '';
        document.getElementById('modalTitle').textContent = 'Nouveau créneau';
        document.getElementById('dateCours').value = date;
        document.getElementById('heureDebut').value = hour;
        document.getElementById('heureFin').value = addMinutesToTime(hour, 60);
        document.getElementById('typeCours').value = 'cours';
        document.getElementById('matiereId').value = '';
        document.getElementById('enseignantId').value = document.getElementById('filterEnseignant').value || '';
        document.getElementById('salleId').value = '';
        document.getElementById('groupeId').value = document.getElementById('filterGroupe').value || '';
        document.getElementById('recurrent').value = '0';
        document.getElementById('dateFinRecurrence').value = '';
        document.getElementById('conflictAlert').style.display = 'none';
        toggleRecurrence();
        openModal('creneauModal');
    }

    function showDetail(id) {
        const creneau = allCreneaux.find((item) => item.id == id);
        if (!creneau) return;

        const detail = document.getElementById('detailBody');
        detail.innerHTML = `
            <div style="display:flex;flex-direction:column;gap:.75rem;font-size:.9rem">
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Matière</span>
                    <strong>${escapeHtml(creneau.matiere_nom || '')}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Enseignant</span>
                    <strong>${escapeHtml(creneau.enseignant_nom || '')}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Salle</span>
                    <strong>${escapeHtml(creneau.salle_nom || '')}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Groupe</span>
                    <strong>${escapeHtml(creneau.groupe_nom || '')}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Date</span>
                    <strong>${escapeHtml(creneau.date_cours)}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--border)">
                    <span style="color:var(--text-secondary)">Horaire</span>
                    <strong>${escapeHtml((creneau.heure_debut || '').substring(0, 5))} - ${escapeHtml((creneau.heure_fin || '').substring(0, 5))}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;padding:.5rem 0">
                    <span style="color:var(--text-secondary)">Type</span>
                    ${typeBadge(creneau.type)}
                </div>
            </div>
            ${canManagePlanning ? `<div class="form-actions" style="margin-top:1rem">
                <button class="btn btn-outline" onclick="editCreneau(${creneau.id})"><i class='bx bx-edit'></i> Modifier</button>
                <button class="btn btn-danger" onclick="deleteCreneau(${creneau.id})"><i class='bx bx-trash'></i> Supprimer</button>
            </div>` : ''}`;
        openModal('detailModal');
    }

    function editCreneau(id) {
        if (!canManagePlanning) return;
        const creneau = allCreneaux.find((item) => item.id == id);
        if (!creneau) return;

        closeModal('detailModal');
        document.getElementById('creneauId').value = creneau.id;
        document.getElementById('modalTitle').textContent = 'Modifier le créneau';
        document.getElementById('dateCours').value = creneau.date_cours;
        document.getElementById('heureDebut').value = (creneau.heure_debut || '').substring(0, 5);
        document.getElementById('heureFin').value = (creneau.heure_fin || '').substring(0, 5);
        document.getElementById('typeCours').value = creneau.type || 'cours';
        document.getElementById('matiereId').value = creneau.matiere_id || '';
        document.getElementById('enseignantId').value = creneau.enseignant_id || '';
        document.getElementById('salleId').value = creneau.salle_id || '';
        document.getElementById('groupeId').value = creneau.groupe_id || '';
        document.getElementById('recurrent').value = String(creneau.recurrent || 0);
        document.getElementById('dateFinRecurrence').value = creneau.date_fin_recurrence || '';
        document.getElementById('conflictAlert').style.display = 'none';
        toggleRecurrence();
        openModal('creneauModal');
    }

    async function saveCreneau() {
        if (!canManagePlanning) return;

        const id = document.getElementById('creneauId').value;
        const payload = {
            date_cours: document.getElementById('dateCours').value,
            heure_debut: document.getElementById('heureDebut').value,
            heure_fin: document.getElementById('heureFin').value,
            matiere_id: document.getElementById('matiereId').value,
            enseignant_id: document.getElementById('enseignantId').value,
            salle_id: document.getElementById('salleId').value,
            groupe_id: document.getElementById('groupeId').value,
            type: document.getElementById('typeCours').value,
            recurrent: document.getElementById('recurrent').value,
            freq_recurrence: document.getElementById('recurrent').value === '1' ? 'hebdomadaire' : null,
            date_fin_recurrence: document.getElementById('dateFinRecurrence').value || null
        };

        document.getElementById('conflictAlert').style.display = 'none';
        if (id) payload.id = id;

        const endpoint = id ? `${BASE}/creneaux/update.php` : `${BASE}/creneaux/add.php`;
        const res = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.conflits) {
            document.getElementById('conflictMsg').textContent = 'Conflit détecté avec : ' + data.conflits.join(', ');
            document.getElementById('conflictAlert').style.display = 'flex';
            return;
        }

        closeModal('creneauModal');
        showAlert(document.getElementById('alertContainer'), data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        renderCurrentView();
    }

    async function deleteCreneau(id) {
        if (!canManagePlanning || !confirm('Supprimer ce créneau ?')) return;

        const res = await fetch(`${BASE}/creneaux/delete.php?id=${id}`);
        const data = await res.json();
        closeModal('detailModal');
        showAlert(document.getElementById('alertContainer'), data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        renderCurrentView();
    }

    function toggleRecurrence() {
        document.getElementById('recurrenceOptions').style.display = document.getElementById('recurrent').value === '1' ? 'block' : 'none';
    }

    function exportPlanning(type) {
        const range = getCurrentRange();
        const params = buildQueryParams(range);
        const url = type === 'xlsx' ? `${BASE}/exports/xlsx.php?${params}` : `${BASE}/exports/ical.php?${params}`;
        window.location.href = url;
    }

    async function duplicateSemester() {
        if (!canManagePlanning) return;

        const range = getCurrentRange();
        const sourceStart = prompt('Date de début source (YYYY-MM-DD)', toISO(range.start));
        if (!sourceStart) return;
        const sourceEnd = prompt('Date de fin source (YYYY-MM-DD)', toISO(range.end));
        if (!sourceEnd) return;
        const targetStart = prompt('Date de début cible (YYYY-MM-DD)', toISO(addDays(range.start, 180)));
        if (!targetStart) return;

        const res = await fetch(`${BASE}/creneaux/duplicate.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                source_start: sourceStart,
                source_end: sourceEnd,
                target_start: targetStart
            })
        });
        const data = await res.json();
        showAlert(document.getElementById('alertContainer'), data.message.includes('Erreur') ? 'danger' : 'success', data.message);
        renderCurrentView();
    }

    function startOfDay(date) {
        const copy = new Date(date);
        copy.setHours(0, 0, 0, 0);
        return copy;
    }

    function startOfWeek(date, includeWeekend = false) {
        const copy = startOfDay(date);
        const day = copy.getDay();
        const diff = copy.getDate() - day + (day === 0 ? -6 : 1);
        copy.setDate(diff);
        if (!includeWeekend) {
            copy.setHours(0, 0, 0, 0);
        }
        return copy;
    }

    function endOfWeek(date) {
        return addDays(startOfWeek(date, true), 6);
    }

    function startOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth(), 1);
    }

    function endOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth() + 1, 0);
    }

    function addDays(date, days) {
        const copy = new Date(date);
        copy.setDate(copy.getDate() + days);
        return copy;
    }

    function addMonths(date, months) {
        const copy = new Date(date);
        copy.setMonth(copy.getMonth() + months);
        return copy;
    }

    function addMinutesToTime(time, minutesToAdd) {
        const [hours, minutes] = time.split(':').map(Number);
        const total = hours * 60 + minutes + minutesToAdd;
        const newHours = String(Math.floor(total / 60)).padStart(2, '0');
        const newMinutes = String(total % 60).padStart(2, '0');
        return `${newHours}:${newMinutes}`;
    }

    function getDurationMinutes(start, end) {
        const [sh, sm] = (start || '08:00').substring(0, 5).split(':').map(Number);
        const [eh, em] = (end || '09:00').substring(0, 5).split(':').map(Number);
        return (eh * 60 + em) - (sh * 60 + sm);
    }

    function toISO(date) {
        const copy = new Date(date);
        copy.setMinutes(copy.getMinutes() - copy.getTimezoneOffset());
        return copy.toISOString().split('T')[0];
    }

    function formatDateFR(date) {
        return date.toLocaleDateString('fr-MA', { day: '2-digit', month: 'short' });
    }

    function formatDateLong(date) {
        return date.toLocaleDateString('fr-MA', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' });
    }

    async function duplicateSemester() {
        if (!confirm('Dupliquer tout l\'emploi du temps actuel pour le semestre suivant (+~6 mois) ?\n(Créneaux seront copiés avec dates shiftées)')) return;
        try {
            const r = await fetch(`${BASE}/creneaux/duplicate.php?offset=180`, { method: 'POST' });
            const d = await r.json();
            alert(d.message || 'Duplication effectuée');
            renderWeek();
        } catch (e) {
            alert('Erreur lors de la duplication');
        }
    }
</script>
