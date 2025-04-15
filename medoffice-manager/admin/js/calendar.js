/**
 * Calendar functionality for MedOffice Manager
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 */

(function($) {
    'use strict';

    let calendar;
    let currentAppointmentId = 0;

    /**
     * Initialize the calendar
     */
    function initCalendar() {
        // Only initialize if we're on the calendar page
        if (!$('.medoffice-calendar').length) {
            return;
        }

        const calendarEl = document.getElementById('calendar');
        
        if (!calendarEl) {
            return;
        }
        
        // Initialize FullCalendar
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: false, // We'll use our custom toolbar
            locale: 'fr',
            timeZone: 'local',
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            height: 'auto',
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6], // Monday - Saturday
                startTime: '08:00',
                endTime: '18:00',
            },
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                meridiem: false
            },
            select: handleDateSelect,
            eventClick: handleEventClick,
            eventChange: handleEventChange,
            events: loadEvents,
            eventContent: function(info) {
                let title = info.event.title;
                let timeText = '';
                
                // For non-all-day events, show the time
                if (!info.event.allDay) {
                    const start = info.event.start;
                    timeText = start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                }
                
                // Get patient name from extendedProps
                const patientName = info.event.extendedProps.patientName || '';
                
                // Get event status for styling
                const status = info.event.extendedProps.status || 'confirmé';
                let statusClass = 'event-confirmed';
                
                if (status === 'en attente') {
                    statusClass = 'event-pending';
                } else if (status === 'annulé') {
                    statusClass = 'event-cancelled';
                }
                
                return {
                    html: `
                        <div class="fc-event-main-wrapper ${statusClass}">
                            <div class="fc-event-time">${timeText}</div>
                            <div class="fc-event-title-container">
                                <div class="fc-event-title">${title}</div>
                                ${patientName ? `<div class="fc-event-desc">${patientName}</div>` : ''}
                            </div>
                        </div>
                    `
                };
            }
        });
        
        calendar.render();
        
        // Custom toolbar buttons
        $('#today-button').on('click', function() {
            calendar.today();
        });
        
        $('#prev-button').on('click', function() {
            calendar.prev();
        });
        
        $('#next-button').on('click', function() {
            calendar.next();
        });
        
        $('.view-option').on('click', function() {
            const view = $(this).data('view');
            calendar.changeView(view);
            $('.view-option').removeClass('active');
            $(this).addClass('active');
        });
        
        // Set initial active view
        $('.view-option[data-view="dayGridMonth"]').addClass('active');
        
        // Initialize add appointment button
        $('#add-new-appointment').on('click', function() {
            resetAppointmentForm();
            currentAppointmentId = 0;
            
            // Set default date to current time, rounded to nearest half hour
            const now = new Date();
            now.setMinutes(Math.ceil(now.getMinutes() / 30) * 30);
            now.setSeconds(0);
            
            // Set end time 30 minutes after start time
            const end = new Date(now);
            end.setMinutes(end.getMinutes() + 30);
            
            $('#date_debut').val(formatDatetimeForInput(now));
            $('#date_fin').val(formatDatetimeForInput(end));
            
            // Update modal title and show the modal
            $('#appointmentModalLabel').text('Nouveau rendez-vous');
            $('#delete-appointment').hide();
            $('#appointmentModal').modal('show');
        });
        
        // Load patients for the select dropdown
        loadPatientOptions();
        
        // Handle new patient button
        $('#create-new-patient-appointment').on('click', function() {
            $('#appointmentModal').modal('hide');
            $('#newPatientModal').modal('show');
        });
        
        // Save quick patient
        $('#save-quick-patient').on('click', function() {
            saveQuickPatient();
        });
        
        // Save appointment
        $('#save-appointment').on('click', function() {
            saveAppointment();
        });
        
        // Delete appointment
        $('#delete-appointment').on('click', function() {
            if (confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) {
                deleteAppointment();
            }
        });
    }
    
    /**
     * Load events (appointments) from the server
     */
    function loadEvents(info, successCallback, failureCallback) {
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_appointments',
                nonce: medoffice_ajax.nonce,
                start: info.startStr,
                end: info.endStr
            },
            success: function(response) {
                if (response.success) {
                    // Transform the appointment data into FullCalendar events
                    const events = response.data.map(function(appointment) {
                        return {
                            id: appointment.id,
                            title: appointment.titre,
                            start: appointment.date_debut,
                            end: appointment.date_fin,
                            extendedProps: {
                                patientId: appointment.patient_id,
                                patientName: appointment.patient_name, // Assuming this comes from the server
                                description: appointment.description,
                                status: appointment.status
                            },
                            backgroundColor: getStatusColor(appointment.status),
                            borderColor: getStatusColor(appointment.status)
                        };
                    });
                    
                    successCallback(events);
                } else {
                    failureCallback(new Error('Failed to load events'));
                }
            },
            error: function() {
                failureCallback(new Error('AJAX error'));
            }
        });
    }
    
    /**
     * Handle date selection on the calendar
     */
    function handleDateSelect(info) {
        resetAppointmentForm();
        currentAppointmentId = 0;
        
        // Set the selected date/time in the form
        $('#date_debut').val(formatDatetimeForInput(info.start));
        $('#date_fin').val(formatDatetimeForInput(info.end));
        
        // Update modal title and show the modal
        $('#appointmentModalLabel').text('Nouveau rendez-vous');
        $('#delete-appointment').hide();
        $('#appointmentModal').modal('show');
    }
    
    /**
     * Handle event click
     */
    function handleEventClick(info) {
        const event = info.event;
        currentAppointmentId = event.id;
        
        // Fill the form with event data
        $('#appointment_id').val(event.id);
        $('#appointment_patient_selector').val(event.extendedProps.patientId);
        $('#date_debut').val(formatDatetimeForInput(event.start));
        $('#date_fin').val(formatDatetimeForInput(event.end));
        $('#titre').val(event.title);
        $('#description').val(event.extendedProps.description);
        $('#status').val(event.extendedProps.status);
        
        // Update modal title and show delete button
        $('#appointmentModalLabel').text('Modifier le rendez-vous');
        $('#delete-appointment').show();
        
        $('#appointmentModal').modal('show');
    }
    
    /**
     * Handle event change (drag & drop)
     */
    function handleEventChange(info) {
        const event = info.event;
        const eventData = {
            id: event.id,
            patient_id: event.extendedProps.patientId,
            date_debut: event.start.toISOString(),
            date_fin: event.end ? event.end.toISOString() : new Date(event.start.getTime() + 30*60000).toISOString(),
            titre: event.title,
            description: event.extendedProps.description,
            status: event.extendedProps.status
        };
        
        // Save the changes to the server
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_save_appointment',
                nonce: medoffice_ajax.nonce,
                appointment: eventData
            },
            error: function() {
                alert('Erreur lors de la mise à jour du rendez-vous.');
                info.revert();
            }
        });
    }
    
    /**
     * Save appointment
     */
    function saveAppointment() {
        const patientId = $('#appointment_patient_selector').val();
        const dateDebut = $('#date_debut').val();
        const dateFin = $('#date_fin').val();
        const titre = $('#titre').val();
        
        // Validate required fields
        if (!patientId || !dateDebut || !dateFin || !titre) {
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        
        // Prepare appointment data
        const appointmentData = {
            id: currentAppointmentId,
            patient_id: patientId,
            date_debut: dateDebut,
            date_fin: dateFin,
            titre: titre,
            description: $('#description').val(),
            status: $('#status').val()
        };
        
        // Save to server
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_save_appointment',
                nonce: medoffice_ajax.nonce,
                appointment: appointmentData
            },
            beforeSend: function() {
                $('#save-appointment').prop('disabled', true).text('Enregistrement...');
            },
            success: function(response) {
                if (response.success) {
                    $('#appointmentModal').modal('hide');
                    calendar.refetchEvents();
                    
                    if (currentAppointmentId === 0) {
                        alert('Rendez-vous créé avec succès !');
                    } else {
                        alert('Rendez-vous mis à jour avec succès !');
                    }
                } else {
                    alert('Erreur lors de l\'enregistrement du rendez-vous : ' + (response.data.message || 'Erreur inconnue'));
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#save-appointment').prop('disabled', false).text('Enregistrer');
            }
        });
    }
    
    /**
     * Delete appointment
     */
    function deleteAppointment() {
        if (!currentAppointmentId) {
            return;
        }
        
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_delete_appointment',
                nonce: medoffice_ajax.nonce,
                appointment_id: currentAppointmentId
            },
            beforeSend: function() {
                $('#delete-appointment').prop('disabled', true).text('Suppression...');
            },
            success: function(response) {
                if (response.success) {
                    $('#appointmentModal').modal('hide');
                    calendar.refetchEvents();
                    alert('Rendez-vous supprimé avec succès !');
                } else {
                    alert('Erreur lors de la suppression du rendez-vous.');
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#delete-appointment').prop('disabled', false).text('Supprimer');
            }
        });
    }
    
    /**
     * Save quick patient
     */
    function saveQuickPatient() {
        const nom = $('#quick_nom').val();
        const prenom = $('#quick_prenom').val();
        const sexe = $('#quick_sexe').val();
        const telephone = $('#quick_telephone').val();
        
        // Validate required fields
        if (!nom || !prenom || !sexe || !telephone) {
            alert('Veuillez remplir tous les champs obligatoires.');
            return;
        }
        
        // Prepare patient data
        const patientData = {
            id: 0,
            nom: nom,
            prenom: prenom,
            sexe: sexe,
            telephone: telephone
        };
        
        // Save to server
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_save_patient',
                nonce: medoffice_ajax.nonce,
                patient: patientData
            },
            beforeSend: function() {
                $('#save-quick-patient').prop('disabled', true).text('Enregistrement...');
            },
            success: function(response) {
                if (response.success) {
                    $('#newPatientModal').modal('hide');
                    
                    // Add the new patient to the dropdown and select it
                    const newPatientId = response.data.patient_id;
                    const newPatientName = prenom + ' ' + nom;
                    
                    const newOption = new Option(newPatientName, newPatientId, true, true);
                    $('#appointment_patient_selector').append(newOption).trigger('change');
                    
                    // Show the appointment modal again
                    $('#appointmentModal').modal('show');
                    
                    // Reset the form
                    $('#quick-patient-form')[0].reset();
                } else {
                    alert('Erreur lors de l\'enregistrement du patient : ' + (response.data.message || 'Erreur inconnue'));
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#save-quick-patient').prop('disabled', false).text('Enregistrer');
            }
        });
    }
    
    /**
     * Load patient options for select dropdown
     */
    function loadPatientOptions() {
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_patients',
                nonce: medoffice_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const patients = response.data;
                    const select = $('#appointment_patient_selector');
                    
                    select.empty().append('<option value="">Sélectionner un patient</option>');
                    
                    patients.forEach(function(patient) {
                        select.append(`<option value="${patient.id}">${patient.prenom} ${patient.nom}</option>`);
                    });
                }
            }
        });
    }
    
    /**
     * Reset appointment form
     */
    function resetAppointmentForm() {
        $('#appointment-form')[0].reset();
        $('#appointment_id').val(0);
    }
    
    /**
     * Format a date for datetime-local input
     */
    function formatDatetimeForInput(date) {
        if (!(date instanceof Date)) {
            date = new Date(date);
        }
        
        // Format to YYYY-MM-DDThh:mm
        return date.getFullYear() + '-' + 
               padNumber(date.getMonth() + 1) + '-' + 
               padNumber(date.getDate()) + 'T' + 
               padNumber(date.getHours()) + ':' + 
               padNumber(date.getMinutes());
    }
    
    /**
     * Pad a number with leading zero if needed
     */
    function padNumber(num) {
        return num.toString().padStart(2, '0');
    }
    
    /**
     * Get color based on appointment status
     */
    function getStatusColor(status) {
        switch (status) {
            case 'confirmé': return '#4e73df'; // blue
            case 'en attente': return '#f6c23e'; // yellow
            case 'annulé': return '#e74a3b'; // red
            default: return '#4e73df'; // default to blue
        }
    }

    /**
     * Document ready handler
     */
    $(document).ready(function() {
        initCalendar();
    });

})(jQuery);
