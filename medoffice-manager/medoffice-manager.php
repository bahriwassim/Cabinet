<?php
/**
 * Plugin Name: MedOffice Manager
 * Plugin URI: https://stikyconsulting.com/medoffice-manager
 * Description: Gestion de cabinet médical en Tunisie avec gestion des patients, consultations, ordonnances et rendez-vous
 * Version: 1.0.0
 * Author: StikyConsulting
 * Author URI: https://stikyconsulting.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: medoffice-manager
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Currently plugin version.
 */
define('MEDOFFICE_MANAGER_VERSION', '1.0.0');
define('MEDOFFICE_MANAGER_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MEDOFFICE_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_medoffice_manager() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-medoffice-manager-activator.php';
    MedOffice_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_medoffice_manager() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-medoffice-manager-deactivator.php';
    MedOffice_Manager_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_medoffice_manager');
register_deactivation_hook(__FILE__, 'deactivate_medoffice_manager');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-medoffice-manager.php';

/**
 * Begins execution of the plugin.
 */
function run_medoffice_manager() {
    $plugin = new MedOffice_Manager();
    $plugin->run();
}
run_medoffice_manager();
