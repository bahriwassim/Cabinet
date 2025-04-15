<?php
/**
 * Consultation form template
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/admin/partials
 */
?>

<form id="consultation-form">
    <input type="hidden" id="consultation_id" name="consultation_id" value="0">
    
    <ul class="nav nav-tabs mb-3" id="consultationTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab" aria-controls="info-tab-pane" aria-selected="true">
                <i class="fas fa-info-circle"></i> <?php _e('Informations', 'medoffice-manager'); ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="diagnostic-tab" data-bs-toggle="tab" data-bs-target="#diagnostic-tab-pane" type="button" role="tab" aria-controls="diagnostic-tab-pane" aria-selected="false">
                <i class="fas fa-stethoscope"></i> <?php _e('Diagnostic & Traitement', 'medoffice-manager'); ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ordonnance-tab" data-bs-toggle="tab" data-bs-target="#ordonnance-tab-pane" type="button" role="tab" aria-controls="ordonnance-tab-pane" aria-selected="false">
                <i class="fas fa-prescription"></i> <?php _e('Ordonnance', 'medoffice-manager'); ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="honoraires-tab" data-bs-toggle="tab" data-bs-target="#honoraires-tab-pane" type="button" role="tab" aria-controls="honoraires-tab-pane" aria-selected="false">
                <i class="fas fa-money-bill-alt"></i> <?php _e('Honoraires', 'medoffice-manager'); ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pieces-tab" data-bs-toggle="tab" data-bs-target="#pieces-tab-pane" type="button" role="tab" aria-controls="pieces-tab-pane" aria-selected="false">
                <i class="fas fa-paperclip"></i> <?php _e('Pièces jointes', 'medoffice-manager'); ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes-tab-pane" type="button" role="tab" aria-controls="notes-tab-pane" aria-selected="false">
                <i class="fas fa-sticky-note"></i> <?php _e('Notes internes', 'medoffice-manager'); ?>
            </button>
        </li>
    </ul>
    
    <div class="tab-content" id="consultationTabsContent">
        <!-- Tab 1: Informations -->
        <div class="tab-pane fade show active" id="info-tab-pane" role="tabpanel" aria-labelledby="info-tab" tabindex="0">
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="patient_selector" class="form-label"><?php _e('Patient', 'medoffice-manager'); ?> *</label>
                    <div class="input-group mb-3">
                        <select class="form-select" id="patient_selector" name="patient_id" required>
                            <option value=""><?php _e('Sélectionner un patient', 'medoffice-manager'); ?></option>
                            <!-- Options loaded via AJAX -->
                        </select>
                        <button class="btn btn-outline-primary" type="button" id="create-new-patient">
                            <i class="fas fa-plus"></i> <?php _e('Nouveau', 'medoffice-manager'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="date_consultation" class="form-label"><?php _e('Date de consultation', 'medoffice-manager'); ?> *</label>
                    <input type="datetime-local" class="form-control" id="date_consultation" name="date_consultation" required>
                </div>
                <div class="col-md-6">
                    <label for="motif" class="form-label"><?php _e('Motif de consultation', 'medoffice-manager'); ?></label>
                    <input type="text" class="form-control" id="motif" name="motif">
                </div>
            </div>
            
            <div class="patient-info alert alert-info" style="display: none;">
                <h6 class="alert-heading"><?php _e('Informations du patient', 'medoffice-manager'); ?></h6>
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><?php _e('Nom complet:', 'medoffice-manager'); ?></strong> <span id="patient-name-display"></span></p>
                        <p><strong><?php _e('Sexe:', 'medoffice-manager'); ?></strong> <span id="patient-gender-display"></span></p>
                        <p><strong><?php _e('Âge:', 'medoffice-manager'); ?></strong> <span id="patient-age-display"></span></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><?php _e('Téléphone:', 'medoffice-manager'); ?></strong> <span id="patient-phone-display"></span></p>
                        <p><strong><?php _e('Dernière consultation:', 'medoffice-manager'); ?></strong> <span id="patient-last-visit-display"><?php _e('Première visite', 'medoffice-manager'); ?></span></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tab 2: Diagnostic & Traitement -->
        <div class="tab-pane fade" id="diagnostic-tab-pane" role="tabpanel" aria-labelledby="diagnostic-tab" tabindex="0">
            <div class="mb-3">
                <label for="diagnostic" class="form-label"><?php _e('Diagnostic', 'medoffice-manager'); ?></label>
                <textarea class="form-control" id="diagnostic" name="diagnostic" rows="4"></textarea>
            </div>
            
            <div class="mb-3">
                <label for="traitement" class="form-label"><?php _e('Traitement', 'medoffice-manager'); ?></label>
                <textarea class="form-control" id="traitement" name="traitement" rows="4"></textarea>
            </div>
        </div>
        
        <!-- Tab 3: Ordonnance -->
        <div class="tab-pane fade" id="ordonnance-tab-pane" role="tabpanel" aria-labelledby="ordonnance-tab" tabindex="0">
            <div class="mb-3">
                <label for="ordonnance" class="form-label"><?php _e('Contenu de l\'ordonnance', 'medoffice-manager'); ?></label>
                <textarea class="form-control" id="ordonnance" name="ordonnance" rows="10"></textarea>
            </div>
            
            <div class="row">
                <div class="col-md-12">
                    <button type="button" class="btn btn-info mb-3" id="preview-ordonnance">
                        <i class="fas fa-eye"></i> <?php _e('Aperçu de l\'ordonnance', 'medoffice-manager'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Tab 4: Honoraires -->
        <div class="tab-pane fade" id="honoraires-tab-pane" role="tabpanel" aria-labelledby="honoraires-tab" tabindex="0">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="total_honoraire" class="form-label"><?php _e('Total des honoraires', 'medoffice-manager'); ?> *</label>
                    <div class="input-group">
                        <input type="number" step="0.01" class="form-control" id="total_honoraire" name="total_honoraire" required>
                        <span class="input-group-text">TND</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <label for="est_paye" class="form-label"><?php _e('Statut de paiement', 'medoffice-manager'); ?></label>
                    <select class="form-select" id="est_paye" name="est_paye">
                        <option value="0"><?php _e('Non payé', 'medoffice-manager'); ?></option>
                        <option value="1"><?php _e('Payé', 'medoffice-manager'); ?></option>
                    </select>
                </div>
            </div>
            
            <div id="paiements-section" class="mb-3">
                <h6><?php _e('Paiements par tranches', 'medoffice-manager'); ?></h6>
                <p class="text-muted"><?php _e('Vous pourrez ajouter des paiements après avoir enregistré la consultation.', 'medoffice-manager'); ?></p>
            </div>
        </div>
        
        <!-- Tab 5: Pièces jointes -->
        <div class="tab-pane fade" id="pieces-tab-pane" role="tabpanel" aria-labelledby="pieces-tab" tabindex="0">
            <div class="mb-3">
                <label for="attachements" class="form-label"><?php _e('Ajouter des pièces jointes', 'medoffice-manager'); ?></label>
                <input class="form-control" type="file" id="attachements" name="attachements[]" multiple>
                <div class="form-text"><?php _e('Vous pouvez sélectionner plusieurs fichiers.', 'medoffice-manager'); ?></div>
            </div>
            
            <div id="attachements-list" class="mt-4">
                <h6><?php _e('Pièces jointes existantes', 'medoffice-manager'); ?></h6>
                <p class="text-muted"><?php _e('Les pièces jointes seront disponibles après avoir enregistré la consultation.', 'medoffice-manager'); ?></p>
            </div>
        </div>
        
        <!-- Tab 6: Notes internes -->
        <div class="tab-pane fade" id="notes-tab-pane" role="tabpanel" aria-labelledby="notes-tab" tabindex="0">
            <div class="mb-3">
                <label for="notes_interne" class="form-label"><?php _e('Notes internes (visibles uniquement par le médecin)', 'medoffice-manager'); ?></label>
                <textarea class="form-control" id="notes_interne" name="notes_interne" rows="8"></textarea>
            </div>
        </div>
    </div>
</form>

<!-- Ordonnance Preview Modal -->
<div class="modal fade" id="ordonnancePreviewModal" tabindex="-1" aria-labelledby="ordonnancePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ordonnancePreviewModalLabel"><?php _e('Aperçu de l\'ordonnance', 'medoffice-manager'); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="ordonnance-preview-content">
                    <!-- Will be filled dynamically -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Fermer', 'medoffice-manager'); ?></button>
                <button type="button" class="btn btn-primary" id="print-preview-ordonnance">
                    <i class="fas fa-print"></i> <?php _e('Imprimer', 'medoffice-manager'); ?>
                </button>
            </div>
        </div>
    </div>
</div>
