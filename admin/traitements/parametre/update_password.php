<?php
require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $ancien_mdp = $_POST['ancien_mdp'];
    $nouveau_mdp = $_POST['nouveau_mdp'];
    $confirmer_mdp = $_POST['confirmer_mdp'];

    // Récupérer l'admin (id 1 pour l'instant)
    $stmt = $conn->prepare("SELECT mot_de_passe FROM admin WHERE id_admin = 1");
    $stmt->execute();
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifier l'ancien mot de passe
    if (!password_verify($ancien_mdp, $admin['mot_de_passe'])) {
        header("Location: ../../parametres.php?error=Ancien mot de passe incorrect");
        exit;
    }

    // Vérifier que les nouveaux mots de passe correspondent
    if ($nouveau_mdp !== $confirmer_mdp) {
        header("Location: ../../parametres.php?error=Les nouveaux mots de passe ne correspondent pas");
        exit;
    }

    // Vérifier la longueur minimale
    if (strlen($nouveau_mdp) < 6) {
        header("Location: ../../parametres.php?error=Le mot de passe doit contenir au moins 6 caractères");
        exit;
    }

    // Hasher et mettre à jour
    $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE admin SET mot_de_passe = ? WHERE id_admin = 1");
    $stmt->execute([$hash]);

    header("Location: ../../parametres.php?success=Mot de passe modifié avec succès");
    exit;

} else {
    header("Location: ../../parametres.php");
    exit;
}