const champRecherche = document.querySelector('#recherche');
const lignes = document.querySelectorAll('tr.ligne');
const liensSupprimer = document.querySelectorAll('.lien-supprimer');

if (champRecherche) {
    champRecherche.addEventListener('input', function () {
        const texte = champRecherche.value.trim().toLowerCase();
        lignes.forEach(function (ligne) {
            const contenu = (ligne.cells[1].textContent + ' ' + ligne.cells[2].textContent).toLowerCase();
            ligne.style.display = contenu.includes(texte) ? '' : 'none';
        });
    });
}

liensSupprimer.forEach(function (lien) {
    lien.addEventListener('click', function (evenement) {
        if (!confirm('Voulez-vous vraiment supprimer cet étudiant ?')) {
            evenement.preventDefault();
        }
    });
});
