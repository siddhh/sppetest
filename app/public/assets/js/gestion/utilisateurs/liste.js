/**
 * Listing des utilisateurs
 */
 $(document).ready(function () {

    /**
     * Initialisation
     */
    var url_donnees = '/ajax/gestion/utilisateurs/recherche';
    var $tableau = $('#tbUsers')
    var $donnees = $tableau.find('tbody');
    var $loading = $tableau.find('.table-loading');
    var $vide = $tableau.find('.table-empty');
    var $filtre = $('.search-filter');
    var requete_en_cours = null;
    Pagination.init();

    /**
     * Méthodes utiles
     */

    // Ajout d'un utilisateur dans le tableau de résultats
    var tableau_ajout_utilisateur = function(user) {
        let servicesLabels = [];
        for(const iUsers in user.services) {
            servicesLabels.push(user.services[iUsers].label);
        }
        $donnees.append(
            $('<tr class="item"></tr>').append(
                '<td data-user-id="' + user.id + '"><a href="/gestion/utilisateur/' + user.id + '/modification">' + user.prenom + ' ' + user.nom + '</a></td>',
                '<td><a href="mailto:' + user.balp + '">' + user.balp + '</a></td>',
                '<td>' + servicesLabels.join(', ') + '</td>',
                '<td class="text-center"><a href="/?_switch_user=' + user.login + '" class="btn btn-primary"><i class="fa fa-address-card"></i></a></td>'
            )
        );
    };
    // Purge des utilisateurs dans le tableau de résultats
    var tableau_purge = function() {
        $donnees.find('tr.item').remove();
    };
    // Permet de faire un appel serveur
    var appel_serveur = function(filtre = '', page = 1) {
        Pagination.purge();
        $vide.hide();
        $loading.show();
        tableau_purge();
        requete_en_cours = $.ajax({
                url: url_donnees + "?search=" + encodeURIComponent(filtre) + '&supprimeLe=0&page=' + page,
                method: 'GET'
            })
            .done(function(reponse) {
                Pagination.maj(reponse.pagination);
                if(reponse.pagination.total === 0) {
                    $vide.show();
                } else {
                    for(var i = 0 ; i < reponse.donnees.length ; i++) {
                        tableau_ajout_utilisateur(reponse.donnees[i]);
                    }
                }
            })
            .fail(function(erreur) {
                if(erreur.status != 0) {
                    alert("Impossible de récupérer les données pour l'instant.");
                }
            })
            .always(function() {
                $loading.hide();
            });
    };

    /**
     * Récupération des informations
     */
    appel_serveur($filtre.val());

    /**
     * Filtrage
     */
    $filtre.on('keyup', function() {
        if(requete_en_cours) {
            requete_en_cours.abort();
        }
        appel_serveur($(this).val(), 1);
    });

    /**
     * Pagination
     */
    Pagination.changementDePage(function(page) {
        appel_serveur($filtre.val(), page);
    });

});