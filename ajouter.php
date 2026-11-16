<?php
require_once 'functions.php';
exiger_connexion();

$etudiants = lire_etudiants();
$id = (int) ($_GET['id'] ?? 0);
$modification = $id > 0;

if ($modification && !isset($etudiants[$id])) {
    message_flash('Étudiant introuvable.', 'erreur');
    header('Location: liste.php');
    exit;
}

$nom = '';
$prenom = '';
$filiere = '';
$note = '';
$email = '';
$photo_actuelle = '';

if ($modification) {
    $nom = $etudiants[$id]['nom'];
    $prenom = $etudiants[$id]['prenom'];
    $filiere = $etudiants[$id]['filiere'];
    $note = (string) $etudiants[$id]['note'];
    $email = $etudiants[$id]['email'];
    $photo_actuelle = $etudiants[$id]['photo'];
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = nettoyer($_POST['nom'] ?? '');
    $prenom = nettoyer($_POST['prenom'] ?? '');
    $filiere = $_POST['filiere'] ?? '';
    $note = str_replace(',', '.', trim($_POST['note'] ?? ''));
    $email = nettoyer($_POST['email'] ?? '');
    $extension = '';
    $photo_envoyee = isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE;

    if ($nom === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if ($prenom === '') {
        $erreurs[] = 'Le prénom est obligatoire.';
    }
    if (!in_array($filiere, FILIERES, true)) {
        $erreurs[] = 'Choisissez une filière valide.';
    }
    if (!is_numeric($note) || $note < 0 || $note > 20) {
        $erreurs[] = 'La note doit être un nombre compris entre 0 et 20.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = "L'adresse email n'est pas valide.";
    }
    if ($nom !== '' && $prenom !== '') {
        foreach ($etudiants as $e) {
            if ($e['id'] !== $id && mb_strtolower($e['nom']) === mb_strtolower($nom) && mb_strtolower($e['prenom']) === mb_strtolower($prenom)) {
                $erreurs[] = 'Un étudiant portant ce nom et ce prénom existe déjà.';
                break;
            }
        }
    }

    if ($photo_envoyee) {
        $fichier = $_FILES['photo'];
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if ($fichier['error'] === UPLOAD_ERR_INI_SIZE || $fichier['error'] === UPLOAD_ERR_FORM_SIZE) {
            $erreurs[] = 'La photo dépasse la taille maximale de 1 Mo.';
        } elseif ($fichier['error'] !== UPLOAD_ERR_OK) {
            $erreurs[] = "Une erreur est survenue pendant l'envoi de la photo.";
        } else {
            if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $erreurs[] = 'La photo doit être au format JPG, JPEG ou PNG.';
            }
            if ($fichier['size'] > 1048576) {
                $erreurs[] = 'La photo dépasse la taille maximale de 1 Mo.';
            }
        }
    }

    if (count($erreurs) === 0) {
        $photo = $photo_actuelle;
        if ($photo_envoyee) {
            $nouveau_nom = uniqid('etu_') . '.' . $extension;
            if (move_uploaded_file($fichier['tmp_name'], DOSSIER_PHOTOS . $nouveau_nom)) {
                if ($photo_actuelle !== '' && file_exists(DOSSIER_PHOTOS . $photo_actuelle)) {
                    unlink(DOSSIER_PHOTOS . $photo_actuelle);
                }
                $photo = $nouveau_nom;
            } else {
                $erreurs[] = "La photo n'a pas pu être enregistrée.";
            }
        }
    }

    if (count($erreurs) === 0) {
        if (!$modification) {
            $id = count($etudiants) > 0 ? max(array_keys($etudiants)) + 1 : 1;
        }
        $etudiants[$id] = [
            'id' => $id,
            'nom' => $nom,
            'prenom' => $prenom,
            'filiere' => $filiere,
            'note' => (float) $note,
            'photo' => $photo,
            'email' => $email,
        ];
        sauver_etudiants($etudiants);
        message_flash($modification ? 'Étudiant modifié avec succès.' : 'Étudiant ajouté avec succès.');
        header('Location: liste.php');
        exit;
    }
}

$titre = $modification ? 'Modifier un étudiant' : 'Ajouter un étudiant';
include 'header.php';
?>
<div class="carte carte-formulaire">
    <h1><?= h($titre) ?></h1>

    <?php if (count($erreurs) > 0): ?>
    <div class="message erreur">
        <ul>
            <?php foreach ($erreurs as $erreur): ?>
            <li><?= h($erreur) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data">
        <label for="nom">Nom</label>
        <input type="text" id="nom" name="nom" value="<?= h($nom) ?>">

        <label for="prenom">Prénom</label>
        <input type="text" id="prenom" name="prenom" value="<?= h($prenom) ?>">

        <label for="filiere">Filière</label>
        <select id="filiere" name="filiere">
            <option value="">-- Choisir une filière --</option>
            <?php foreach (FILIERES as $f): ?>
            <option value="<?= h($f) ?>"<?= $f === $filiere ? ' selected' : '' ?>><?= h($f) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="email">Email (facultatif)</label>
        <input type="text" id="email" name="email" value="<?= h($email) ?>">

        <label for="note">Note (sur 20)</label>
        <input type="text" id="note" name="note" value="<?= h($note) ?>">

        <label for="photo">Photo (facultatif, JPG ou PNG, 1 Mo maximum)</label>
        <?php if ($photo_actuelle !== '' && file_exists(DOSSIER_PHOTOS . $photo_actuelle)): ?>
        <img class="photo photo-formulaire" src="uploads/<?= h($photo_actuelle) ?>" alt="Photo actuelle">
        <?php endif; ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="1048576">
        <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png">

        <div class="actions-formulaire">
            <button type="submit" class="bouton">Enregistrer</button>
            <a class="bouton bouton-clair" href="liste.php">Annuler</a>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>
