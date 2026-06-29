<?php
require_once "../include/db.php";

 $activePage = "parametres";

/* ===========================================
   PARAMÈTRES DU SYSTÈME
   =========================================== */

 $stmt = $conn->query("SELECT * FROM parametre LIMIT 1");
 $parametre = $stmt->fetch(PDO::FETCH_ASSOC);

/* ===========================================
   INFOS ADMIN (id 1 par défaut)
   =========================================== */

 $stmt = $conn->prepare("SELECT * FROM admin WHERE id_admin = 1");
 $stmt->execute();
 $admin = $stmt->fetch(PDO::FETCH_ASSOC);

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
    <title>QueueFlow - Paramètres</title>

    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="assets/css/components.css">
    <link rel="stylesheet" href="assets/css/parametres.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

    <div class="dashboard">

        <!-- SIDEBAR -->
        <?php include "includes/sidebar_admin.php"; ?>

        <div class="main-content">

            <!-- HEADER -->
            <?php
            $pageTitle = "Paramètres";
            $pageSubtitle = "Configuration générale de QueueFlow";
            include "includes/header_admin.php";
            ?>

            <section class="content">

                <!-- ALERTES -->
                <?= $alert ?>

                <div class="settings-header">
                    <h2>Paramètres du Système</h2>
                    <p>Configuration générale de QueueFlow</p>
                </div>

                <div class="settings-banner">
                    <i class="fas fa-cog"></i>
                    <div>
                        <h3>Configuration du système</h3>
                        <p>Gérez les paramètres généraux de votre plateforme.</p>
                    </div>
                </div>

                <div class="settings-grid">

                    <!-- Carte 1 : Informations générales -->
                    <div class="setting-card">
                        <h3>Informations générales</h3>
                        <form method="POST" action="traitements/parametre/update_infos.php">
                            <div class="form-group">
                                <label>Nom de l'établissement</label>
                                <input type="text" name="nom_etablissement" value="<?= htmlspecialchars($parametre['nom_etablissement']) ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email de contact</label>
                                <input type="email" name="email_contact" value="<?= htmlspecialchars($parametre['email_contact']) ?>" required>
                            </div>
                            <button type="submit" class="btn-save-card">
                                <i class="fas fa-check"></i> Enregistrer
                            </button>
                        </form>
                    </div>

                    <!-- Carte 2 : Horaires d'ouverture -->
                    <div class="setting-card">
                        <h3>Horaires d'ouverture</h3>
                        <form method="POST" action="traitements/parametre/update_horaires.php">
                            <div class="form-group">
                                <label>Ouverture</label>
                                <input type="time" name="heure_ouverture" value="<?= $parametre['heure_ouverture'] ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Fermeture</label>
                                <input type="time" name="heure_fermeture" value="<?= $parametre['heure_fermeture'] ?>" required>
                            </div>
                            <button type="submit" class="btn-save-card">
                                <i class="fas fa-check"></i> Enregistrer
                            </button>
                        </form>
                    </div>

                    <!-- Carte 3 : Notifications -->
                    <div class="setting-card">
                        <h3>Notifications</h3>
                        <form method="POST" action="traitements/parametre/update_notifications.php">
                            <div class="toggle-row">
                                <span>Notifications Email</span>
                                <label class="switch">
                                    <input type="checkbox" name="notification_email" value="Oui" <?= $parametre['notification_email'] === 'Oui' ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="toggle-row">
                                <span>SMS</span>
                                <label class="switch">
                                    <input type="checkbox" name="notification_sms" value="Oui" <?= $parametre['notification_sms'] === 'Oui' ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <button type="submit" class="btn-save-card">
                                <i class="fas fa-check"></i> Enregistrer
                            </button>
                        </form>
                    </div>

                    <!-- Carte 4 : Sécurité -->
                    <div class="setting-card">
                        <h3>Sécurité</h3>
                        <div class="admin-info">
                            <p><strong>Nom :</strong> <?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?></p>
                            <p><strong>Email :</strong> <?= htmlspecialchars($admin['email']) ?></p>
                        </div>
                        <button class="security-btn" id="openPasswordModal">
                            <i class="fas fa-lock"></i>
                            Changer le mot de passe
                        </button>
                    </div>

                </div>

            </section>

        </div>

    </div>

    <!-- MODAL PROFIL -->
    <?php include "includes/profile_modal.php"; ?>

    <!-- MODAL CHANGER MOT DE PASSE -->
    <div class="modal-overlay" id="passwordModal">
        <div class="modal-box">
            <div class="modal-header">
                <h2>Changer le mot de passe</h2>
                <span class="close-modal" id="closePasswordModal">&times;</span>
            </div>
            <form method="POST" action="traitements/parametre/update_password.php">

                <div class="form-group">
                    <label>Ancien mot de passe</label>
                    <input type="password" name="ancien_mdp" required placeholder="Votre mot de passe actuel">
                </div>

                <div class="form-group">
                    <label>Nouveau mot de passe</label>
                    <input type="password" name="nouveau_mdp" required placeholder="Minimum 6 caractères" minlength="6">
                </div>

                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="confirmer_mdp" required placeholder="Retapez le nouveau mot de passe" minlength="6">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" id="cancelPasswordModal">Annuler</button>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-lock"></i> Enregistrer
                    </button>
                </div>

            </form>
        </div>
    </div>

    <script src="assets/js/includes.js"></script>
    <script>
        const openPasswordModal = document.getElementById('openPasswordModal');
        const passwordModal = document.getElementById('passwordModal');
        const closePasswordModal = document.getElementById('closePasswordModal');
        const cancelPasswordModal = document.getElementById('cancelPasswordModal');

        openPasswordModal.addEventListener('click', () => {
            passwordModal.classList.add('active');
        });

        closePasswordModal.addEventListener('click', () => {
            passwordModal.classList.remove('active');
        });

        cancelPasswordModal.addEventListener('click', () => {
            passwordModal.classList.remove('active');
        });

        passwordModal.addEventListener('click', (e) => {
            if (e.target === passwordModal) {
                passwordModal.classList.remove('active');
            }
        });
    </script>

</body>
</html>