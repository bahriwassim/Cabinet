/**
 * Script d'initialisation pour les différents onglets du plugin
 * Ce script s'assure que tous les composants sont correctement initialisés
 * quand l'utilisateur navigue entre les onglets
 * 
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 */

(function($) {
    'use strict';

    // Vérifier si Bootstrap est bien chargé
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap n\'est pas chargé correctement. Certaines fonctionnalités peuvent ne pas fonctionner.');
    } else {
        console.log('Bootstrap est correctement chargé.');
    }

    // Activation forcée des tooltips et popovers
    function activateBootstrapComponents() {
        try {
            // Tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });

            // Dropdowns
            var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
            dropdownElementList.map(function (dropdownToggleEl) {
                return new bootstrap.Dropdown(dropdownToggleEl);
            });

            // Modals
            var modalElementList = [].slice.call(document.querySelectorAll('.modal'));
            modalElementList.map(function (modalEl) {
                return new bootstrap.Modal(modalEl);
            });

            console.log('Composants Bootstrap activés avec succès');
        } catch (e) {
            console.error('Erreur lors de l\'activation des composants Bootstrap:', e);
        }
    }

    // Initialisation de tous les DataTables présents sur la page
    function initDataTables() {
        if ($.fn.DataTable) {
            // Si DataTables est chargé, vérifier si les tables sont déjà initialisées
            try {
                // N'initialiser les tableaux que s'ils ne sont pas déjà initialisés
                // par patients.js ou consultations.js
                
                // Autres tables DataTable génériques si nécessaires (différentes de patients-table ou consultations-table)
                // Elles seraient initialisées ici...
                
                console.log('Vérification des DataTables effectuée');
            } catch (e) {
                console.error('Erreur lors de la vérification des DataTables:', e);
            }
        } else {
            console.warn('DataTables n\'est pas chargé. Les tableaux ne seront pas interactifs.');
        }
    }

    // Initialisation du calendrier FullCalendar
    function initFullCalendar() {
        if (typeof FullCalendar !== 'undefined') {
            try {
                if ($('#calendar').length) {
                    var calendarEl = document.getElementById('calendar');
                    var calendar = new FullCalendar.Calendar(calendarEl, {
                        initialView: 'dayGridMonth',
                        headerToolbar: false, // Nous utilisons nos propres boutons
                        locale: 'fr',
                        height: 650,
                        eventTimeFormat: {
                            hour: '2-digit',
                            minute: '2-digit',
                            hour12: false
                        }
                    });
                    calendar.render();
                    console.log('FullCalendar initialisé');
                    
                    // Associer nos boutons personnalisés au calendrier
                    $('.view-option').on('click', function() {
                        var view = $(this).data('view');
                        calendar.changeView(view);
                        $('.view-option').removeClass('active');
                        $(this).addClass('active');
                    });
                    
                    $('#today-button').on('click', function() {
                        calendar.today();
                    });
                    
                    $('#prev-button').on('click', function() {
                        calendar.prev();
                    });
                    
                    $('#next-button').on('click', function() {
                        calendar.next();
                    });
                }
            } catch (e) {
                console.error('Erreur lors de l\'initialisation de FullCalendar:', e);
            }
        } else {
            console.warn('FullCalendar n\'est pas chargé. Le calendrier ne sera pas interactif.');
        }
    }

    // Exécuter au chargement du document
    $(document).ready(function() {
        console.log('Initialisation des composants...');
        activateBootstrapComponents();
        initDataTables();
        initFullCalendar();
    });

})(jQuery);