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

    // Initialisation forcée des tables DataTables lors du changement d'onglet
    function initDataTables() {
        if ($.fn.DataTable) {
            try {
                console.log('Initialisation forcée des DataTables lors du changement d\'onglet');
                
                // Initialisation forcée pour patients si on est sur cette page
                if ($('.medoffice-patients').length && typeof initPatients === 'function') {
                    console.log('Réinitialisation de la page patients détectée');
                    // Essayer de détruire la table existante
                    if (window.patientsTable) {
                        try {
                            window.patientsTable.destroy();
                            console.log('Table patients existante détruite avec succès');
                        } catch (err) {
                            console.log('Aucune table patients à détruire ou erreur:', err);
                        }
                    }
                    
                    // Rappeler l'initialisation
                    setTimeout(function() {
                        initPatients();
                        console.log('Table patients réinitialisée avec succès');
                    }, 100);
                }
                
                // Initialisation forcée pour consultations si on est sur cette page
                if ($('.medoffice-consultations').length && typeof initConsultations === 'function') {
                    console.log('Réinitialisation de la page consultations détectée');
                    // Essayer de détruire la table existante
                    if (window.consultationsTable) {
                        try {
                            window.consultationsTable.destroy();
                            console.log('Table consultations existante détruite avec succès');
                        } catch (err) {
                            console.log('Aucune table consultations à détruire ou erreur:', err);
                        }
                    }
                    
                    // Rappeler l'initialisation
                    setTimeout(function() {
                        initConsultations();
                        console.log('Table consultations réinitialisée avec succès');
                    }, 100);
                }
                
                console.log('Vérification et réinitialisation des DataTables terminée');
            } catch (e) {
                console.error('Erreur lors de la réinitialisation des DataTables:', e);
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

    // Ajout d'écouteurs d'événements pour les changements d'onglets
    function setupTabListeners() {
        console.log('Configuration des écouteurs d\'événements pour les onglets');
        
        // Pour les liens d'onglets dans la navigation principale
        $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
            console.log('Changement d\'onglet détecté:', e.target.getAttribute('href'));
            // Réinitialiser les composants après un changement d'onglet
            setTimeout(function() {
                initDataTables();
                initFullCalendar();
                activateBootstrapComponents();
            }, 200);
        });
        
        // Pour les links de la barre latérale qui chargent les différentes pages
        $('#medoffice-menu a, .medoffice-menu-item').on('click', function() {
            console.log('Clic sur item du menu détecté');
            // Besoin d'un délai car le contenu sera chargé après le clic
            setTimeout(function() {
                initDataTables();
                initFullCalendar();
                activateBootstrapComponents();
            }, 500);
        });
        
        console.log('Écouteurs d\'événements pour les onglets configurés');
    }
    
    // Exécuter au chargement du document
    $(document).ready(function() {
        console.log('Initialisation des composants...');
        activateBootstrapComponents();
        initDataTables();
        initFullCalendar();
        setupTabListeners();
        
        // Force une réinitialisation après 1 seconde pour s'assurer que tout est chargé
        setTimeout(function() {
            console.log('Réinitialisation forcée après délai');
            initDataTables();
        }, 1000);
    });

})(jQuery);