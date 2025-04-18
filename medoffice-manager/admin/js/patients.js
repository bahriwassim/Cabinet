/**
 * Patients functionality for MedOffice Manager
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 */

(function($) {
    'use strict';

    // Définir les variables globales pour tout le plugin
    let currentPatientId = 0;
    let currentView = 'list'; // 'list' or 'grid'

    // Supprimer toute référence à window.patientsTable pour éviter des conflits
    if (window.patientsTable) {
        try {
            window.patientsTable.destroy();
        } catch (e) {
            console.log('Table déjà détruite ou non initialisée');
        }
        window.patientsTable = null;
    }

    /**
     * Initialize the patients page
     */
    function initPatients() {
        console.log('Initialisation de la page patients...');

        // Only initialize if we're on the patients page
        if (!$('.medoffice-patients').length) {
            console.log('Non sur la page patients, sortie');
            return;
        }

        console.log('Sur la page patients, initialisation des composants');

        // Vérifier si la table existe dans le DOM
        if (!$('#patients-table').length) {
            console.error('Erreur: Table #patients-table non trouvée dans le DOM');
            return;
        }

        // Détruire DataTable existante si elle existe
        if ($.fn.DataTable.isDataTable('#patients-table')) {
            $('#patients-table').DataTable().destroy();
            console.log('Table patients détruite pour réinitialisation');
        }

        // Initialiser une nouvelle table
        console.log('Création nouvelle instance DataTable pour patients');
        if ($.fn.DataTable.isDataTable('#patients-table')) {
            $('#patients-table').DataTable().destroy();
            $('#patients-table').empty();
        }
        window.patientsTable = $('#patients-table').DataTable({
            ajax: {
                url: medoffice_ajax.ajax_url,
                type: 'POST',
                data: function(d) {
                    d.action = 'medoffice_get_patients';
                    d.nonce = medoffice_ajax.nonce;
                    d.filter = $('#patients-table').data('filter') || 'all';
                },
                dataSrc: function(response) {
                    if (response.success) {
                        // Also update grid view while we're at it
                        updateGridView(response.data);
                        return response.data;
                    } else {
                        console.error('Error fetching patients:', response.data);
                        return [];
                    }
                }
            },
            columns: [
                { data: "id" },
                { 
                    data: null,
                    render: function(data, type, row) {
                        return `${row.prenom} ${row.nom}`;
                    }
                },
                { data: 'sexe', name: 'sexe' },
                { 
                    data: 'date_naissance',
                    name: 'date_naissance',
                    render: function(data, type, row) {
                        if (!data) return 'N/A';

                        // Calculate age
                        const birthDate = new Date(data);
                        const today = new Date();
                        let age = today.getFullYear() - birthDate.getFullYear();
                        const m = today.getMonth() - birthDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                            age--;
                        }

                        return age + ' ans';
                    }
                },
                { data: 'telephone', name: 'telephone' },
                { 
                    data: 'date_creation',
                    name: 'date_creation',
                    render: function(data, type, row) {
                        return new Date(data).toLocaleDateString();
                    }
                },
                {
                    data: null,
                    name: 'actions',
                    orderable: false,
                    render: function(data, type, row) {
                        return `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info view-patient" data-id="${row.id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary edit-patient" data-id="${row.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-patient" data-id="${row.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            }
        });
    }

    /**
     * Update grid view with patients data
     */
    function updateGridView(patients) {
        const gridContainer = $('#patient-grid-view');
        gridContainer.empty();

        patients.forEach(function(patient) {
            const avatarColor = patient.sexe === 'Homme' ? 'primary' : 'danger';
            const creationDate = new Date(patient.date_creation).toLocaleDateString();

            // Calculate age if birth date exists
            let age = 'N/A';
            if (patient.date_naissance) {
                const birthDate = new Date(patient.date_naissance);
                const today = new Date();
                let ageYears = today.getFullYear() - birthDate.getFullYear();
                const m = today.getMonth() - birthDate.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                    ageYears--;
                }
                age = ageYears + ' ans';
            }

            const cardHtml = `
                <div class="col">
                    <div class="card h-100 patient-card">
                        <div class="card-header bg-${avatarColor} text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="avatar-circle bg-white text-${avatarColor}">
                                    <i class="fas fa-${patient.sexe === 'Homme' ? 'male' : 'female'} fa-2x"></i>
                                </div>
                                <h5 class="card-title mb-0">${patient.prenom} ${patient.nom}</h5>
                            </div>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="fas fa-phone me-2"></i> Téléphone:</span>
                                    <span>${patient.telephone}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="fas fa-birthday-cake me-2"></i> Âge:</span>
                                    <span>${age}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><i class="fas fa-calendar me-2"></i> Ajouté le:</span>
                                    <span>${creationDate}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="card-footer">
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-info view-patient" data-id="${patient.id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-primary edit-patient" data-id="${patient.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-danger delete-patient" data-id="${patient.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            gridContainer.append(cardHtml);
        });
    }

    /**
     * View patient details
     */
    function viewPatient(patientId) {
        currentPatientId = patientId;

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_patient',
                nonce: medoffice_ajax.nonce,
                patient_id: patientId
            },
            beforeSend: function() {
                $('#patient-details-content').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>');
                $('#patientDetailsModal').modal('show');
            },
            success: function(response) {
                if (response.success) {
                    const patient = response.data.patient;
                    const consultations = response.data.consultations || [];
                    const appointments = response.data.appointments || [];

                    // Calculate age if birth date exists
                    let age = 'N/A';
                    if (patient.date_naissance) {
                        const birthDate = new Date(patient.date_naissance);
                        const today = new Date();
                        let ageYears = today.getFullYear() - birthDate.getFullYear();
                        const m = today.getMonth() - birthDate.getMonth();
                        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                            ageYears--;
                        }
                        age = ageYears + ' ans';
                    }

                    // Format date of birth
                    const birthDate = patient.date_naissance ? new Date(patient.date_naissance).toLocaleDateString() : 'N/A';

                    // Build consultations list
                    let consultationsHtml = '';
                    if (consultations.length > 0) {
                        consultationsHtml = '<div class="table-responsive"><table class="table table-bordered table-sm">';
                        consultationsHtml += '<thead><tr><th>Date</th><th>Motif</th><th>Honoraires</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';

                        consultations.forEach(function(consultation) {
                            const date = new Date(consultation.date_consultation).toLocaleString();
                            const statusBadge = consultation.est_paye === '1' ? 
                                '<span class="badge bg-success">Payé</span>' : 
                                '<span class="badge bg-warning">Non payé</span>';

                            consultationsHtml += `
                                <tr>
                                    <td>${date}</td>
                                    <td>${consultation.motif || 'N/A'}</td>
                                    <td>${consultation.total_honoraire} TND</td>
                                    <td>${statusBadge}</td>
                                    <td>
                                        <a href="admin.php?page=medoffice-consultations&view=${consultation.id}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });

                        consultationsHtml += '</tbody></table></div>';
                    } else {
                        consultationsHtml = '<p class="text-muted">Aucune consultation trouvée.</p>';
                    }

                    // Build appointments list
                    let appointmentsHtml = '';
                    if (appointments.length > 0) {
                        appointmentsHtml = '<div class="table-responsive"><table class="table table-bordered table-sm">';
                        appointmentsHtml += '<thead><tr><th>Date</th><th>Titre</th><th>Statut</th><th>Actions</th></tr></thead><tbody>';

                        appointments.forEach(function(appointment) {
                            const date = new Date(appointment.date_debut).toLocaleString();
                            let statusBadge = '<span class="badge bg-primary">Confirmé</span>';

                            if (appointment.status === 'en attente') {
                                statusBadge = '<span class="badge bg-warning">En attente</span>';
                            } else if (appointment.status === 'annulé') {
                                statusBadge = '<span class="badge bg-danger">Annulé</span>';
                            }

                            appointmentsHtml += `
                                <tr>
                                    <td>${date}</td>
                                    <td>${appointment.titre}</td>
                                    <td>${statusBadge}</td>
                                    <td>
                                        <a href="admin.php?page=medoffice-calendar&event=${appointment.id}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            `;
                        });

                        appointmentsHtml += '</tbody></table></div>';
                    } else {
                        appointmentsHtml = '<p class="text-muted">Aucun rendez-vous trouvé.</p>';
                    }

                    // Build the details HTML
                    const detailsHtml = `
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-user me-2"></i> Informations personnelles</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Nom complet:</div>
                                            <div class="col-md-8">${patient.prenom} ${patient.nom}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Sexe:</div>
                                            <div class="col-md-8">${patient.sexe}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Date de naissance:</div>
                                            <div class="col-md-8">${birthDate}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Âge:</div>
                                            <div class="col-md-8">${age}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Téléphone:</div>
                                            <div class="col-md-8">${patient.telephone}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Email:</div>
                                            <div class="col-md-8">${patient.email || 'N/A'}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Adresse:</div>
                                            <div class="col-md-8">${patient.adresse || 'N/A'}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-notes-medical me-2"></i> Résumé médical</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Date d'ajout:</div>
                                            <div class="col-md-7">${new Date(patient.date_creation).toLocaleString()}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Consultations:</div>
                                            <div class="col-md-7">${consultations.length}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-5 fw-bold">Prochains rendez-vous:</div>
                                            <div class="col-md-7">${appointments.filter(a => new Date(a.date_debut) > new Date()).length}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-12 fw-bold">Notes:</div>
                                            <div class="col-md-12">
                                                <div class="border rounded p-2 mt-1" style="min-height: 100px">
                                                    ${patient.notes || 'Aucune note'}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="patientDetailsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="consultations-tab" data-bs-toggle="tab" data-bs-target="#consultations-tab-pane" type="button" role="tab">
                                    <i class="fas fa-stethoscope me-1"></i> Consultations
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="appointments-tab" data-bs-toggle="tab" data-bs-target="#appointments-tab-pane" type="button" role="tab">
                                    <i class="fas fa-calendar-alt me-1"></i> Rendez-vous
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="patientDetailsTabsContent">
                            <div class="tab-pane fade show active" id="consultations-tab-pane" role="tabpanel" aria-labelledby="consultations-tab" tabindex="0">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Historique des consultations</h5>
                                    <a href="admin.php?page=medoffice-consultations&patient=${patient.id}" class="btn btn-sm btn-success">
                                        <i class="fas fa-plus me-1"></i> Nouvelle consultation
                                    </a>
                                </div>
                                ${consultationsHtml}
                            </div>
                            <div class="tab-pane fade" id="appointments-tab-pane" role="tabpanel" aria-labelledby="appointments-tab" tabindex="0">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Rendez-vous</h5>
                                    <a href="admin.php?page=medoffice-calendar&patient=${patient.id}" class="btn btn-sm btn-success">
                                        <i class="fas fa-plus me-1"></i> Nouveau rendez-vous
                                    </a>
                                </div>
                                ${appointmentsHtml}
                            </div>
                        </div>
                    `;

                    $('#patient-details-content').html(detailsHtml);

                } else {
                    $('#patient-details-content').html('<div class="alert alert-danger">Erreur lors du chargement des détails du patient.</div>');
                }
            },
            error: function() {
                $('#patient-details-content').html('<div class="alert alert-danger">Erreur de communication avec le serveur.</div>');
            }
        });
    }

    /**
     * Edit patient
     */
    function editPatient(patientId) {
        currentPatientId = patientId;

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_patient',
                nonce: medoffice_ajax.nonce,
                patient_id: patientId
            },
            success: function(response) {
                if (response.success) {
                    const patient = response.data.patient;

                    // Fill the form
                    $('#patient_id').val(patient.id);
                    $('#nom').val(patient.nom);
                    $('#prenom').val(patient.prenom);
                    $('#sexe').val(patient.sexe);
                    $('#date_naissance').val(patient.date_naissance ? patient.date_naissance.substring(0, 10) : '');
                    $('#telephone').val(patient.telephone);
                    $('#email').val(patient.email);
                    $('#adresse').val(patient.adresse);
                    $('#notes').val(patient.notes);

                    // Update modal title
                    $('#patientModalLabel').text('Modifier le patient');

                    // Show the modal
                    $('#patientModal').modal('show');
                } else {
                    alert('Erreur lors du chargement des données du patient.');
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            }
        });
    }

    /**
     * Save patient
     */
    function savePatient() {
        const patientForm = $('#patient-form');

        // Basic form validation
        if (!patientForm[0].checkValidity()) {
            patientForm[0].reportValidity();
            return;
        }

        // Prepare patient data
        const patientData = {
            id: $('#patient_id').val(),
            nom: $('#nom').val(),
            prenom: $('#prenom').val(),
            sexe: $('#sexe').val(),
            date_naissance: $('#date_naissance').val(),
            telephone: $('#telephone').val(),
            email: $('#email').val(),
            adresse: $('#adresse').val(),
            notes: $('#notes').val()
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
                $('#save-patient').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
            },
            success: function(response) {
                if (response.success) {
                    $('#patientModal').modal('hide');
                    window.patientsTable.ajax.reload();

                    if (patientData.id === '0') {
                        alert('Patient ajouté avec succès !');
                    } else {
                        alert('Patient mis à jour avec succès !');
                    }
                } else {
                    alert('Erreur lors de l\'enregistrement : ' + (response.data ? response.data.message : 'Erreur inconnue'));
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#save-patient').prop('disabled', false).html('Enregistrer');
            }
        });
    }

    /**
     * Delete patient
     */
    function deletePatient() {
        if (!currentPatientId) {
            return;
        }

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_delete_patient',
                nonce: medoffice_ajax.nonce,
                patient_id: currentPatientId
            },
            beforeSend: function() {
                $('#confirm-delete-patient').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Suppression...');
            },
            success: function(response) {
                if (response.success) {
                    $('#deletePatientModal').modal('hide');
                    window.patientsTable.ajax.reload();
                    alert('Patient supprimé avec succès !');
                } else {
                    alert('Erreur lors de la suppression : ' + (response.data ? response.data.message : 'Erreur inconnue'));
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#confirm-delete-patient').prop('disabled', false).html('Supprimer');
            }
        });
    }

    /**
     * Reset patient form
     */
    function resetPatientForm() {
        $('#patient-form')[0].reset();
        $('#patient_id').val(0);
    }

    /**
     * Document ready handler
     */
    $(document).ready(function() {
        initPatients();
    });

    // Rendre la fonction initPatients accessible globalement
    window.initPatients = initPatients;

})(jQuery);