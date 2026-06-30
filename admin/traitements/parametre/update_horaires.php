<?php
require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $heure_ouverture = $_POST['heure_ouverture'];
    $heure_fermeture = $_POST['heure_fermeture'];

    if (empty($heure_ouverture) || empty($heure_fermeture)) {
        header("Location: ../../parametres.php?error=Les horaires sont obligatoires");
        exit;
    }

    $stmt = $conn->prepare("
        UPDATE parametre 
        SET heure_ouverture = ?, heure_fermeture = ?
        WHERE id_parametre = 1
    ");
    $stmt->execute([$heure_ouverture, $heure_fermeture]);

    header("Location: ../../parametres.php?success=Horaires d'ouverture mis à jour");
    exit;

} else {
    header("Location: ../../parametres.php");
    exit;
}