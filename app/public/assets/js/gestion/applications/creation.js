$(document).ready(function(){
    // Mémorisation de la liste des sous-domaines
    var $listeSousDomaines = $('#application_sousDomaine').html();

    // Affichage des sous-domaines correspondant au domaine sélectionné
    $('#domaine').on('change',function(){
        let classeDomaineSelectionne = "."+($('#domaine option:selected').data('id'));
        // On efface l'éventuelle sélection antérieure et on ajoute la liste complète des sous-domaines
        $('#application_sousDomaine').empty().append($listeSousDomaines);
        // On rend visible les sous-domaines correspondant au domaine sélectionné
        $(classeDomaineSelectionne).toggleClass('d-none');
    })

    $(document).on("submit", "form", function(e){
        // Transformation en majuscules pour que le contrôle de doublon soit fait
        libelleMajuscule = $('#application_label').val().toUpperCase();
        $('#application_label').val(libelleMajuscule);
    });
});
