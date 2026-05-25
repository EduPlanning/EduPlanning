const BASE = "http://localhost/emploi_du_temps/backend/controllers";

function getUser() {
  return JSON.parse(localStorage.getItem("user") || "null");
}

function getUserId() {
  return getUser()?.id ?? null;
}

function getRole() {
  return getUser()?.role ?? null;
}

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#39;");
}

document.addEventListener("DOMContentLoaded", () => {
  const toggle = document.getElementById("header-toggle");
  const navbar = document.getElementById("nav-bar");
  const body = document.getElementById("body-pd");

  if (toggle && navbar) {
    toggle.addEventListener("click", () => {
      navbar.classList.toggle("show");
      body?.classList.toggle("body-pd");
    });
  }

  const links = document.querySelectorAll(".nav_link");
  links.forEach((link) => {
    if (link.href && window.location.href.includes(link.getAttribute("href"))) {
      link.classList.add("active");
    }
    link.addEventListener("click", function () {
      links.forEach((item) => item.classList.remove("active"));
      this.classList.add("active");
    });
  });

  const user = getUser();
  const userNameEl = document.getElementById("headerUserName");
  const logEl = document.getElementById("urlLogInOut");
  const iconEl = document.getElementById("iLogInOut");
  const spanEl = document.getElementById("spanLogInOut");

  if (!user) {
    if (userNameEl) userNameEl.textContent = "Invité";
    if (logEl) logEl.setAttribute("href", "../users/login.php");
    if (iconEl) iconEl.className = "bx bx-log-in nav_icon";
    if (spanEl) spanEl.textContent = "Connexion";
    document
      .querySelectorAll(".nav_admin_only, .nav_teacher_only")
      .forEach((el) => (el.style.display = "none"));
    return;
  }

  const roleNames = {
    administrateur: "Administrateur",
    enseignant: "Enseignant",
    etudiant: "Étudiant",
  };

  if (userNameEl) {
    userNameEl.textContent = `${user.prenom} ${user.nom} (${roleNames[user.role] || user.role})`;
  }
  if (logEl) logEl.setAttribute("href", "../users/logout.php");
  if (iconEl) iconEl.className = "bx bx-log-out nav_icon";
  if (spanEl) spanEl.textContent = "Déconnexion";

  if (user.role !== "administrateur") {
    document
      .querySelectorAll(".nav_admin_only")
      .forEach((el) => (el.style.display = "none"));
  }

  if (user.role !== "enseignant") {
    document
      .querySelectorAll(".nav_teacher_only")
      .forEach((el) => (el.style.display = "none"));
  }

  loadNotifBadge();

  const bell = document.getElementById("notifBell");
  const panel = document.getElementById("notifPanel");
  if (bell && panel) {
    bell.addEventListener("click", (event) => {
      event.stopPropagation();
      const isOpen = panel.style.display === "flex";
      panel.style.display = isOpen ? "none" : "flex";
      if (!isOpen) {
        panel.style.flexDirection = "column";
        loadNotifications();
      }
    });

    document.addEventListener("click", () => {
      panel.style.display = "none";
    });
    panel.addEventListener("click", (event) => event.stopPropagation());
  }
});

function loadNotifBadge() {
  if (!getUserId()) return;
  fetch(`${BASE}/notifications/index.php`)
    .then((response) => response.json())
    .then((data) => {
      const badge = document.getElementById("notifBadge");
      if (!badge) return;
      badge.textContent = data.non_lues || 0;
      badge.style.display = data.non_lues > 0 ? "flex" : "none";
    })
    .catch(() => {});
}

function loadNotifications() {
  if (!getUserId()) return;
  const list = document.getElementById("notifList");
  if (!list) return;
  list.innerHTML = '<p class="notif_empty">Chargement...</p>';

  fetch(`${BASE}/notifications/index.php`)
    .then((response) => response.json())
    .then((data) => {
      const badge = document.getElementById("notifBadge");
      if (badge) {
        badge.textContent = data.non_lues || 0;
        badge.style.display = data.non_lues > 0 ? "flex" : "none";
      }

      if (!data.notifications || data.notifications.length === 0) {
        list.innerHTML = '<p class="notif_empty">Aucune notification</p>';
        return;
      }

      list.innerHTML = "";
      data.notifications.forEach((notification) => {
        const div = document.createElement("div");
        div.className = "notif_item" + (notification.lu == 0 ? " unread" : "");
        div.innerHTML = `<div>${escapeHtml(notification.message)}</div>
          <div class="notif_item_time">${formatDate(notification.cree_le)}</div>`;
        list.appendChild(div);
      });
    })
    .catch(() => {
      list.innerHTML = '<p class="notif_empty">Impossible de charger les notifications.</p>';
    });
}

function markNotifRead() {
  if (!getUserId()) return;
  fetch(`${BASE}/notifications/index.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "mark_read" }),
  }).then(() => loadNotifications());
}

function formatDate(dateStr) {
  if (!dateStr) return "";
  const date = new Date(dateStr);
  return (
    date.toLocaleDateString("fr-MA", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    }) +
    " " +
    date.toLocaleTimeString("fr-MA", {
      hour: "2-digit",
      minute: "2-digit",
    })
  );
}

function showAlert(container, type, message) {
  const icons = {
    success: "bx-check-circle",
    danger: "bx-x-circle",
    warning: "bx-error",
    info: "bx-info-circle",
  };
  const alert = document.createElement("div");
  alert.className = `alert alert-${type}`;
  alert.innerHTML = `<i class="bx ${icons[type] || "bx-info-circle"}"></i><span>${escapeHtml(
    message
  )}</span>`;
  container.prepend(alert);
  setTimeout(() => alert.remove(), 4000);
}

function openModal(id) {
  document.getElementById(id)?.classList.add("active");
}

function closeModal(id) {
  document.getElementById(id)?.classList.remove("active");
}

document.addEventListener("click", (event) => {
  if (event.target.classList.contains("modal-overlay")) {
    event.target.classList.remove("active");
  }
});

function typeBadge(type) {
  const map = {
    cours: ["badge-blue", "Cours"],
    td: ["badge-green", "TD"],
    tp: ["badge-orange", "TP"],
    examen: ["badge-red", "Examen"],
  };
  const [cls, label] = map[type] || ["badge-gray", type];
  return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
}

function roleBadge(role) {
  const map = {
    administrateur: ["badge-red", "Admin"],
    enseignant: ["badge-blue", "Enseignant"],
    etudiant: ["badge-green", "Étudiant"],
  };
  const [cls, label] = map[role] || ["badge-gray", role];
  return `<span class="badge ${cls}">${escapeHtml(label)}</span>`;
}
