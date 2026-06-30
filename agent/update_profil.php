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
 $nom = trim($_POST['nom'] ?? '');
 $prenom = trim($_POST['prenom'] ?? '');
 $email = trim($_POST['email'] ?? '');

if (empty($nom) || empty($prenom) || empty($email)) {
    $_SESSION['error_agent'] = "Tous les champs sont obligatoires.";
    header("Location: profil_agent.php");
    exit();
}

// Vérifier si l'email n'est pas déjà pris par un autre agent
 $stmt = $conn->prepare("SELECT id_agent FROM agent WHERE email = ? AND id_agent != ?");
 $stmt->execute([$email, $id_agent]);
if ($stmt->fetch()) {
    $_SESSION['error_agent'] = "Cet email est déjà utilisé par un autre compte.";
    header("Location: profil_agent.php");
    exit();
}

// Récupérer la photo actuelle depuis la base
 $stmt = $conn->prepare("SELECT photo FROM agent WHERE id_agent = ?");
 $stmt->execute([$id_agent]);
 $currentAgent = $stmt->fetch(PDO::FETCH_ASSOC);
 $photo = $currentAgent['photo'] ?? 'default-agent.png';

// Gestion de l'upload de la nouvelle photo
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $fileInfo = pathinfo($_FILES['photo']['name']);
    $ext = strtolower($fileInfo['extension']);

    if (in_array($ext, $allowed)) {
        $newName = 'agent_' . $id_agent . '.' . $ext;
        
        // CORRECTION : Ajout du dossier /profiles/ dans la destination
        $dest = '../assets/images/profiles/' . $newName;

        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            $photo = $newName;
        }
    } else {
        $_SESSION['error_agent'] = "Format d'image invalide (jpg, png, gif uniquement).";
        header("Location: profil_agent.php");
        exit();
    }
}

// Mise à jour en base
 $stmt = $conn->prepare("
    UPDATE agent 
    SET nom = ?, prenom = ?, email = ?, photo = ? 
    WHERE id_agent = ?
");
 $stmt->execute([$nom, $prenom, $email, $photo, $id_agent]);

// SUPPRESSION de la mise à jour de $_SESSION['user'] car on lit tout depuis la DB maintenant

 $_SESSION['success_agent'] = "Profil mis à jour avec succès.";
header("Location: profil_agent.php");
exit();