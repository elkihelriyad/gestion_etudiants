<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = lire_etudiants();

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="etudiants.csv"');

echo "\xEF\xBB\xBF";
$sortie = fopen('php://output', 'w');
fputcsv($sortie, ['id', 'nom', 'prenom', 'filiere', 'note', 'photo', 'email'], ';', '"', '');
foreach ($etudiants as $e) {
    fputcsv($sortie, [$e['id'], $e['nom'], $e['prenom'], $e['filiere'], $e['note'], $e['photo'], $e['email']], ';', '"', '');
}
fclose($sortie);
