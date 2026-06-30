<?php
require_once __DIR__ . "/../../../include/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Accès refusé");
}

 $nom_complet = trim($_POST['nom_complet']);
 $telephone = trim($_POST['telephone'] ?? ''); // AJOUT : Récupération du téléphone
 $id_service = $_POST['id_service'];
 $priorite = $_POST['priorite'] ?? "Normale";
 $description = trim($_POST['description'] ?? "");

/* Vérifications */
if (empty($nom_complet) || empty($telephone)) {
    header("Location: ../../gestion_tickets.php?error=Le nom et le téléphone sont requis");
    exit;
}

if (empty($id_service)) {
    header("Location: ../../gestion_tickets.php?error=Veuillez choisir un service");
    exit;
}

/* Vérifier que le service existe */
 $stmt = $conn->prepare("SELECT COUNT(*) FROM service WHERE id_service = ?");
 $stmt->execute([$id_service]);

if ($stmt->fetchColumn() == 0) {
    header("Location: ../../gestion_tickets.php?error=Service introuvable");
    exit;
}

/* CORRECTION : Vérifier si l'usager existe déjà via son téléphone */
 $stmt = $conn->prepare("SELECT id_usager FROM usager WHERE telephone = ?");
 $stmt->execute([$telephone]);
 $usager = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usager) {
    $id_usager = $usager['id_usager'];
} else {
    // Créer l'usager seulement s'il n'existe pas
    $stmt = $conn->prepare("INSERT INTO usager (nom_complet, telephone, date_creation) VALUES (?, ?, CURDATE())");
    $stmt->execute([$nom_complet, $telephone]);
    $id_usager = $conn->lastInsertId();
}

/* Prefixe selon le service */
 $prefixes = [
    1 => 'C',   // Consultation
    2 => 'L',   // Laboratoire
    3 => 'P'    // Pédiatrie
];
 $prefix = isset($prefixes[$id_service]) ? $prefixes[$id_service] : 'X';

/* CORRECTION : Compter les tickets DU JOUR pour le numéro */
 $stmt = $conn->prepare("
    SELECT COUNT(*) FROM ticket 
    WHERE id_service = ? AND date_creation = CURDATE()
");
 $stmt->execute([$id_service]);
 $count = $stmt->fetchColumn();

/* Numéro du ticket */
 $numero = $prefix . str_pad($count + 1, 3, "0", STR_PAD_LEFT);

/* CORRECTION : On garde 'Urgence' (ne pas 'Prioritaire') */
/* Insertion ticket */
 $stmt = $conn->prepare("
    INSERT INTO ticket (
        numero_ticket,
        priorite,
        statut,
        date_creation,
        heure_creation,
        id_usager,
        id_service,
        description
    ) VALUES (
        ?, ?, 'En attente', CURDATE(), CURTIME(), ?, ?, ?
    )
");

 $stmt->execute([
    $numero,
    $priorite, 
    $id_usager,
    $id_service,
    $description
]);

header("Location: ../../gestion_tickets.php?updated=1");
exit;