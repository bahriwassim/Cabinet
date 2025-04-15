<?php
/**
 * Settings view for the plugin
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/admin/partials
 */

// Get existing settings
global $wpdb;
$settings_table = $wpdb->prefix . 'medoffice_settings';
$settings = array();

$results = $wpdb->get_results("SELECT setting_key, setting_value FROM $settings_table");
foreach ($results as $result) {
    $settings[$result->setting_key] = $result->setting_value;
}
?>

<div class="wrap medoffice-settings">
    <h1 class="wp-heading-inline">
        <i class="fas fa-cog"></i> <?php _e('Paramètres', 'medoffice-manager'); ?>
    </h1>
    
    <hr class="wp-header-end">
    
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('Paramètres du cabinet', 'medoffice-manager'); ?>
                    </h6>
                </div>
                <div class="card-body">
                    <form id="settings-form" method="post">
                        <ul class="nav nav-tabs mb-3" id="settingsTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general-tab-pane" type="button" role="tab" aria-controls="general-tab-pane" aria-selected="true">
                                    <i class="fas fa-home"></i> <?php _e('Général', 'medoffice-manager'); ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab" data-bs-target="#contact-tab-pane" type="button" role="tab" aria-controls="contact-tab-pane" aria-selected="false">
                                    <i class="fas fa-envelope"></i> <?php _e('Contact', 'medoffice-manager'); ?>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="appearance-tab" data-bs-toggle="tab" data-bs-target="#appearance-tab-pane" type="button" role="tab" aria-controls="appearance-tab-pane" aria-selected="false">
                                    <i class="fas fa-paint-brush"></i> <?php _e('Apparence', 'medoffice-manager'); ?>
                                </button>
                            </li>
                        </ul>
                        
                        <div class="tab-content" id="settingsTabsContent">
                            <!-- Tab 1: Général -->
                            <div class="tab-pane fade show active" id="general-tab-pane" role="tabpanel" aria-labelledby="general-tab" tabindex="0">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="nom_cabinet" class="form-label"><?php _e('Nom du cabinet', 'medoffice-manager'); ?> *</label>
                                        <input type="text" class="form-control" id="nom_cabinet" name="nom_cabinet" value="<?php echo esc_attr($settings['nom_cabinet'] ?? 'Cabinet Médical'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nom_medecin" class="form-label"><?php _e('Nom du médecin', 'medoffice-manager'); ?></label>
                                        <input type="text" class="form-control" id="nom_medecin" name="nom_medecin" value="<?php echo esc_attr($settings['nom_medecin'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="specialite" class="form-label"><?php _e('Spécialité', 'medoffice-manager'); ?></label>
                                        <input type="text" class="form-control" id="specialite" name="specialite" value="<?php echo esc_attr($settings['specialite'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="adresse_cabinet" class="form-label"><?php _e('Adresse du cabinet', 'medoffice-manager'); ?></label>
                                        <textarea class="form-control" id="adresse_cabinet" name="adresse_cabinet" rows="2"><?php echo esc_textarea($settings['adresse_cabinet'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab 2: Contact -->
                            <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel" aria-labelledby="contact-tab" tabindex="0">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label for="telephone_cabinet" class="form-label"><?php _e('Téléphone du cabinet', 'medoffice-manager'); ?></label>
                                        <input type="tel" class="form-control" id="telephone_cabinet" name="telephone_cabinet" value="<?php echo esc_attr($settings['telephone_cabinet'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email_cabinet" class="form-label"><?php _e('Email du cabinet', 'medoffice-manager'); ?></label>
                                        <input type="email" class="form-control" id="email_cabinet" name="email_cabinet" value="<?php echo esc_attr($settings['email_cabinet'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="whatsapp_numero" class="form-label"><?php _e('Numéro WhatsApp', 'medoffice-manager'); ?></label>
                                    <div class="input-group">
                                        <span class="input-group-text">+</span>
                                        <input type="text" class="form-control" id="whatsapp_numero" name="whatsapp_numero" value="<?php echo esc_attr($settings['whatsapp_numero'] ?? ''); ?>" placeholder="2167XXXXXXX">
                                    </div>
                                    <div class="form-text"><?php _e('Format international sans le "+". Ex: 2167XXXXXXX', 'medoffice-manager'); ?></div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0"><?php _e('Formulaire de contact rapide', 'medoffice-manager'); ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="activer_formulaire_contact" name="activer_formulaire_contact" value="1" <?php checked(($settings['activer_formulaire_contact'] ?? '0'), '1'); ?>>
                                                <label class="form-check-label" for="activer_formulaire_contact"><?php _e('Activer le formulaire de contact', 'medoffice-manager'); ?></label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="email_contact" class="form-label"><?php _e('Email de réception des messages', 'medoffice-manager'); ?></label>
                                            <input type="email" class="form-control" id="email_contact" name="email_contact" value="<?php echo esc_attr($settings['email_contact'] ?? ''); ?>">
                                        </div>
                                        
                                        <p class="alert alert-info">
                                            <?php _e('Pour afficher le formulaire de contact, utilisez le shortcode:', 'medoffice-manager'); ?> 
                                            <code>[medoffice_contact_form]</code>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="card mb-3">
                                    <div class="card-header">
                                        <h6 class="mb-0"><?php _e('Chat WhatsApp', 'medoffice-manager'); ?></h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" role="switch" id="activer_whatsapp" name="activer_whatsapp" value="1" <?php checked(($settings['activer_whatsapp'] ?? '0'), '1'); ?>>
                                                <label class="form-check-label" for="activer_whatsapp"><?php _e('Activer le bouton WhatsApp', 'medoffice-manager'); ?></label>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-3">
                                            <label for="message_whatsapp" class="form-label"><?php _e('Message pré-rempli', 'medoffice-manager'); ?></label>
                                            <textarea class="form-control" id="message_whatsapp" name="message_whatsapp" rows="2"><?php echo esc_textarea($settings['message_whatsapp'] ?? 'Bonjour, je souhaiterais prendre rendez-vous.'); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tab 3: Apparence -->
                            <div class="tab-pane fade" id="appearance-tab-pane" role="tabpanel" aria-labelledby="appearance-tab" tabindex="0">
                                <div class="mb-3">
                                    <label for="logo_cabinet" class="form-label"><?php _e('Logo du cabinet', 'medoffice-manager'); ?></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="logo_cabinet" name="logo_cabinet" value="<?php echo esc_attr($settings['logo_cabinet'] ?? ''); ?>">
                                        <button class="btn btn-outline-secondary" type="button" id="select-logo">
                                            <?php _e('Sélectionner', 'medoffice-manager'); ?>
                                        </button>
                                    </div>
                                    <div class="form-text"><?php _e('URL ou ID du média (utiliser le bouton Sélectionner)', 'medoffice-manager'); ?></div>
                                    
                                    <div class="logo-preview mt-2">
                                        <?php if (!empty($settings['logo_cabinet'])): ?>
                                            <img src="<?php echo esc_url(wp_get_attachment_url($settings['logo_cabinet']) ?: $settings['logo_cabinet']); ?>" alt="Logo" style="max-height: 100px;">
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="couleur_theme" class="form-label"><?php _e('Couleur principale', 'medoffice-manager'); ?></label>
                                    <input type="color" class="form-control form-control-color" id="couleur_theme" name="couleur_theme" value="<?php echo esc_attr($settings['couleur_theme'] ?? '#4e73df'); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <p class="alert alert-info">
                                        <?php _e('Ces paramètres affectent l\'apparence des ordonnances et des impressions.', 'medoffice-manager'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary" id="save-settings">
                                <i class="fas fa-save"></i> <?php _e('Enregistrer les paramètres', 'medoffice-manager'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <?php _e('À propos', 'medoffice-manager'); ?>
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?php _e('MedOffice Manager', 'medoffice-manager'); ?></h5>
                            <p><?php _e('Version:', 'medoffice-manager'); ?> <?php echo MEDOFFICE_MANAGER_VERSION; ?></p>
                            <p><?php _e('Développé par:', 'medoffice-manager'); ?> <a href="https://stikyconsulting.com" target="_blank">StikyConsulting</a></p>
                            <p><?php _e('Pour la gestion de cabinet médical en Tunisie', 'medoffice-manager'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5><?php _e('Besoin d\'aide ?', 'medoffice-manager'); ?></h5>
                            <p><?php _e('Contactez-nous à:', 'medoffice-manager'); ?> <a href="mailto:contact@stikyconsulting.com">contact@stikyconsulting.com</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
