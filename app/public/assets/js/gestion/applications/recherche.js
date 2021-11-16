$(document).ready(function(){
    var requete_en_cours = null;
    var bufferRechercheApplication = null;
    var $filtre = $('#recherche');
    Pagination.init();

    var appel_serveur = function(filtre = '', page = 1) {
        Pagination.purge();
        let tableau = '';
        $('#tableauApplications').empty().append('Chargement des données en cours...');

        requete_en_cours = $.ajax({
            url: '/ajax/gestion/applications/recherche',
            method: 'GET',
            data: {
                Saisie: filtre,
                page: page
            }
        })
        .done(function(reponse) {
            $('#tableauApplications').empty();
            Pagination.maj(reponse.pagination);
            if(reponse.pagination.total === 0) {
                $('#tableauApplications').append('<tr><td colspan="5">Aucun résultat correspondant à votre saisie n’a été trouvé</td></tr>');
            } else {
                let longueur = reponse.donnees.length;
                for (i=0;i<longueur;i++) {
                    let ligneTableau = '<tr><td><a href="/gestion/application/' +  reponse.donnees[i]['id'] + '/modification">' + reponse.donnees[i]['Libelle'] + '</a></td><td>' + reponse.donnees[i]['Domaine'] + '</td><td>' + reponse.donnees[i]['SousDomaine'] + '</td><td>' + reponse.donnees[i]['Exploitant'] + '</td><td>' + reponse.donnees[i]['MOE'] + '</td></tr>';
                    tableau = tableau + ligneTableau;
                }
                $('#tableauApplications').append(tableau);
            }
        })
        .fail(function(erreur) {
            if (erreur.status !== 0) {
                alert("Impossible d'accéder aux données pour l'instant");
            }
        });
    }

    // Recherche s il n y a pas eu de saisie depuis 500ms
    $('#recherche').on('keyup',function(){
        let recherche = $(this).val();
        if (bufferRechercheApplication !== null) {
            clearTimeout(bufferRechercheApplication);
            if(requete_en_cours) {
                requete_en_cours.abort();
            }
        }
        bufferRechercheApplication = setTimeout(function() {
            appel_serveur(recherche, 1);
        }, 500);
    })

    /**
     * Récupération des informations
     */
     appel_serveur($filtre.val());


    /**
     * Pagination
     */
     Pagination.changementDePage(function(page) {
        appel_serveur($filtre.val(), page);
    })


})
