<?php
require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Si la checkbox est cochée, elle envoie "Oui". Sinon elle n'existe pas.
    $email = isset($_POST['notification_email']) ? 'Oui' : 'Non';
    $sms = isset($_POST['notification_sms']) ? 'Oui' : 'Non';

    $stmt = $conn->prepare("
        UPDATE parametre 
        SET notification_email = ?, notification_sms = ?
        WHERE id_parametre = 1
    ");
    $stmt->execute([$email, $sms]);

    header("Location: ../../parametres.php?success=Notifications mises à jour");
    exit;

} else {
    header("Location: ../../parametres.php");
    exit;
}