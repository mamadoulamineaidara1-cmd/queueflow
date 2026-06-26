<?php
session_start();

// Sécurité : si pas connecté ou pas agent
if (!isset($_SESSION['type']) || $_SESSION['type'] !== "agent") {
    header("Location: ../auth/login.php");
    exit();
}

 $id_agent = $_SESSION['id_agent'] ?? null;
 $id_service = $_SESSION['id_service'] ?? null;

if (!$id_agent || !$id_service) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

require_once "../include/db.php";

 $activePage = "file_attente";
 $pageTitle = "File d'attente";
 $pageSubtitle = "Gestion des tickets en attente";

// ===== 1. Stats : En attente =====
 $stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_service = ? AND statut = 'En attente'
");
 $stmt->execute([$id_service]);
 $enAttente = $stmt->fetchColumn();

// ===== 2. Stats : Urgences en attente =====
 $stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_service = ? AND statut = 'En attente' AND priorite = 'Urgence'
");
 $stmt->execute([$id_service]);
 $urgences = $stmt->fetchColumn();

// ===== 3. Stats : En cours =====
 $stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_service = ? AND statut = 'En cours'
");
 $stmt->execute([$id_service]);
 $enCours = $stmt->fetchColumn();

// ===== 4. Liste complète des tickets du service =====
 $stmt = $conn->prepare("
    SELECT 
        t.numero_ticket, 
        t.priorite, 
        t.statut, 
        t.heure_creation,
        u.nom_complet
    FROM ticket t
    INNER JOIN usager u ON t.id_usager = u.id_usager
    WHERE t.id_service = ?
    ORDER BY 
        CASE 
            WHEN t.statut = 'En cours' THEN 1 
            WHEN t.priorite = 'Urgence' THEN 2 
            ELSE 3 
        END, 
        t.id_ticket ASC
");
 $stmt->execute([$id_service]);
 $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - File d'attente</title>

    <!-- CSS partagé admin -->
    <link rel="stylesheet" href="../admin/assets/css/dashboard.css">
    <link rel="stylesheet" href="../admin/assets/css/components.css">

    <!-- CSS agent -->
    <link rel="stylesheet" href="assets/css/layout_agent.css">
    <link rel="stylesheet" href="assets/css/file_attente_detaille.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="dashboard">

        <!-- ========== SIDEBAR ========== -->
        <?php include 'includes/sidebar_agent.php'; ?>

        <!-- ========== CONTENU ========== -->
        <div class="main-content">

            <!-- ========== HEADER ========== -->
            <?php include 'includes/header_agent.php'; ?>

            <!-- ========== CONTENU FILE D'ATTENTE ========== -->
            <section class="content">

                <div class="header-ligne">

                    <div class="queue-header">
                        <div>
                            <h2>File d'Attente</h2>
                            <p>Gestion des tickets de votre service</p>
                        </div>
                    </div>

                    <div class="live-indicator">
                        <span class="live-dot"></span>
                        Mise à jour en temps réel
                    </div>

                </div>

                <div class="queue-stats">

                    <div class="queue-stat-card">
                        <div class="queue-stat-icon"><i class="fas fa-users"></i></div>
                        <div>
                            <h3><?= $enAttente ?></h3>
                            <p>En attente</p>
                        </div>
                    </div>

                    <div class="queue-stat-card">
                        <div class="queue-stat-icon" style="color:#dc2626;background:#fee2e2;"><i class="fas fa-exclamation-circle"></i></div>
                        <div>
                            <h3><?= $urgences ?></h3>
                            <p>Urgences</p>
                        </div>
                    </div>

                    <div class="queue-stat-card">
                        <div class="queue-stat-icon" style="color:#2563eb;background:#eff6ff;"><i class="fas fa-bullhorn"></i></div>
                        <div>
                            <h3><?= $enCours ?></h3>
                            <p>En cours</p>
                        </div>
                    </div>

                </div>

                <div class="queue-list-container">

                    <?php if (empty($tickets)): ?>

                        <div style="text-align:center;padding:60px 0;color:#94a3b8;">
                            <i class="fas fa-inbox" style="font-size:48px;margin-bottom:15px;display:block;"></i>
                            <h3 style="color:#64748b;margin-bottom:5px;">Aucun ticket</h3>
                            <p>Aucun ticket n'a été créé pour votre service aujourd'hui.</p>
                        </div>

                    <?php else: ?>

                        <?php foreach ($tickets as $ticket): ?>

                            <?php 
                                // Classe CSS selon la priorité et le statut
                                $rowClass = "";
                                if ($ticket['statut'] === 'En cours') {
                                    $rowClass = "active-ticket";
                                } elseif ($ticket['priorite'] === 'Urgence') {
                                    $rowClass = "priority-ticket";
                                }
                            ?>

                            <div class="ticket-row <?= $rowClass ?>">

                                <div class="ticket-left">
                                    <div class="ticket-number"><?= htmlspecialchars($ticket['numero_ticket']) ?></div>
                                    <div>
                                        <h4><?= htmlspecialchars($ticket['nom_complet']) ?></h4>
                                        <p>Heure : <?= htmlspecialchars($ticket['heure_creation']) ?></p>
                                    </div>
                                </div>

                                <div class="ticket-right">
                                    
                                    <?php if ($ticket['priorite'] === 'Urgence'): ?>
                                        <span class="badge danger">Urgence</span>
                                    <?php else: ?>
                                        <span class="waiting-badge">Normale</span>
                                    <?php endif; ?>

                                    <?php if ($ticket['statut'] === 'En cours'): ?>
                                        <span class="badge success">En cours</span>
                                    <?php elseif ($ticket['statut'] === 'Traité'): ?>
                                        <span class="waiting-badge" style="background:#dcfce7;color:#166534;">Traité</span>
                                    <?php else: ?>
                                        <span class="waiting-badge"><?= $ticket['statut'] ?></span>
                                    <?php endif; ?>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>

            </section>

        </div>

    </div>

    <!-- ========== MODAL PROFIL ========== -->
    <?php include 'includes/profile_modal_agent.php'; ?>

    <script src="../admin/assets/js/dashboard.js"></script>
    <script src="assets/js/file_attente.js"></script>

</body>

</html>