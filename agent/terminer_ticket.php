<?php
session_start();
require_once "../include/db.php";

// Sécurité : agent seulement

if (!isset($_SESSION['type']) || $_SESSION['type'] !== "agent") {
    header("Location: ../auth/login.php");
    exit();
}

// Sécurité : vérifier que c'est bien un POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard_agent.php");
    exit();
}

 $id_agent = $_SESSION['id_agent'];
 $id_ticket = $_POST['id_ticket'] ?? null;

if (!$id_ticket) {
    header("Location: dashboard_agent.php");
    exit();
}

// 1. Récupérer les infos du ticket AVANT de le modifier
 $stmt = $conn->prepare("
    SELECT id_ticket, heure_appel 
    FROM ticket 
    WHERE id_ticket = ? AND id_agent = ? AND statut = 'En cours'
    LIMIT 1
");
 $stmt->execute([$id_ticket, $id_agent]);
 $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if ($ticket) {
    $heure_appel = $ticket['heure_appel'];

    // 2. Mettre à jour le ticket : Traité
    $stmt = $conn->prepare("
        UPDATE ticket 
        SET statut = 'Traité', 
            heure_fin = CURTIME() 
        WHERE id_ticket = ?
    ");
    $stmt->execute([$id_ticket]);

    // 3. Insérer dans l'historique
    $stmt = $conn->prepare("
        INSERT INTO historique_ticket (id_ticket, id_agent, heure_debut, heure_fin, duree_traitement)
        VALUES (?, ?, ?, CURTIME(), TIME_TO_SEC(TIMEDIFF(CURTIME(), ?)))
    ");
    $stmt->execute([$id_ticket, $id_agent, $heure_appel, $heure_appel]);

    $_SESSION['success_agent'] = "Ticket marqué comme traité.";
} else {
    $_SESSION['error_agent'] = "Ticket invalide ou déjà terminé.";
}

// 4. Retour au dashboard
header("Location: dashboard_agent.php");
exit();