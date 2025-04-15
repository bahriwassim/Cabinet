<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      MedOffice_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('MEDOFFICE_MANAGER_VERSION')) {
            $this->version = MEDOFFICE_MANAGER_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'medoffice-manager';

        $this->load_dependencies();
        $this->define_admin_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - MedOffice_Manager_Admin. Defines all hooks for the admin area.
     * - MedOffice_Manager_Patients. Manages patient functionality.
     * - MedOffice_Manager_Consultations. Manages consultation functionality.
     * - MedOffice_Manager_Appointments. Manages appointment functionality.
     * - MedOffice_Manager_Prescriptions. Manages prescription functionality.
     * - MedOffice_Manager_DB. Handles database operations.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-medoffice-manager-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-medoffice-manager-db.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-medoffice-manager-patients.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-medoffice-manager-consultations.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-medoffice-manager-appointments.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-medoffice-manager-prescriptions.php';
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new MedOffice_Manager_Admin($this->get_plugin_name(), $this->get_version());
        
        // Add admin menu
        add_action('admin_menu', array($plugin_admin, 'add_plugin_admin_menu'));
        
        // Add admin styles and scripts
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($plugin_admin, 'enqueue_scripts'));
        
        // Register AJAX handlers
        add_action('wp_ajax_medoffice_save_patient', array('MedOffice_Manager_Patients', 'save_patient'));
        add_action('wp_ajax_medoffice_get_patients', array('MedOffice_Manager_Patients', 'get_patients'));
        add_action('wp_ajax_medoffice_get_patient', array('MedOffice_Manager_Patients', 'get_patient'));
        add_action('wp_ajax_medoffice_delete_patient', array('MedOffice_Manager_Patients', 'delete_patient'));
        
        add_action('wp_ajax_medoffice_save_consultation', array('MedOffice_Manager_Consultations', 'save_consultation'));
        add_action('wp_ajax_medoffice_get_consultations', array('MedOffice_Manager_Consultations', 'get_consultations'));
        add_action('wp_ajax_medoffice_get_consultation', array('MedOffice_Manager_Consultations', 'get_consultation'));
        add_action('wp_ajax_medoffice_delete_consultation', array('MedOffice_Manager_Consultations', 'delete_consultation'));
        
        add_action('wp_ajax_medoffice_save_appointment', array('MedOffice_Manager_Appointments', 'save_appointment'));
        add_action('wp_ajax_medoffice_get_appointments', array('MedOffice_Manager_Appointments', 'get_appointments'));
        add_action('wp_ajax_medoffice_delete_appointment', array('MedOffice_Manager_Appointments', 'delete_appointment'));
        
        add_action('wp_ajax_medoffice_save_prescription', array('MedOffice_Manager_Prescriptions', 'save_prescription'));
        add_action('wp_ajax_medoffice_get_prescription', array('MedOffice_Manager_Prescriptions', 'get_prescription'));
        
        // Dashboard stats
        add_action('wp_ajax_medoffice_get_dashboard_stats', array($plugin_admin, 'get_dashboard_stats'));
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        // Nothing to run as we're directly adding hooks in define_admin_hooks()
    }
}
