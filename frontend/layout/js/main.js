// ============================================================
// main.js — EduPlanning global script
// ============================================================

const BASE = "http://localhost/emploi_du_temps/backend/controllers";

// Auth helpers
function getUser() {
  return JSON.parse(localStorage.getItem("user") || "null");
}
function getUserId() {
  const u = getUser();
  return u ? u.id : null;
}
function getRole() {
  const u = getUser();
  return u ? u.role : null;
}

// ---------- Sidebar toggle ----------
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

  // Active nav link
  const links = document.querySelectorAll(".nav_link");
  links.forEach((l) => {
    if (l.href && window.location.href.includes(l.getAttribute("href"))) {
      l.classList.add("active");
    }
    l.addEventListener("click", function () {
      links.forEach((x) => x.classList.remove("active"));
      this.classList.add("active");
    });
  });

  // Auth state UI
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
    // Hide admin-only links
    document
      .querySelectorAll(".nav_admin_only")
      .forEach((el) => (el.style.display = "none"));
  } else {
    const roleNames = {
      administrateur: "Administrateur",
      enseignant: "Enseignant",
      etudiant: "Étudiant",
    };
    if (userNameEl)
      userNameEl.textContent = `${user.prenom} ${user.nom} (${roleNames[user.role] || user.role})`;
    if (logEl) logEl.setAttribute("href", "../users/logout.php");
    if (iconEl) iconEl.className = "bx bx-log-out nav_icon";
    if (spanEl) spanEl.textContent = "Déconnexion";
    // Hide admin-only links for non-admins
    if (user.role !== "administrateur") {
      document
        .querySelectorAll(".nav_admin_only")
        .forEach((el) => (el.style.display = "none"));
    }
    // Load notifications badge
    loadNotifBadge();
  }

  // Notif panel toggle
  const bell = document.getElementById("notifBell");
  const panel = document.getElementById("notifPanel");
  if (bell && panel) {
    bell.addEventListener("click", (e) => {
      e.stopPropagation();
      const open = panel.style.display !== "none" && panel.style.display !== "";
      if (open) {
        panel.style.display = "none";
      } else {
        panel.style.display = "flex";
        panel.style.flexDirection = "column";
        loadNotifications();
      }
    });
    document.addEventListener("click", () => {
      panel.style.display = "none";
    });
    panel.addEventListener("click", (e) => e.stopPropagation());
  }
});

// ---------- Notifications ----------
function loadNotifBadge() {
  const uid = getUserId();
  if (!uid) return;
  fetch(`${BASE}/notifications/index.php?user_id=${uid}`)
    .then((r) => r.json())
    .then((data) => {
      const badge = document.getElementById("notifBadge");
      if (badge && data.non_lues > 0) {
        badge.textContent = data.non_lues;
        badge.style.display = "flex";
      }
    })
    .catch(() => {});
}

function loadNotifications() {
  const uid = getUserId();
  if (!uid) return;
  const list = document.getElementById("notifList");
  if (!list) return;
  list.innerHTML = '<p class="notif_empty">Chargement…</p>';
  fetch(`${BASE}/notifications/index.php?user_id=${uid}`)
    .then((r) => r.json())
    .then((data) => {
      const badge = document.getElementById("notifBadge");
      if (badge) {
        badge.textContent = data.non_lues;
        badge.style.display = data.non_lues > 0 ? "flex" : "none";
      }
      if (!data.notifications || data.notifications.length === 0) {
        list.innerHTML = '<p class="notif_empty">Aucune notification</p>';
        return;
      }
      list.innerHTML = "";
      data.notifications.forEach((n) => {
        const div = document.createElement("div");
        div.className = "notif_item" + (n.lu == 0 ? " unread" : "");
        div.innerHTML = `<div>${n.message}</div>
                    <div class="notif_item_time">${formatDate(n.cree_le)}</div>`;
        list.appendChild(div);
      });
    });
}

function markNotifRead() {
  const uid = getUserId();
  if (!uid) return;
  fetch(`${BASE}/notifications/index.php`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "mark_read", user_id: uid }),
  }).then(() => loadNotifications());
}

// ---------- Utilities ----------
function formatDate(dateStr) {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return (
    d.toLocaleDateString("fr-MA", {
      day: "2-digit",
      month: "short",
      year: "numeric",
    }) +
    " " +
    d.toLocaleTimeString("fr-MA", { hour: "2-digit", minute: "2-digit" })
  );
}

function showAlert(container, type, msg) {
  const icons = {
    success: "bx-check-circle",
    danger: "bx-x-circle",
    warning: "bx-error",
    info: "bx-info-circle",
  };
  const el = document.createElement("div");
  el.className = `alert alert-${type}`;
  el.innerHTML = `<i class="bx ${icons[type] || "bx-info-circle"}"></i><span>${msg}</span>`;
  container.prepend(el);
  setTimeout(() => el.remove(), 4000);
}

function openModal(id) {
  document.getElementById(id)?.classList.add("active");
}
function closeModal(id) {
  document.getElementById(id)?.classList.remove("active");
}

// Close modal on overlay click
document.addEventListener("click", (e) => {
  if (e.target.classList.contains("modal-overlay")) {
    e.target.classList.remove("active");
  }
});

// ---------- Type badge helper ----------
function typeBadge(type) {
  const map = {
    cours: ["badge-blue", "Cours"],
    td: ["badge-green", "TD"],
    tp: ["badge-orange", "TP"],
    examen: ["badge-red", "Examen"],
  };
  const [cls, label] = map[type] || ["badge-gray", type];
  return `<span class="badge ${cls}">${label}</span>`;
}

function roleBadge(role) {
  const map = {
    administrateur: ["badge-red", "Admin"],
    enseignant: ["badge-blue", "Enseignant"],
    etudiant: ["badge-green", "Étudiant"],
  };
  const [cls, label] = map[role] || ["badge-gray", role];
  return `<span class="badge ${cls}">${label}</span>`;
}
