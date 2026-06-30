<?php
session_start();
require_once "../include/db.php";

if (!isset($_SESSION['type']) || $_SESSION['type'] !== "agent") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: profil_agent.php");
    exit();
}

 $id_agent = $_SESSION['id_agent'];
 $ancien_mdp = $_POST['ancien_mdp'] ?? '';
 $nouveau_mdp = $_POST['nouveau_mdp'] ?? '';
 $confirmer_mdp = $_POST['confirmer_mdp'] ?? '';

if (empty($ancien_mdp) || empty($nouveau_mdp) || empty($confirmer_mdp)) {
    $_SESSION['error_agent'] = "Tous les champs sont obligatoires.";
    header("Location: profil_agent.php");
    exit();
}

// CORRECTION : Vérifier la longueur du nouveau mot de passe
if (strlen($nouveau_mdp) < 6) {
    $_SESSION['error_agent'] = "Le nouveau mot de passe doit contenir au moins 6 caractères.";
    header("Location: profil_agent.php");
    exit();
}

// Vérifier l'ancien mot de passe
 $stmt = $conn->prepare("SELECT mot_de_passe FROM agent WHERE id_agent = ?");
 $stmt->execute([$id_agent]);
 $agent = $stmt->fetch(PDO::FETCH_ASSOC);

if (!password_verify($ancien_mdp, $agent['mot_de_passe'])) {
    $_SESSION['error_agent'] = "L'ancien mot de passe est incorrect.";
    header("Location: profil_agent.php");
    exit();
}

// Vérifier que les nouveaux mots de passe correspondent
if ($nouveau_mdp !== $confirmer_mdp) {
    $_SESSION['error_agent'] = "Les nouveaux mots de passe ne correspondent pas.";
    header("Location: profil_agent.php");
    exit();
}

// Hasher et mettre à jour
 $hash = password_hash($nouveau_mdp, PASSWORD_DEFAULT);
 $stmt = $conn->prepare("UPDATE agent SET mot_de_passe = ? WHERE id_agent = ?");
 $stmt->execute([$hash, $id_agent]);

 $_SESSION['success_agent'] = "Mot de passe modifié avec succès.";
header("Location: profil_agent.php");
exit();