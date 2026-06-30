<?php

require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    $id_service = $_POST['id_service'];


//    Vérifier les tickets liés

    $stmt = $conn->prepare("
        SELECT COUNT(*)
        FROM ticket
        WHERE id_service = ?
    ");

    $stmt->execute([$id_service]);

    $nbTickets = $stmt->fetchColumn();

   
   // Si des tickets existent

    if ($nbTickets > 0)
    {
        header("Location: ../../gestion_services.php?error=service_utilise");
        exit;
    }

//    Suppression

    $stmt = $conn->prepare("
        DELETE FROM service
        WHERE id_service = ?
    ");

    $stmt->execute([$id_service]);
}

header("Location: ../../gestion_services.php?success=service_supprime");
exit;