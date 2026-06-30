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

 $activePage = "historique";
 $pageTitle = "Historique";
 $pageSubtitle = "Historique des tickets traités";

// CORRECTION : Récupérer l'agent depuis la base au lieu de $_SESSION['user']
 $stmt = $conn->prepare("SELECT nom, prenom FROM agent WHERE id_agent = ?");
 $stmt->execute([$id_agent]);
 $agent = $stmt->fetch(PDO::FETCH_ASSOC);

// Sécurité : si l'agent a été supprimé de la base par un admin
if (!$agent) {
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

// Récupérer les initiales de l'agent
 $agentInitiales = ucfirst(substr($agent['prenom'], 0, 1)) . ". " . ucfirst(substr($agent['nom'], 0, 1)) . ".";

// ===== 1. Stats : Traités aujourd'hui =====
 $stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM historique_ticket 
    WHERE id_agent = ? AND DATE(heure_fin) = CURDATE()
");
// ... LE RESTE DU CODE HTML RESTE IDENTIQUE ...
 $stmt->execute([$id_agent]);
 $totalAujourdhui = $stmt->fetchColumn();

// ===== 2. Stats : Traités cette semaine (Lundi au Dimanche) =====
 $stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM historique_ticket 
    WHERE id_agent = ? AND YEARWEEK(heure_fin, 1) = YEARWEEK(CURDATE(), 1)
");
 $stmt->execute([$id_agent]);
 $totalSemaine = $stmt->fetchColumn();

// ===== 3. Stats : Total traité (depuis le début) =====
 $stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM historique_ticket 
    WHERE id_agent = ?
");
 $stmt->execute([$id_agent]);
 $totalTraite = $stmt->fetchColumn();

// ===== 4. Liste de l'historique =====
 $stmt = $conn->prepare("
    SELECT 
        t.numero_ticket, 
        u.nom_complet, 
        s.nom_service,
        h.heure_debut, 
        h.heure_fin, 
        h.duree_traitement
    FROM historique_ticket h
    INNER JOIN ticket t ON h.id_ticket = t.id_ticket
    INNER JOIN usager u ON t.id_usager = u.id_usager
    INNER JOIN service s ON t.id_service = s.id_service
    WHERE h.id_agent = ?
    ORDER BY h.id_historique DESC
");
 $stmt->execute([$id_agent]);
 $historique = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Historique</title>

    <!-- CSS partagé admin -->
    <link rel="stylesheet" href="../admin/assets/css/dashboard.css">
    <link rel="stylesheet" href="../admin/assets/css/components.css">

    <!-- CSS agent -->
    <link rel="stylesheet" href="assets/css/layout_agent.css">
    <link rel="stylesheet" href="assets/css/historique_ticket.css">

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

            <!-- ========== CONTENU HISTORIQUE ========== -->
            <section class="content">

                <div class="history-stats">

                    <div class="history-card">
                        <div class="history-icon"><i class="fas fa-calendar-day"></i></div>
                        <div class="history-info">
                            <h3><?= $totalAujourdhui ?></h3>
                            <p>Aujourd'hui</p>
                        </div>
                    </div>

                    <div class="history-card">
                        <div class="history-icon"><i class="fas fa-calendar-week"></i></div>
                        <div class="history-info">
                            <h3><?= $totalSemaine ?></h3>
                            <p>Cette semaine</p>
                        </div>
                    </div>

                    <div class="history-card">
                        <div class="history-icon"><i class="fas fa-check-circle"></i></div>
                        <div class="history-info">
                            <h3><?= $totalTraite ?></h3>
                            <p>Total traité</p>
                        </div>
                    </div>

                </div>

                <div class="history-search">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Rechercher un ticket ou un usager">
                </div>

                <?php if (empty($historique)): ?>

                    <div style="text-align:center;padding:60px 0;color:#94a3b8;background:white;border-radius:20px;">
                        <i class="fas fa-clipboard-check" style="font-size:48px;margin-bottom:15px;display:block;"></i>
                        <h3 style="color:#64748b;margin-bottom:5px;">Aucun historique</h3>
                        <p>Vous n'avez encore traité aucun ticket.</p>
                    </div>

                <?php else: ?>

                    <?php foreach ($historique as $row): 

                        // Calcul de la durée
                        $minutes = floor($row['duree_traitement'] / 60);
                        $secondes = $row['duree_traitement'] % 60;
                        
                        if ($minutes > 0) {
                            $dureeFormat = $minutes . " min";
                        } else {
                            $dureeFormat = $secondes . " sec";
                        }
                    ?>

                        <div class="history-ticket">

                            <div class="history-top">
                                <div class="ticket-user-info">
                                    <div class="history-number"><?= htmlspecialchars($row['numero_ticket']) ?></div>
                                    <div>
                                        <h4><?= htmlspecialchars($row['nom_complet']) ?></h4>
                                        <p>Service <?= htmlspecialchars($row['nom_service']) ?></p>
                                    </div>
                                </div>
                                <span class="badge success">Traité</span>
                            </div>

                            <div class="history-details">
                                <div class="detail-box">
                                    <span>Heure</span>
                                    <strong><?= htmlspecialchars($row['heure_debut']) . " - " . htmlspecialchars($row['heure_fin']) ?></strong>
                                </div>
                                <div class="detail-box">
                                    <span>Durée</span>
                                    <strong><?= $dureeFormat ?></strong>
                                </div>
                                <div class="detail-box">
                                    <span>Agent</span>
                                    <strong><?= htmlspecialchars($agentInitiales) ?></strong>
                                </div>
                            </div>

                        </div>

                    <?php endforeach; ?>

                <?php endif; ?>

            </section>

        </div>

    </div>

    <!-- ========== MODAL PROFIL ========== -->
    <?php include 'includes/profile_modal_agent.php'; ?>

    <script src="../admin/assets/js/dashboard.js"></script>
    <script src="assets/js/historique.js"></script>

</body>

</html>