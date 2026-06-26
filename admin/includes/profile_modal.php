<?php
/* ===========================================
   MODAL PROFIL
   Avant l'auth : affiche les infos par défaut
   Après l'auth  : affiche les infos de $_SESSION
   =========================================== */

 $nom = 'Admin';
 $prenom = 'Principal';
 $email = 'admin@queueflow.com';
 $photo = 'default.png';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $nom = $user['nom'] ?? $nom;
    $prenom = $user['prenom'] ?? $prenom;
    $email = $user['email'] ?? $email;
    $photo = !empty($user['photo']) ? $user['photo'] : $photo;
}
?>

<div class="modal-overlay profile-modal" id="profileModal">

    <div class="modal-content">

        <span class="close-modal" id="closeModal">&times;</span>

        <div class="modal-profile">

             <!-- IMAGE ADMIN (locale) -->
             <img src="assets/images/<?= htmlspecialchars($photo) ?>" alt="profil">
            <h2><?= htmlspecialchars($prenom . ' ' . $nom) ?></h2>

            <p><?= htmlspecialchars($email) ?></p>

            <span class="badge primary">Administrateur</span>

            <div class="mini-info">
                <p><span class="dot-online"></span> En ligne</p>
                <p>Dernière connexion : Aujourd'hui</p>
                <p>Accès : Complet</p>
            </div>

        </div>

        <div class="modal-actions">

            <button class="btn-primary">
                <i class="fas fa-user"></i> Mon Profil
            </button>

            <a href="../auth/logout.php" class="btn-danger-soft">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>

        </div>

    </div>

</div>