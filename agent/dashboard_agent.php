<?php
session_start();

// Sécurité : si pas connecté ou pas agent
if (!isset($_SESSION['type']) || $_SESSION['type'] !== "agent") {
    header("Location: ../auth/login.php");
    exit();
}

// Sécurité : si les variables de session n'existent pas, on redirige vers le login
 $id_agent = $_SESSION['id_agent'] ?? null;
 $id_service = $_SESSION['id_service'] ?? null;

if (!$id_agent || !$id_service) {
    // Détruire la vieille session et renvoyer au login
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

// Affichage des messages
 $success = $_SESSION['success_agent'] ?? null;
 $error = $_SESSION['error_agent'] ?? null;
unset($_SESSION['success_agent'], $_SESSION['error_agent']);

require_once "../include/db.php";

 $activePage = "dashboard";
 $pageTitle = "Dashboard";
 $pageSubtitle = "Vue d'ensemble de votre activité";

// ===== 1. Ticket en cours de cet agent =====
 $stmt = $conn->prepare("
    SELECT
        t.id_ticket,
        t.numero_ticket,
        t.priorite,
        t.heure_appel,
        t.nb_appels,
        u.nom_complet,
        s.nom_service
    FROM ticket t
    INNER JOIN usager u ON t.id_usager = u.id_usager
    INNER JOIN service s ON t.id_service = s.id_service
    WHERE t.id_agent = ? AND t.statut = 'En cours'
    LIMIT 1
");
 $stmt->execute([$id_agent]);
 $ticketEnCours = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== 2. Nombre de tickets en attente pour le service =====
 $stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM ticket
    WHERE id_service = ? AND statut = 'En attente'
");
 $stmt->execute([$id_service]);
 $enAttente = $stmt->fetchColumn();

// ===== 3. Tickets traités aujourd'hui par cet agent =====
 $stmt = $conn->prepare("
    SELECT COUNT(*)
    FROM ticket
    WHERE id_agent = ? AND statut = 'Traité' AND date_creation = CURDATE()
");
 $stmt->execute([$id_agent]);
 $traitesAujourdhui = $stmt->fetchColumn();

// ===== 4. File d'attente du service =====
 $stmt = $conn->prepare("
    SELECT t.numero_ticket, t.priorite, u.nom_complet
    FROM ticket t
    INNER JOIN usager u ON t.id_usager = u.id_usager
    WHERE t.id_service = ? AND t.statut IN ('En attente', 'En cours')
    ORDER BY t.id_ticket ASC
");
 $stmt->execute([$id_service]);
 $fileAttente = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Agent Dashboard</title>

    <!-- CSS partagé admin -->
    <link rel="stylesheet" href="../admin/assets/css/dashboard.css">
    <link rel="stylesheet" href="../admin/assets/css/components.css">

    <!-- CSS agent -->
    <link rel="stylesheet" href="assets/css/layout_agent.css">
    <link rel="stylesheet" href="assets/css/dashboard_agent.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="dashboard">

        <!-- ========== SIDEBAR ========== -->
        <?php include "includes/sidebar_agent.php"; ?>

        <!-- ========== CONTENU ========== -->
        <div class="main-content">

            <!-- ========== HEADER ========== -->
            <?php include "includes/header_agent.php"; ?>

            <!-- ========== CONTENU DASHBOARD ========== -->
            <section class="content">

                <?php if ($success): ?>
                    <div style="background:#dcfce7;color:#166534;padding:15px 20px;border-radius:10px;margin-bottom:20px;font-weight:500;">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div style="background:#fee2e2;color:#991b1b;padding:15px 20px;border-radius:10px;margin-bottom:20px;font-weight:500;">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- STATS -->
                <div class="agent-stats">

                    <div class="agent-stat-card">
                        <div class="agent-stat-icon"><i class="fas fa-ticket-alt"></i></div>
                        <div>
                            <h3><?= $ticketEnCours ? htmlspecialchars($ticketEnCours['numero_ticket']) : '---' ?></h3>
                            <p>Ticket en cours</p>
                        </div>
                    </div>

                    <div class="agent-stat-card">
                        <div class="agent-stat-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <h3><?= $enAttente ?></h3>
                            <p>En attente</p>
                        </div>
                    </div>

                    <div class="agent-stat-card">
                        <div class="agent-stat-icon"><i class="fas fa-check-circle"></i></div>
                        <div>
                            <h3><?= $traitesAujourdhui ?></h3>
                            <p>Traités aujourd'hui</p>
                        </div>
                    </div>

                </div>

                <!-- TICKET EN COURS -->
                <div class="current-ticket-card">

                    <div class="card-header">
                        <h3>Ticket actuellement appelé</h3>
                        <?php if ($ticketEnCours): ?>
                            <span class="badge success">En service</span>
                        <?php else: ?>
                            <span class="badge" style="background:#f1f5f9;color:#64748b;">Aucun ticket</span>
                        <?php endif; ?>
                    </div>

                    <?php if ($ticketEnCours): ?>

                        <div class="ticket-display"><?= htmlspecialchars($ticketEnCours['numero_ticket']) ?></div>

                        <div class="ticket-user"><?= htmlspecialchars($ticketEnCours['nom_complet']) ?></div>

                        <div class="ticket-service"><?= htmlspecialchars($ticketEnCours['nom_service']) ?></div>

                        <div class="ticket-actions">
                            <!-- BOUTON ABSENT -->
                            <form method="POST" action="traitements/absent_ticket.php">
                                <input type="hidden" name="id_ticket" value="<?= $ticketEnCours['id_ticket'] ?>">
                                <!-- CORRECTION : min() pour ne jamais dépasser 3/3 -->
                                <button type="submit" class="btn-absent">
                                    <i class="fas fa-user-slash"></i> 
                                    Absent (<?= min($ticketEnCours['nb_appels'] + 1, 3) ?>/3)
                                </button>
                            </form>

                            <!-- BOUTON TERMINER -->
                            <form method="POST" action="terminer_ticket.php">
                                <input type="hidden" name="id_ticket" value="<?= $ticketEnCours['id_ticket'] ?>">
                                <button type="submit" class="btn-finish"><i class="fas fa-check"></i> Terminer</button>
                            </form>
                        </div>

                    <?php else: ?>

                        <div class="ticket-display" style="color:#94a3b8;font-size:50px;">---</div>

                        <div class="ticket-user" style="color:#94a3b8;">Aucun ticket en cours</div>

                        <div class="ticket-service" style="color:#94a3b8;">Cliquez sur Appeler suivant</div>

                        <div class="ticket-actions">
                            <form method="POST" action="appel_ticket.php">
                                <button type="submit" class="btn-next"><i class="fas fa-forward"></i> Appeler suivant</button>
                            </form>
                        </div>

                    <?php endif; ?>

                </div>

                <!-- FILE D'ATTENTE -->
                <div class="queue-card">

                    <div class="card-header">
                        <h3>File d'attente</h3>
                        <span class="queue-count"><?= $enAttente ?> personne<?= $enAttente > 1 ? 's' : '' ?></span>
                    </div>

                    <div class="queue-list">

                        <?php if (empty($fileAttente)): ?>

                            <div style="text-align:center;padding:40px 0;color:#94a3b8;">
                                <i class="fas fa-inbox" style="font-size:36px;margin-bottom:12px;display:block;"></i>
                                Aucun ticket en attente
                            </div>

                        <?php else: ?>

                            <?php foreach ($fileAttente as $ticket): ?>

                                <div class="queue-item">
                                    <div>
                                        <strong><?= htmlspecialchars($ticket['numero_ticket']) ?></strong>
                                        <p><?= htmlspecialchars($ticket['nom_complet']) ?></p>
                                    </div>
                                    <span class="wait-time <?= $ticket['priorite'] === 'Urgence' ? 'urgent' : '' ?>">
                                        <?= $ticket['priorite'] ?>
                                    </span>
                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>

                </div>

            </section>

        </div>
    </div>

    <!-- ========== MODAL PROFIL ========== -->
    <?php include "includes/profile_modal_agent.php"; ?>

    <script src="../admin/assets/js/dashboard.js"></script>
    <script src="assets/js/dashboard_agent.js"></script>

</body>
</html>