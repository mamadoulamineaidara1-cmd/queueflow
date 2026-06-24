<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../include/db.php";

// Valeur par défaut si pas de photo
 $photo = 'default-agent.png';

// Récupérer la photo depuis la base de données
if (!empty($_SESSION['id_agent'])) {
    $stmt = $conn->prepare("SELECT photo FROM agent WHERE id_agent = ?");
    $stmt->execute([$_SESSION['id_agent']]);
    $agentData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($agentData && !empty($agentData['photo'])) {
        $photo = $agentData['photo'];
    }
}
?>

<header class="header">

    <div class="header-left">

        <h1>
            <?= $pageTitle ?? 'Dashboard' ?>
        </h1>

        <p>
            <?= $pageSubtitle ?? "Vue d'ensemble de votre activité" ?>
        </p>

    </div>

    <div class="header-right">

        <div class="date-box">
            <i class="fas fa-calendar-alt"></i>
            <?= date('d/m/Y') ?>
        </div>

        <div class="profile" id="openProfile">
            <!-- CORRECTION : Ajout du dossier /profiles/ dans le chemin -->
            <img src="../assets/images/profiles/<?= htmlspecialchars($photo) ?>" alt="Profil Agent">
        </div>

    </div>

</header>