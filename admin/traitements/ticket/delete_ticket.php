<?php

require_once __DIR__ . "/../../../include/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Accès refusé");
}

$id_ticket = $_POST['id_ticket'];

/* Vérifier que le ticket existe */
$stmt = $conn->prepare("SELECT COUNT(*) FROM ticket WHERE id_ticket = ?");
$stmt->execute([$id_ticket]);

if ($stmt->fetchColumn() == 0) {
    header("Location: ../../gestion_tickets.php?error=Ticket introuvable");
    exit;
}

try {

    // Supprimer d'abord dans historique_ticket
    $stmt = $conn->prepare("
        DELETE FROM historique_ticket
        WHERE id_ticket = ?
    ");
    $stmt->execute([$id_ticket]);

    // Puis supprimer le ticket
    $stmt = $conn->prepare("
        DELETE FROM ticket
        WHERE id_ticket = ?
    ");
    $stmt->execute([$id_ticket]);

    header("Location: ../../gestion_tickets.php?deleted=1");
    exit;

} catch (PDOException $e) {

    header("Location: ../../gestion_tickets.php?error=Suppression impossible");
    exit;

}