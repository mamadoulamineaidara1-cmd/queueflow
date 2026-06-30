<?php

require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom_service = trim($_POST['nom_service']);
    $description = trim($_POST['description']);
    $statut = $_POST['statut'];

    $stmt = $conn->prepare("
        INSERT INTO service(
            nom_service,
            description,
            statut
        )
        VALUES (?, ?, ?)
    ");

    $stmt->execute([
        $nom_service,
        $description,
        $statut
    ]);

}

header("Location: ../../gestion_services.php");
exit;