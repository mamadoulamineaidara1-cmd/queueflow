<?php
require_once "../include/db.php";

 $activePage = "prendre-ticket";

 $erreur = null;
 $ticketTrouve = null;

// Traitement du formulaire de recherche
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $telephone = trim($_POST['telephone'] ?? '');

    if (empty($telephone)) {
        $erreur = "Veuillez entrer votre numéro de téléphone.";
    } else {
        // Chercher l'usager par téléphone
        $stmt = $conn->prepare("SELECT id_usager FROM usager WHERE telephone = ?");
        $stmt->execute([$telephone]);
        $usager = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usager) {
            // Chercher un ticket actif pour cet usager aujourd'hui
            $stmt = $conn->prepare("
                SELECT id_ticket 
                FROM ticket 
                WHERE id_usager = ? 
                AND date_creation = CURDATE() 
                AND statut IN ('En attente', 'En cours')
                ORDER BY id_ticket DESC 
                LIMIT 1
            ");
            $stmt->execute([$usager['id_usager']]);
            $ticketTrouve = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($ticketTrouve) {
                // Rediriger vers la page du ticket généré
                header("Location: ticket_genere.php?id_ticket=" . $ticketTrouve['id_ticket']);
                exit();
            } else {
                $erreur = "Aucun ticket actif trouvé pour ce numéro aujourd'hui.";
            }
        } else {
            $erreur = "Aucun compte trouvé avec ce numéro de téléphone.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow Hospital - Retrouver mon ticket</title>

    <link rel="stylesheet" href="assets/css/layout_client.css">
    <link rel="stylesheet" href="assets/css/prendre_ticket.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        .retrouver-container {
            max-width: 500px;
            margin: 60px auto;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
        }
        .retrouver-icon {
            font-size: 60px;
            color: #2563eb;
            margin-bottom: 20px;
        }
        .retrouver-container h2 {
            color: #0f172a;
            margin-bottom: 10px;
        }
        .retrouver-container p {
            color: #64748b;
            margin-bottom: 30px;
        }
        .retrouver-form {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .retrouver-form input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 16px;
        }
        .btn-next {
            padding: 15px 30px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-next:hover {
            background: #1d4ed8;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: 500;
        }
        .back-link {
            display: inline-block;
            margin-top: 25px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            color: #2563eb;
        }
    </style>
</head>
<body>

    <?php include 'includes/header_client.php'; ?>

    <section class="ticket-section">
        <div class="retrouver-container">
            <div class="retrouver-icon">
                <i class="fas fa-search"></i>
            </div>
            <h2>Retrouver mon ticket</h2>
            <p>Entrez le numéro de téléphone utilisé lors de la prise de ticket.</p>

            <?php if ($erreur): ?>
                <div class="alert-error"><?= $erreur ?></div>
            <?php endif; ?>

            <form method="POST" class="retrouver-form">
                <input type="text" name="telephone" placeholder="Ex: 77 000 00 00" required>
                <button type="submit" class="btn-next">
                    <i class="fas fa-search"></i> Retrouver
                </button>
            </form>

            <a href="prendre_ticket.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Prendre un nouveau ticket
            </a>
        </div>
    </section>

    <?php include 'includes/footer_client.php'; ?>

</body>
</html>