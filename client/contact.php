<?php
 $activePage = "contact";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow Hospital - Contact</title>

    <link rel="stylesheet" href="assets/css/layout_client.css">
    <link rel="stylesheet" href="assets/css/contact.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include 'includes/header_client.php'; ?>

    <main class="contact-container">

        <div class="contact-header">
            <h1>Contact & Assistance</h1>
            <p>Notre équipe est disponible pour répondre à vos questions</p>
        </div>

        <div class="contact-grid">

            <div class="contact-info">

                <div class="info-card">
                    <i class="fas fa-location-dot"></i>
                    <h3>Adresse</h3>
                    <p>Hôpital QueueFlow<br>Dakar, Sénégal</p>
                </div>

                <div class="info-card">
                    <i class="fas fa-phone"></i>
                    <h3>Téléphone</h3>
                    <p>+221 77 000 00 00</p>
                </div>

                <div class="info-card">
                    <i class="fas fa-envelope"></i>
                    <h3>Email</h3>
                    <p>contact@queueflow.sn</p>
                </div>

                <div class="info-card">
                    <i class="fas fa-clock"></i>
                    <h3>Horaires</h3>
                    <p>Lundi - Dimanche<br>24h / 24</p>
                </div>

            </div>

            <div class="contact-form-card">

                <h2>Envoyer un message</h2>

                <form>

                    <div class="form-group">
                        <label>Nom complet</label>
                        <input type="text" name="nom" placeholder="Votre nom">
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="exemple@email.com">
                    </div>

                    <div class="form-group">
                        <label>Sujet</label>
                        <input type="text" name="sujet" placeholder="Objet du message">
                    </div>

                    <div class="form-group">
                        <label>Message</label>
                        <textarea name="message" placeholder="Votre message"></textarea>
                    </div>

                    <button type="submit" class="btn-send">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer
                    </button>

                </form>

            </div>

        </div>

    </main>

    <?php include 'includes/footer_client.php'; ?>

</body>
</html>