<?php
require_once "../include/db.php";

$activePage = "prendre-ticket";

$id_ticket = $_GET['id_ticket'] ?? null;

if (!$id_ticket) {
    header("Location: accueil.php");
    exit();
}

// ===== Récupérer les infos du ticket =====
$stmt = $conn->prepare("
    SELECT t.numero_ticket, t.heure_creation, t.id_service,
           u.nom_complet,
           s.nom_service
    FROM ticket t
    INNER JOIN usager u ON t.id_usager = u.id_usager
    INNER JOIN service s ON t.id_service = s.id_service
    WHERE t.id_ticket = ?
");
$stmt->execute([$id_ticket]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    header("Location: accueil.php");
    exit();
}

// ===== Calculer la position =====
$stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_service = ? 
    AND statut = 'En attente' 
    AND date_creation = CURDATE()
    AND id_ticket < ?
");
$stmt->execute([$ticket['id_service'], $id_ticket]);
$position = $stmt->fetchColumn() + 1;

// ===== Temps estimé =====
$temps_estime_min = $position * 5;

// ===== Formatage date et heure (format "15 Mai 2026" comme sur la maquette) =====
$mois_fr = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];
$date_actuelle  = date('j') . ' ' . $mois_fr[(int) date('n')] . ' ' . date('Y');
$heure_actuelle = date('H:i');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow Hospital - Votre Ticket</title>

    <link rel="stylesheet" href="assets/css/layout_client.css">
    <link rel="stylesheet" href="assets/css/ticket_thermique.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include 'includes/header_client.php'; ?>

    <div class="ticket-wrapper">
        <div class="ticket-paper">

            <!-- BOUTON FERMER (X) -->
            <a href="accueil.php" class="ticket-close-btn" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </a>

            <!-- EN-TÊTE -->
            <div class="ticket-top">
                <div class="ticket-logo">
                    <i class="fas fa-hospital"></i>
                </div>
                <div class="ticket-top-text">
                    <h2>QueueFlow Hospital</h2>
                </div>
            </div>

            <div class="ticket-inner">

                <!-- SUCCÈS -->
                <div class="ticket-success-msg">
                    <i class="fas fa-check-circle"></i>
                    <h3>Ticket Généré avec succès</h3>
                </div>

                <!-- NUMÉRO DU TICKET -->
                <div class="ticket-big-number"><?= htmlspecialchars($ticket['numero_ticket']) ?></div>

                <!-- USAGER / SERVICE -->
                <div class="ticket-info-row">
                    <div class="ticket-info-block">
                        <span>Usager</span>
                        <strong><?= htmlspecialchars($ticket['nom_complet']) ?></strong>
                    </div>
                    <div class="ticket-info-block">
                        <span>Service</span>
                        <strong><?= htmlspecialchars($ticket['nom_service']) ?></strong>
                    </div>
                </div>

                <!-- PAIEMENT -->
                <div class="ticket-paid-stamp">
                    <i class="fas fa-check-circle"></i>
                    <span>Payé : 1 000 FCFA</span>
                </div>

                <hr class="ticket-divider">

                <!-- DÉTAILS -->
                <div class="ticket-details-row">
                    <div class="ticket-detail-block">
                        <span>Position</span>
                        <strong><?= $position ?></strong>
                    </div>
                    <div class="ticket-detail-block">
                        <span>Temps estimé</span>
                        <strong><?= $temps_estime_min ?> min</strong>
                    </div>
                </div>

                <hr class="ticket-divider">

                <!-- DATE ET HEURE -->
                <div class="ticket-meta">
                    <p>Date : <?= $date_actuelle ?></p>
                    <p>Heure : <?= $heure_actuelle ?></p>
                </div>

                <!-- BOUTON IMPRIMER -->
                <button class="print-ticket" onclick="window.print()">
                    <i class="fas fa-print"></i>
                    Imprimer le ticket
                </button>

            </div>
        </div>
    </div>

    <!-- FOOTER masqué à l'impression -->
    <?php include 'includes/footer_client.php'; ?>

</body>
</html>