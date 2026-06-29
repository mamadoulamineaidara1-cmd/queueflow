<?php
require_once "../include/db.php";

 $activePage = "services";

// STATISTIQUES
 $stmt = $conn->prepare("SELECT COUNT(*) FROM service WHERE statut = 'Actif'");
 $stmt->execute();
 $services_actifs = $stmt->fetchColumn();

 $today = date("Y-m-d");

 $stmt = $conn->prepare("SELECT COUNT(*) FROM ticket WHERE date_creation = ?");
 $stmt->execute([$today]);
 $tickets_today = $stmt->fetchColumn();

// LISTE DES SERVICES
 $stmt = $conn->prepare("SELECT * FROM service ORDER BY id_service ASC");
 $stmt->execute();
 $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

function countTicketsByService($conn, $id_service) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) FROM ticket 
        WHERE id_service = ? AND date_creation = CURDATE()
    ");
    $stmt->execute([$id_service]);
    return $stmt->fetchColumn();
}

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

function getServiceBorderColor($nom) {
    $colors = [
        'Consultation' => '#2563eb',
        'Laboratoire'  => '#7c3aed',
        'Pédiatrie'    => '#d97706'
    ];
    return $colors[$nom] ?? '#2563eb';
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Gestion des Services</title>

    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/services.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="dashboard">

        <!-- SIDEBAR -->
        <?php include "includes/sidebar_admin.php"; ?>

        <div class="main-content">

            <!-- HEADER -->
            <?php 
            $pageTitle = "Services";
            $pageSubtitle = "Administration des services disponibles";
            include "includes/header_admin.php"; 
            ?>

            <section class="content">

                <!-- ALERTES -->
                <?php if(isset($_GET['error']) && $_GET['error'] == 'service_utilise'): ?>
                    <div class="alert-danger">
                        <i class="fas fa-exclamation-triangle"></i>
                        Impossible de supprimer ce service car il contient des tickets.
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['success']) && $_GET['success'] == 'service_supprime'): ?>
                    <div class="alert-success">
                        <i class="fas fa-check-circle"></i>
                        Service supprimé avec succès.
                    </div>
                <?php endif; ?>

                <div class="services-header">
                    <div>
                        <h2>Gestion des Services</h2>
                        <p>Consultation (C) · Laboratoire (L) · Pédiatrie (P)</p>
                    </div>
                    <button class="btn-add-service" id="openAddService">
                        <i class="fas fa-plus"></i>
                        Nouveau Service
                    </button>
                </div>

                <div class="service-stats">
                    <div class="service-stat">
                        <h2><?= $services_actifs ?></h2>
                        <span>Services actifs</span>
                    </div>
                    <div class="service-stat">
                        <h2><?= $tickets_today ?></h2>
                        <span>Tickets aujourd'hui</span>
                    </div>
                </div>

                <div class="services-grid">

                    <?php foreach ($services as $service): ?>

                        <div class="service-card" style="border-top-color: <?= getServiceBorderColor($service['nom_service']) ?>">

                            <div class="service-top">
                                <div class="service-icon <?= getServiceClass($service['nom_service']) ?>">
                                    <i class="fas <?= getServiceIcon($service['nom_service']) ?>"></i>
                                </div>
                                <span class="badge <?= $service['statut'] == 'Actif' ? 'success' : 'danger' ?>">
                                    <?= $service['statut'] ?>
                                </span>
                            </div>

                            <h3><?= htmlspecialchars($service['nom_service']) ?></h3>

                            <p class="service-description">
                                <?= htmlspecialchars($service['description']) ?>
                            </p>

                            <p>
                                <strong><?= countTicketsByService($conn, $service['id_service']) ?></strong> tickets aujourd'hui
                            </p>

                            <div class="service-actions">
                                <button
                                    class="edit-service openEditService"
                                    data-id="<?= $service['id_service'] ?>"
                                    data-nom="<?= htmlspecialchars($service['nom_service']) ?>"
                                    data-description="<?= htmlspecialchars($service['description']) ?>"
                                    data-statut="<?= $service['statut'] ?>">
                                    Modifier
                                </button>
                                <button
                                    class="delete-service openDeleteService"
                                    data-id="<?= $service['id_service'] ?>">
                                    Supprimer
                                </button>
                            </div>

                        </div>

                    <?php endforeach; ?>

                </div>

            </section>

        </div>

    </div>

    <!-- MODAL PROFIL -->
    <?php include "includes/profile_modal.php"; ?>

    <!-- MODAL AJOUTER SERVICE -->
    <div class="modal-overlay" id="addServiceModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Nouveau Service</h2>
                <span class="close-modal" id="closeAddService">&times;</span>
            </div>
            <form action="traitements/service/add_service.php" method="POST">
                <div class="form-group">
                    <label>Nom du service</label>
                    <input type="text" name="nom_service" placeholder="Ex: Consultation" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Description du service"></textarea>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut">
                        <option value="Actif">Actif</option>
                        <option value="Inactif">Inactif</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelAddService">Annuler</button>
                    <button type="submit" class="btn-save">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL MODIFIER SERVICE -->
    <div class="modal-overlay" id="editServiceModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Modifier Service</h2>
                <span class="close-modal closeEditService">&times;</span>
            </div>
            <form action="traitements/service/update_service.php" method="POST">
                <input type="hidden" name="id_service" id="edit_id_service">
                <div class="form-group">
                    <label>Nom du service</label>
                    <input type="text" name="nom_service" id="edit_nom_service" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea rows="4" name="description" id="edit_description"></textarea>
                </div>
                <div class="form-group">
                    <label>Statut</label>
                    <select name="statut" id="edit_statut">
                        <option value="Actif">Actif</option>
                        <option value="Inactif">Inactif</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn-save">Enregistrer</button>
                    <button type="button" class="btn-cancel closeEditServiceBtn">Annuler</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL SUPPRIMER SERVICE -->
    <div class="modal-overlay" id="deleteServiceModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Supprimer Service</h2>
                <span class="close-modal closeDeleteService">&times;</span>
            </div>
            <form action="traitements/service/delete_service.php" method="POST">
                <input type="hidden" name="id_service" id="delete_id_service">
                <p>Êtes-vous sûr de vouloir supprimer ce service ?</p>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel cancelDeleteService">Annuler</button>
                    <button type="submit" class="btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/includes.js"></script>
    <script src="assets/js/services.js"></script>

</body>
</html>