$(document).ready(function () {

    /**
     * Initialisations
     */

    let formAction = $('form').data('action');
    let $chkEnableMotdepasse = $('#user_motdepasseUpdate');
    let $txtMotdepasse = $('#user_motdepasseDisplayed');
    let $divMotdepasseStatus = $('.motdepasse-status');

    /**
     * Quasi équivalent de ucwords en Php
     *  (met toutes les lettres en minuscules puis met les premières lettres de chaque mot en majuscules, typiquement utilisé pour les prénoms)
     */
    function ucwords (str) {
        return (str + '').toLowerCase().replace(/^([a-z])|\s+([a-z])/g, function ($1) {
            return $1.toUpperCase();
        });
    }

    /**
     * Récupère le label de l'utilisateur
     */
    function getUserLabel() {
        return $('#user_prenom').val() + ' ' + $('#user_nom').val() + ' <' + $('#user_balp').val() + '>';
    }

    /**
     * Lorsque le nom, prénom ou balp sont modifiés, on reformate directement les valeurs
     */
    $('#user_prenom').on('change', function(event){
        $(this).val(ucwords($(this).val()));
    });
    $('#user_nom').on('change', function(event){
        $(this).val($(this).val().toUpperCase());
    });
    $('#user_balp').on('change', function(event){
        $(this).val($(this).val().toLowerCase());
    });

    /**
     * Active / désactive le champ motdepasse en fonction de la case à cocher
     */
    let updateMotdepasseState = function() {
        $txtMotdepasse.prop('disabled', !$chkEnableMotdepasse.is(':checked'));
    };
    $chkEnableMotdepasse.on('change', updateMotdepasseState);
    updateMotdepasseState();

    /**
     * Prévient l'utilisateur si le mot de passe est faible ou invalide
     */
    let updateMotdepasseConseil = function() {
        let status = null;
        let message = null;
        let motdepasse = $txtMotdepasse.val();
        if (motdepasse.length == 0) {
            status = 'warning';
            message = 'Si vous laissez le mot de passe vide, l\'utilisateur ne pourra pas se connecter.'
        } else if (motdepasse.length < 6 || motdepasse.length > 32) {
            status = 'danger';
            message = 'Le mot de passe doit être composé de 6 à 32 caractères.';
        }
        $divMotdepasseStatus.removeClass('text-danger').removeClass('text-warning')
        if (null === status) {
            $txtMotdepasse.css({ 'border-color': 'inherit'});
        } else if ('danger' === status) {
            $txtMotdepasse.css({ 'border-color': '#FF0000' });
            $divMotdepasseStatus.addClass('text-danger');
        } else if ('warning' === status) {
            $txtMotdepasse.css({ 'border-color': '#FFFF00' });
            $divMotdepasseStatus.addClass('text-warning');
        }
        $divMotdepasseStatus.text(message);
    };
    $txtMotdepasse.on('keyup', updateMotdepasseConseil);

    /**
     * Lorsque le formulaire est validé
     */
    $('form').on('submit', function(event) {
        // Force le champ mot de passe à vide, si l'utilisateur ne souhaite pas définir un nouveau mot de passe
        if (!$chkEnableMotdepasse.is(':checked')) {
            $txtMotdepasse.val();
        }
        // Demande confirmation aupres de l'administrateur si il créée un nouvel utilisateur sans mot de passe
        if ($txtMotdepasse.val().length == 0
                && (formAction == 'create' || (formAction == 'update' && $chkEnableMotdepasse.is(':checked'))))
        {
            if (!confirm('L\'utilisateur ' + getUserLabel() + ' n\'aura pas de mot de passe. Il ne pourra pas se connecter à SPPE. Souhaitez-vous malgré tout continuer ?')) {
                event.preventDefault();
            }
        }
    })

    /**
     * Lorsque l'on clique sur le bouton suppression, on demande à l'utilisateur de confirmer...
     */
    $('#user_actionRemove').on('click', function(event) {
        if (!confirm('Souhaitez-vous réellement supprimer l\'utilisateur  ' + getUserLabel() + ' ?')) {
            event.preventDefault();
        }
    });

});