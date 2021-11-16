$(document).ready(function () {

    /**
     * Initialisation
     */

    // Périmètre applicatif
    let $modalPerimetreApplicatif = $('#serviceApplicationsModal');
    let $formControlPerimetreApplicatif = $('#service_perimetreApplicatif');
    let $perimetreApplicatifTableBody = $('#liste-resultats-applications');
    let $perimetreApplicatifSelection = $('#selectionApplication');

    // Périmètre utilisateur
    let $modalUtilisateurs = $('#serviceUsersModal');
    let $formControlUtilisateurs = $('#service_users');
    let $utilisateursTableBody = $('#liste-resultats-users');
    let $utilisateursSelection = $('#selectionUser');


    /**
     * Gestion de la modale de gestion des applications associées au service
     */

    // Initialisation et affichage de la modale lors du clic sur le bouton
    $('#majServiceApplications').on('click', function() {
        // Défini le titre de la modale
        let serviceLabelVal = $('#service_label').val();
        let $titreModale = $modalPerimetreApplicatif.find('.modal-header h5').empty();
        $titreModale.append('<span>Périmètre applicatif du service \"' + serviceLabelVal + '\"</span>');
        // Affiche la modale et affiche ou cache les lignes à partir des applications actuellement sélectionnées
        $modalPerimetreApplicatif.modal({backdrop: 'static', keyboard: false});
        $perimetreApplicatifTableBody.find('tr').hide();
        $perimetreApplicatifSelection.find('option').show();
        $perimetreApplicatifSelection.find('option:selected').prop('selected', false);
        $formControlPerimetreApplicatif.find('option:selected').each(function() {
            let applicationId = $(this).val();
            $perimetreApplicatifTableBody.find('tr[data-application-id="' + applicationId + '"]').show();
            $perimetreApplicatifSelection.find('option[value="' + applicationId + '"]').hide();
        });
        $perimetreApplicatifSelection.selectpicker('refresh');
    });
    
    // Lorsqu'on souhaite ajouter une application
    $('#btn-ajouter-application').on('click', function() {
        let applicationId = $('#selectionApplication option:selected').val();
        $perimetreApplicatifTableBody.find('tr[data-application-id="' + applicationId + '"]').show();
        $perimetreApplicatifSelection.find('option[value="' + applicationId + '"]').hide();
        $perimetreApplicatifSelection.find('option:selected').prop('selected', false);
        $perimetreApplicatifSelection.selectpicker('refresh');
    });

    // Lorsqu'on souhaite retirer une application
    $('#btn-retirer-application').on('click', function() {
        if ($perimetreApplicatifTableBody.find('tr :checked').length <= 0) {
            window.afficherToast('Veuillez sélectionner au moins une application', 'danger');
        } else {
            if (confirm('Confirmez-vous la désassociation de ces applications ?')) {
                $perimetreApplicatifTableBody.find(':checked').each(function() {
                    let applicationId = $(this).parents('tr').data('application-id');
                    $perimetreApplicatifSelection.find('option[value="' + applicationId + '"]').show();
                    $(this).parents('tr').hide();
                });
                $perimetreApplicatifSelection.find('option:selected').prop('selected', false);
                $perimetreApplicatifSelection.selectpicker('refresh');
                $('.checkall').prop('checked', null);
                $('.checkall-box').prop('checked', null);
            }
        }
    });

    // Si on valide les modifications effectuées dans la modale, on affecte le controle associé
    $modalPerimetreApplicatif.find('.validation').on('click', function() {
        $formControlPerimetreApplicatif.find('option').prop('selected', false);
        $perimetreApplicatifTableBody.find('tr:visible').each(function(){
            let applicationId = $(this).data('application-id');
            $formControlPerimetreApplicatif.find('option[value="' + applicationId + '"]').prop('selected', true);
        });
    });


    /**
     * Gestion de la modale de gestion des utilisateurs associés au service
     */

    // Initialisation et affichage de la modale lors du clic sur le bouton
    $('#majServiceUsers').on('click', function() {
        // Défini le titre de la modale
        let serviceLabelVal = $('#service_label').val();
        let $titreModale = $modalUtilisateurs.find('.modal-header h5').empty();
        $titreModale.append('<span>Utilisateurs composant le service \"' + serviceLabelVal + '\"</span>');
        // Affiche la modale et affiche ou cache les lignes à partir des applications actuellement sélectionnées
        $modalUtilisateurs.modal({backdrop: 'static', keyboard: false});
        $utilisateursTableBody.find('tr').hide();
        $utilisateursSelection.find('option').show();
        $utilisateursSelection.find('option:selected').prop('selected', false);
        $formControlUtilisateurs.find('option:selected').each(function() {
            let userId = $(this).val();
            $utilisateursTableBody.find('tr[data-user-id="' + userId + '"]').show();
            $utilisateursSelection.find('option[value="' + userId + '"]').hide();
        });
        $utilisateursSelection.selectpicker('refresh');
    });

    // Lorsqu'on souhaite ajouter une application
    $('#btn-ajouter-user').on('click', function() {
        let userId = $('#selectionUser option:selected').val();
        $utilisateursTableBody.find('tr[data-user-id="' + userId + '"]').show();
        $utilisateursSelection.find('option[value="' + userId + '"]').hide();
        $utilisateursSelection.find('option:selected').prop('selected', false);
        $utilisateursSelection.selectpicker('refresh');
    });

    // Lorsqu'on souhaite retirer une application
    $('#btn-retirer-user').on('click', function() {
        if ($utilisateursTableBody.find('tr :checked').length <= 0) {
            window.afficherToast('Veuillez sélectionner au moins un utilisateur', 'danger');
        } else {
            if (confirm('Confirmez-vous la désassociation de ces utilisateurs ?')) {
                $utilisateursTableBody.find(':checked').each(function() {
                    let userId = $(this).parents('tr').data('user-id');
                    $utilisateursSelection.find('option[value="' + userId + '"]').show();
                    $(this).parents('tr').hide();
                });
                $utilisateursSelection.find('option:selected').prop('selected', false);
                $utilisateursSelection.selectpicker('refresh');
                $('.checkall').prop('checked', null);
                $('.checkall-box').prop('checked', null);
            }
        }
    });

    // Si on valide les modifications effectuées dans la modale, on affecte le controle associé
    $modalUtilisateurs.find('.validation').on('click', function() {
        $formControlUtilisateurs.find('option').prop('selected', false);
        $utilisateursTableBody.find('tr:visible').each(function(){
            let userId = $(this).data('user-id');
            $formControlUtilisateurs.find('option[value="' + userId + '"]').prop('selected', true);
        });
    });


    /*
     * Suppression du service (lors d'un clic sur le bouton suppression d'un service)
     */

    $('.suppression-service').on('click', function () {
        var serviceId = $('#btSupprimerService').attr('data-service-id');
        window.location = '/gestion/service/' + serviceId + '/suppression';
     });

});
