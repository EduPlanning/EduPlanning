<?php include('../inc/header.php') ?>

<div class="page-header">
    <div>
        <h1>Notifications</h1>
        <p>Dernières alertes et messages système</p>
    </div>
    <div>
        <button class="btn btn-outline" id="btnMarkRead"><i class='bx bx-check-circle'></i> Tout marquer lu</button>
    </div>
</div>

<div id="notifAlert"></div>
<div class="card">
    <div class="card-body" id="notifContainer">
        <p class="notif_empty">Chargement des notifications…</p>
    </div>
</div>

<?php include('../inc/footer.php') ?>

<script>
    const user = getUser();
    if (!user) {
        location.replace('../users/login.php');
    }

    async function loadNotificationsPage() {
        const container = document.getElementById('notifContainer');
        container.innerHTML = '<p class="notif_empty">Chargement des notifications…</p>';
        try {
            const res = await fetch(`${BASE}/notifications/index.php?user_id=${user.id}`);
            const data = await res.json();
            if (!data.notifications || data.notifications.length === 0) {
                container.innerHTML = '<p class="notif_empty">Aucune notification</p>';
                return;
            }
            container.innerHTML = '';
            data.notifications.forEach(n => {
                const div = document.createElement('div');
                div.className = 'notif_item' + (n.lu == 0 ? ' unread' : '');
                div.innerHTML = `
                <div>${n.message}</div>
                <div class="notif_item_time">${formatDate(n.cree_le)}</div>`;
                container.appendChild(div);
            });
        } catch (e) {
            container.innerHTML = '<p class="notif_empty">Impossible de charger les notifications.</p>';
        }
    }

    document.getElementById('btnMarkRead').addEventListener('click', async () => {
        try {
            await fetch(`${BASE}/notifications/index.php`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'mark_read',
                    user_id: user.id
                })
            });
            loadNotificationsPage();
        } catch (e) {
            console.warn(e);
        }
    });

    loadNotificationsPage();
</script>