<?php
require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../gestion_agents.php");
    exit;
}

 $nom = trim($_POST['nom']);
 $prenom = trim($_POST['prenom']);
 $email = trim($_POST['email']);
 $mot_de_passe = $_POST['mot_de_passe'];
 $id_service = $_POST['id_service'];
 $photo = null;

/* Vérification du mot de passe */
if (strlen($mot_de_passe) < 6) {
    header("Location: ../../gestion_agents.php?error=Le mot de passe doit avoir au moins 6 caractères");
    exit;
}

/* Vérification service */
if (empty($id_service)) {
    header("Location: ../../gestion_agents.php?error=Veuillez choisir un service");
    exit;
}

/* Vérification email unique */
 $stmt = $conn->prepare("SELECT COUNT(*) FROM agent WHERE email = ?");
 $stmt->execute([$email]);

if ($stmt->fetchColumn() > 0) {
    header("Location: ../../gestion_agents.php?error=Cet email est déjà utilisé");
    exit;
}

/* Traitement photo */
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {

    $tmpName = $_FILES['photo']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed)) {

        $newName = 'agent_' . time() . '.' . $ext;
        $destination = '../../../assets/images/profiles/' . $newName;

        if (move_uploaded_file($tmpName, $destination)) {
            $photo = $newName;
        }
    }
}

/* Hashage du mot de passe */
 $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

/* Insertion */
 $stmt = $conn->prepare("
    INSERT INTO agent (nom, prenom, email, mot_de_passe, id_service, photo, statut)
    VALUES (?, ?, ?, ?, ?, ?, 'Actif')
");

 $stmt->execute([
    $nom,
    $prenom,
    $email,
    $mot_de_passe_hash,
    $id_service,
    $photo
]);

header("Location: ../../gestion_agents.php?success=Agent ajouté avec succès");
exit;