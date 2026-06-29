<?php
// sécurité admin
// require_once "middleware/auth_admin.php";

require_once "../include/db.php";

 $activePage = "agents";

/* ===========================================
   FONCTIONS HELPER
   =========================================== */

function getServiceColor($nom) {
    $colors = [
        'Consultation' => '#2563eb',
        'Laboratoire'  => '#7c3aed',
        'Pédiatrie'    => '#d97706'
    ];
    return $colors[$nom] ?? '#2563eb';
}

function getServiceClass($nom) {
    $classes = [
        'Consultation' => 'consultation',
        'Laboratoire'  => 'laboratoire',
        'Pédiatrie'    => 'pediatrie'
    ];
    return $classes[$nom] ?? '';
}

function getServiceIcon($nom) {
    $icons = [
        'Consultation' => 'fa-stethoscope',
        'Laboratoire'  => 'fa-flask',
        'Pédiatrie'    => 'fa-baby'
    ];
    return $icons[$nom] ?? 'fa-building';
}

/* ===========================================
   STATISTIQUES
   =========================================== */

 $stmt = $conn->query("SELECT COUNT(*) FROM agent");
 $total_agents = $stmt->fetchColumn();

 $stmt = $conn->prepare("SELECT COUNT(*) FROM agent WHERE statut = 'Actif'");
 $stmt->execute();
 $agents_actifs = $stmt->fetchColumn();

 $stmt = $conn->prepare("SELECT COUNT(*) FROM agent WHERE statut = 'Inactif'");
 $stmt->execute();
 $agents_inactifs = $stmt->fetchColumn();

/* ===========================================
   SERVICES (pour les select des formulaires)
   =========================================== */

 $stmt = $conn->query("SELECT * FROM service ORDER BY id_service ASC");
 $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================================
   LISTE DES AGENTS
   =========================================== */

 $stmt = $conn->query("
    SELECT a.*, s.nom_service
    FROM agent a
    INNER JOIN service s ON a.id_service = s.id_service
    ORDER BY a.id_agent DESC
");
 $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ===========================================
   ALERTES
   =========================================== */

 $alert = '';

if (isset($_GET['success'])) {
    $alert = '<div class="alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_GET['success']) . '</div>';
}

if (isset($_GET['error'])) {
    $alert = '<div class="alert-danger"><i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($_GET['error']) . '</div>';
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Gestion des Agents</title>

    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/agents.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body>

    <div class="dashboard">

        <!-- SIDEBAR -->
        <?php include "includes/sidebar_admin.php"; ?>

        <div class="main-content">

            <!-- HEADER -->
            <?php
            $pageTitle = "Agents";
            $pageSubtitle = "Administration des comptes agents";
            include "includes/header_admin.php";
            ?>

            <section class="content">

                <!-- ALERTES -->
                <?= $alert ?>

                <!-- HEADER PAGE -->
                <div class="agents-header">
                    <div>
                        <h2>Gestion des Agents</h2>
                        <p>Chaque agent est assigné à un seul service</p>
                    </div>
                    <button class="btn-add-agent" id="openAddAgent">
                        <i class="fas fa-plus"></i>
                        Nouvel Agent
                    </button>
                </div>

                <!-- STATISTIQUES -->
                <div class="agent-stats">

                    <div class="agent-stat">
                        <h2><?= $total_agents ?></h2>
                        <span>Agents actifs</span>
                    </div>

                    <div class="agent-stat">
                        <h2><?= $agents_actifs ?></h2>
                        <span>En ligne</span>
                    </div>

                    <div class="agent-stat">
                        <h2><?= $agents_inactifs ?></h2>
                        <span>Hors ligne</span>
                    </div>

                </div>

                <!-- GRILLE DES AGENTS -->
                <div class="agents-grid">

                    <?php if (!empty($agents)): ?>

                        <?php foreach ($agents as $agent): ?>

                            <div class="agent-card" style="border-top-color: <?= getServiceColor($agent['nom_service']) ?>">

                                <?php if (!empty($agent['photo'])): ?>
                                    <img src="../assets/images/profiles/<?= htmlspecialchars($agent['photo']) ?>" alt="agent">
                                <?php else: ?>
                                    <div class="agent-avatar">
                                        <?= strtoupper(mb_substr($agent['prenom'], 0, 1)) ?><?= strtoupper(mb_substr($agent['nom'], 0, 1)) ?>
                                    </div>
                                <?php endif; ?>

                                <h3><?= htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']) ?></h3>

                                <p class="agent-role">
                                    <i class="fas <?= getServiceIcon($agent['nom_service']) ?>"></i>
                                    Agent <?= htmlspecialchars($agent['nom_service']) ?>
                                </p>

                                <div class="agent-info">
                                    <p><strong>Email :</strong> <?= htmlspecialchars($agent['email']) ?></p>
                                    <p><strong>Service :</strong> <?= htmlspecialchars($agent['nom_service']) ?></p>
                                </div>

                                <?php if ($agent['statut'] == 'Actif'): ?>
                                    <span class="badge success">● En ligne</span>
                                <?php else: ?>
                                    <span class="badge warning">● Hors ligne</span>
                                <?php endif; ?>

                                <div class="agent-actions">
                                    <button
                                        class="edit-agent openEditAgent"
                                        data-id="<?= $agent['id_agent'] ?>"
                                        data-nom="<?= htmlspecialchars($agent['nom']) ?>"
                                        data-prenom="<?= htmlspecialchars($agent['prenom']) ?>"
                                        data-email="<?= htmlspecialchars($agent['email']) ?>"
                                        data-service="<?= $agent['id_service'] ?>"
                                        data-statut="<?= $agent['statut'] ?>">
                                        Modifier
                                    </button>
                                    <button
                                        class="delete-agent openDeleteAgent"
                                        data-id="<?= $agent['id_agent'] ?>">
                                        Supprimer
                                    </button>
                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="empty-agents">
                            <i class="fas fa-user-slash"></i>
                            <p>Aucun agent enregistré</p>
                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </div>

    </div>

    <!-- MODAL PROFIL -->
    <?php include "includes/profile_modal.php"; ?>

    <!-- MODAL AJOUTER AGENT -->
    <div class="modal-overlay" id="addAgentModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Nouvel Agent</h2>
                <span class="close-modal" id="closeAddAgent">&times;</span>
            </div>

            <form action="traitements/agent/add_agent.php" method="POST" enctype="multipart/form-data" class="agent-form">

                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" required placeholder="Mane">
                </div>

                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" required placeholder="khady">
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="disa.mane@email.com">
                </div>

                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="mot_de_passe" required placeholder="Minimum 6 caractères" minlength="6">
                </div>

                <div class="form-group full-width">
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

                <div class="form-group full-width">
                    <label>Photo (optionnelle)</label>
                    <input type="file" name="photo" accept="image/*">
                </div>

                <div class="modal-footer full-width">
                    <button type="button" class="btn-cancel" id="cancelAddAgent">Annuler</button>
                    <button type="submit" class="btn-save">Enregistrer</button>
                </div>

            </form>
        </div>
    </div>

    <!-- MODAL MODIFIER AGENT -->
    <div class="modal-overlay" id="editAgentModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Modifier Agent</h2>
                <span class="close-modal closeEditAgent">&times;</span>
            </div>

            <form action="traitements/agent/update_agent.php" method="POST" enctype="multipart/form-data" class="agent-form">

                <input type="hidden" name="id_agent" id="edit_id_agent">

                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" id="edit_nom" required>
                </div>

                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" id="edit_prenom" required>
                </div>

                <div class="form-group full-width">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>

                <div class="form-group full-width">
                    <label>Service</label>
                    <select name="id_service" id="edit_service" required>

                        <?php foreach ($services as $service): ?>
                            <option value="<?= $service['id_service'] ?>">
                                <?= htmlspecialchars($service['nom_service']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Statut</label>
                    <select name="statut" id="edit_statut">
                        <option value="Actif">Actif (En ligne)</option>
                        <option value="Inactif">Inactif (Hors ligne)</option>
                    </select>
                </div>

                <div class="form-group full-width">
                    <label>Nouvelle photo (optionnelle)</label>
                    <input type="file" name="photo" accept="image/*">
                </div>

                <div class="modal-footer full-width">
                    <button type="submit" class="btn-save">Enregistrer</button>
                    <button type="button" class="btn-cancel closeEditAgentBtn">Annuler</button>
                </div>

            </form>
        </div>
    </div>

    <!-- MODAL SUPPRIMER AGENT -->
    <div class="modal-overlay" id="deleteAgentModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Supprimer Agent</h2>
                <span class="close-modal closeDeleteAgent">&times;</span>
            </div>

            <form action="traitements/agent/delete_agent.php" method="POST">
                <input type="hidden" name="id_agent" id="delete_id_agent">

                <p>Êtes-vous sûr de vouloir supprimer cet agent ?</p>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel cancelDeleteAgent">Annuler</button>
                    <button type="submit" class="btn-danger">Supprimer</button>
                </div>
            </form>
        </div>
    </div>

    <script src="assets/js/includes.js"></script>
    <script src="assets/js/agents.js"></script>

</body>

</html>