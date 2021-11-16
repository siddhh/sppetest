$(document).ready(function () {

    /**
     * initialisation de variables
     */
     var $table = $('#table-domaines');
     var $tableBody = $table.find('tbody');
     var $confirmationAnnulationModal = $('#confirmationAnnulationModal');
     var $confirmationSuppressionModal = $('#confirmationSuppressionModal');
     var $erreurServeurModal = $('#erreurServeurModal');
     var $displayEntry = null;
     var $editEntry = null;
     var $parentEntry = null;
     var label = '';

    /**
     * Objet JS permettant de gérer les appels serveurs d'ajout / modfication / suppression
     */
    var API = {
        baseUrl: '/ajax/gestion/references/domaines',
        appelServeur: function(url, method, data, success, always) {
            $.ajax({
                url: url,
                method: method,
                transformRequest: function(obj) {
                    var str = [];
                    for(var p in obj)
                        str.push(encodeURIComponent(p) + '=' + encodeURIComponent(obj[p]));
                    return str.join('&');
                },
                data: data,
                complete: always,
                success: success,
                error: function(xhr) {
                    if (xhr.status === 422) {
                        var data = JSON.parse(xhr.responseText);
                        $erreurServeurModal.find('.modal-body p').html(data.errors.pop());
                        $erreurServeurModal.modal('show');
                    } else {
                        alert("Impossible d'effectuer cette opération pour le moment.")
                    }
                }
            });
        },
        ajout: function(data, done, always) {
            this.appelServeur(API.baseUrl, 'POST', data, done, always);
        },
        modification: function(data, done, always) {
            this.appelServeur(API.baseUrl + "/" + data['domaine[id]'], 'PUT', {'domaine[label]': data['domaine[label]']}, done, always);
        },
        suppression: function(data, done, always) {
            this.appelServeur(API.baseUrl + "/" + data['domaine[id]'], 'DELETE', {}, done, always);
        }
    };

    /**
     * récupération des templates
     */
    var $templateAffichageDomaine = $("#template-affichage-domaine").find('tbody').html();
    $("#template-affichage-domaine").remove();
    var $templateAffichageSousDomaine = $("#template-affichage-sous-domaine").find('tbody').html();
    $("#template-affichage-sous-domaine").remove();
    var $templateSaisieDomaine = $("#template-saisie-domaine").find('tbody').html();
    $("#template-saisie-domaine").remove();

    /**
     * clic sur le bouton de création de domaine
     */
    $("#btn-creation-domaine").on('click', function() {
        $tableBody.prepend($templateSaisieDomaine);
        $editEntry = $tableBody.children(':first');
        $editEntry.find('input').focus();
        $displayEntry = null;
        formStateRefresh();
    });

    /**
     * clic sur le bouton de création de sous-domaine
     */
        $tableBody.on('click', '.btn-add', function() {
        $parentEntry = $(this).parents('tr');
        var domaineParent = $parentEntry.attr('data-id');
        $parentEntry.after($templateSaisieDomaine);
        $editEntry = $parentEntry.next();
        $editEntry.attr('data-parent', domaineParent);
        $editEntry.find('input').focus();
        $displayEntry = null;
        formStateRefresh();
    });

    /**
     * clic sur le bouton de modification de (sous-)domaine
     */
        $tableBody.on('click', '.btn-edit', function() {
        $displayEntry = $(this).parents('tr');
        $displayEntry.after($templateSaisieDomaine);
        $editEntry = $displayEntry.next();
        $displayEntry.addClass('d-none');
        if ($displayEntry.is('[data-parent]')) {     // sous-domaine
            label = $displayEntry.find('small').text();
        } else {     // domaine
            label = $displayEntry.find('td:first').text();
        }
        $editEntry.find('input').val(label);
        $editEntry.find('input').focus();
        formStateRefresh();
    });

    /**
     * clic sur le bouton d'annulation de création/modification de (sous-)domaine
     */
    $tableBody.on('click', ".btn-cancel", function() {
        if (($displayEntry == null) && ($editEntry.find('input').val() == '')) {     // création sans saisie du label
            $editEntry.remove();
            formStateRefresh();
        } else if (($displayEntry != null) && ($editEntry.find('input').val().trim() == label)) {     // modification sans modification du label
                $editEntry.remove();
                $displayEntry.removeClass('d-none');
                formStateRefresh();
        } else {     // autres cas
            $confirmationAnnulationModal.modal('show');
        }
    });
    $confirmationAnnulationModal.on('click', '.btn.btn-primary', function() {
        $editEntry.remove();
        if ($displayEntry != null) {     // sous-domaine
            $displayEntry.removeClass('d-none');
        }
        formStateRefresh();
    });

    /**
     * clic sur le bouton de confirmation de création/modification de (sous-)domaine
     */
    $tableBody.on('click', ".btn-apply", function() {
        $editEntry.addClass('item-loading');
        $editEntry.find('input, button').prop('disabled', 'disabled');
        label = $editEntry.find('input').val().trim();
        label = label.charAt(0).toUpperCase() + label.slice(1);
        var data = {'domaine[label]': label};
        if ($displayEntry == null) {     // création
            if ($editEntry.is('[data-parent]')) {
                data['domaine[domaineParent]'] = $editEntry.attr('data-parent');
            }
            API.ajout(data, function(reponse) {
                var $jalon = null;
                if ($editEntry.is('[data-parent]')) {
                    // positionnement du nouveau sous-domaine
                    var $sousDomaines = $tableBody.find('tr').filter('[data-parent="' + data['domaine[domaineParent]'] + '"]');
                    if ($sousDomaines.length == 0) {
                        $parentEntry.after($templateAffichageSousDomaine);
                        $displayEntry = $parentEntry.next();
                    } else {
                        $sousDomaines.each(function() {
                            if ($(this).find('td:first').text() > label) {
                                $jalon = $(this);
                                return false;
                            }
                        });
                        if ($jalon == null) {
                            $sousDomaines.last().after($templateAffichageSousDomaine);
                            $displayEntry = $sousDomaines.last().next();
                        } else {
                            $jalon.before($templateAffichageSousDomaine);
                            $displayEntry = $jalon.prev();
                        }
                    }
                    $displayEntry.attr('data-parent', data['domaine[domaineParent]']);
                    $displayEntry.find('small').text(label);
                } else {
                    // positionnement du nouveau domaine
                    $tableBody.find('tr').not('[data-parent]').each(function() {
                        if ($(this).find('td:first').text() > label) {
                            $jalon = $(this);
                            return false;
                        }
                    });
                    if ($jalon == null) {
                        $tableBody.append($templateAffichageDomaine);
                        $displayEntry = $tableBody.children(':last');
                    } else {
                        $jalon.before($templateAffichageDomaine);
                        $displayEntry = $jalon.prev();
                    }
                    $displayEntry.find('td:first').text(label);
                }
                $displayEntry.attr('data-id', reponse.data.nouvelId);
                $editEntry.remove();
                formStateRefresh();
            }, function() {
                $editEntry.removeClass('item-loading');
                $editEntry.find('input, button').prop('disabled', null);
            });
        } else {     // modification
            data['domaine[id]'] = $displayEntry.attr('data-id');
            if ($displayEntry.is('[data-parent]')) {
                data['domaine[domaineParent]'] = $displayEntry.attr('data-parent');
            }
            API.modification(data, function(reponse) {
                var $jalon = null;
                if ($displayEntry.is('[data-parent]')) {
                    // positionnement du sous-domaine renommé
                    $displayEntry.remove();
                    var $sousDomaines = $tableBody.find('tr').filter('[data-parent="' + data['domaine[domaineParent]'] + '"]');
                    if ($sousDomaines.length == 0) {
                        $parentEntry = $tableBody.find('tr').filter('[data-id="' + data['domaine[id]'] + '"]');
                        $parentEntry.after($templateAffichageSousDomaine);
                        $displayEntry = $parentEntry.next();
                    } else {
                        $sousDomaines.each(function() {
                            if ($(this).find('td:first').text() > label) {
                                $jalon = $(this);
                                return false;
                            }
                        });
                        if ($jalon == null) {
                            $sousDomaines.last().after($templateAffichageSousDomaine);
                            $displayEntry = $sousDomaines.last().next();
                        } else {
                            $jalon.before($templateAffichageSousDomaine);
                            $displayEntry = $jalon.prev();
                        }
                    }
                    $displayEntry.attr('data-parent', data['domaine[domaineParent]']);
                    $displayEntry.find('small').text(label);
                } else {
                    // positionnement du domaine renommé
                    $displayEntry.remove();
                    $tableBody.find('tr').not('[data-parent]').each(function() {
                        if ($(this).find('td:first').text() > label) {
                            $jalon = $(this);
                            return false;
                        }
                    });
                    if ($jalon == null) {
                        $tableBody.append($templateAffichageDomaine);
                        $displayEntry = $tableBody.children(':last');
                    } else {
                        $jalon.before($templateAffichageDomaine);
                        $displayEntry = $jalon.prev();
                    }
                    $displayEntry.find('td:first').text(label);
                    // et de ses sous-domaines
                    $jalon = $displayEntry;
                    $tableBody.find('tr').filter('[data-parent="' + data['domaine[id]'] + '"]').each(function() {
                        $(this).attr('data-parent', reponse.data.nouvelId);
                        $jalon.after($(this));
                        $jalon = $jalon.next();
                    });
                }
                $displayEntry.attr('data-id', reponse.data.nouvelId);
                $editEntry.remove();
                formStateRefresh();
            }, function() {
                $editEntry.removeClass('item-loading');
                $editEntry.find('input, button').prop('disabled', null);
            });
        }
    });

    /**
     * clic sur le bouton de suppression de (sous-)domaine
     */
     $tableBody.on('click', '.btn-delete', function() {
        $displayEntry = $(this).parents('tr');
        var message = 'Le ';
        if ($displayEntry.is('[data-parent]')) {
            message += 'sous-domaine "' + $displayEntry.find('small').text() + '" du domaine "'
                 + $tableBody.find('tr[data-id="' + $displayEntry.attr('data-parent') + '"]').find('td:first').text()
                 + '" va être supprimé.';
        } else {
            message += 'domaine "' + $displayEntry.find('td:first').text() + '"';
            if ($displayEntry.next().is('[data-parent="' + $displayEntry.attr('data-id') + '"]')) {
                message += ' et ses sous-domaines vont être supprimés.';
            } else {
                message += '" va être supprimé.';
            }
        }
        $confirmationSuppressionModal.find('#confirmationSuppressionModal-message').html(message);
        $confirmationSuppressionModal.modal('show');
     });
     $('#confirmationSuppressionModal').on('click', '.btn.btn-primary', function() {
        var data = {'domaine[id]': $displayEntry.attr('data-id')};
        $displayEntry.addClass('item-loading');
        $displayEntry.find('input, button').prop('disabled', 'disabled');

        API.suppression(data, function() {
            $tableBody.find('tr').filter('[data-parent="' + data['domaine[id]'] + '"]').remove();
            $displayEntry.remove();
            formStateRefresh();
        }, function() {
            $displayEntry.removeClass('item-loading');
            $displayEntry.find('input, button').prop('disabled', null);
        });
     });

    /**
     * Fonction permettant de définir le comportement lorsqu'une référence est en modification / ajout.
     * - Si nous avons encore des champs en édition :
     *      - Alors on demande confirmation si l'utilisateur souhaite fermer la fenêtre
     *      - On désactive les boutons d'ajout / modification d'autres lignes
     *      - On opacifie les autres lignes de références
     * - Sinon rien
     */
     function formStateRefresh() {
        var $autreReferences = $tableBody.children('tr:not(.item-editing)');
        var $btns = $('#btn-creation-domaine, .btn-add, .btn-edit, .btn-delete');

        $(window).off('beforeunload');
        $btns.prop('disabled', null);
        $autreReferences.removeClass('item-transparency');
        if ($('.item-editing').length > 0) {
            $(window).on('beforeunload', function() {
                return "Des modifications sont toujours en cours et ne seront pas enregistrées si vous quittez la page actuelle. Souhaitez-vous quand même quitter la page ?";
            });
            $btns.prop('disabled', 'disabled');
            $autreReferences.addClass('item-transparency');
        }
    }

});
