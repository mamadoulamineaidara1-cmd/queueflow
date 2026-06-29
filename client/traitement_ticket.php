<?php
require_once "../include/db.php";

// Sécurité : vérifier que c'est bien un POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: accueil.php");
    exit();
}

// ===== ÉTAPE 1 : Récupérer les données du formulaire =====
 $nom_complet = trim($_POST['nom_complet'] ?? '');
 $telephone = trim($_POST['telephone'] ?? '');
 $id_service = $_POST['id_service'] ?? null;

if (empty($nom_complet) || empty($telephone) || empty($id_service)) {
    header("Location: prendre_ticket.php?error=Veuillez remplir tous les champs.");
    exit();
}

// ===== SÉCURITÉ : Vérifier que le service fait partie des 3 autorisés =====
 $prefixes = [
    1 => 'C', // Consultation
    2 => 'L', // Laboratoire
    3 => 'P'  // Pédiatrie
];

if (!isset($prefixes[$id_service])) {
    header("Location: prendre_ticket.php?error=Service invalide.");
    exit();
}

 $prefixe = $prefixes[$id_service];

// ===== ÉTAPE 2 : Vérifier si l'usager existe déjà =====
 $stmt = $conn->prepare("SELECT id_usager FROM usager WHERE telephone = ?");
 $stmt->execute([$telephone]);
 $usager = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usager) {
    $id_usager = $usager['id_usager'];
} else {
    $stmt = $conn->prepare("INSERT INTO usager (nom_complet, telephone, date_creation) VALUES (?, ?, CURDATE())");
    $stmt->execute([$nom_complet, $telephone]);
    $id_usager = $conn->lastInsertId();
}

// ===== SÉCURITÉ ANTI-DUPLICATION : Vérifier si un ticket existe déjà aujourd'hui pour ce service =====
 $stmt = $conn->prepare("
    SELECT id_ticket 
    FROM ticket 
    WHERE id_usager = ? AND id_service = ? AND date_creation = CURDATE() AND statut IN ('En attente', 'En cours')
");
 $stmt->execute([$id_usager, $id_service]);
 $ticketExistant = $stmt->fetch(PDO::FETCH_ASSOC);

// Si un ticket existe déjà, on redirige directement vers celui-ci (qu'il ait été créé par le client ou l'admin)
if ($ticketExistant) {
    header("Location: ticket_genere.php?id_ticket=" . $ticketExistant['id_ticket']);
    exit();
}

// ===== ÉTAPE 3 : Générer le numéro du ticket =====
 $stmt = $conn->prepare("
    SELECT numero_ticket 
    FROM ticket 
    WHERE id_service = ? AND date_creation = CURDATE() 
    ORDER BY id_ticket DESC 
    LIMIT 1
");
 $stmt->execute([$id_service]);
 $dernierTicket = $stmt->fetch(PDO::FETCH_ASSOC);

if ($dernierTicket) {
    $numero = intval(substr($dernierTicket['numero_ticket'], 1));
    $numero++;
} else {
    $numero = 1;
}

 $numero_ticket = $prefixe . str_pad($numero, 3, '0', STR_PAD_LEFT);

// ===== ÉTAPE 4 : Insérer dans la table ticket =====
 $stmt = $conn->prepare("
    INSERT INTO ticket (numero_ticket, priorite, statut, date_creation, heure_creation, id_usager, id_service, id_agent)
    VALUES (?, 'Normale', 'En attente', CURDATE(), CURTIME(), ?, ?, NULL)
");
 $stmt->execute([$numero_ticket, $id_usager, $id_service]);

 $id_ticket = $conn->lastInsertId();

// ===== ÉTAPE 5 : Rediriger vers la page du ticket généré =====
header("Location: ticket_genere.php?id_ticket=" . $id_ticket);
exit();