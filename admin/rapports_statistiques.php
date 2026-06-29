<?php
require_once "../include/db.php";

 $activePage = "rapports";

/* ===========================================
   FONCTIONS HELPER
   =========================================== */

function getServiceIcon($nom) {
    $icons = [
        'Consultation' => 'fa-stethoscope',
        'Laboratoire'  => 'fa-flask',
        'Pédiatrie'    => 'fa-baby'
    ];
    return $icons[$nom] ?? 'fa-building';
}

function getServiceClass($nom) {
    $classes = [
        'Consultation' => 'consultation',
        'Laboratoire'  => 'laboratoire',
        'Pédiatrie'    => 'pediatrie'
    ];
    return $classes[$nom] ?? '';
}

function getServiceColor($nom) {
    $colors = [
        'Consultation' => '#2563eb',
        'Laboratoire'  => '#7c3aed',
        'Pédiatrie'    => '#d97706'
    ];
    return $colors[$nom] ?? '#2563eb';
}

/* ===========================================
   STATISTIQUES GLOBALES
   =========================================== */

// 1. Total des tickets créés
 $stmt = $conn->query("SELECT COUNT(*) FROM ticket");
 $totalTickets = $stmt->fetchColumn();

// 2. Tickets traités
 $stmt = $conn->query("SELECT COUNT(*) FROM ticket WHERE statut = 'Traité'");
 $ticketsTraites = $stmt->fetchColumn();

// 3. Temps moyen de traitement (en minutes)
 $stmt = $conn->query("
    SELECT AVG(TIMESTAMPDIFF(MINUTE, heure_appel, heure_fin)) AS moy
    FROM ticket
    WHERE statut = 'Traité'
      AND heure_appel IS NOT NULL
      AND heure_fin IS NOT NULL
");
 $moyenne = $stmt->fetch(PDO::FETCH_ASSOC)['moy'];
 $tempsMoyen = $moyenne !== null ? round($moyenne) . " min" : "N/A";

// 4. Agents actifs
 $stmt = $conn->query("SELECT COUNT(*) FROM agent WHERE statut = 'Actif'");
 $agentsActifs = $stmt->fetchColumn();

// 5. Taux de traitement
 $tauxTraitement = $totalTickets > 0 ? round(($ticketsTraites / $totalTickets) * 100) : 0;

// 6. Tickets par service
 $stmt = $conn->query("
    SELECT s.nom_service, COUNT(t.id_ticket) AS total
    FROM service s
    LEFT JOIN ticket t ON t.id_service = s.id_service
    GROUP BY s.id_service
    ORDER BY total DESC
");
 $statsServices = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 7. Performance des agents (sans colonne étoiles)
 $stmt = $conn->query("
    SELECT
        CONCAT(a.prenom, ' ', a.nom) AS nom_complet,
        s.nom_service,
        COUNT(t.id_ticket) AS nb_tickets
    FROM agent a
    INNER JOIN service s ON a.id_service = s.id_service
    LEFT JOIN ticket t ON a.id_agent = t.id_agent
    GROUP BY a.id_agent
    ORDER BY nb_tickets DESC
");
 $perfAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Pour les barres : trouver le max
 $maxTicketsService = 0;
foreach ($statsServices as $row) {
    if ($row['total'] > $maxTicketsService) {
        $maxTicketsService = $row['total'];
    }
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Rapports & Statistiques</title>

    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/rapports_statistique.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <?php include "includes/sidebar_admin.php"; ?>

    <div class="main-content">

        <!-- HEADER -->
        <?php
        $pageTitle = "Rapports";
        $pageSubtitle = "Analyse des performances du système";
        include "includes/header_admin.php";
        ?>

        <section class="content">

            <!-- En-tête rapports -->
            <div class="report-header">
                <div>
                    <h2>Rapports & Statistiques</h2>
                    <p>Analyse des performances du système</p>
                </div>
                <a href="export_pdf.php" class="btn-export-pdf">
                    <i class="fas fa-download"></i>
                    Exporter PDF
                </a>
            </div>

            <!-- ────── CARTES STATISTIQUES ────── -->
            <div class="stats-grid">

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-ticket-alt"></i></div>
                    <h3><?= number_format($totalTickets, 0, ' ', ' ') ?></h3>
                    <p>Tickets créés</p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    <h3><?= number_format($ticketsTraites, 0, ' ', ' ') ?></h3>
                    <p>Tickets traités</p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <h3><?= $tempsMoyen ?></h3>
                    <p>Temps moyen</p>
                </div>

                <div class="stat-card">
                    <div class="stat-icon"><i class="fas fa-user-tie"></i></div>
                    <h3><?= $agentsActifs ?></h3>
                    <p>Agents actifs</p>
                </div>

            </div>

            <!-- ────── GRAPHIQUES ────── -->
            <div class="charts-grid">

                <div class="chart-card">

                    <h3>Tickets par service</h3>

                    <div class="chart-container">
                        <canvas id="serviceChart"></canvas>
                    </div>

                </div>

                <div class="chart-card">

                    <h3>Etat des tickets</h3>

                    <div class="chart-container">
                        <canvas id="etatChart"></canvas>
                    </div>

                </div>

            </div>

            <!-- ────── TABLEAU PERFORMANCE AGENTS ────── -->
            <div class="report-table-card">

                <h3>Performance des Agents</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Agent</th>
                            <th>Service</th>
                            <th>Tickets traités</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($perfAgents) > 0): ?>
                            <?php foreach ($perfAgents as $agent): ?>
                                <tr>
                                    <td><?= htmlspecialchars($agent['nom_complet']) ?></td>
                                    <td>
                                        <i class="fas <?= getServiceIcon($agent['nom_service']) ?>"></i>
                                        <?= htmlspecialchars($agent['nom_service']) ?>
                                    </td>
                                    <td><strong><?= $agent['nb_tickets'] ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="empty-state">Aucune donnée disponible</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>

        </section>

    </div>

</div>

<!-- MODAL PROFIL -->
<?php include "includes/profile_modal.php"; ?>

<script>

const serviceLabels = [
<?php foreach($statsServices as $row): ?>
"<?= $row['nom_service'] ?>",
<?php endforeach; ?>
];

const serviceData = [
<?php foreach($statsServices as $row): ?>
<?= $row['total'] ?>,
<?php endforeach; ?>
];

const ticketEtatLabels = [
"Traités",
"En attente",
"En cours",
"Annulés"
];

const ticketEtatData = [
<?php
$stmt=$conn->query("SELECT COUNT(*) FROM ticket WHERE statut='Traité'");
echo $stmt->fetchColumn();
?>,

<?php
$stmt=$conn->query("SELECT COUNT(*) FROM ticket WHERE statut='En attente'");
echo $stmt->fetchColumn();
?>,

<?php
$stmt=$conn->query("SELECT COUNT(*) FROM ticket WHERE statut='En cours'");
echo $stmt->fetchColumn();
?>,

<?php
$stmt=$conn->query("SELECT COUNT(*) FROM ticket WHERE statut='Annulé'");
echo $stmt->fetchColumn();
?>
];

</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="assets/js/rapport.js"></script>
<script src="assets/js/includes.js"></script>

</body>
</html>