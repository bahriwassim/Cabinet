<?php
/**
 * Dashboard view for the plugin
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/admin/partials
 */
?>

<div class="wrap medoffice-dashboard">
    <h1 class="wp-heading-inline">
        <i class="fas fa-tachometer-alt"></i> <?php _e('Tableau de bord', 'medoffice-manager'); ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <div class="dashboard-nav mb-4">
        <nav class="nav">
            <a class="nav-link active" href="admin.php?page=medoffice-dashboard">
                <i class="fas fa-tachometer-alt"></i> Tableau de bord
            </a>
            <a class="nav-link" href="admin.php?page=medoffice-patients">
                <i class="fas fa-users"></i> Patients
            </a>
            <a class="nav-link" href="admin.php?page=medoffice-consultations">
                <i class="fas fa-stethoscope"></i> Consultations
            </a>
            <a class="nav-link" href="admin.php?page=medoffice-calendar">
                <i class="fas fa-calendar-alt"></i> Rendez-vous
            </a>
            <a class="nav-link" href="admin.php?page=medoffice-settings">
                <i class="fas fa-cog"></i> Paramètres
            </a>
        </nav>
    </div>
    
    <div class="row stats-cards mt-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                <?php _e('Patients', 'medoffice-manager'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-patients">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                <?php _e('Consultations', 'medoffice-manager'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-consultations">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-stethoscope fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                <?php _e('Rendez-vous (Aujourd\'hui)', 'medoffice-manager'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-appointments-today">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                <?php _e('Honoraires non payés', 'medoffice-manager'); ?>
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800" id="unpaid-fees">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('Consultations par mois', 'medoffice-manager'); ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="consultationsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('Prochains rendez-vous', 'medoffice-manager'); ?>
                    </h6>
                    <a href="<?php echo admin_url('admin.php?page=medoffice-calendar'); ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-calendar"></i> <?php _e('Voir tous', 'medoffice-manager'); ?>
                    </a>
                </div>
                <div class="card-body">
                    <div id="upcoming-appointments-list">
                        <div class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('Patients récents', 'medoffice-manager'); ?>
                    </h6>
                    <a href="<?php echo admin_url('admin.php?page=medoffice-patients'); ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-users"></i> <?php _e('Voir tous', 'medoffice-manager'); ?>
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="recent-patients-table" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th><?php _e('Nom complet', 'medoffice-manager'); ?></th>
                                    <th><?php _e('Sexe', 'medoffice-manager'); ?></th>
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
            </div>
        </div>
    </div>
</div>
