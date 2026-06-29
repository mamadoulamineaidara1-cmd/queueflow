<?php
session_start();
require "../include/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

 $email = trim($_POST['email'] ?? '');
 $password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Tous les champs sont obligatoires";
    header("Location: login.php");
    exit();
}

// ============ ADMIN ============
 $sql = "SELECT * FROM admin WHERE email = ?";
 $stmt = $conn->prepare($sql);
 $stmt->execute([$email]);
 $admin = $stmt->fetch(PDO::FETCH_ASSOC);

if ($admin && password_verify($password, $admin['mot_de_passe'])) {

    $_SESSION['user'] = $admin;
    $_SESSION['type'] = "admin";

    header("Location: ../admin/dashboard.php");
    exit();
}

// ============ AGENT ============
 $sql = "SELECT * FROM agent WHERE email = ?";
 $stmt = $conn->prepare($sql);
 $stmt->execute([$email]);
 $agent = $stmt->fetch(PDO::FETCH_ASSOC);

if ($agent) {

    // Vérifier si le compte est actif
    if ($agent['statut'] === "Inactif") {
        $_SESSION['error'] = "Compte désactivé";
        header("Location: login.php");
        exit();
    }

    // Vérifier le mot de passe
    if (password_verify($password, $agent['mot_de_passe'])) {

        $_SESSION['user'] = $agent;
        $_SESSION['type'] = "agent";
        $_SESSION['id_agent'] = $agent['id_agent'];
        $_SESSION['id_service'] = $agent['id_service'];

        header("Location: ../agent/dashboard_agent.php");
        exit();
    }
}

// ============ ERREUR ============
 $_SESSION['error'] = "Email ou mot de passe incorrect";
header("Location: login.php");
exit();