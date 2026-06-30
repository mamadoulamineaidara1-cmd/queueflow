<?php

require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id_service = $_POST['id_service'];
    $nom_service = trim($_POST['nom_service']);
    $description = trim($_POST['description']);
    $statut = $_POST['statut'];

    $stmt = $conn->prepare("
        UPDATE service
        SET
            nom_service = ?,
            description = ?,
            statut = ?
        WHERE id_service = ?
    ");

    $stmt->execute([
        $nom_service,
        $description,
        $statut,
        $id_service
    ]);

    header("Location: ../../gestion_services.php");
    exit;
}