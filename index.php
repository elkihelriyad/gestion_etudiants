<?php
require_once 'functions.php';

if (connecte()) {
    header('Location: liste.php');
    exit;
}

$erreur = '';
$login = $_COOKIE['dernier_user'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $mdp = $_POST['mdp'] ?? '';

    if (isset(UTILISATEURS[$login]) && UTILISATEURS[$login] === $mdp) {
        $_SESSION['login'] = $login;
        setcookie('dernier_user', $login, time() + 86400 * 7, '/');
        header('Location: liste.php');
        exit;
    }
    $erreur = 'Login ou mot de passe incorrect.';
}

$titre = 'Connexion';
include 'header.php';
?>
<div class="carte carte-formulaire carte-connexion">
    <h1>Connexion</h1>
    <?php if ($erreur !== ''): ?>
    <div class="message erreur"><?= h($erreur) ?></div>
    <?php endif; ?>
    <form method="post">
        <label for="login">Login</label>
        <input type="text" id="login" name="login" value="<?= h($login) ?>">

        <label for="mdp">Mot de passe</label>
        <input type="password" id="mdp" name="mdp">

        <div class="actions-formulaire">
            <button type="submit" class="bouton">Se connecter</button>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>
