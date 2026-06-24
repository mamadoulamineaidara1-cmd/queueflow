<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../include/db.php";

// Valeurs par défaut
 $photo = 'default-agent.png';
 $nomComplet = 'Agent';
 $email = '';
 $service = '';

// Récupérer les infos depuis la base de données
if (!empty($_SESSION['id_agent'])) {
    $stmt = $conn->prepare("
        SELECT a.nom, a.prenom, a.email, a.photo, s.nom_service
        FROM agent a
        INNER JOIN service s ON a.id_service = s.id_service
        WHERE a.id_agent = ?
    ");
    $stmt->execute([$_SESSION['id_agent']]);
    $agent = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($agent) {
        $nomComplet = htmlspecialchars($agent['prenom'] . ' ' . $agent['nom']);
        $email = htmlspecialchars($agent['email']);
        $service = htmlspecialchars($agent['nom_service'] ?? '');
        
        if (!empty($agent['photo'])) {
            $photo = $agent['photo'];
        }
    }
}
?>

<div class="modal-overlay profile-modal" id="profileModal">

    <div class="modal-content">

        <span class="close-modal" id="closeModal">&times;</span>

        <div class="modal-profile">

            <!-- CORRECTION : Ajout du dossier /profiles/ dans le chemin -->
            <img src="../assets/images/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil Agent">

            <h2><?= $nomComplet ?></h2>

            <p><?= $email ?></p>

            <span class="badge success">Agent</span>

            <div class="mini-info">
                <p><span class="dot-online"></span> En ligne</p>
                <p>Dernière connexion : <?= date('H:i') ?></p>
                <p>Service : <?= $service ?></p>
            </div>

        </div>

        <div class="modal-actions">

            <a href="profil_agent.php" class="btn-primary">
                <i class="fas fa-user"></i> Mon Profil
            </a>

            <a href="../auth/logout.php" class="btn-danger-soft">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>

        </div>

    </div>

</div>