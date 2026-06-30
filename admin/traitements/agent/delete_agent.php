<?php
require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../gestion_agents.php");
    exit;
}

 $id_agent = $_POST['id_agent'];

/* Vérifier si l'agent a des tickets en cours */
 $stmt = $conn->prepare("
    SELECT COUNT(*) 
    FROM ticket 
    WHERE id_agent = ? 
    AND statut IN ('En attente', 'En cours')
");
 $stmt->execute([$id_agent]);
 $nb_tickets = $stmt->fetchColumn();

if ($nb_tickets > 0) {
    header("Location: ../../gestion_agents.php?error=Impossible de supprimer cet agent car il a " . $nb_tickets . " ticket(s) en cours");
    exit;
}

/* Suppression */
 $stmt = $conn->prepare("DELETE FROM agent WHERE id_agent = ?");
 $stmt->execute([$id_agent]);

header("Location: ../../gestion_agents.php?success=Agent supprimé avec succès");
exit;