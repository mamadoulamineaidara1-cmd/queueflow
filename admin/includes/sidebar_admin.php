<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$page = basename($_SERVER['PHP_SELF'], ".php");
?>

<aside class="sidebar">

    <div class="logo">
        <h2>QueueFlow</h2>
        <span>Gestion intelligente</span>
    </div>

    <ul class="menu">

        <li class="<?= $page == 'dashboard.php' ? 'active' : '' ?>">
            <a href="dashboard.php">
                <i class="fas fa-desktop"></i>
                Dashboard
            </a>
        </li>

        <li class="<?= $page == 'gestion_agents.php' ? 'active' : '' ?>">
            <a href="gestion_agents.php">
                <i class="fas fa-user-tie"></i>
                Agents
            </a>
        </li>

        <li class="<?= $page == 'gestion_services.php' ? 'active' : '' ?>">
            <a href="gestion_services.php">
                <i class="fas fa-building"></i>
                Services
            </a>
        </li>

        <li class="<?= $page == 'gestion_tickets.php' ? 'active' : '' ?>">
            <a href="gestion_tickets.php">
                <i class="fas fa-ticket"></i>
                Tickets
            </a>
        </li>

        <li class="<?= $page == 'rapports_statistiques.php' ? 'active' : '' ?>">
            <a href="rapports_statistiques.php">
            <i class="fas fa-chart-line"></i>
               Rapports et Statistique
            </a>
        </li>

        <li class="<?= $page == 'parametres.php' ? 'active' : '' ?>">
            <a href="parametres.php">
                <i class="fas fa-gear"></i>
                Paramètres
            </a>
        </li>

    </ul>

</aside>