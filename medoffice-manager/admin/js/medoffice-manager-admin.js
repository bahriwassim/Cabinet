/**
 * Main admin JavaScript for MedOffice Manager
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 */

(function($) {
    'use strict';

    /**
     * Dashboard initialization
     */
    function initDashboard() {
        // Only execute on dashboard page
        if (!$('.medoffice-dashboard').length) {
            return;
        }

        // Fetch dashboard stats
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_dashboard_stats',
                nonce: medoffice_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;

                    // Update cards
                    $('#total-patients').text(data.total_patients);
                    $('#total-consultations').text(data.total_consultations);
                    $('#total-appointments-today').text(data.total_appointments_today);
                    $('#unpaid-fees').text(data.unpaid_fees + ' TND');

                    // Create chart
                    createConsultationsChart(data.chart_data);

                    // Populate upcoming appointments
                    populateUpcomingAppointments(data.upcoming_appointments);

                    // Populate recent patients table
                    populateRecentPatientsTable(data.recent_patients);
                }
            },
            error: function() {
                alert('Erreur lors du chargement des statistiques.');
            }
        });
    }

    /**
     * Create consultations chart
     */
    function createConsultationsChart(chartData) {
        const ctx = document.getElementById('consultationsChart');

        if (!ctx) {
            return;
        }

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Consultations',
                    data: chartData.data,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.05)',
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            borderDash: [2],
                            drawBorder: false
                        },
                        ticks: {
                            precision: 0
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
    }

    /**
     * Populate upcoming appointments
     */
    function populateUpcomingAppointments(appointments) {
        const container = $('#upcoming-appointments-list');

        if (appointments.length === 0) {
            container.html('<p class="text-center text-muted">Aucun rendez-vous à venir</p>');
            return;
        }

        let html = '<div class="list-group">';

        appointments.forEach(appointment => {
            const dateObj = new Date(appointment.date_debut);
            const formattedDate = dateObj.toLocaleDateString() + ' ' + dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

            let statusClass = 'bg-success';
            if (appointment.status === 'en attente') {
                statusClass = 'bg-warning';
            } else if (appointment.status === 'annulé') {
                statusClass = 'bg-danger';
            }

            html += `
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1">${appointment.titre}</h6>
                        <small class="text-muted">${formattedDate}</small>
                    </div>
                    <p class="mb-1">${appointment.prenom} ${appointment.nom}</p>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">${appointment.description || ''}</small>
                        <span class="badge ${statusClass}">${appointment.status}</span>
                    </div>
                </a>
            `;
        });

        html += '</div>';
        container.html(html);
    }

    /**
     * Populate recent patients table
     */
    function populateRecentPatientsTable(patients) {
        const tableBody = $('#recent-patients-table tbody');

        if (patients.length === 0) {
            tableBody.html('<tr><td colspan="6" class="text-center">Aucun patient trouvé</td></tr>');
            return;
        }

        tableBody.empty();

        patients.forEach(patient => {
            const creationDate = new Date(patient.date_creation);
            const formattedDate = creationDate.toLocaleDateString();

            tableBody.append(`
                <tr>
                    <td>${patient.id}</td>
                    <td>${patient.prenom} ${patient.nom}</td>
                    <td>${patient.sexe}</td>
                    <td>${patient.telephone}</td>
                    <td>${formattedDate}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-info view-patient" data-id="${patient.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="admin.php?page=medoffice-patients" class="btn btn-sm btn-primary">
                            <i class="fas fa-edit"></i>
                        </a>
                    </td>
                </tr>
            `);
        });

        // Handle view patient click
        $('.view-patient').on('click', function() {
            const patientId = $(this).data('id');
            window.location.href = 'admin.php?page=medoffice-patients&view=' + patientId;
        });
    }

    /**
     * Initialize settings page
     */
    function initSettings() {
        // Only execute on settings page
        if (!$('.medoffice-settings').length) {
            return;
        }

        // Media uploader for logo
        $('#select-logo').on('click', function(e) {
            e.preventDefault();

            const customUploader = wp.media({
                title: 'Sélectionner un logo',
                button: {
                    text: 'Utiliser ce média'
                },
                multiple: false
            });

            customUploader.on('select', function() {
                const attachment = customUploader.state().get('selection').first().toJSON();
                $('#logo_cabinet').val(attachment.id);
                $('.logo-preview').html(`<img src="${attachment.url}" alt="Logo" style="max-height: 100px;">`);
            });

            customUploader.open();
        });

        // Save settings
        $('#settings-form').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();

            $.ajax({
                url: medoffice_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'medoffice_save_settings',
                    nonce: medoffice_ajax.nonce,
                    form_data: formData
                },
                beforeSend: function() {
                    $('#save-settings').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
                },
                success: function(response) {
                    if (response.success) {
                        alert('Paramètres enregistrés avec succès.');
                    } else {
                        alert('Erreur lors de l\'enregistrement des paramètres.');
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur.');
                },
                complete: function() {
                    $('#save-settings').prop('disabled', false).html('<i class="fas fa-save"></i> Enregistrer les paramètres');
                }
            });
        });
    }

    /**
     * Document ready handler
     */
    $(document).ready(function() {
        // Initialize dashboard
        initDashboard();

        // Initialize settings
        initSettings();

        // Save settings
        $(document).on('click', '#save-settings', function(e) {
            e.preventDefault();
            $('#settings-form').submit();
        });

        // Global AJAX error handler
        $(document).ajaxError(function(event, jqXHR, settings, error) {
            console.error('AJAX Error:', error, jqXHR.responseText);
        });

        // Initialize all modules
        if (typeof window.initPatients === 'function') {
            window.initPatients();
        }
        if (typeof window.initConsultations === 'function') {
            window.initConsultations();
        }
        if (typeof window.initCalendar === 'function') {
            window.initCalendar();
        }

        // Initialisation des DataTables
        if ($('#patients-table').length) {
            $('#patients-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: medoffice_ajax.ajax_url,
                    type: 'POST',
                    data: function(d) {
                        d.action = 'medoffice_get_patients';
                        d.nonce = medoffice_ajax.nonce;
                    }
                }
            });
        }

        if ($('#consultations-table').length) {
            $('#consultations-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: medoffice_ajax.ajax_url,
                    type: 'POST',
                    data: function(d) {
                        d.action = 'medoffice_get_consultations';
                        d.nonce = medoffice_ajax.nonce;
                    }
                }
            });
        }

        // Autocomplete pour la recherche de patients
        $('#patient_search').autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: medoffice_ajax.ajax_url,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'medoffice_get_patients',
                        term: request.term,
                        nonce: medoffice_ajax.nonce
                    },
                    success: function(data) {
                        response(data);
                    }
                });
            },
            minLength: 2,
            select: function(event, ui) {
                $('#patient_id').val(ui.item.id);
            }
        });
    });

})(jQuery);