<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($titre ?? 'Gestion des étudiants') ?> - Gestion des étudiants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="theme-<?= theme_actuel() ?>">
    <header class="entete">
        <div class="entete-contenu">
            <a class="logo" href="<?= connecte() ? 'liste.php' : 'index.php' ?>">Gestion des étudiants</a>
            <nav class="menu">
                <?php if (connecte()): ?>
                <a href="liste.php">Liste</a>
                <a href="ajouter.php">Ajouter</a>
                <span class="utilisateur"><?= h($_SESSION['login']) ?></span>
                <a href="logout.php">Déconnexion</a>
                <?php endif; ?>
                <a class="lien-theme" href="?<?= h(http_build_query(array_merge($_GET, ['theme' => theme_actuel() === 'sombre' ? 'clair' : 'sombre']))) ?>"><?= theme_actuel() === 'sombre' ? 'Thème clair' : 'Thème sombre' ?></a>
            </nav>
        </div>
    </header>
    <main class="contenu">
