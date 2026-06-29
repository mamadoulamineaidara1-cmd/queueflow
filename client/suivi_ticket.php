<?php
require_once "../include/db.php";

 $activePage = "suivi-ticket";

 $ticketData = null;
 $position = 0;
 $temps_estime = 0;
 $ticketAppel = null;
 $derniersAppels = [];
 $erreur = null;

// Traitement de la recherche
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $numero_recherche = strtoupper(trim($_POST['numero_ticket'] ?? ''));

    if (!empty($numero_recherche)) {

        // 1. Récupérer le ticket cherché
        $stmt = $conn->prepare("
            SELECT t.id_ticket, t.numero_ticket, t.statut, t.id_service, s.nom_service
            FROM ticket t
            INNER JOIN service s ON t.id_service = s.id_service
            WHERE t.numero_ticket = ? AND t.date_creation = CURDATE()
        ");
        $stmt->execute([$numero_recherche]);
        $ticketData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ticketData) {
            $id_service = $ticketData['id_service'];

            // 2. Si le ticket est "En attente", on calcule sa position DANS SON SERVICE
            if ($ticketData['statut'] === 'En attente') {
                $stmt = $conn->prepare("
                    SELECT COUNT(*) 
                    FROM ticket 
                    WHERE id_service = ? AND statut = 'En attente' 
                    AND date_creation = CURDATE() AND id_ticket < ?
                ");
                $stmt->execute([$id_service, $ticketData['id_ticket']]);
                $position = $stmt->fetchColumn();
                $temps_estime = $position * 5; // 5 min par personne
            }

            // 3. Récupérer le ticket actuel appelé POUR CE SERVICE UNIQUEMENT
            $stmt = $conn->prepare("
                SELECT numero_ticket 
                FROM ticket 
                WHERE id_service = ? AND statut = 'En cours' 
                LIMIT 1
            ");
            $stmt->execute([$id_service]);
            $ticketAppel = $stmt->fetch(PDO::FETCH_ASSOC);

            // 4. Récupérer les derniers tickets appelés/traités POUR CE SERVICE UNIQUEMENT
            $stmt = $conn->prepare("
                SELECT numero_ticket, statut 
                FROM ticket 
                WHERE id_service = ? AND statut IN ('En cours', 'Traité') 
                AND date_creation = CURDATE() 
                ORDER BY id_ticket DESC 
                LIMIT 4
            ");
            $stmt->execute([$id_service]);
            $derniersAppels = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } else {
            $erreur = "Aucun ticket trouvé avec ce numéro pour aujourd'hui.";
        }
    } else {
        $erreur = "Veuillez entrer un numéro de ticket.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow Hospital - Suivi Ticket</title>

    <link rel="stylesheet" href="assets/css/layout_client.css">
    <link rel="stylesheet" href="assets/css/suivi_ticket.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <!-- ========== HEADER ========== -->
    <?php include 'includes/header_client.php'; ?>

    <!-- ========== CONTENU ========== -->
    <main class="tracking-container">

        <div class="tracking-header">
            <h1>Suivi du Ticket</h1>
            <p>Consultez votre position en temps réel dans la file d'attente</p>
        </div>

        <!-- FORMULAIRE DE RECHERCHE -->
        <div class="ticket-tracking-card" style="margin-bottom: 25px;">
            <form method="POST" style="display:flex; gap:10px;">
                <input type="text" name="numero_ticket" placeholder="Ex: C002" required style="flex:1; padding:12px; border:1px solid #e2e8f0; border-radius:10px; font-size:16px;">
                <button type="submit" class="btn-generate" style="padding:12px 25px; background:#2563eb; color:white; border:none; border-radius:10px; cursor:pointer; font-weight:600;">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </form>
        </div>

        <!-- AFFICHAGE ERREUR -->
        <?php if ($erreur): ?>
            <div style="background:#fee2e2; color:#991b1b; padding:15px; border-radius:10px; text-align:center; font-weight:500;">
                <?= $erreur ?>
            </div>
        <?php endif; ?>

        <!-- AFFICHAGE RÉSULTAT -->
        <?php if ($ticketData): ?>

            <div class="ticket-tracking-card">

                <div class="ticket-badge">Ticket <?= htmlspecialchars($ticketData['numero_ticket']) ?></div>

                <div class="tracking-grid">

                    <div class="tracking-item">
                        <span>Position actuelle</span>
                        <!-- S'il est en cours ou traité, la position est 0 -->
                        <h2><?= $ticketData['statut'] === 'En attente' ? $position : '---' ?></h2>
                    </div>

                    <div class="tracking-item">
                        <span>Temps estimé</span>
                        <h2><?= $ticketData['statut'] === 'En attente' ? $temps_estime . ' min' : '---' ?></h2>
                    </div>

                    <div class="tracking-item">
                        <span>Service</span>
                        <h2><?= htmlspecialchars($ticketData['nom_service']) ?></h2>
                    </div>

                    <div class="tracking-item">
                        <span>Statut</span>
                        <!-- Classe CSS dynamique selon le statut -->
                        <?php 
                            $statusClass = 'waiting-status'; 
                            if($ticketData['statut'] === 'En cours') $statusClass = 'active-status';
                            if($ticketData['statut'] === 'Traité') $statusClass = 'done-status';
                        ?>
                        <h2 class="<?= $statusClass ?>"><?= htmlspecialchars($ticketData['statut']) ?></h2>
                    </div>

                </div>

            </div>

            <?php if ($ticketData['statut'] === 'En attente'): ?>
                <div class="progress-card">
                    <h3>Progression dans la file</h3>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?= ($position > 0) ? max(5, 100 - ($position * 10)) : 100 ?>%;"></div>
                    </div>
                    <p><?= $position ?> personne<?= $position > 1 ? 's' : '' ?> avant vous</p>
                </div>
            <?php endif; ?>

            <?php if ($ticketAppel): ?>
                <div class="current-ticket-card">
                    <h3>Ticket actuellement appelé</h3>
                    <div class="current-ticket"><?= htmlspecialchars($ticketAppel['numero_ticket']) ?></div>
                    <span><?= htmlspecialchars($ticketData['nom_service']) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($derniersAppels)): ?>
                <div class="history-calls">
                    <h3>Derniers Tickets Appelés</h3>
                    <div class="calls-list">
                        <?php foreach ($derniersAppels as $appel): ?>
                            <div class="call-item <?= $appel['statut'] === 'En cours' ? 'active' : '' ?>">
                                <?= htmlspecialchars($appel['numero_ticket']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </main>

    <!-- ========== FOOTER ========== -->
    <?php include 'includes/footer_client.php'; ?>

    <script src="assets/js/suivi_ticket.js"></script>

</body>
</html>