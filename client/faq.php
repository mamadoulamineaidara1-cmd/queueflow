<?php
 $activePage = "faq";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QueueFlow Hospital - FAQ</title>

    <link rel="stylesheet" href="assets/css/layout_client.css">
    <link rel="stylesheet" href="assets/css/faq.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include 'includes/header_client.php'; ?>

    <main class="faq-container">

        <div class="faq-header">
            <h1>Questions Fréquentes</h1>
            <p>Trouvez rapidement les réponses à vos questions</p>
        </div>

        <div class="faq-list">

            <div class="faq-item">
                <button class="faq-question">
                    Comment obtenir un ticket ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Remplissez le formulaire de prise de ticket puis choisissez le service souhaité (Consultation, Laboratoire ou Pédiatrie). Après le paiement, votre ticket sera généré automatiquement.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Comment suivre mon ticket ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Rendez-vous dans la page "Suivi Ticket" et entrez votre numéro de ticket (ex: C002). Vous verrez votre position en temps réel et le ticket actuellement appelé pour votre service.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    J'ai perdu mon ticket, comment le récupérer ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Allez dans la page "Retrouver mon ticket" et saisissez votre numéro de téléphone. Si un ticket actif est lié à ce numéro aujourd'hui, il s'affichera immédiatement.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Que faire en cas d'urgence ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Présentez-vous directement au guichet de votre service. L'agent priorisera votre ticket urgence. Si vous n'avez pas encore de ticket, prenez-en un au préalable.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Puis-je annuler mon ticket ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Un agent ou un administrateur peut annuler un ticket non traité. Vous pouvez contacter le personnel sur place.</p>
                </div>
            </div>

            <div class="faq-item">
                <button class="faq-question">
                    Les temps d'attente sont-ils exacts ?
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="faq-answer">
                    <p>Les temps affichés sont des estimations calculées selon le nombre de personnes devant vous dans votre service. Ils peuvent varier légèrement selon le temps de traitement réel de chaque patient.</p>
                </div>
            </div>

        </div>

    </main>

    <?php include 'includes/footer_client.php'; ?>

    <script src="assets/js/faq.js"></script>

</body>
</html>