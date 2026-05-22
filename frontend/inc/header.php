<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../layout/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="../layout/css/style.css">
    <link rel="stylesheet" href="../layout/css/responsive.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>EduPlanning — Gestion Emploi du Temps</title>
</head>
<body id="body-pd">

    <header class="header" id="header">
        <div class="header_left">
            <div class="header_toggle">
                <i class='bx bx-menu' id="header-toggle"></i>
            </div>
            <span class="header_title">EduPlanning</span>
        </div>
        <div class="header_right">
            <div class="notif_bell" id="notifBell">
                <i class='bx bx-bell'></i>
                <span class="notif_badge" id="notifBadge" style="display:none">0</span>
            </div>
            <div class="header_user" id="headerUser">
                <i class='bx bx-user-circle'></i>
                <span id="headerUserName">—</span>
            </div>
        </div>
    </header>

    <!-- Notifications dropdown -->
    <div class="notif_panel" id="notifPanel" style="display:none">
        <div class="notif_panel_header">
            <span>Notifications</span>
            <button onclick="markNotifRead()">Tout marquer lu</button>
        </div>
        <div id="notifList"><p class="notif_empty">Aucune notification</p></div>
    </div>

    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="../dashboard/index.php" class="nav_logo">
                    <i class='bx bx-calendar-check nav_logo_icon'></i>
                    <span class="nav_logo-name">EduPlanning</span>
                </a>
                <div class="nav_list">
                    <a href="../dashboard/index.php" class="nav_link">
                        <i class='bx bx-grid-alt nav_icon'></i>
                        <span class="nav_name">Tableau de bord</span>
                    </a>
                    <a href="../planning/index.php" class="nav_link">
                        <i class='bx bx-calendar nav_icon'></i>
                        <span class="nav_name">Emploi du Temps</span>
                    </a>
                    <a href="../ressources/salles.php" class="nav_link nav_admin_only">
                        <i class='bx bx-building nav_icon'></i>
                        <span class="nav_name">Salles</span>
                    </a>
                    <a href="../ressources/matieres.php" class="nav_link nav_admin_only">
                        <i class='bx bx-book nav_icon'></i>
                        <span class="nav_name">Matières</span>
                    </a>
                    <a href="../ressources/groupes.php" class="nav_link nav_admin_only">
                        <i class='bx bx-group nav_icon'></i>
                        <span class="nav_name">Groupes</span>
                    </a>
                    <a href="../ressources/utilisateurs.php" class="nav_link nav_admin_only">
                        <i class='bx bx-user nav_icon'></i>
                        <span class="nav_name">Utilisateurs</span>
                    </a>
                    <a href="../notifications/index.php" class="nav_link">
                        <i class='bx bx-bell nav_icon'></i>
                        <span class="nav_name">Notifications</span>
                    </a>
                </div>
            </div>
            <a id="urlLogInOut" href="../users/login.php" class="nav_link">
                <i id='iLogInOut' class="bx bx-log-in nav_icon"></i>
                <span id="spanLogInOut" class="nav_name">Connexion</span>
            </a>
        </nav>
    </div>

    <div class="main-content">
