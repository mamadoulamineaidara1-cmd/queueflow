<?php
require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom_etablissement = trim($_POST['nom_etablissement']);
    $email_contact = trim($_POST['email_contact']);

    if (empty($nom_etablissement) || empty($email_contact)) {
        header("Location: ../../parametres.php?error=Tous les champs sont obligatoires");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE parametre 
        SET nom_etablissement = ?, email_contact = ?
        WHERE id_parametre = 1
    ");
    $stmt->execute([$nom_etablissement, $email_contact]);

    header("Location: ../../parametres.php?success=Informations générales mises à jour");
    exit;

} else {
    header("Location: ../../parametres.php");
    exit;
}