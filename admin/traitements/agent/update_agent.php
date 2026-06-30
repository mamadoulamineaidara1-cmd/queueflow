<?php
require_once "../../../include/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../gestion_agents.php");
    exit;
}

 $id_agent = $_POST['id_agent'];
 $nom = trim($_POST['nom']);
 $prenom = trim($_POST['prenom']);
 $email = trim($_POST['email']);
 $id_service = $_POST['id_service'];
 $statut = $_POST['statut'];
 $photo = null;

/* Vérification email unique (sauf lui-même) */
 $stmt = $conn->prepare("SELECT COUNT(*) FROM agent WHERE email = ? AND id_agent != ?");
 $stmt->execute([$email, $id_agent]);

if ($stmt->fetchColumn() > 0) {
    header("Location: ../../gestion_agents.php?error=Cet email est déjà utilisé par un autre agent");
    exit;
}

/* Traitement photo */
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {

    $tmpName = $_FILES['photo']['tmp_name'];
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    if (in_array($ext, $allowed)) {

        $newName = 'agent_' . $id_agent . '_' . time() . '.' . $ext;
        $destination = '../../../assets/images/profiles/' . $newName;

        if (move_uploaded_file($tmpName, $destination)) {
            $photo = $newName;
        }
    }
}

/* Mise à jour */
 $stmt = $conn->prepare("
    UPDATE agent
    SET nom = ?,
        prenom = ?,
        email = ?,
        id_service = ?,
        statut = ?,
        photo = COALESCE(?, photo)
    WHERE id_agent = ?
");

 $stmt->execute([
    $nom,
    $prenom,
    $email,
    $id_service,
    $statut,
    $photo,
    $id_agent
]);

header("Location: ../../gestion_agents.php?success=Agent modifié avec succès");
exit;