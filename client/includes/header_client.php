<header class="client-header">

    <div class="logo">
        <i class="fas fa-hospital"></i>
        <span>QueueFlow Hospital</span>
    </div>

    <nav>
        <!-- CORRECTION : Liens .html remplacés par .php -->
        <a href="accueil.php" class="<?= ($activePage ?? '') === 'accueil' ? 'active' : '' ?>">Accueil</a>
        <a href="prendre_ticket.php" class="<?= ($activePage ?? '') === 'prendre-ticket' ? 'active' : '' ?>">Prendre Ticket</a>
        <a href="suivi_ticket.php" class="<?= ($activePage ?? '') === 'suivi-ticket' ? 'active' : '' ?>">Suivi Ticket</a>
        <a href="contact.php" class="<?= ($activePage ?? '') === 'contact' ? 'active' : '' ?>">Contact</a>
        <a href="faq.php" class="<?= ($activePage ?? '') === 'faq' ? 'active' : '' ?>">FAQ</a>
    </nav>

</header>