<?php
/**
 * Calendar view for the plugin
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/admin/partials
 */
?>

<div class="wrap medoffice-calendar">
    <h1 class="wp-heading-inline">
        <i class="fas fa-calendar-alt"></i> <?php _e('Calendrier des rendez-vous', 'medoffice-manager'); ?>
    </h1>
    
    <a href="#" class="page-title-action" id="add-new-appointment">
        <i class="fas fa-plus"></i> <?php _e('Nouveau rendez-vous', 'medoffice-manager'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('Calendrier', 'medoffice-manager'); ?>
                    </h6>
                    <div class="d-flex calendar-toolbar">
                        <div class="btn-group btn-group-sm me-2">
                            <button type="button" class="btn btn-outline-primary view-option" data-view="dayGridMonth"><?php _e('Mois', 'medoffice-manager'); ?></button>
                            <button type="button" class="btn btn-outline-primary view-option" data-view="timeGridWeek"><?php _e('Semaine', 'medoffice-manager'); ?></button>
                            <button type="button" class="btn btn-outline-primary view-option" data-view="timeGridDay"><?php _e('Jour', 'medoffice-manager'); ?></button>
                            <button type="button" class="btn btn-outline-primary view-option" data-view="listWeek"><?php _e('Liste', 'medoffice-manager'); ?></button>
                        </div>
                        <button class="btn btn-sm btn-outline-secondary me-2" id="today-button">
                            <?php _e('Aujourd\'hui', 'medoffice-manager'); ?>
                        </button>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" id="prev-button">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="btn btn-outline-secondary" id="next-button">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Appointment Modal -->
    <div class="modal fade" id="appointmentModal" tabindex="-1" aria-labelledby="appointmentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="appointmentModalLabel"><?php _e('Nouveau rendez-vous', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="appointment-form">
                        <input type="hidden" id="appointment_id" name="appointment_id" value="0">
                        
                        <div class="mb-3">
                            <label for="appointment_patient_selector" class="form-label"><?php _e('Patient', 'medoffice-manager'); ?> *</label>
                            <div class="input-group">
                                <select class="form-select" id="appointment_patient_selector" name="patient_id" required>
                                    <option value=""><?php _e('Sélectionner un patient', 'medoffice-manager'); ?></option>
                                    <!-- Options loaded via AJAX -->
                                </select>
                                <button class="btn btn-outline-primary" type="button" id="create-new-patient-appointment">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="date_debut" class="form-label"><?php _e('Début', 'medoffice-manager'); ?> *</label>
                                <input type="datetime-local" class="form-control" id="date_debut" name="date_debut" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date_fin" class="form-label"><?php _e('Fin', 'medoffice-manager'); ?> *</label>
                                <input type="datetime-local" class="form-control" id="date_fin" name="date_fin" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="titre" class="form-label"><?php _e('Titre', 'medoffice-manager'); ?> *</label>
                            <input type="text" class="form-control" id="titre" name="titre" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label"><?php _e('Description', 'medoffice-manager'); ?></label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label"><?php _e('Statut', 'medoffice-manager'); ?></label>
                            <select class="form-select" id="status" name="status">
                                <option value="confirmé"><?php _e('Confirmé', 'medoffice-manager'); ?></option>
                                <option value="en attente"><?php _e('En attente', 'medoffice-manager'); ?></option>
                                <option value="annulé"><?php _e('Annulé', 'medoffice-manager'); ?></option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Annuler', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-danger" id="delete-appointment" style="display:none;"><?php _e('Supprimer', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-primary" id="save-appointment"><?php _e('Enregistrer', 'medoffice-manager'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- New Patient Modal for Calendar -->
    <div class="modal fade" id="newPatientModal" tabindex="-1" aria-labelledby="newPatientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newPatientModalLabel"><?php _e('Nouveau patient', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="quick-patient-form">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quick_nom" class="form-label"><?php _e('Nom', 'medoffice-manager'); ?> *</label>
                                <input type="text" class="form-control" id="quick_nom" name="nom" required>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_prenom" class="form-label"><?php _e('Prénom', 'medoffice-manager'); ?> *</label>
                                <input type="text" class="form-control" id="quick_prenom" name="prenom" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="quick_sexe" class="form-label"><?php _e('Sexe', 'medoffice-manager'); ?> *</label>
                                <select class="form-select" id="quick_sexe" name="sexe" required>
                                    <option value=""><?php _e('Sélectionner', 'medoffice-manager'); ?></option>
                                    <option value="Homme"><?php _e('Homme', 'medoffice-manager'); ?></option>
                                    <option value="Femme"><?php _e('Femme', 'medoffice-manager'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="quick_telephone" class="form-label"><?php _e('Téléphone', 'medoffice-manager'); ?> *</label>
                                <input type="tel" class="form-control" id="quick_telephone" name="telephone" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Annuler', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-primary" id="save-quick-patient"><?php _e('Enregistrer', 'medoffice-manager'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
