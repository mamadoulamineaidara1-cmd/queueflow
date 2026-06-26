<?php
session_start();
require_once "../include/db.php";

// Sécurité : agent seulement
if (!isset($_SESSION['type']) || $_SESSION['type'] !== "agent") {
    header("Location: ../auth/login.php");
    exit();
}

 $id_agent = $_SESSION['id_agent'];
 $id_service = $_SESSION['id_service'];

// 1. Vérifier si l'agent n'a pas DÉJÀ un ticket en cours
 $stmt = $conn->prepare("
    SELECT id_ticket 
    FROM ticket 
    WHERE id_agent = ? AND statut = 'En cours'
    LIMIT 1
");
 $stmt->execute([$id_agent]);

if ($stmt->fetch()) {
    $_SESSION['error_agent'] = "Vous avez déjà un ticket en cours, terminez-le d'abord.";
    header("Location: dashboard_agent.php");
    exit();
}

// 2. Chercher le PROCHAIN ticket en attente pour son service
 $stmt = $conn->prepare("
    SELECT id_ticket 
    FROM ticket 
    WHERE id_service = ? AND statut = 'En attente'
    ORDER BY 
        CASE 
            WHEN priorite = 'Urgence' THEN 1 
            ELSE 2 
        END, 
        id_ticket ASC
    LIMIT 1
");
 $stmt->execute([$id_service]);
 $prochainTicket = $stmt->fetch(PDO::FETCH_ASSOC);

// 3. Si un ticket existe, on le passe en "En cours" et nb_appels à 1
if ($prochainTicket) {
    $stmt = $conn->prepare("
        UPDATE ticket 
        SET statut = 'En cours', 
            id_agent = ?, 
            heure_appel = CURTIME(),
            nb_appels = 1
        WHERE id_ticket = ?
    ");
    $stmt->execute([$id_agent, $prochainTicket['id_ticket']]);
    
    $_SESSION['success_agent'] = "Ticket appelé avec succès.";
} else {
    $_SESSION['error_agent'] = "Aucun ticket en attente dans votre service.";
}

// 4. Retour au dashboard
header("Location: dashboard_agent.php");
exit();