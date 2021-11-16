/**
 * Functions globales à toute l'application
 */

$(document).ready(function() {

    /**
     * Initialisation
     */
     let $body = $('body');

    /**
     * Initialisation des composants impliquant une initialisation globale
     */
    // On met en place les select-picker présents dans la page
    $.fn.selectpicker.Constructor.BootstrapVersion = '4';
    $('.select-picker').selectpicker({
        'hideDisabled': true,
        'liveSearch': true,
        'style': '',
        'styleBase': 'form-control'
    });
    // Lors d'un reset de formulaire, on rafraîchi l'affichage des select-pickers
    $('form').on('reset', function() {
        setTimeout(function() {
            $('.select-picker').selectpicker('refresh');
        });
    });
    // On initialise les timepickers en place dans la page
    // @deprecated
    $('.datepicker').datetimepicker({
        locale: 'fr',
        format: 'LT',
        useCurrent: false,
    });
    // On initialise les timepickers en place dans la page
    // @deprecated
    $('.timepicker').datetimepicker({
        locale: 'fr',
        format: 'L',
        useCurrent: false,
    });
    // On initialise les datetimepicker
    $('.form-datetimepicker').datetimepicker();
    // On initialise les datepicker
    $('.form-datepicker').datetimepicker({
        locale: 'fr',
        format: 'L',
    });

    /**
     * Gestion des "Tout sélectionner" par conteneur
     * .checkall-container          => Conteneur d'où l'événement "CheckboxesChange" est lancé, à capturer dans le script
     *      .checkall               => Case "Tout"
     *
     *      .checkall-box-handle    => (Facultatif) Au clic sur cet élément, on coche / décoche la case enfant (pratique pour cocher une case associée à une ligne entière)
     *          .checkall-box       => Case "Item unique"
     *
     *      .checkall-box-handle
     *          .checkall-box
     *
     *      .checkall-box-handle
     *          .checkall-box
     *
     *      (...)
     */
    // On décoche tout en arrivant sur la page et on initialise les containers
    $('.checkall, .checkall-box').prop('checked', '');
    $('.checkall-container').data('checkedBoxes', $([]));
    // Lors d'un changement de valeur au niveau de la case "Tout selectionner"
    $body.on('change', '.checkall', function (e) {
        e.preventDefault();
        // On initialise quelques variables
        let $this = $(this);
        let $checkboxesContainer = $this.parents('.checkall-container');
        let $checkboxes = $checkboxesContainer.find('.checkall-box');
        let $checkboxesHandler = $checkboxesContainer.find('.checkall-box-handle');

        // Si on est coché, alors on coche toutes les cases à cocher
        if ($this.is(':checked')) {
            $checkboxes.prop('checked', 'checked');
            $checkboxesHandler.addClass('checkall-box-checked');
        // Sinon, on décoche toutes les cases à cocher
        } else {
            $checkboxes.prop('checked', null);
            $checkboxesHandler.removeClass('checkall-box-checked');
        }

        // On récupère toutes les cases cochées du container
        let $checkboxesChecked = $checkboxesContainer.find('.checkall-box:checked');

        // On émet un événement depuis le container afin de pouvoir le capturer par la suite, on lui passe également
        // le nombre de case cochées ainsi que les cases cochées.
        $checkboxesContainer.trigger('CheckboxesChange', [
            $checkboxesChecked.length,
            $checkboxesChecked
        ]);
        $checkboxesContainer.data('checkedBoxes', $checkboxesChecked);
    });
    // Lors d'un changement de valuer au niveau d'une case ".checkall-box"
    $body.on('change', '.checkall-box', function (e) {
        e.preventDefault();
        // On initialise quelques variables
        let $this = $(this);
        let $checkboxeHandler = $this.parents('.checkall-box-handle');
        let $checkboxesContainer = $this.parents('.checkall-container');
        let $checkboxes = $checkboxesContainer.find('.checkall-box');
        let $checkboxesChecked = $checkboxesContainer.find('.checkall-box:checked');
        let $checkAll = $checkboxesContainer.find('.checkall');

        // On ajoute / retire la classe "checkall-box-checked" du handler
        $checkboxeHandler.toggleClass('checkall-box-checked', $this.is(':checked'));

        // Si le nombre total de case à coché du container est égal au nombre déjà coché, alors on coche "Tout sélectionner"
        if ($checkboxes.length === $checkboxesChecked.length) {
            $checkAll.prop('checked', 'checked');
        // Sinon, on le décoche
        } else {
            $checkAll.prop('checked', null);
        }

        // On émet un événement depuis le container afin de pouvoir le capturer par la suite, on lui passe également
        // le nombre de case cochées ainsi que les cases cochées.
        $checkboxesContainer.trigger('CheckboxesChange', [
            $checkboxesChecked.length,
            $checkboxesChecked
        ]);
        $checkboxesContainer.data('checkedBoxes', $checkboxesChecked);
    });
    // Lors d'un clic sur un élément ".checkall-box-handle"
    $body.on('click', '.checkall-box-handle', function(e) {
        let $ckb = $(this).find('.checkall-box');
        if (e.target !== $ckb.get(0)) {
            e.preventDefault();
            e.stopPropagation();
            $ckb.prop('checked', $ckb.is(':checked') ? '' : 'checked');
            $ckb.trigger('change');
        }
    });

    /**
     * Gestion du "big loading" : affichage d'un système de chargement global
     * - status: true - on affiche le chargement
     * - status: false - on masque le chargement
     */
    window.bigLoadingDisplay = function(status, callback) {
        let $bigLoading = $('.big-loading');

        if (status) {
            $bigLoading.stop(1, 1).fadeIn(200);
        } else {
            $bigLoading.stop(1, 1).fadeOut(200);
        }
    };

    /**
     * Réinitialise les champs d'un formulaire automatiquement
     *  Si plusieurs formulaires existent dans votre page, utilisez data-form-reset-selector.
     */
     $('.form-reset').on('click', function(e) {
        e.preventDefault();
        let $forms;
        if($(this).data('form-reset-selector')) {
            $forms = $($(this).data('form-reset-selector'));
        } else {
            $forms = $('form');
        }
        $forms.find('input, textarea').each(function() {
            switch($(this).attr('type')) {
                case 'checkbox': case 'radio':
                    $(this).prop('checked', false);
                    break;
                default:
                    $(this).val('');
            }
        });
        $forms.find('select').each(function(){
            $(this).val('');
            if ($(this).hasClass('select-picker')) {
                $(this).selectpicker('refresh');
            }
        });
    });

    /**
     * Lors d'un changement d'un filtre select avec redirection.
     */
    $('.select-redirect').on('change', function (e) {
        e.preventDefault();
        if ($(this).val() !== '') {
            document.location.href = $(this).val();
        }
    });

    /**
     * Mise en place du système de tri de colonne en JQ de tableau.
     * Il suffit d'indiquer la classe `table-tri` au niveau de la colonne que l'on souhaite trier.
     * Le script fait le reste !
     */
     $('.table-tri').each(function() {
        let $this = $(this);
        if ($this.parents('table').find('tbody tr').length <= 2) {
            $this.removeClass('table-tri');
            $this.removeClass('table-tri__active');
        }
    });
    $body.on('click', '.table-tri', function(e) {
        e.preventDefault();
        let $headColonne = $(this);
        let $table = $headColonne.parents('table');
        let $tbody = $table.find('tbody');
        let thIndex = $headColonne.index();

        if ($headColonne.hasClass('table-tri__active')) {
            $headColonne.toggleClass('table-tri__inverse');
        }
        const inverse = $headColonne.hasClass('table-tri__inverse');
        $table.find('.table-tri__active').removeClass('table-tri__active');
        $table.find('.table-tri__inverse').not($headColonne).removeClass('table-tri__inverse');
        $headColonne.addClass('table-tri__active');

        $tbody.find('td').filter(function() {
            return $(this).index() === thIndex;
        }).sortElements(function(a, b){

            // On récupère les textes des lignes A et B
            let textA = $.text([a]);
            let textB = $.text([b]);

            // Si une valeur tri-value est indiquée, le tri sera alors fondé sur celle-ci
            if (
                $(a).data('tri-value') !== undefined ||
                $(b).data('tri-value') !== undefined
            ) {
                textA = $(a).data('tri-value');
                textB = $(b).data('tri-value');
            } else {
                // Si les contenus ressemblent à des dates, alors on les formate pour pouvoir les utiliser façon US
                //   (pour permettre un ordre alphabétique correct)
                let dateTextA = textA.toString().match(/^(\d{2})\/(\d{2})\/(\d{4})(\ \d{2}\:\d{2})?$/);
                let dateTextB = textB.toString().match(/^(\d{2})\/(\d{2})\/(\d{4})(\ \d{2}\:\d{2})?$/);
                if (dateTextA !== null || dateTextB !== null) {
                    textA = dateTextA[3] + '-' + dateTextA[2] + '-' + dateTextA[1];
                    textB = dateTextB[3] + '-' + dateTextB[2] + '-' + dateTextB[1];
                    if (undefined !== dateTextA[4]) {
                        textA += dateTextA[4];
                    }
                    if (undefined !== dateTextB[4]) {
                        textB += dateTextB[4];
                    }
                }
            }

            // On tri !
            if( textA === textB )
                return 0;
            return textA > textB ?
                inverse ? -1 : 1
                : inverse ? 1 : -1;

        }, function() {
            // parentNode is the element we want to move
            return this.parentNode;
        });
    });

    /**
     * Permet de plier ou déplier une card bootstrap.
     * (On traite uniquement lors d'un clic sur `.card-deploy`.)
     */
    $body.on('click', '.card-deploy .card-header', function(e) {
        e.preventDefault();
        $(this).parents('.card-deploy').toggleClass('card-deploy-deployed');
    });

    /**
     * Système d'affichage du changelog de l'application liée au projet GitLab
     */
    // Initialisation de quelques variables utiles pour l'affichage du changelog
    let changelog_page = 1;
    let changelog_end_reached = false;
    let $changelog_open = $('.open-modal-changelog');
    let $modal_changelog = $('.modal-changelog');
    let $modal_changelog_body = $modal_changelog.find('.modal-body');
    let $modal_changelog_loader = $modal_changelog.find('.table-loading');

    // Lors que l'on clique sur le lien permettant d'ouvrir la modale de changelog
    $changelog_open.click(function(e) {
        e.preventDefault();
        $modal_changelog.modal('show');
        changelog_page = 1;
        changelog_end_reached = false;
        changelogLoad(1, true);
    });

    // Lors que nous scrollons dans les versions, et lorsque l'on arrive vers la fin de la liste, on charge la page
    // suivante.
    $modal_changelog_body.scroll(function() {
        if (!changelog_end_reached && !$modal_changelog_loader.hasClass('show')) {
            var scrollHeight = $modal_changelog_body.prop('scrollHeight');
            var scrollPos = $modal_changelog_body.height() + $modal_changelog_body.scrollTop();

            if (((scrollHeight - 200) >= scrollPos) / scrollHeight === 0) {
                changelog_page++;
                changelogLoad(changelog_page);
            }
        }
    });

    // Fonction permettant de charger une page dans le tableau présentant les versions du changelog
    let changelogLoad = function(page, withReset = false) {
        let $modal_title_small = $modal_changelog.find('.modal-title small');
        $modal_changelog_loader.addClass('show');

        if (withReset) {
            $modal_title_small.html('');
            $modal_changelog.find('.release-item').remove();
        }

        $.get('/ajax/changelog/' + page, function(data) {
            for (let i = 0 ; i < data.releases.length ; i++) {
                let $tr = $('<tr class="release-item"></tr>').append(
                    $('<td class="text-center">' + data.releases[i].name + '</td>'),
                    $('<td class="text-center">' + moment(data.releases[i].disponibleLe).format('DD/MM/YYYY HH:mm') + '</td>'),
                    $('<td></td>').html(data.releases[i].description),
                );
                $tr.attr('data-type', data.releases[i].type);
                $tr.insertBefore($modal_changelog_loader);
            }

            if (data.info.pagination.courante === data.info.pagination.total) {
                changelog_end_reached = true;
            }

            $modal_title_small.html('(maj le ' + moment(data.info.majLe).format('DD/MM/YYYY à HH:mm:ss') + ')');

            $modal_changelog_loader.removeClass('show');
        })
        .fail(function () {
            alert("Impossible de récupérer la liste des versions pour l'instant.");
            $modal_changelog.modal('hide');
        });
    };

});