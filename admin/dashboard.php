<?php
// sécurité admin
//  require_once "middleware/auth_admin.php";

// connexion base de données
require_once "../include/db.php";

// page active pour sidebar
 $activePage = "dashboard";

// date du jour
 $today = date("Y-m-d");

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

 $stmt = $conn->prepare("
    SELECT COUNT(*) FROM ticket WHERE date_creation = ?
");
 $stmt->execute([$today]);
 $tickets_today = $stmt->fetchColumn();

 $stmt = $conn->prepare("
    SELECT COUNT(*) FROM ticket 
    WHERE statut = 'Traité' AND date_creation = ?
");
 $stmt->execute([$today]);
 $tickets_servis = $stmt->fetchColumn();

 $stmt = $conn->prepare("
    SELECT COUNT(*) FROM ticket WHERE statut = 'En attente'
");
 $stmt->execute();
 $tickets_attente = $stmt->fetchColumn();

 $stmt = $conn->prepare("
    SELECT AVG(TIMESTAMPDIFF(MINUTE, heure_appel, heure_fin)) 
    FROM ticket 
    WHERE statut = 'Traité' 
    AND heure_appel IS NOT NULL 
    AND heure_fin IS NOT NULL
    AND date_creation = ?
");
 $stmt->execute([$today]);
 $temps_moyen = round($stmt->fetchColumn()) ?: 0;

 $stmt = $conn->prepare("
    SELECT COUNT(*) FROM ticket 
    WHERE statut IN ('En attente', 'En cours')
");
 $stmt->execute();
 $total_en_file = $stmt->fetchColumn();

 $stmt = $conn->prepare("
 SELECT COUNT(*) FROM ticket 
 WHERE statut = 'Annulé' AND date_creation = ?
");
$stmt->execute([$today]);
$tickets_abandonnes = $stmt->fetchColumn();

/* ===========================================
   SERVICES
   =========================================== */

 $stmt = $conn->prepare("SELECT * FROM service ORDER BY id_service ASC");
 $stmt->execute();
 $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

function countTicketsByService($conn, $id_service) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM ticket 
        WHERE id_service = ? AND statut IN ('En attente', 'En cours')
    ");
    $stmt->execute([$id_service]);
    return $stmt->fetchColumn();
}

/* ===========================================
   TICKETS EN TEMPS RÉEL (TABLEAU)
   =========================================== */

 $stmt = $conn->prepare("
    SELECT 
        t.numero_ticket,
        t.statut,
        t.heure_creation,
        u.nom_complet,
        s.nom_service
    FROM ticket t
    INNER JOIN usager u ON t.id_usager = u.id_usager
    INNER JOIN service s ON t.id_service = s.id_service
    ORDER BY t.id_ticket DESC
    LIMIT 10
");
 $stmt->execute();
 $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================================
   TICKET EN COURS PAR SERVICE (3 CARTES)
   =========================================== */

 $tickets_en_cours = [];

foreach ($services as $service) {
    $stmt = $conn->prepare("
        SELECT 
            t.numero_ticket,
            a.nom AS agent_nom,
            a.prenom AS agent_prenom
        FROM ticket t
        LEFT JOIN agent a ON t.id_agent = a.id_agent
        WHERE t.statut = 'En cours' 
        AND t.id_service = ?
        ORDER BY t.id_ticket DESC
        LIMIT 1
    ");
    $stmt->execute([$service['id_service']]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    $tickets_en_cours[] = [
        'service' => $service['nom_service'],
        'ticket'  => $ticket
    ];
}

/* ===========================================
   FILE D'ATTENTE PAR SERVICE (3 LISTES)
   =========================================== */

 $files_par_service = [];

foreach ($services as $service) {
    $stmt = $conn->prepare("
        SELECT 
            t.numero_ticket,
            t.temps_estime
        FROM ticket t
        WHERE t.statut = 'En attente' 
        AND t.id_service = ?
        ORDER BY t.id_ticket ASC
        LIMIT 5
    ");
    $stmt->execute([$service['id_service']]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $files_par_service[] = [
        'service' => $service['nom_service'],
        'tickets' => $items,
        'total'   => countTicketsByService($conn, $service['id_service'])
    ];
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Dashboard</title>

    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <?php include "includes/sidebar_admin.php"; ?>

    <div class="main-content">

        <!-- HEADER -->
        <?php include "includes/header_admin.php"; ?>

        <!-- CONTENU DASHBOARD -->
        <section class="content">

            <!-- CARDS STATISTIQUES GLOBALES -->
            <div class="cards">

                <div class="card">
                    <div class="card-info">
                        <h3>Tickets du jour</h3>
                        <h2><?= $tickets_today ?></h2>
                    </div>
                    <i class="fas fa-ticket-alt"></i>
                </div>

                <div class="card">
                    <div class="card-info">
                        <h3>Tickets servis</h3>
                        <h2><?= $tickets_servis ?></h2>
                    </div>
                    <i class="fas fa-check-circle"></i>
                </div>

                <div class="card">
                    <div class="card-info">
                        <h3>Temps moyen</h3>
                        <h2><?= $temps_moyen ?> min</h2>
                    </div>
                    <i class="fas fa-clock"></i>
                </div>

                <div class="card">
                    <div class="card-info">
                        <h3>En attente</h3>
                        <h2><?= $tickets_attente ?></h2>
                    </div>
                    <i class="fas fa-users"></i>
                </div>

                <div class="card">
                    <div class="card-info">
                        <h3>Abandonnés</h3>
                        <h2><?= $tickets_abandonnes ?></h2>
                    </div>
                    <i class="fas fa-user-times" style="color:#ef4444;"></i>
                </div>

            </div>

            <!-- SERVICES DISPONIBLES -->
            <div class="services-section">

                <div class="section-header">
                    <h2>Services disponibles</h2>
                    <p class="section-subtitle">Consultation (C) · Laboratoire (L) · Pédiatrie (P)</p>
                </div>

                <div class="services-cards">

                    <?php foreach ($services as $service): ?>

                        <div class="dash-service-card">

                            <div class="dash-service-icon <?= getServiceClass($service['nom_service']) ?>">
                                <i class="fas <?= getServiceIcon($service['nom_service']) ?>"></i>
                            </div>

                            <div class="dash-service-info">
                                <h3><?= htmlspecialchars($service['nom_service']) ?></h3>
                                <p><?= countTicketsByService($conn, $service['id_service']) ?> en file</p>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

            <!-- TABLEAU TICKETS -->
            <div class="dashboard-grid">

                <div class="ticket-table">

                    <div class="table-top">
                        <h2>Tickets en temps réel</h2>
                        <span class="queue-count"><?= $total_en_file ?> en file</span>
                    </div>

                    <table>
                        <thead>
                            <tr>
                                <th>Numéro</th>
                                <th>Usager</th>
                                <th>Service</th>
                                <th>Heure</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if (!empty($tickets)): ?>

                                <?php foreach ($tickets as $ticket): ?>

                                    <tr>
                                        <td><strong><?= htmlspecialchars($ticket['numero_ticket']) ?></strong></td>
                                        <td><?= htmlspecialchars($ticket['nom_complet']) ?></td>
                                        <td><?= htmlspecialchars($ticket['nom_service']) ?></td>
                                        <td><?= htmlspecialchars($ticket['heure_creation']) ?></td>
                                        <td>

                                            <?php if ($ticket['statut'] == 'En attente'): ?>
                                                <span class="badge warning">En attente</span>
                                            <?php elseif ($ticket['statut'] == 'En cours'): ?>
                                                <span class="badge primary">Appelé</span>
                                            <?php elseif ($ticket['statut'] == 'Traité'): ?>
                                                <span class="badge success">Terminé</span>
                                            <?php elseif ($ticket['statut'] == 'Annulé'): ?>
                                                <span class="badge danger">Abandonné</span>
                                            <?php endif; ?>

                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="5" class="empty-row">Aucun ticket disponible</td>
                                </tr>

                            <?php endif; ?>

                        </tbody>
                    </table>

                </div>

                <!-- PANNEAU DROIT -->
                <div class="right-panel">

                    <!-- TICKETS EN COURS PAR SERVICE -->
                    <div class="current-tickets-section">

                        <h3 class="panel-title">
                            <i class="fas fa-broadcast-tower"></i>
                            Tickets en cours
                        </h3>

                        <div class="current-tickets-grid">

                            <?php foreach ($tickets_en_cours as $item): ?>

                                <div class="current-ticket-mini">

                                    <div class="mini-header">
                                        <i class="fas <?= getServiceIcon($item['service']) ?>"></i>
                                        <span><?= htmlspecialchars($item['service']) ?></span>
                                    </div>

                                    <?php if (!empty($item['ticket'])): ?>

                                        <div class="mini-number" style="color: <?= getServiceColor($item['service']) ?>">
                                            <?= htmlspecialchars($item['ticket']['numero_ticket']) ?>
                                        </div>

                                        <div class="mini-agent">
                                            <?= htmlspecialchars(
                                                trim(
                                                    ($item['ticket']['agent_nom'] ?? '') . ' ' .
                                                    ($item['ticket']['agent_prenom'] ?? '')
                                                )
                                            ) ?: 'Non assigné' ?>
                                        </div>

                                    <?php else: ?>

                                        <div class="mini-number" style="color: #94a3b8">---</div>
                                        <div class="mini-agent">Aucun</div>

                                    <?php endif; ?>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                    <!-- FILES D'ATTENTE PAR SERVICE -->
                    <div class="queues-section">

                        <h3 class="panel-title">
                            <i class="fas fa-list-ol"></i>
                            Files d'attente
                        </h3>

                        <?php foreach ($files_par_service as $file): ?>

                            <div class="queue-service-block">

                                <div class="queue-service-header">

                                    <div class="queue-service-name">
                                        <i class="fas <?= getServiceIcon($file['service']) ?>"></i>
                                        <span><?= htmlspecialchars($file['service']) ?></span>
                                    </div>

                                    <span class="queue-count-badge"><?= $file['total'] ?></span>

                                </div>

                                <ul>

                                    <?php if (!empty($file['tickets'])): ?>

                                        <?php foreach ($file['tickets'] as $t): ?>

                                            <li>
                                                <div class="queue-ticket">
                                                    <strong><?= htmlspecialchars($t['numero_ticket']) ?></strong>
                                                </div>
                                                <div class="queue-time">
                                                    <?= $t['temps_estime'] ? $t['temps_estime'] . ' min' : '-- min' ?>
                                                </div>
                                            </li>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <li class="empty-queue">Aucun ticket</li>

                                    <?php endif; ?>

                                </ul>

                            </div>

                        <?php endforeach; ?>

                    </div>

                </div>

            </div>

        </section>

    </div>

</div>

<!-- PROFILE MODAL -->
<?php include "includes/profile_modal.php"; ?>
<script src="assets/js/includes.js"></script>
<script src="assets/js/dashboard.js"></script>

</body>
</html>