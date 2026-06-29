<?php
// Pas besoin de session ici, c'est une page publique
require_once "../include/db.php";

 $activePage = "accueil";

// Récupérer les services actifs depuis la base
 $stmt = $conn->query("SELECT * FROM service WHERE statut = 'Actif' ORDER BY id_service ASC");
 $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Association stricte selon ton cahier des charges (Consultation, Laboratoire, Pédiatrie)
 $icons = [
    'Consultation' => 'fa-stethoscope',
    'Laboratoire'  => 'fa-flask',
    'Pédiatrie'    => 'fa-baby'
];

 $descriptions = [
    'Consultation' => 'Consultation médicale générale',
    'Laboratoire'  => 'Analyses médicales',
    'Pédiatrie'    => 'Soins pour enfants'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow Hospital - Accueil</title>

    <!-- CSS partagé -->
    <link rel="stylesheet" href="assets/css/layout_client.css">

    <!-- CSS page -->
    <link rel="stylesheet" href="assets/css/accueil.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <!-- ========== HEADER ========== -->
    <?php include 'includes/header_client.php'; ?>

    <!-- ========== HERO ========== -->
    <section class="hero-section">

        <div class="hero-content">
            <h1>Bienvenue à QueueFlow Hospital</h1>
            <p>Prenez votre ticket en ligne et réduisez votre temps d'attente.</p>
            <a href="prendre_ticket.php" class="btn-ticket">Prendre un Ticket</a>
        </div>

    </section>

    <!-- ========== SERVICES APERÇU ========== -->
    <section class="services-client">

        <h2>Services Disponibles</h2>

        <div class="client-services-grid">

            <?php if (!empty($services)): ?>

                <?php foreach ($services as $service): ?>

                    <a href="prendre_ticket.php?id_service=<?= $service['id_service'] ?>" class="client-service-card">
                        
                        <i class="fas <?= $icons[$service['nom_service']] ?? 'fa-building' ?>"></i>
                        
                        <h3><?= htmlspecialchars($service['nom_service']) ?></h3>
                        
                        <p><?= $descriptions[$service['nom_service']] ?? htmlspecialchars($service['description']) ?></p>

                    </a>

                <?php endforeach; ?>

            <?php else: ?>

                <p style="color:#94a3b8;">Aucun service disponible pour le moment.</p>

            <?php endif; ?>

        </div>

    </section>

    <!-- ========== FOOTER ========== -->
    <?php include 'includes/footer_client.php'; ?>

</body>
</html>