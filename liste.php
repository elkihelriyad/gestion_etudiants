<?php
require_once 'functions.php';
exiger_connexion();

$options_tri = [
    'ajout' => "Ordre d'ajout",
    'nom' => 'Nom',
    'filiere' => 'Filière',
    'note' => 'Note décroissante',
];

$q = trim($_GET['q'] ?? '');
$tri = $_GET['tri'] ?? 'ajout';
if (!in_array($tri, array_keys($options_tri))) {
    $tri = 'ajout';
}

$etudiants = lire_etudiants();

if ($q !== '') {
    $etudiants = array_filter($etudiants, function ($e) use ($q) {
        return stripos($e['nom'], $q) !== false || stripos($e['prenom'], $q) !== false;
    });
}

if ($tri === 'nom') {
    usort($etudiants, function ($a, $b) {
        return strcasecmp($a['nom'], $b['nom']);
    });
} elseif ($tri === 'filiere') {
    usort($etudiants, function ($a, $b) {
        return strcasecmp($a['filiere'], $b['filiere']);
    });
} elseif ($tri === 'note') {
    usort($etudiants, function ($a, $b) {
        return $b['note'] <=> $a['note'];
    });
}

$nombre = count($etudiants);
if ($nombre > 0) {
    $notes = array_column($etudiants, 'note');
    $moyenne = array_sum($notes) / $nombre;
    $meilleure = max($notes);
} else {
    $moyenne = 0;
    $meilleure = 0;
}

$notes_par_filiere = [];
foreach ($etudiants as $e) {
    $notes_par_filiere[$e['filiere']][] = $e['note'];
}
$moyennes_filiere = [];
foreach ($notes_par_filiere as $nom_filiere => $notes_filiere) {
    $moyennes_filiere[$nom_filiere] = array_sum($notes_filiere) / count($notes_filiere);
}
ksort($moyennes_filiere);

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$titre = 'Liste des étudiants';
include 'header.php';
?>
<div class="carte">
    <h1>Liste des étudiants</h1>

    <?php if ($flash !== null): ?>
    <div class="message <?= h($flash['type']) ?>"><?= h($flash['texte']) ?></div>
    <?php endif; ?>

    <form method="get" class="barre-recherche">
        <input type="text" id="recherche" name="q" placeholder="Rechercher un nom ou un prénom" value="<?= h($q) ?>">
        <select name="tri">
            <?php foreach ($options_tri as $valeur => $libelle): ?>
            <option value="<?= h($valeur) ?>"<?= $tri === $valeur ? ' selected' : '' ?>><?= h($libelle) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bouton">Appliquer</button>
    </form>

    <div class="statistiques">
        <div class="statistique">
            <span class="statistique-valeur"><?= $nombre ?></span>
            <span class="statistique-titre">Étudiants affichés</span>
        </div>
        <div class="statistique">
            <span class="statistique-valeur"><?= $nombre > 0 ? number_format($moyenne, 2) : '-' ?></span>
            <span class="statistique-titre">Moyenne des notes</span>
        </div>
        <div class="statistique">
            <span class="statistique-valeur"><?= $nombre > 0 ? number_format($meilleure, 2) : '-' ?></span>
            <span class="statistique-titre">Meilleure note</span>
        </div>
    </div>

    <?php if ($nombre > 0): ?>
    <p class="moyennes-filiere">
        <strong>Moyenne par filière :</strong>
        <?php foreach ($moyennes_filiere as $nom_filiere => $moyenne_filiere): ?>
        <span class="etiquette"><?= h($nom_filiere) ?> : <?= number_format($moyenne_filiere, 2) ?></span>
        <?php endforeach; ?>
    </p>
    <?php endif; ?>

    <div class="tableau-conteneur">
        <table>
            <thead>
                <tr>
                    <th>Photo</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Filière</th>
                    <th>Email</th>
                    <th>Note</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($nombre === 0): ?>
                <tr>
                    <td colspan="7" class="vide">Aucun étudiant</td>
                </tr>
                <?php endif; ?>
                <?php foreach ($etudiants as $e): ?>
                <?php $classe_note = $e['note'] >= 10 ? 'note-verte' : 'note-rouge'; ?>
                <tr class="ligne">
                    <td>
                        <?php if ($e['photo'] !== '' && file_exists(DOSSIER_PHOTOS . $e['photo'])): ?>
                        <img class="photo" src="uploads/<?= h($e['photo']) ?>" alt="Photo de <?= h($e['prenom']) ?>">
                        <?php else: ?>
                        <span class="photo photo-vide"><?= h(mb_strtoupper(mb_substr($e['prenom'], 0, 1))) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= h(nom_majuscules($e['nom'])) ?></td>
                    <td><?= h(prenom_capitalise($e['prenom'])) ?></td>
                    <td><?= h($e['filiere']) ?></td>
                    <td><?= $e['email'] !== '' ? h($e['email']) : '-' ?></td>
                    <td><span class="note <?= $classe_note ?>"><?= number_format($e['note'], 2) ?></span></td>
                    <td class="actions">
                        <a class="lien-modifier" href="ajouter.php?id=<?= $e['id'] ?>">Modifier</a>
                        <a class="lien-supprimer" href="supprimer.php?id=<?= $e['id'] ?>">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <p class="bas-carte">
        <a class="bouton" href="ajouter.php">Ajouter un étudiant</a>
        <a class="bouton bouton-clair" href="export.php">Exporter en CSV</a>
    </p>
</div>
<?php include 'footer.php'; ?>
