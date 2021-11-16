$(document).ready(function(){
    // Mémorisation de la liste des sous-domaines
    var $listeSousDomaines = $('#application_sousDomaine').html();
    // Affichage des sous-domaines correspondants au domaine
    let classeDomaineSelectionne = "."+($('#domaine option:selected').data('id'));
    $(classeDomaineSelectionne).toggleClass('d-none');

    // Affichage des sous-domaines correspondant au domaine sélectionné
    $('#domaine').on('change',function(){
        let classeDomaineSelectionne = "."+($('#domaine option:selected').data('id'));
        // On efface l'éventuelle sélection antérieure et on ajoute la liste complète des sous-domaines ainsi que la sélection
        $('#application_sousDomaine').empty();
        $('#application_sousDomaine').append($listeSousDomaines);
        $('#application_sousDomaine').find("option:selected").removeAttr("selected");
        // On rend visible les sous-domaines correspondant au domaine sélectionné
        $(classeDomaineSelectionne).toggleClass('d-none');
    })

    // Demande de confirmation à la suppression
    $('#suppression').on('click', function(e){
        if (!confirm('Voulez-vous vraiment supprimer cette application?')) {
            e.preventDefault();
        }
    });

    // Traitement à la validation
    $(document).on("submit", "form", function(e){
        // Transformation en majuscules pour que le contrôle de doublon soit fait
        libelleMajuscule = $('#application_label').val().toUpperCase();
        $('#application_label').val(libelleMajuscule);
    });
});
