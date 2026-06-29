<?php
// require_once "middleware/auth_admin.php";
require_once "../include/db.php";

 $activePage = "tickets";

/* ===========================================
   STATISTIQUES
   =========================================== */

 $stmt = $conn->query("SELECT COUNT(*) FROM ticket");
 $total_tickets = $stmt->fetchColumn();

 $stmt = $conn->query("SELECT COUNT(*) FROM ticket WHERE statut = 'En attente'");
 $en_attente = $stmt->fetchColumn();

 $stmt = $conn->query("SELECT COUNT(*) FROM ticket WHERE statut = 'En cours'");
 $appeles = $stmt->fetchColumn();

 $stmt = $conn->query("
 SELECT COUNT(*)
 FROM ticket
 WHERE statut = 'Traité'
");
$termines = $stmt->fetchColumn();

/* ===========================================
   SERVICES (pour filtres + formulaire)
   =========================================== */

 $stmt = $conn->query("SELECT * FROM service ORDER BY id_service ASC");
 $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================================
   LISTE DES TICKETS
   =========================================== */

 $stmt = $conn->query("
    SELECT 
        t.*,
        u.nom_complet,
        s.nom_service
    FROM ticket t
    INNER JOIN usager u ON t.id_usager = u.id_usager
    INNER JOIN service s ON t.id_service = s.id_service
    ORDER BY t.id_ticket DESC
");
 $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================================
   ALERTES
   =========================================== */

 $alert = '';

if (isset($_GET['deleted'])) {
    $alert = '<div class="alert-success"><i class="fas fa-check-circle"></i> Ticket supprimé avec succès</div>';
}

if (isset($_GET['updated'])) {
    $alert = '<div class="alert-success"><i class="fas fa-check-circle"></i> Ticket modifié avec succès</div>';
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Gestion des Tickets</title>

    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/tickets.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="dashboard">

        <!-- SIDEBAR -->
        <?php include "includes/sidebar_admin.php"; ?>

        <div class="main-content">

            <!-- HEADER -->
            <?php
            $pageTitle = "Tickets";
            $pageSubtitle = "Suivi et administration des tickets";
            include "includes/header_admin.php";
            ?>

            <section class="content">

                <!-- ALERTES -->
                <?= $alert ?>

                <!-- PAGE TITRE -->
                <div class="page-header">

                    <div>
                        <h2>Gestion des Tickets</h2>
                        <p>Suivi et administration des tickets</p>
                    </div>

                    <div class="header-actions">
                        <button class="btn-export">
                            <i class="fas fa-file-export"></i>
                            Exporter
                        </button>
                        <button class="btn-add" id="openAddModal">
                            <i class="fas fa-plus"></i>
                            Nouveau Ticket
                        </button>
                    </div>

                </div>

                <!-- STATISTIQUES -->
                <div class="ticket-stats">

                    <div class="stat-card">
                        <h4>Total Tickets</h4>
                        <h2><?= $total_tickets ?></h2>
                    </div>

                    <div class="stat-card">
                        <h4>En attente</h4>
                        <h2><?= $en_attente ?></h2>
                    </div>

                    <div class="stat-card">
                        <h4>Appelés</h4>
                        <h2><?= $appeles ?></h2>
                    </div>

                    <div class="stat-card">
                        <h4>Terminés</h4>
                        <h2><?= $termines ?></h2>
                    </div>

                </div>

                <!-- FILTRES -->
                <div class="filters-card">

                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Rechercher un ticket...">
                    </div>

                    <select id="filterStatut">
                        <option value="">Tous les statuts</option>
                        <option value="En attente">En attente</option>
                        <option value="En cours">En cours</option>
                        <option value="Traité">Terminé</option>
                        <option value="Annulé">Annulé</option>
                    </select>

                    <select id="filterService">
                        <option value="">Tous les services</option>

                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id_service'] ?>">
                                <?= htmlspecialchars($service['nom_service']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <select id="filterPriorite">
                        <option value="">Toutes priorités</option>
                        <option value="Normale">Normale</option>
                        <option value="Urgence">Urgence</option>
                    </select>

                </div>

                <!-- TABLEAU -->
                <div class="tickets-table">

                    <table>

                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Usager</th>
                                <th>Service</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Statut</th>
                                <th>Priorité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php if (!empty($tickets)): ?>

                                <?php foreach ($tickets as $ticket): ?>

                                    <tr>

                                        <td>
                                            <strong><?= htmlspecialchars($ticket['numero_ticket']) ?></strong>
                                        </td>

                                        <td><?= htmlspecialchars($ticket['nom_complet']) ?></td>

                                        <td><?= htmlspecialchars($ticket['nom_service']) ?></td>

                                        <td><?= date('d/m/Y', strtotime($ticket['date_creation'])) ?></td>

                                        <td><?= htmlspecialchars($ticket['heure_creation']) ?></td>

                                        <td>

                                            <?php
                                            $statutClass = '';
                                            if ($ticket['statut'] == 'En attente') $statutClass = 'warning';
                                            if ($ticket['statut'] == 'En cours') $statutClass = 'primary';
                                            if ($ticket['statut'] == 'Traité') $statutClass = 'success';
                                            if ($ticket['statut'] == 'Annulé') $statutClass = 'danger';
                                            ?>

                                            <span class="badge <?= $statutClass ?>">
                                                <?= $ticket['statut'] ?>
                                            </span>

                                        </td>

                                        <td>

                                            <?php
                                            $prioClass = '';
                                            if ($ticket['priorite'] == 'Normale') $prioClass = 'success';
                                            if ($ticket['priorite'] == 'Urgence') $prioClass = 'danger';
                                            ?>

                                            <span class="badge <?= $prioClass ?>">
                                                <?= $ticket['priorite'] ?>
                                            </span>

                                        </td>

                                        <td class="actions">

                                            <button
                                                class="view-btn openViewModal"
                                                class="view-btn openViewModal"
                                                data-id="<?= $ticket['id_ticket'] ?>"
                                                data-numero="<?= htmlspecialchars($ticket['numero_ticket']) ?>"
                                                data-usager="<?= htmlspecialchars($ticket['nom_complet']) ?>"
                                                data-service="<?= htmlspecialchars($ticket['nom_service']) ?>"
                                                data-date="<?= date('d/m/Y', strtotime($ticket['date_creation'])) ?>"
                                                data-heure="<?= htmlspecialchars($ticket['heure_creation']) ?>"
                                                data-statut="<?= $ticket['statut'] ?>"
                                                data-statut-class="<?= $statutClass ?>"
                                                data-priorite="<?= $ticket['priorite'] ?>"
                                                data-priorite-class="<?= $prioClass ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            <button
                                                class="edit-btn openEditModal"
                                                data-id="<?= $ticket['id_ticket'] ?>"
                                                data-usager="<?= htmlspecialchars($ticket['nom_complet']) ?>"
                                                data-service="<?= $ticket['id_service'] ?>"
                                                data-priorite="<?= $ticket['priorite'] ?>"
                                                data-statut="<?= $ticket['statut'] ?>">
                                                <i class="fas fa-pen"></i>
                                            </button>

                                            <button
                                                class="delete-btn openDeleteModal"
                                                data-id="<?= $ticket['id_ticket'] ?>"
                                                data-numero="<?= htmlspecialchars($ticket['numero_ticket']) ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="8" class="empty-row">Aucun ticket trouvé</td>
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

    <!-- MODAL NOUVEAU TICKET -->
    <div class="modal-overlay" id="addTicketModal">

        <div class="modal-box">

            <div class="modal-header">
                <h2>Nouveau Ticket</h2>
                <span class="close-modal" id="closeAddModal">&times;</span>
            </div>

            <form method="POST" action="traitements/ticket/create_ticket.php">

<div class="form-group">
    <label>Nom de l'usager</label>
    <input type="text" name="nom_complet" required placeholder="Nom complet">
</div>

<!-- AJOUT DU TELEPHONE -->
<div class="form-group">
    <label>Téléphone</label>
    <input type="text" name="telephone" required placeholder="77 000 00 00">
</div>

<div class="form-group">
    <label>Numéro du ticket</label>
    <input type="text" value="Généré automatiquement" readonly>
</div>

<div class="form-group">
    <label>Service</label>
    <select name="id_service" required>
        <option value="">-- Choisir un service --</option>
        <?php foreach ($services as $service): ?>
            <option value="<?= $service['id_service'] ?>">
                <?= htmlspecialchars($service['nom_service']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<div class="form-group">
    <label>Priorité</label>
    <select name="priorite">
        <option value="Normale">Normale</option>
        <option value="Urgence">Urgence</option>
    </select>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea rows="4" name="description" placeholder="Optionnelle"></textarea>
</div>

<div class="modal-footer">
    <button type="submit" class="btn-save">
        <i class="fas fa-ticket-alt"></i>
        Enregistrer
    </button>
</div>

</form>

        </div>
    </div>
    <!-- MODAL VOIR TICKET -->
    <div class="modal-overlay" id="viewModal">

        <div class="modal-box view-modal-box">

            <div class="modal-header">
                <h2>Détails du Ticket</h2>
                <span class="close-modal close-view">&times;</span>
            </div>

            <div class="ticket-info">

                <div class="info-item">
                    <span>Numéro du ticket</span>
                    <strong id="viewNumber">-</strong>
                </div>

                <div class="info-item">
                    <span>Usager</span>
                    <strong id="viewUsager">-</strong>
                </div>

                <div class="info-item">
                    <span>Service</span>
                    <strong id="viewService">-</strong>
                </div>

                <div class="info-item">
                    <span>Date</span>
                    <strong id="viewDate">-</strong>
                </div>

                <div class="info-item">
                    <span>Heure</span>
                    <strong id="viewHeure">-</strong>
                </div>

                <div class="info-item">
                    <span>Statut</span>
                    <span class="badge" id="viewStatut">-</span>
                </div>

                <div class="info-item">
                    <span>Priorité</span>
                    <span class="badge" id="viewPriorite">-</span>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel close-view-btn">Fermer</button>
            </div>

        </div>

    </div>

    <!-- MODAL MODIFIER TICKET -->
    <div class="modal-overlay" id="editModal">

        <div class="modal-box">

            <div class="modal-header">
                <h2>Modifier Ticket</h2>
                <span class="close-modal close-edit">&times;</span>
            </div>

            <form method="POST" action="traitements/ticket/update_ticket.php">

                <input type="hidden" name="id_ticket" id="edit_id_ticket">

                <div class="form-group">
                    <label>Usager</label>
                    <input type="text" name="nom_complet" id="edit_usager" required>
                </div>

                <div class="form-group">
                    <label>Service</label>
                    <select name="id_service" id="edit_service" required>

                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id_service'] ?>">
                                <?= htmlspecialchars($service['nom_service']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <label>Priorité</label>
                    <select name="priorite" id="edit_priorite">
                        <option value="Normale">Normale</option>
                        <option value="Urgence">Urgence</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" id="edit_statut">
                        <option value="En attente">En attente</option>
                        <option value="En cours">En cours</option>
                        <option value="Traité">Terminé</option>
                        <option value="Annulé">Annulé</option>
                    </select>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn-save">Enregistrer</button>
                </div>

            </form>

        </div>

    </div>

    <!-- MODAL SUPPRIMER -->
    <div class="modal-overlay" id="deleteModal">

        <div class="modal-box">

            <div class="modal-header">
                <h2>Confirmation</h2>
                <span class="close-modal close-delete">&times;</span>
            </div>

            <form method="POST" action="traitements/ticket/delete_ticket.php">

                <input type="hidden" name="id_ticket" id="delete_id_ticket">

                <p id="deleteNumber"></p>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel cancel-delete-btn">Annuler</button>
                    <button type="submit" class="btn-danger">Supprimer</button>
                </div>

            </form>

        </div>

    </div>

    <script src="assets/js/includes.js"></script>
    <script src="assets/js/tickets.js"></script>

</body>

</html>