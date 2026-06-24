<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<aside class="sidebar">

    <div class="logo">
        <i class="fas fa-layer-group"></i>
        <span>QueueFlow</span>
    </div>

    <ul class="menu">

        <li class="<?= ($activePage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <a href="dashboard_agent.php">
                <i class="fas fa-home"></i>
                Dashboard
            </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'file_attente' ? 'active' : '' ?>">
            <a href="file_attente_detaille.php">
                <i class="fas fa-users"></i>
                File d'attente
            </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'historique' ? 'active' : '' ?>">
            <a href="historique_ticket.php">
                <i class="fas fa-history"></i>
                Historique
            </a>
        </li>

        <li class="<?= ($activePage ?? '') === 'profil' ? 'active' : '' ?>">
            <a href="profil_agent.php">
                <i class="fas fa-user"></i>
                Profil
            </a>
        </li>

    </ul>

</aside>