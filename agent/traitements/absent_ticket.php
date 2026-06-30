<?php
session_start();
require_once "../../include/db.php";

if (!isset($_SESSION['type']) || $_SESSION['type'] !== "agent") {
    header("Location: ../../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../dashboard_agent.php");
    exit();
}

 $id_agent = $_SESSION['id_agent'];
 $id_ticket = $_POST['id_ticket'] ?? null;

if (!$id_ticket) {
    header("Location: ../dashboard_agent.php");
    exit();
}

// 1. Récupérer le ticket pour vérifier à qui il appartient et combien d'appels il a eu
 $stmt = $conn->prepare("
    SELECT id_ticket, nb_appels 
    FROM ticket 
    WHERE id_ticket = ? AND id_agent = ? AND statut = 'En cours'
    LIMIT 1
");
 $stmt->execute([$id_ticket, $id_agent]);
 $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if ($ticket) {
    $nouveaux_appels = $ticket['nb_appels'] + 1;

    // 2. Si c'est le 3ème appel manqué -> On passe le ticket à "Annulé"
    if ($nouveaux_appels >= 3) {
        $stmt = $conn->prepare("
            UPDATE ticket 
            SET statut = 'Annulé', 
                id_agent = NULL, 
                nb_appels = ? 
            WHERE id_ticket = ?
        ");
        $stmt->execute([$nouveaux_appels, $id_ticket]);
        
        $_SESSION['error_agent'] = "Le ticket a été marqué comme Abandonné (3 appels manqués).";
    } else {
        // Sinon on le remet en "En attente" pour le ré-appeler plus tard
        $stmt = $conn->prepare("
            UPDATE ticket 
            SET statut = 'En attente', 
                id_agent = NULL, 
                nb_appels = ? 
            WHERE id_ticket = ?
        ");
        $stmt->execute([$nouveaux_appels, $id_ticket]);
        
        $_SESSION['success_agent'] = "Client absent. Ticket remis en attente (Appel " . $nouveaux_appels . "/3).";
    }
} else {
    $_SESSION['error_agent'] = "Ticket invalide.";
}

header("Location: ../dashboard_agent.php");
exit();