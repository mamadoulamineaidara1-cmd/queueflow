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

 $activePage = "profil";
 $pageTitle = "Profil";
 $pageSubtitle = "Vos informations personnelles";

// Messages de succès/erreur
 $success = $_SESSION['success_agent'] ?? null;
 $error = $_SESSION['error_agent'] ?? null;
unset($_SESSION['success_agent'], $_SESSION['error_agent']);

// ===== Récupérer les infos de l'agent depuis la base =====
 $stmt = $conn->prepare("
    SELECT a.nom, a.prenom, a.email, a.photo, a.date_creation, s.nom_service 
    FROM agent a 
    INNER JOIN service s ON a.id_service = s.id_service 
    WHERE a.id_agent = ?
");
 $stmt->execute([$id_agent]);
 $agent = $stmt->fetch(PDO::FETCH_ASSOC);

 $photo = !empty($agent['photo']) ? $agent['photo'] : 'default-agent.png';
 $nomComplet = htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']);

// ===== Stats : Total traité =====
 $stmt = $conn->prepare("SELECT COUNT(*) FROM historique_ticket WHERE id_agent = ?");
 $stmt->execute([$id_agent]);
 $totalTraite = $stmt->fetchColumn();

// ===== Stats : Temps moyen =====
 $stmt = $conn->prepare("SELECT AVG(duree_traitement) FROM historique_ticket WHERE id_agent = ?");
 $stmt->execute([$id_agent]);
 $moyenneSecondes = $stmt->fetchColumn();
 $tempsMoyen = "00 min 00 sec";
if ($moyenneSecondes) {
    $tempsMoyen = sprintf("%02d min %02d sec", floor($moyenneSecondes / 60), $moyenneSecondes % 60);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow - Profil Agent</title>

    <!-- CSS partagé admin -->
    <link rel="stylesheet" href="../admin/assets/css/dashboard.css">
    <link rel="stylesheet" href="../admin/assets/css/components.css">

    <!-- CSS agent -->
    <link rel="stylesheet" href="assets/css/layout_agent.css">
    <link rel="stylesheet" href="assets/css/profil_agent.css">

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

            <!-- ========== CONTENU PROFIL ========== -->
            <section class="content">

                <!-- Messages -->
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

                <div class="profile-banner">

                <img src="../assets/images/profiles/<?= htmlspecialchars($photo) ?>" alt="agent">

                    <h2><?= $nomComplet ?></h2>

                    <p>Agent - <?= htmlspecialchars($agent['nom_service']) ?></p>

                    <span class="badge success">● En ligne</span>

                </div>

                <div class="profile-stats">

                    <div class="profile-stat-card">
                        <i class="fas fa-ticket-alt"></i>
                        <h3><?= $totalTraite ?></h3>
                        <p>Tickets traités</p>
                    </div>

                    <div class="profile-stat-card">
                        <i class="fas fa-clock"></i>
                        <h3><?= $tempsMoyen ?></h3>
                        <p>Temps moyen</p>
                    </div>

                    <!-- <div class="profile-stat-card">
                        <i class="fas fa-star"></i>
                        <h3><?= $totalTraite > 0 ? '100%' : '0%' ?></h3>
                        <p>Performance</p>
                    </div> -->
                    <div class="profile-stat-card">
                        <i class="fas fa-star"></i>
                        <h3><?= $totalTraite ?></h3>
                        <p>Productivité totale</p>
                    </div>

                </div>

                <div class="agent-info-card">

                    <h3>Informations personnelles</h3>

                    <div class="info-grid">

                        <div class="info-item">
                            <span>Email</span>
                            <strong><?= htmlspecialchars($agent['email']) ?></strong>
                        </div>

                        <div class="info-item">
                            <span>Service</span>
                            <strong><?= htmlspecialchars($agent['nom_service']) ?></strong>
                        </div>

                        <div class="info-item">
                            <span>Date de création</span>
                            <strong><?= date('d/m/Y', strtotime($agent['date_creation'])) ?></strong>
                        </div>

                        <div class="info-item">
                            <span>Statut</span>
                            <strong style="color:#16a34a;">Actif</strong>
                        </div>

                    </div>

                </div>

                <div class="profile-actions">

                    <button class="btn-edit-profile" id="openEditProfile">
                        <i class="fas fa-pen"></i> Modifier Profil
                    </button>

                    <button class="btn-change-password" id="openPasswordModal">
                        <i class="fas fa-lock"></i> Changer Mot de Passe
                    </button>

                </div>

            </section>

        </div>

    </div>

    <!-- ========== MODAL PROFIL (Rapide) ========== -->
    <?php include 'includes/profile_modal_agent.php'; ?>

    <!-- ========== MODAL MODIFIER PROFIL ========== -->
    <div class="modal-overlay" id="editProfileModal">

        <div class="modal-box">

            <div class="modal-header">
                <h2>Modifier Profil</h2>
                <span class="close-modal" id="closeEditProfile">&times;</span>
            </div>

            <form method="POST" action="update_profil.php" enctype="multipart/form-data">

                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($agent['nom']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($agent['prenom']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($agent['email']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Photo</label>
                    <input type="file" name="photo" accept="image/*">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('editProfileModal').style.display='none'">Annuler</button>
                    <button type="submit" class="btn-save">Enregistrer</button>
                </div>

            </form>

        </div>

    </div>

    <!-- ========== MODAL CHANGER MOT DE PASSE ========== -->
    <div class="modal-overlay" id="passwordModal">

        <div class="modal-box">

            <div class="modal-header">
                <h2>Changer Mot de Passe</h2>
                <span class="close-modal" id="closePasswordModal">&times;</span>
            </div>

            <form method="POST" action="update_password.php">

                <div class="form-group">
                    <label>Ancien mot de passe</label>
                    <input type="password" name="ancien_mdp" required>
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="nouveau_mdp" required>
                </div>

                <div class="form-group">
                    <label>Confirmer le nouveau mot de passe</label>
                    <input type="password" name="confirmer_mdp" required>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('passwordModal').style.display='none'">Annuler</button>
                    <button type="submit" class="btn-save">Modifier</button>
                </div>

            </form>

        </div>

    </div>

    <script src="../admin/assets/js/dashboard.js"></script>
    <script src="assets/js/profil_agent.js"></script>

</body>

</html>