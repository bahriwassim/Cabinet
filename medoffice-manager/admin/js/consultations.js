/**
 * Consultations functionality for MedOffice Manager
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 */

(function($) {
    'use strict';

    // Définir les variables globales pour tout le plugin
    let currentConsultationId = 0;
    let currentFilter = 'all';
    let patientsList = [];
    let selectedPatientId = 0;
    let attachmentFiles = [];

    // Supprimer toute référence à consultationsTable pour éviter des conflits
    if (window.consultationsTable) {
        try {
            window.consultationsTable.destroy();
        } catch (e) {
            console.log('Table consultations déjà détruite ou non initialisée');
        }
        window.consultationsTable = null;
    }

    /**
     * Initialize the consultations page
     */
    function initConsultations() {
        console.log('Initialisation de la page consultations...');

        // Only initialize if we're on the consultations page
        if (!$('.medoffice-consultations').length) {
            console.log('Non sur la page consultations, sortie');
            return;
        }

        console.log('Sur la page consultations, initialisation des composants');

        // Vérifier si la table existe dans le DOM
        if (!$('#consultations-table').length) {
            console.error('Erreur: Table #consultations-table non trouvée dans le DOM');
            return;
        }

        // Détruire DataTable existante si elle existe
        if ($.fn.DataTable.isDataTable('#consultations-table')) {
            $('#consultations-table').DataTable().destroy();
            console.log('Table consultations détruite pour réinitialisation');
        }

        // Initialiser une nouvelle table
        console.log('Création nouvelle instance DataTable pour consultations');
        window.consultationsTable = $('#consultations-table').DataTable({
            ajax: {
                url: medoffice_ajax.ajax_url,
                type: 'POST',
                data: function(d) {
                    d.action = 'medoffice_get_consultations';
                    d.nonce = medoffice_ajax.nonce;
                    d.filter = currentFilter;

                    // Add date range filters if present
                    const startDate = $('#date-filter-start').val();
                    const endDate = $('#date-filter-end').val();

                    if (startDate) {
                        d.date_start = startDate;
                    }

                    if (endDate) {
                        d.date_end = endDate;
                    }
                },
                dataSrc: function(response) {
                    if (response.success) {
                        return response.data;
                    } else {
                        console.error('Error fetching consultations:', response.data);
                        return [];
                    }
                }
            },
            columns: [
                { data: "id" },
                { 
                    data: "date_consultation",
                    render: function(data, type, row) {
                        return new Date(data).toLocaleString();
                    }
                },
                { 
                    data: "patient_nom",
                    render: function(data, type, row) {
                        return row.patient_prenom + ' ' + row.patient_nom;
                    }
                },
                { data: "motif" },
                { 
                    data: "total_honoraire",
                    render: function(data, type, row) {
                        return data + ' TND';
                    }
                },
                { 
                    data: 'est_paye',
                    render: function(data, type, row) {
                        if (data === '1') {
                            return '<span class="badge bg-success">Payé</span>';
                        } else {
                            return '<span class="badge bg-warning">Non payé</span>';
                        }
                    }
                },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        let buttons = `
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-info view-consultation" data-id="${row.id}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-primary edit-consultation" data-id="${row.id}">
                                    <i class="fas fa-edit"></i>
                                </button>
                        `;

                        // Add payment button if not fully paid
                        if (row.est_paye !== '1') {
                            buttons += `
                                <button type="button" class="btn btn-sm btn-success add-payment" data-id="${row.id}">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                            `;
                        }

                        buttons += `
                                <button type="button" class="btn btn-sm btn-danger delete-consultation" data-id="${row.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        `;

                        return buttons;
                    }
                }
            ],
            order: [[0, 'desc']],
            pageLength: 10,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
            }
        });

        // Initialize search
        $('#consultation-search').on('keyup', function() {
            window.consultationsTable.search($(this).val()).draw();
        });

        // Add new consultation button
        $('#add-new-consultation').on('click', function() {
            resetConsultationForm();
            currentConsultationId = 0;

            // Set default date to current date/time
            const now = new Date();
            $('#date_consultation').val(formatDatetimeForInput(now));

            // Update modal title
            $('#consultationModalLabel').text('Nouvelle consultation');

            // Show the modal
            $('#consultationModal').modal('show');

            // Load the patients list for the dropdown
            loadPatientsList();
        });

        // Handle consultation filters
        $('.filter-consultation-option').on('click', function(e) {
            e.preventDefault();
            currentFilter = $(this).data('filter');

            // Reset date filters when using predefined filters
            $('#date-filter-start').val('');
            $('#date-filter-end').val('');

            // Reload the table with the new filter
            window.consultationsTable.ajax.reload();
        });

        // Handle date range filter
        $('#apply-date-filter').on('click', function() {
            // Set the filter to custom to avoid conflicts with predefined filters
            currentFilter = 'custom';
            window.consultationsTable.ajax.reload();
        });

        // Save consultation
        $('#save-consultation').on('click', function() {
            saveConsultation();
        });

        // Create new patient from consultation form
        $('#create-new-patient').on('click', function() {
            // Show the patient form modal
            $('#consultationModal').modal('hide');
            resetPatientForm();
            $('#patientModalLabel').text('Ajouter un patient');
            $('#patientModal').modal('show');
        });

        // Handle patient selection change
        // Initialize select2 for better search
        $('#patient_selector').select2({
            placeholder: 'Rechercher un patient...',
            allowClear: true,
            width: '100%',
            language: 'fr',
            minimumInputLength: 2,
            ajax: {
                url: medoffice_ajax.ajax_url,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'medoffice_search_patients',
                        nonce: medoffice_ajax.nonce,
                        search: params.term
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.success ? data.data.map(function(patient) {
                            return {
                                id: patient.id,
                                text: patient.prenom + ' ' + patient.nom + ' - ' + patient.telephone
                            };
                        }) : []
                    };
                }
            }
        }).on('change', function() {
            selectedPatientId = $(this).val();
            if (selectedPatientId) {
                loadPatientInfo(selectedPatientId);
            } else {
                $('.patient-info').hide();
            }
        });

        // Preview ordonnance
        $('#preview-ordonnance').on('click', function() {
            previewOrdonnance();
        });

        // Print preview ordonnance
        $('#print-preview-ordonnance').on('click', function() {
            printOrdonnance();
        });

        // Handle consultation actions (view, edit, delete, payment)
        $('#consultations-table').on('click', '.view-consultation', function() {
            const consultationId = $(this).data('id');
            viewConsultation(consultationId);
        });

        $(document).on('click', '.edit-consultation', function() {
            const consultationId = $(this).data('id');
            editConsultation(consultationId);
        });

        $(document).on('click', '.delete-consultation', function() {
            const consultationId = $(this).data('id');
            currentConsultationId = consultationId;
            $('#deleteConsultationModal').modal('show');
        });

        $('#consultations-table').on('click', '.add-payment', function() {
            const consultationId = $(this).data('id');
            showPaymentModal(consultationId);
        });

        // Confirm delete consultation
        $('#confirm-delete-consultation').on('click', function() {
            deleteConsultation();
        });

        // Consultation details modal actions
        $('#edit-consultation-btn').on('click', function() {
            $('#consultationDetailsModal').modal('hide');
            editConsultation(currentConsultationId);
        });

        $('#print-prescription-btn').on('click', function() {
            printPrescription(currentConsultationId);
        });

        // Payment modal actions
        $('#save-payment').on('click', function() {
            savePayment();
        });

        // File input change handler
        $('#attachements').on('change', function(e) {
            attachmentFiles = e.target.files;
        });

        // Handle patient form submission from within consultation form
        $('#save-patient').on('click', function() {
            const inConsultationContext = $('#consultationModal').hasClass('show');
            if (inConsultationContext) {
                savePatientFromConsultation();
            } else {
                // Normal patient save handled by patients.js
            }
        });

        // Check URL parameters for patient ID
        const urlParams = new URLSearchParams(window.location.search);
        const patientIdParam = urlParams.get('patient');

        if (patientIdParam) {
            // Open new consultation form with preselected patient
            setTimeout(function() {
                $('#add-new-consultation').click();

                // Wait for patient list to load, then select the patient
                const checkPatientListInterval = setInterval(function() {
                    if ($('#patient_selector option').length > 1) {
                        $('#patient_selector').val(patientIdParam).trigger('change');
                        clearInterval(checkPatientListInterval);
                    }
                }, 500);
            }, 500);
        }

        // Check URL parameters for consultation ID to view
        const consultationIdParam = urlParams.get('view');

        if (consultationIdParam) {
            // Open consultation details
            setTimeout(function() {
                viewConsultation(consultationIdParam);
            }, 500);
        }
    }

    /**
     * Load patients list for dropdown
     */
    function loadPatientsList() {
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_patients',
                nonce: medoffice_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    patientsList = response.data;

                    const patientSelector = $('#patient_selector');
                    patientSelector.empty().append('<option value="">Sélectionner un patient</option>');

                    patientsList.forEach(function(patient) {
                        patientSelector.append(`<option value="${patient.id}">${patient.prenom} ${patient.nom} - ${patient.telephone}</option>`);
                    });

                    // If a patient was previously selected, restore the selection
                    if (selectedPatientId) {
                        patientSelector.val(selectedPatientId).trigger('change');
                    }
                }
            }
        });
    }

    /**
     * Load patient info when selected
     */
    function loadPatientInfo(patientId) {
        // Find patient in the list
        const patient = patientsList.find(p => p.id === patientId);

        if (!patient) {
            $('.patient-info').hide();
            return;
        }

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

        // Update the patient info display
        $('#patient-name-display').text(`${patient.prenom} ${patient.nom}`);
        $('#patient-gender-display').text(patient.sexe);
        $('#patient-age-display').text(age);
        $('#patient-phone-display').text(patient.telephone);

        // Get last consultation date
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_patient',
                nonce: medoffice_ajax.nonce,
                patient_id: patientId,
                basic_info: true
            },
            success: function(response) {
                if (response.success && response.data.last_consultation) {
                    const lastDate = new Date(response.data.last_consultation).toLocaleDateString();
                    $('#patient-last-visit-display').text(lastDate);
                } else {
                    $('#patient-last-visit-display').text('Première visite');
                }
            }
        });

        $('.patient-info').show();
    }

    /**
     * Preview ordonnance
     */
    function previewOrdonnance() {
        const patientId = $('#patient_selector').val();
        const ordonnanceContent = $('#ordonnance').val();

        if (!patientId) {
            alert('Veuillez sélectionner un patient.');
            return;
        }

        // Load the prescription template
        $.get(medoffice_ajax.plugin_url + 'admin/partials/prescription-template.php', function(template) {
            // Get patient info
            const patient = patientsList.find(p => p.id === patientId);

            if (!patient) {
                alert('Erreur: Patient non trouvé.');
                return;
            }

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

            // Get cabinet settings
            $.ajax({
                url: medoffice_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'medoffice_get_settings',
                    nonce: medoffice_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const settings = response.data;

                        // Populate the template with data
                        let html = $(template);

                        html.find('#cabinet-name').text(settings.nom_cabinet || 'Cabinet Médical');
                        html.find('#doctor-name').text('Dr. ' + (settings.nom_medecin || '[Nom du Médecin]'));
                        html.find('#doctor-speciality').text(settings.specialite || '[Spécialité]');
                        html.find('#doctor-address').text(settings.adresse_cabinet || '[Adresse du Cabinet]');
                        html.find('#doctor-phone').text(settings.telephone_cabinet || '[Téléphone]');

                        html.find('#patient-fullname').text(`${patient.prenom} ${patient.nom}`);
                        html.find('#patient-age').text(age);

                        const today = new Date().toLocaleDateString();
                        html.find('#prescription-date').text(today);

                        // Handle prescription content
                        const prescriptionContent = ordonnanceContent || 'Aucun contenu dans l\'ordonnance';
                        html.find('#prescription-content').html(prescriptionContent.replace(/\n/g, '<br>'));

                        // Display the preview
                        $('#ordonnance-preview-content').html(html);

                        // Show the modal
                        $('#ordonnancePreviewModal').modal('show');
                    } else {
                        alert('Erreur lors du chargement des paramètres du cabinet.');
                    }
                },
                error: function() {
                    alert('Erreur de communication avec le serveur.');
                }
            });
        });
    }

    /**
     * Print ordonnance preview
     */
    function printOrdonnance() {
        const content = $('#ordonnance-preview-content').html();

        if (!content) {
            alert('Erreur: Contenu de l\'ordonnance non disponible.');
            return;
        }

        // Create a new window for printing
        const printWindow = window.open('', '_blank');

        if (!printWindow) {
            alert('Veuillez autoriser les fenêtres pop-up pour imprimer l\'ordonnance.');
            return;
        }

        // Write HTML content to the new window
        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Ordonnance</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        max-width: 148mm; /* A5 width */
                        margin: 0 auto;
                        padding: 10mm;
                    }
                    .prescription-container {
                        border: 1px solid #ddd;
                        padding: 10mm;
                    }
                    .prescription-header {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 10mm;
                    }
                    .doctor-info {
                        flex: 1;
                    }
                    .prescription-title {
                        display: flex;
                        align-items: center;
                    }
                    .prescription-title h1 {
                        font-size: 20px;
                        margin: 0;
                    }
                    .prescription-info {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 8mm;
                    }
                    .prescription-content {
                        min-height: 100mm;
                        border-top: 1px solid #eee;
                        border-bottom: 1px solid #eee;
                        padding: 5mm 0;
                        margin-bottom: 8mm;
                    }
                    .prescription-footer {
                        display: flex;
                        justify-content: flex-end;
                    }
                    .doctor-signature {
                        text-align: center;
                        width: 50mm;
                    }
                    .signature-line {
                        margin-top: 15mm;
                        border-top: 1px solid #000;
                    }
                    h2 {
                        margin: 0 0 2mm 0;
                        font-size: 16px;
                    }
                    p {
                        margin: 0 0 1mm 0;
                        font-size: 12px;
                    }
                    @media print {
                        body {
                            padding: 0;
                        }
                        .prescription-container {
                            border: none;
                        }
                    }
                </style>
            </head>
            <body>
                ${content}
            </body>
            </html>
        `);

        // Wait for content to load, then print
        printWindow.document.close();
        printWindow.addEventListener('load', function() {
            printWindow.print();
            // Close the window after printing (optional)
            // printWindow.close();
        });
    }

    /**
     * Print prescription from consultation ID
     */
    function printPrescription(consultationId) {
        if (!consultationId) {
            return;
        }

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_prescription',
                nonce: medoffice_ajax.nonce,
                consultation_id: consultationId
            },
            success: function(response) {
                if (response.success) {
                    const prescription = response.data;

                    // Load the prescription template
                    $.get(medoffice_ajax.plugin_url + 'admin/partials/prescription-template.php', function(template) {
                        // Populate the template with data
                        let html = $(template);

                        html.find('#cabinet-name').text(prescription.nom_cabinet || 'Cabinet Médical');
                        html.find('#doctor-name').text('Dr. ' + (prescription.nom_medecin || '[Nom du Médecin]'));
                        html.find('#doctor-speciality').text(prescription.specialite || '[Spécialité]');
                        html.find('#doctor-address').text(prescription.adresse_cabinet || '[Adresse du Cabinet]');
                        html.find('#doctor-phone').text(prescription.telephone_cabinet || '[Téléphone]');

                        html.find('#patient-fullname').text(prescription.patient_name || '[Nom du Patient]');
                        html.find('#patient-age').text(prescription.patient_age || '[Âge]');

                        const date = new Date(prescription.date_consultation).toLocaleDateString();
                        html.find('#prescription-date').text(date);

                        // Handle prescription content
                        const prescriptionContent = prescription.contenu || 'Aucun contenu dans l\'ordonnance';
                        html.find('#prescription-content').html(prescriptionContent.replace(/\n/g, '<br>'));

                        // Create a new window for printing
                        const printWindow = window.open('', '_blank');

                        if (!printWindow) {
                            alert('Veuillez autoriser les fenêtres pop-up pour imprimer l\'ordonnance.');
                            return;
                        }

                        // Write HTML content to the new window
                        printWindow.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Ordonnance</title>
                                <style>
                                    body {
                                        font-family: Arial, sans-serif;
                                        max-width: 148mm; /* A5 width */
                                        margin: 0 auto;
                                        padding: 10mm;
                                    }
                                    .prescription-container {
                                        border: 1px solid #ddd;
                                        padding: 10mm;
                                    }
                                    .prescription-header {
                                        display: flex;
                                        justify-content: space-between;
                                        margin-bottom: 10mm;
                                    }
                                    .doctor-info {
                                        flex: 1;
                                    }
                                    .prescription-title {
                                        display: flex;
                                        align-items: center;
                                    }
                                    .prescription-title h1 {
                                        font-size: 20px;
                                        margin: 0;
                                    }
                                    .prescription-info {
                                        display: flex;
                                        justify-content: space-between;
                                        margin-bottom: 8mm;
                                    }
                                    .prescription-content {
                                        min-height: 100mm;
                                        border-top: 1px solid #eee;
                                        border-bottom: 1px solid #eee;
                                        padding: 5mm 0;
                                        margin-bottom: 8mm;
                                    }
                                    .prescription-footer {
                                        display: flex;
                                        justify-content: flex-end;
                                    }
                                    .doctor-signature {
                                        text-align: center;
                                        width: 50mm;
                                    }
                                    .signature-line {
                                        margin-top: 15mm;
                                        border-top: 1px solid #000;
                                    }
                                    h2 {
                                        margin: 0 0 2mm 0;
                                        font-size: 16px;
                                    }
                                    p {
                                        margin: 0 0 1mm 0;
                                        font-size: 12px;
                                    }
                                    @media print {
                                        body {
                                            padding: 0;
                                        }
                                        .prescription-container {
                                            border: none;
                                        }
                                    }
                                </style>
                            </head>
                            <body>
                                ${html[0].outerHTML}
                            </body>
                            </html>
                        `);

                        // Wait for content to load, then print
                        printWindow.document.close();
                        printWindow.addEventListener('load', function() {
                            printWindow.print();
                            // Close the window after printing (optional)
                            // printWindow.close();
                        });
                    });
                } else {
                    alert('Erreur lors du chargement de l\'ordonnance.');
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            }
        });
    }

    /**
     * View consultation details
     */
    function viewConsultation(consultationId) {
        currentConsultationId = consultationId;

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_consultation',
                nonce: medoffice_ajax.nonce,
                consultation_id: consultationId
            },
            beforeSend: function() {
                $('#consultation-details-content').html('<div class="text-center p-4"><div class="spinner-border text-primary" role="status"></div></div>');
                $('#consultationDetailsModal').modal('show');
            },
            success: function(response) {
                if (response.success) {
                    const consultation = response.data.consultation;
                    const patient = response.data.patient;
                    const payments = response.data.payments || [];
                    const attachments = response.data.attachments || [];

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

                    // Calculate total payments
                    let totalPaid = 0;
                    payments.forEach(function(payment) {
                        totalPaid += parseFloat(payment.montant);
                    });

                    // Calculate remaining amount
                    const totalHonoraire = parseFloat(consultation.total_honoraire);
                    const remainingAmount = totalHonoraire - totalPaid;

                    // Format date
                    const consultationDate = new Date(consultation.date_consultation).toLocaleString();

                    // Build payments list
                    let paymentsHtml = '';
                    if (payments.length > 0) {
                        paymentsHtml = '<div class="table-responsive mt-3"><table class="table table-bordered table-sm">';
                        paymentsHtml += '<thead><tr><th>Date</th><th>Montant</th><th>Méthode</th><th>Notes</th></tr></thead><tbody>';

                        payments.forEach(function(payment) {
                            const paymentDate = new Date(payment.date_paiement).toLocaleDateString();

                            paymentsHtml += `
                                <tr>
                                    <td>${paymentDate}</td>
                                    <td>${payment.montant} TND</td>
                                    <td>${payment.methode_paiement}</td>
                                    <td>${payment.notes || '-'}</td>
                                </tr>
                            `;
                        });

                        paymentsHtml += '</tbody></table></div>';
                    } else {
                        paymentsHtml = '<p class="text-muted">Aucun paiement enregistré.</p>';
                    }

                    // Build attachments list
                    let attachmentsHtml = '';
                    if (attachments.length > 0) {
                        attachmentsHtml = '<div class="list-group mt-3">';

                        attachments.forEach(function(attachment) {
                            const fileIcon = getFileIconClass(attachment.type_fichier);

                            attachmentsHtml += `
                                <a href="${attachment.url_fichier}" class="list-group-item list-group-item-action" target="_blank">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><i class="${fileIcon} me-2"></i> ${attachment.nom_fichier}</h6>
                                        <small>${formatFileSize(attachment.taille_fichier)}</small>
                                    </div>
                                    <small class="text-muted">Ajouté le ${new Date(attachment.date_creation).toLocaleDateString()}</small>
                                </a>
                            `;
                        });

                        attachmentsHtml += '</div>';
                    } else {
                        attachmentsHtml = '<p class="text-muted">Aucune pièce jointe.</p>';
                    }

                    // Build the details HTML
                    const detailsHtml = `
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i> Informations générales</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Date:</div>
                                            <div class="col-md-8">${consultationDate}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-4 fw-bold">Patient:</div>
                                            <div class="col-md-8">${patient.prenom} ${patient.nom}</div>
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
                                            <div class="col-md-4 fw-bold">Motif:</div>
                                            <div class="col-md-8">${consultation.motif || '-'}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-money-bill-alt me-2"></i> Honoraires</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-2">
                                            <div class="col-md-6 fw-bold">Total honoraires:</div>
                                            <div class="col-md-6 text-end">${consultation.total_honoraire} TND</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6 fw-bold">Montant payé:</div>
                                            <div class="col-md-6 text-end">${totalPaid.toFixed(2)} TND</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6 fw-bold">Reste à payer:</div>
                                            <div class="col-md-6 text-end ${remainingAmount > 0 ? 'text-danger' : 'text-success'}">${remainingAmount.toFixed(2)} TND</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-6 fw-bold">Statut:</div>
                                            <div class="col-md-6 text-end">
                                                ${consultation.est_paye === '1' ? 
                                                    '<span class="badge bg-success">Payé</span>' : 
                                                    '<span class="badge bg-warning">Non payé</span>'}
                                            </div>
                                        </div>

                                        <h6 class="mt-4 mb-2">Historique des paiements</h6>
                                        ${paymentsHtml}

                                        ${remainingAmount > 0 ? `
                                            <div class="mt-3">
                                                <button type="button" class="btn btn-sm btn-success add-payment" data-id="${consultation.id}">
                                                    <i class="fas fa-plus me-1"></i> Ajouter un paiement
                                                </button>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-stethoscope me-2"></i> Diagnostic & Traitement</h5>
                                    </div>
                                    <div class="card-body">
                                        <h6>Diagnostic</h6>
                                        <div class="border rounded p-2 mb-3" style="min-height: 80px">
                                            ${consultation.diagnostic ? consultation.diagnostic.replace(/\n/g, '<br>') : 'Aucun diagnostic renseigné'}
                                        </div>

                                        <h6>Traitement</h6>
                                        <div class="border rounded p-2 mb-3" style="min-height: 80px">
                                            ${consultation.traitement ? consultation.traitement.replace(/\n/g, '<br>') : 'Aucun traitement renseigné'}
                                        </div>

                                        <h6>Notes internes</h6>
                                        <div class="border rounded p-2" style="min-height: 80px">
                                            ${consultation.notes_interne ? consultation.notes_interne.replace(/\n/g, '<br>') : 'Aucune note'}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="consultationDetailsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="ordonnance-detail-tab" data-bs-toggle="tab" data-bs-target="#ordonnance-detail-tab-pane" type="button" role="tab">
                                    <i class="fas fa-prescription me-1"></i> Ordonnance
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="attachments-detail-tab" data-bs-toggle="tab" data-bs-target="#attachments-detail-tab-pane" type="button" role="tab">
                                    <i class="fas fa-paperclip me-1"></i> Pièces jointes
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="consultationDetailsTabsContent">
                            <div class="tab-pane fade show active" id="ordonnance-detail-tab-pane" role="tabpanel" aria-labelledby="ordonnance-detail-tab" tabindex="0">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Contenu de l'ordonnance</h5>
                                    <button type="button" class="btn btn-sm btn-info print-ordonnance" data-id="${consultation.id}">
                                        <i class="fas fa-print me-1"></i> Imprimer
                                    </button>
                                </div>
                                <div class="border rounded p-3" style="min-height: 200px">
                                    ${consultation.ordonnance ? consultation.ordonnance.replace(/\n/g, '<br>') : 'Aucune ordonnance'}
                                </div>
                            </div>
                            <div class="tab-pane fade" id="attachments-detail-tab-pane" role="tabpanel" aria-labelledby="attachments-detail-tab" tabindex="0">
                                <div class="d-flex justify-content-between mb-3">
                                    <h5>Pièces jointes</h5>
                                </div>
                                ${attachmentsHtml}
                            </div>
                        </div>
                    `;

                    $('#consultation-details-content').html(detailsHtml);

                    // Handle print ordonnance button
                    $('.print-ordonnance').on('click', function() {
                        printPrescription($(this).data('id'));
                    });

                    // Handle add payment button
                    $('.add-payment').on('click', function() {
                        $('#consultationDetailsModal').modal('hide');
                        showPaymentModal($(this).data('id'));
                    });

                } else {
                    $('#consultation-details-content').html('<div class="alert alert-danger">Erreur lors du chargement des détails de la consultation.</div>');
                }
            },
            error: function() {
                $('#consultation-details-content').html('<div class="alert alert-danger">Erreur de communication avec le serveur.</div>');
            }
        });
    }

    /**
     * Edit consultation
     */
    function editConsultation(consultationId) {
        currentConsultationId = consultationId;

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_consultation',
                nonce: medoffice_ajax.nonce,
                consultation_id: consultationId
            },
            success: function(response) {
                if (response.success) {
                    resetConsultationForm();

                    const consultation = response.data.consultation;
                    const attachments = response.data.attachments || [];

                    // Load patients list first
                    loadPatientsList();

                    // Fill the form
                    $('#consultation_id').val(consultation.id);

                    // Format the date for datetime-local input
                    const consultationDate = new Date(consultation.date_consultation);
                    $('#date_consultation').val(formatDatetimeForInput(consultationDate));

                    $('#motif').val(consultation.motif);
                    $('#diagnostic').val(consultation.diagnostic);
                    $('#traitement').val(consultation.traitement);
                    $('#ordonnance').val(consultation.ordonnance);
                    $('#total_honoraire').val(consultation.total_honoraire);
                    $('#est_paye').val(consultation.est_paye);
                    $('#notes_interne').val(consultation.notes_interne);

                    // Set the patient ID and trigger change to show patient info
                    selectedPatientId = consultation.patient_id;
                    setTimeout(function() {
                        $('#patient_selector').val(consultation.patient_id).trigger('change');
                    }, 500);

                    // List existing attachments
                    if (attachments.length > 0) {
                        let attachmentsHtml = '<ul class="list-group">';

                        attachments.forEach(function(attachment) {
                            const fileIcon = getFileIconClass(attachment.type_fichier);

                            attachmentsHtml += `
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="${fileIcon} me-2"></i>
                                        <a href="${attachment.url_fichier}" target="_blank">${attachment.nom_fichier}</a>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">${formatFileSize(attachment.taille_fichier)}</span>
                                </li>
                            `;
                        });

                        attachmentsHtml += '</ul>';
                        $('#attachements-list').html(attachmentsHtml);
                    } else {
                        $('#attachements-list').html('<p class="text-muted">Aucune pièce jointe.</p>');
                    }

                    // Update modal title
                    $('#consultationModalLabel').text('Modifier la consultation');

                    // Show the modal
                    $('#consultationModal').modal('show');
                } else {
                    alert('Erreur lors du chargement des données de la consultation.');
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            }
        });
    }

    /**
     * Show payment modal
     */
    function showPaymentModal(consultationId) {
        currentConsultationId = consultationId;

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_consultation',
                nonce: medoffice_ajax.nonce,
                consultation_id: consultationId,
                include_payments: true
            },
            success: function(response) {
                if (response.success) {
                    const consultation = response.data.consultation;
                    const payments = response.data.payments || [];

                    // Calculate total payments
                    let totalPaid = 0;
                    payments.forEach(function(payment) {
                        totalPaid += parseFloat(payment.montant);
                    });

                    // Calculate remaining amount
                    const totalHonoraire = parseFloat(consultation.total_honoraire);
                    const remainingAmount = totalHonoraire - totalPaid;

                    // Set values in the payment form
                    $('#payment_consultation_id').val(consultationId);
                    $('#date_paiement').val(new Date().toISOString().split('T')[0]);
                    $('#montant').val(remainingAmount.toFixed(2));

                    // Update payment summary
                    $('#total-honoraires').text(totalHonoraire.toFixed(2) + ' TND');
                    $('#montant-paye').text(totalPaid.toFixed(2) + ' TND');
                    $('#reste-payer').text(remainingAmount.toFixed(2) + ' TND');

                    // Show the modal
                    $('#paymentModal').modal('show');
                } else {
                    alert('Erreur lors du chargement des données de la consultation.');
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            }
        });
    }

    /**
     * Save payment
     */
    /**
 * Handle payment installments
 */
function saveInstallment(consultationId, date, amount) {
    return $.ajax({
        url: medoffice_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'medoffice_save_installment',
            nonce: medoffice_ajax.nonce,
            consultation_id: consultationId,
            date_echeance: date,
            montant: amount
        }
    });
}

function savePayment() {
        const paymentForm = $('#payment-form');

        // Basic form validation
        if (!paymentForm[0].checkValidity()) {
            paymentForm[0].reportValidity();
            return;
        }

        // Prepare payment data
        const paymentData = {
            consultation_id: $('#payment_consultation_id').val(),
            montant: $('#montant').val(),
            date_paiement: $('#date_paiement').val(),
            methode_paiement: $('#methode_paiement').val(),
            notes: $('#notes_paiement').val()
        };

        // Save to server
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_save_payment',
                nonce: medoffice_ajax.nonce,
                payment: paymentData
            },
            beforeSend: function() {
                $('#save-payment').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
            },
            success: function(response) {
                if (response.success) {
                    $('#paymentModal').modal('hide');
                    window.consultationsTable.ajax.reload();

                    alert('Paiement enregistré avec succès !');

                    // If the consultation details modal was open, refresh it
                    if ($('#consultationDetailsModal').hasClass('show')) {
                        viewConsultation(currentConsultationId);
                    }
                } else {
                    alert('Erreur lors de l\'enregistrement : ' + (response.data ? response.data.message : 'Erreur inconnue'));
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#save-payment').prop('disabled', false).html('Enregistrer');
            }
        });
    }

    /**
     * Save consultation
     */
    function saveConsultation() {
        const consultationForm = $('#consultation-form');

        // Basic form validation
        if (!consultationForm[0].checkValidity()) {
            // Find the first invalid tab and activate it
            const invalidTab = consultationForm.find(':invalid').closest('.tab-pane').attr('id');
            if (invalidTab) {
                $(`button[data-bs-target="#${invalidTab}"]`).tab('show');
            }

            consultationForm[0].reportValidity();
            return;
        }

        // Prepare consultation data
        const consultationData = new FormData();

        // Basic fields
        consultationData.append('id', $('#consultation_id').val());
        consultationData.append('patient_id', $('#patient_selector').val());
        consultationData.append('date_consultation', $('#date_consultation').val());
        consultationData.append('motif', $('#motif').val());
        consultationData.append('diagnostic', $('#diagnostic').val());
        consultationData.append('traitement', $('#traitement').val());
        consultationData.append('ordonnance', $('#ordonnance').val());
        consultationData.append('total_honoraire', $('#total_honoraire').val());
        consultationData.append('est_paye', $('#est_paye').val());
        consultationData.append('notes_interne', $('#notes_interne').val());

        // Add attachments if any
        if (attachmentFiles.length > 0) {
            for (let i = 0; i < attachmentFiles.length; i++) {
                consultationData.append('attachments[]', attachmentFiles[i]);
            }
        }

        // Add action and nonce
        consultationData.append('action', 'medoffice_save_consultation');
        consultationData.append('nonce', medoffice_ajax.nonce);

        // Save to server
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: consultationData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#save-consultation').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enregistrement...');
            },
            success: function(response) {
                if (response.success) {
                    $('#consultationModal').modal('hide');
                    window.consultationsTable.ajax.reload();

                    if ($('#consultation_id').val() === '0') {
                        alert('Consultation ajoutée avec succès !');
                    } else {
                        alert('Consultation mise à jour avec succès !');
                    }
                } else {
                    alert('Erreur lors de l\'enregistrement : ' + (response.data ? response.data.message : 'Erreur inconnue'));
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#save-consultation').prop('disabled', false).html('Enregistrer');
            }
        });
    }

    /**
     * Delete consultation
     */
    function deleteConsultation() {
        if (!currentConsultationId) {
            return;
        }

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_delete_consultation',
                nonce: medoffice_ajax.nonce,
                consultation_id: currentConsultationId
            },
            beforeSend: function() {
                $('#confirm-delete-consultation').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Suppression...');
            },
            success: function(response) {
                if (response.success) {
                    $('#deleteConsultationModal').modal('hide');
                    window.consultationsTable.ajax.reload();
                    alert('Consultation supprimée avec succès !');
                } else {
                    alert('Erreur lors de la suppression : ' + (response.data ? response.data.message : 'Erreur inconnue'));
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            },
            complete: function() {
                $('#confirm-delete-consultation').prop('disabled', false).html('Supprimer');
            }
        });
    }

    /**
     * Save patient from the consultation form
     */
    function savePatientFromConsultation() {
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

                    // Reload patients list and select the new patient
                    const newPatientId = response.data.patient_id;
                    selectedPatientId = newPatientId;
                    loadPatientsList();

                    // Show the consultation modal again
                    $('#consultationModal').modal('show');

                    // Reset patient form
                    resetPatientForm();
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
     * Reset patient form
     */
    function resetPatientForm() {
        $('#patient-form')[0].reset();
        $('#patient_id').val(0);
    }

    /**
     * Reset consultation form
     */
    function resetConsultationForm() {
        $('#consultation-form')[0].reset();
        $('#consultation_id').val(0);
        $('.patient-info').hide();
        $('#attachements-list').html('<p class="text-muted">Les pièces jointes seront disponibles après avoir enregistré la consultation.</p>');
        $('#attachements').val('');
        attachmentFiles = [];

        // Reset the active tab to the first one
        $('#info-tab').tab('show');
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
     * Get file icon class based on MIME type
     */
    function getFileIconClass(mimeType) {
        if (!mimeType) {
            return 'fas fa-file';
        }

        if (mimeType.startsWith('image/')) {
            return 'fas fa-file-image';
        } else if (mimeType.startsWith('application/pdf')) {
            return 'fas fa-file-pdf';
        } else if (mimeType.startsWith('application/msword') || mimeType.includes('wordprocessingml')) {
            return 'fas fa-file-word';
        } else if (mimeType.startsWith('application/vnd.ms-excel') || mimeType.includes('spreadsheetml')) {
            return 'fas fa-file-excel';
        } else if (mimeType.startsWith('application/vnd.ms-powerpoint') || mimeType.includes('presentationml')) {
            return 'fas fa-file-powerpoint';
        } else if (mimeType.startsWith('text/')) {
            return 'fas fa-file-alt';
        } else if (mimeType.startsWith('audio/')) {
            return 'fas fa-file-audio';
        } else if (mimeType.startsWith('video/')) {
            return 'fas fa-file-video';
        } else if (mimeType.includes('zip') || mimeType.includes('compressed')) {
            return 'fas fa-file-archive';
        } else {
            return 'fas fa-file';
        }
    }

    /**
     * Format file size in human-readable format
     */
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));

        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Document ready handler
     */
    $(document).ready(function() {
        initConsultations();
    });

    // Rendre la fonction initConsultations accessible globalement
    window.initConsultations = initConsultations;

})(jQuery);
/**
     * Show payment installments modal
     */
    function showInstallmentsModal(consultationId) {
        currentConsultationId = consultationId;

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_consultation',
                nonce: medoffice_ajax.nonce,
                consultation_id: consultationId,
                include_payments: true,
                include_installments: true
            },
            success: function(response) {
                if (response.success) {
                    const consultation = response.data.consultation;
                    const payments = response.data.payments || [];
                    const installments = response.data.installments || [];

                    // Calculate total and remaining amounts
                    let totalPaid = 0;
                    payments.forEach(function(payment) {
                        totalPaid += parseFloat(payment.montant);
                    });

                    const totalHonoraire = parseFloat(consultation.total_honoraire);
                    const remainingAmount = totalHonoraire - totalPaid;

                    // Update modal content
                    let installmentsHtml = `
                        <div class="mb-3">
                            <label>Montant total: ${totalHonoraire} TND</label><br>
                            <label>Montant payé: ${totalPaid} TND</label><br>
                            <label>Reste à payer: ${remainingAmount} TND</label>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date échéance</th>
                                    <th>Montant</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="installments-list">
                    `;

                    installments.forEach(function(installment) {
                        installmentsHtml += `
                            <tr>
                                <td>${new Date(installment.date_echeance).toLocaleDateString()}</td>
                                <td>${installment.montant} TND</td>
                                <td>${installment.est_paye ? '<span class="badge bg-success">Payé</span>' : '<span class="badge bg-warning">En attente</span>'}</td>
                                <td>
                                    <button class="btn btn-sm btn-success pay-installment" data-id="${installment.id}">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    installmentsHtml += `
                            </tbody>
                        </table>
                        <button type="button" class="btn btn-primary" id="add-installment">
                            <i class="fas fa-plus"></i> Ajouter une échéance
                        </button>
                    `;

                    $('#installments-modal-content').html(installmentsHtml);
                    $('#installmentsModal').modal('show');

                    // Add event handlers
                    $('#add-installment').on('click', function() {
                        addInstallment(consultationId, remainingAmount);
                    });

                    $('.pay-installment').on('click', function() {
                        const installmentId = $(this).data('id');
                        payInstallment(installmentId);
                    });
                }
            }
        });
    }

    /**
     * Add new installment
     */
    function addInstallment(consultationId, remainingAmount) {
        const date = prompt("Date d'échéance (YYYY-MM-DD):");
        const amount = prompt("Montant (TND):", remainingAmount);

        if (!date || !amount) return;

        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_add_installment',
                nonce: medoffice_ajax.nonce,
                consultation_id: consultationId,
                date_echeance: date,
                montant: amount
            },
            success: function(response) {
                if (response.success) {
                    showInstallmentsModal(consultationId);
                }
            }
        });
    }