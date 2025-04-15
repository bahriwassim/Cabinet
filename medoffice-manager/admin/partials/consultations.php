<?php
/**
 * Consultations view for the plugin
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/admin/partials
 */
?>

<div class="wrap medoffice-consultations">
    <h1 class="wp-heading-inline">
        <i class="fas fa-stethoscope"></i> <?php _e('Gestion des consultations', 'medoffice-manager'); ?>
    </h1>
    
    <a href="#" class="page-title-action btn btn-primary btn-sm" id="add-new-consultation">
        <i class="fas fa-plus"></i> <?php _e('Nouvelle consultation', 'medoffice-manager'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('Liste des consultations', 'medoffice-manager'); ?>
                    </h6>
                    <div class="d-flex">
                        <div class="dropdown me-2">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" id="filterConsultationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-filter"></i> <?php _e('Filtres', 'medoffice-manager'); ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterConsultationDropdown">
                                <li>
                                    <a class="dropdown-item filter-consultation-option" data-filter="all" href="#">
                                        <?php _e('Toutes les consultations', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-consultation-option" data-filter="paid" href="#">
                                        <?php _e('Honoraires payés', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-consultation-option" data-filter="unpaid" href="#">
                                        <?php _e('Honoraires non payés', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item filter-consultation-option" data-filter="today" href="#">
                                        <?php _e('Aujourd\'hui', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-consultation-option" data-filter="this-week" href="#">
                                        <?php _e('Cette semaine', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item filter-consultation-option" data-filter="this-month" href="#">
                                        <?php _e('Ce mois', 'medoffice-manager'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="date-range-filter">
                            <div class="input-group input-group-sm">
                                <input type="date" class="form-control form-control-sm" id="date-filter-start">
                                <span class="input-group-text">-</span>
                                <input type="date" class="form-control form-control-sm" id="date-filter-end">
                                <button class="btn btn-outline-primary btn-sm" id="apply-date-filter">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="search-box mb-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" id="consultation-search" placeholder="<?php _e('Rechercher une consultation par patient, diagnostic...', 'medoffice-manager'); ?>">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="consultations-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?php _e('Date', 'medoffice-manager'); ?></th>
                                    <th><?php _e('Patient', 'medoffice-manager'); ?></th>
                                    <th><?php _e('Motif', 'medoffice-manager'); ?></th>
                                    <th><?php _e('Honoraires', 'medoffice-manager'); ?></th>
                                    <th><?php _e('Statut paiement', 'medoffice-manager'); ?></th>
                                    <th><?php _e('Actions', 'medoffice-manager'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filled by AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Consultation Form Modal -->
    <div class="modal fade" id="consultationModal" tabindex="-1" aria-labelledby="consultationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consultationModalLabel"><?php _e('Nouvelle consultation', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'admin/partials/consultation-form.php'; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Annuler', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-primary" id="save-consultation"><?php _e('Enregistrer', 'medoffice-manager'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Consultation Details Modal -->
    <div class="modal fade" id="consultationDetailsModal" tabindex="-1" aria-labelledby="consultationDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="consultationDetailsModalLabel"><?php _e('Détails de la consultation', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="consultation-details-content">
                        <!-- Will be filled with AJAX -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Fermer', 'medoffice-manager'); ?></button>
                    <a href="#" class="btn btn-primary" id="edit-consultation-btn">
                        <i class="fas fa-edit"></i> <?php _e('Modifier', 'medoffice-manager'); ?>
                    </a>
                    <a href="#" class="btn btn-info" id="print-prescription-btn">
                        <i class="fas fa-print"></i> <?php _e('Imprimer ordonnance', 'medoffice-manager'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel"><?php _e('Ajouter un paiement', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="payment-form">
                        <input type="hidden" id="payment_consultation_id" name="consultation_id">
                        
                        <div class="mb-3">
                            <label for="montant" class="form-label"><?php _e('Montant', 'medoffice-manager'); ?> *</label>
                            <div class="input-group">
                                <input type="number" step="0.01" class="form-control" id="montant" name="montant" required>
                                <span class="input-group-text">TND</span>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="date_paiement" class="form-label"><?php _e('Date de paiement', 'medoffice-manager'); ?> *</label>
                            <input type="date" class="form-control" id="date_paiement" name="date_paiement" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="methode_paiement" class="form-label"><?php _e('Méthode de paiement', 'medoffice-manager'); ?></label>
                            <select class="form-select" id="methode_paiement" name="methode_paiement">
                                <option value="Espèces"><?php _e('Espèces', 'medoffice-manager'); ?></option>
                                <option value="Chèque"><?php _e('Chèque', 'medoffice-manager'); ?></option>
                                <option value="Carte bancaire"><?php _e('Carte bancaire', 'medoffice-manager'); ?></option>
                                <option value="Virement"><?php _e('Virement', 'medoffice-manager'); ?></option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes_paiement" class="form-label"><?php _e('Notes', 'medoffice-manager'); ?></label>
                            <textarea class="form-control" id="notes_paiement" name="notes" rows="2"></textarea>
                        </div>
                    </form>
                    
                    <div class="payment-summary mt-4">
                        <h6><?php _e('Résumé', 'medoffice-manager'); ?></h6>
                        <div class="row">
                            <div class="col-6"><?php _e('Total honoraires:', 'medoffice-manager'); ?></div>
                            <div class="col-6 text-end" id="total-honoraires">0.00 TND</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><?php _e('Montant payé:', 'medoffice-manager'); ?></div>
                            <div class="col-6 text-end" id="montant-paye">0.00 TND</div>
                        </div>
                        <div class="row">
                            <div class="col-6"><?php _e('Reste à payer:', 'medoffice-manager'); ?></div>
                            <div class="col-6 text-end" id="reste-payer">0.00 TND</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Annuler', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-primary" id="save-payment"><?php _e('Enregistrer', 'medoffice-manager'); ?></button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConsultationModal" tabindex="-1" aria-labelledby="deleteConsultationModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConsultationModalLabel"><?php _e('Confirmer la suppression', 'medoffice-manager'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p><?php _e('Êtes-vous sûr de vouloir supprimer cette consultation ? Cette action est irréversible.', 'medoffice-manager'); ?></p>
                    <p class="text-danger"><?php _e('Tous les paiements, ordonnances et pièces jointes associés seront également supprimés.', 'medoffice-manager'); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php _e('Annuler', 'medoffice-manager'); ?></button>
                    <button type="button" class="btn btn-danger" id="confirm-delete-consultation"><?php _e('Supprimer', 'medoffice-manager'); ?></button>
                </div>
            </div>
        </div>
    </div>
</div>
