<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user = $_SESSION['user'] ?? null;

// image par défaut
$photo = !empty($user['photo']) ? $user['photo'] : 'default.png';
?>

<header class="header">

     <div class="header-left">
                <!-- <h1 class="page-title">Dashboard</h1> -->
        <h1 class="page-title">
            <?= $pageTitle ?? 'Dashboard' ?>
        </h1>

        <p class="page-subtitle">
            <?= $pageSubtitle ?? "Vue d'ensemble de votre activité" ?>
        </p>
    </div>

    <div class="header-right">

        <div class="date-box">
            <i class="fas fa-calendar-alt"></i>
            <span class="date-text">
                <?= date("l, d F Y") ?>
            </span>
        </div>

        <div class="profile" id="openProfile">

            <!-- IMAGE ADMIN (locale) -->
            <img src="assets/images/<?= htmlspecialchars($photo) ?>" alt="profil">

        </div>

    </div>

</header>