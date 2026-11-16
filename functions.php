<?php
session_start();

if (isset($_GET['theme']) && in_array($_GET['theme'], ['clair', 'sombre'], true)) {
    setcookie('theme', $_GET['theme'], time() + 86400 * 30, '/');
    $_COOKIE['theme'] = $_GET['theme'];
}

const FICHIER = __DIR__ . '/data/etudiants.txt';
const DOSSIER_PHOTOS = __DIR__ . '/uploads/';
const FILIERES = ['GI', 'IDIA', 'GE', 'GC'];
const UTILISATEURS = [
    'admin' => 'admin123',
    'prof' => 'ensa2026',
];

function h(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

function connecte(): bool
{
    return isset($_SESSION['login']);
}

function exiger_connexion(): void
{
    if (!connecte()) {
        header('Location: index.php');
        exit;
    }
}

function nettoyer(string $s): string
{
    $s = str_replace(';', '', $s);
    $s = str_replace(["\r", "\n"], ' ', $s);
    return trim($s);
}

function lire_etudiants(): array
{
    $etudiants = [];
    if (!file_exists(FICHIER)) {
        return $etudiants;
    }
    $lignes = file(FICHIER, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $champs = explode(';', trim($ligne));
        if (count($champs) < 6) {
            continue;
        }
        $id = (int) $champs[0];
        $etudiants[$id] = [
            'id' => $id,
            'nom' => $champs[1],
            'prenom' => $champs[2],
            'filiere' => $champs[3],
            'note' => (float) $champs[4],
            'photo' => $champs[5],
            'email' => $champs[6] ?? '',
        ];
    }
    return $etudiants;
}

function sauver_etudiants(array $liste): void
{
    $lignes = [];
    foreach ($liste as $e) {
        $lignes[] = implode(';', [$e['id'], $e['nom'], $e['prenom'], $e['filiere'], $e['note'], $e['photo'], $e['email']]);
    }
    $contenu = count($lignes) > 0 ? implode("\n", $lignes) . "\n" : '';
    file_put_contents(FICHIER, $contenu, LOCK_EX);
}

function theme_actuel(): string
{
    $theme = $_COOKIE['theme'] ?? 'clair';
    return $theme === 'sombre' ? 'sombre' : 'clair';
}

function message_flash(string $texte, string $type = 'succes'): void
{
    $_SESSION['flash'] = ['texte' => $texte, 'type' => $type];
}

function nom_majuscules(string $nom): string
{
    return mb_strtoupper($nom);
}

function prenom_capitalise(string $prenom): string
{
    return mb_strtoupper(mb_substr($prenom, 0, 1)) . mb_substr($prenom, 1);
}
