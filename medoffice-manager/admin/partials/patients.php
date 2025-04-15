<?php
/**
 * Patients view for the plugin
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/admin/partials
 */
?>

<div class="wrap medoffice-patients">
    <h1 class="wp-heading-inline">
        <i class="fas fa-users"></i> <?php _e('Gestion des patients', 'medoffice-manager'); ?>
    </h1>
    
    <a href="#" class="page-title-action" id="add-new-patient">
        <i class="fas fa-plus"></i> <?php _e('Ajouter un patient', 'medoffice-manager'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('Liste des patients', 'medoffice-manager'); ?>
                    </h6>
                    <div class="d-flex">
                        <button class="btn btn-outline-primary btn-sm me-2" id="toggle-view-mode">
                            <i class="fas fa-th-large"></i> <?php _e('Mode grille/liste', 'medoffice-manager'); ?>
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> <?php _e('Filtres', 'medoffice-manager'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                <li>
                                    <a class="dropdown-item filter-option" data-filter="all" href="#">
                                        <?php _e('Tous les patients', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-option" data-filter="male" href="#">
                                        <?php _e('Hommes', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-option" data-filter="female" href="#">
                                        <?php _e('Femmes', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item filter-option" data-filter="recent" href="#">
                                        <?php _e('Patients récents (30 jours)', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="search-box mb-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="patient-search" placeholder="<?php _e('Rechercher un patient par nom, téléphone...', 'medoffice-manager'); ?>">
                        </div>
                    </div>
                    
                    <!-- List View (default) -->
                    <div id="patient-list-view">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="patients-table" width="100%" cellspacing="0">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th><?php _e('Nom complet', 'medoffice-manager'); ?></th>
                                        <th><?php _e('Sexe', 'medoffice-manager'); ?></th>
                                        <th><?php _e('Âge', 'medoffice-manager'); ?></th>
                                        <th><?php _e('Téléphone', 'medoffice-manager'); ?></th>
                                        <th><?php _e('Date d\'ajout', 'medoffice-manager'); ?></th>
                                        <th><?php _e('Actions', 'medoffice-manager'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Filled by AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Grid View (hidden by default) -->
                    <div id="patient-grid-view" class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4" style="display: none;">
                        <!-- Filled by AJAX -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Patient Form Modal -->
    <div class="modal fade" id="patientModal" tabindex="-1" aria-labelledby="patientModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientModalLabel"><?php _e('Ajouter un patient', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="patient-form">
                        <input type="hidden" id="patient_id" name="patient_id" value="0">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nom" class="form-label"><?php _e('Nom', 'medoffice-manager'); ?> *</label>
                                <input type="text" class="form-control" id="nom" name="nom" required>
                            </div>
                            <div class="col-md-6">
                                <label for="prenom" class="form-label"><?php _e('Prénom', 'medoffice-manager'); ?> *</label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="sexe" class="form-label"><?php _e('Sexe', 'medoffice-manager'); ?> *</label>
                                <select class="form-select" id="sexe" name="sexe" required>
                                    <option value=""><?php _e('Sélectionner', 'medoffice-manager'); ?></option>
                                    <option value="Homme"><?php _e('Homme', 'medoffice-manager'); ?></option>
                                    <option value="Femme"><?php _e('Femme', 'medoffice-manager'); ?></option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="date_naissance" class="form-label"><?php _e('Date de naissance', 'medoffice-manager'); ?></label>
                                <input type="date" class="form-control" id="date_naissance" name="date_naissance">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telephone" class="form-label"><?php _e('Téléphone', 'medoffice-manager'); ?> *</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label"><?php _e('Email', 'medoffice-manager'); ?></label>
                                <input type="email" class="form-control" id="email" name="email">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="adresse" class="form-label"><?php _e('Adresse', 'medoffice-manager'); ?></label>
                            <textarea class="form-control" id="adresse" name="adresse" rows="2"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label"><?php _e('Notes', 'medoffice-manager'); ?></label>
                            <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Annuler', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-primary" id="save-patient"><?php _e('Enregistrer', 'medoffice-manager'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Patient Details Modal -->
    <div class="modal fade" id="patientDetailsModal" tabindex="-1" aria-labelledby="patientDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="patientDetailsModalLabel"><?php _e('Détails du patient', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="patient-details-content">
                        <!-- Will be filled with AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Fermer', 'medoffice-manager'); ?></button>
                    <a href="#" class="btn btn-primary" id="edit-patient-btn">
                        <i class="fas fa-edit"></i> <?php _e('Modifier', 'medoffice-manager'); ?>
                    </a>
                    <a href="#" class="btn btn-success" id="add-consultation-btn">
                        <i class="fas fa-stethoscope"></i> <?php _e('Nouvelle consultation', 'medoffice-manager'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deletePatientModal" tabindex="-1" aria-labelledby="deletePatientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deletePatientModalLabel"><?php _e('Confirmer la suppression', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php _e('Êtes-vous sûr de vouloir supprimer ce patient ? Cette action est irréversible.', 'medoffice-manager'); ?></p>
                    <p class="text-danger"><?php _e('Toutes les consultations et rendez-vous associés à ce patient seront également supprimés.', 'medoffice-manager'); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Annuler', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-patient"><?php _e('Supprimer', 'medoffice-manager'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
