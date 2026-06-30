<?php
require_once __DIR__ . "/../../../include/db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    die("Accès refusé");
}

 $id_ticket = $_POST['id_ticket'];
 $nom_complet = trim($_POST['nom_complet'] ?? '');
 $statut = $_POST['statut'];
 $id_service = $_POST['id_service'];
 $priorite = $_POST['priorite'];

/* Vérifier que le ticket existe */
 $stmt = $conn->prepare("SELECT id_ticket, id_usager FROM ticket WHERE id_ticket = ?");
 $stmt->execute([$id_ticket]);

if ($stmt->fetchColumn() == 0) {
    header("Location: ../../gestion_tickets.php?error=Ticket introuvable");
    exit;
}

 $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

/* SÉCURITÉ : Empêcher de modifier un ticket "En cours" par un autre moyen */
if ($ticket['statut'] === 'En cours' && $statut !== 'Traité' && $statut !== 'Annulé') {
    header("Location: ../../gestion_tickets.php?error=Impossible de modifier un ticket en cours");
    exit();
}

/* Vérifier que le service existe */
 $stmt = $conn->prepare("SELECT COUNT(*) FROM service WHERE id_service = ?");
 $stmt->execute([$id_service]);

if ($stmt->fetchColumn() == 0) {
    header("Location: ../../gestion_tickets.php?error=Service introuvable");
    exit();
}

/* Mise à jour du ticket et du nom de l'usager si modifié */
 $stmt = $conn->prepare("
    UPDATE ticket
    SET statut = ?,
        id_service = ?,
        priorite = ?
    WHERE id_ticket = ?
");
 $stmt->execute([$statut, $id_service, $priorite, $id_ticket]);

/* Si l'admin a modifié le nom, on met aussi à jour la table usager */
if (!empty($nom_complet)) {
    $stmt = $conn->prepare("UPDATE usager SET nom_complet = ? WHERE id_usager = ?");
    $stmt->execute([$nom_complet, $ticket['id_usager']]);
}

header("Location: ../../gestion_tickets.php?updated=1");
exit;