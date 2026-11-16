<?php
require_once 'functions.php';
exiger_connexion();

$id = (int) ($_GET['id'] ?? 0);
$etudiants = lire_etudiants();

if (isset($etudiants[$id])) {
    $photo = $etudiants[$id]['photo'];
    if ($photo !== '' && file_exists(DOSSIER_PHOTOS . $photo)) {
        unlink(DOSSIER_PHOTOS . $photo);
    }
    unset($etudiants[$id]);
    sauver_etudiants($etudiants);
    message_flash('Étudiant supprimé avec succès.');
} else {
    message_flash('Étudiant introuvable.', 'erreur');
}

header('Location: liste.php');
exit;
