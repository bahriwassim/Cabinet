<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/admin
 */

class MedOffice_Manager_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        // Vérifie si nous sommes sur une page de notre plugin
        $screen = get_current_screen();
        
        // Nous ajoutons des styles à toutes les pages d'administration pour éviter les problèmes
        // (plus sécuritaire que de filtrer uniquement sur certaines pages)

        // Bibliothèques CSS externes
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css', array(), '5.2.3', 'all');
        wp_enqueue_style('datatables-bs5', 'https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css', array('bootstrap'), '1.13.4', 'all');
        wp_enqueue_style('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css', array(), '5.11.3', 'all');
        wp_enqueue_style('fontawesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0', 'all');
        
        // Notre CSS personnalisé (chargé en dernier pour pouvoir surcharger les styles des bibliothèques)
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/medoffice-manager-admin.css', array('bootstrap', 'datatables-bs5', 'fullcalendar', 'fontawesome'), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        // Nous ne filtrons plus par écran pour éviter les problèmes
        // Assurons-nous que jQuery est chargé en premier
        wp_enqueue_script('jquery');
        
        // Bibliothèques externes
        wp_enqueue_script('bootstrap-bundle', 'https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js', array('jquery'), '5.2.3', true);
        wp_enqueue_script('chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js', array('jquery'), '4.3.0', true);
        wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array('jquery'), '1.13.4', true);
        wp_enqueue_script('datatables-bs5', 'https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js', array('jquery', 'datatables', 'bootstrap-bundle'), '1.13.4', true);
        wp_enqueue_script('fullcalendar', 'https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js', array('jquery'), '5.11.3', true);
        wp_enqueue_script('jspdf', 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js', array('jquery'), '2.5.1', true);
        
        // Script d'initialisation Bootstrap (doit être chargé avant nos scripts personnalisés)
        wp_enqueue_script('bootstrap-init', plugin_dir_url(__FILE__) . 'js/bootstrap-init.js', array('jquery', 'bootstrap-bundle'), $this->version, true);
        
        // Scripts du plugin (chargés dans le footer pour s'assurer que les dépendances sont disponibles)
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/medoffice-manager-admin.js', array('jquery', 'bootstrap-bundle', 'bootstrap-init'), $this->version, true);
        wp_enqueue_script('medoffice-patients', plugin_dir_url(__FILE__) . 'js/patients.js', array('jquery', 'bootstrap-bundle', 'datatables', 'datatables-bs5', 'bootstrap-init'), $this->version, true);
        wp_enqueue_script('medoffice-consultations', plugin_dir_url(__FILE__) . 'js/consultations.js', array('jquery', 'bootstrap-bundle', 'datatables', 'datatables-bs5', 'bootstrap-init'), $this->version, true);
        wp_enqueue_script('medoffice-calendar', plugin_dir_url(__FILE__) . 'js/calendar.js', array('jquery', 'bootstrap-bundle', 'fullcalendar', 'bootstrap-init'), $this->version, true);
        wp_enqueue_script('medoffice-prescription', plugin_dir_url(__FILE__) . 'js/prescription.js', array('jquery', 'bootstrap-bundle', 'jspdf', 'bootstrap-init'), $this->version, true);

        // Paramètres AJAX pour tous les scripts
        $ajax_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('medoffice_nonce'),
            'plugin_url' => MEDOFFICE_MANAGER_PLUGIN_URL
        );
        
        wp_localize_script($this->plugin_name, 'medoffice_ajax', $ajax_data);
        wp_localize_script('medoffice-patients', 'medoffice_ajax', $ajax_data);
        wp_localize_script('medoffice-consultations', 'medoffice_ajax', $ajax_data);
        wp_localize_script('medoffice-calendar', 'medoffice_ajax', $ajax_data);
        wp_localize_script('medoffice-prescription', 'medoffice_ajax', $ajax_data);
    }

    /**
     * Add menu items for the plugin.
     */
    public function add_plugin_admin_menu() {
        $icon = 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(MEDOFFICE_MANAGER_PLUGIN_DIR . 'assets/medoffice-icon.svg'));
        
        add_menu_page(
            __('MedOffice Manager', 'medoffice-manager'),
            __('MedOffice', 'medoffice-manager'),
            'manage_options',
            'medoffice-manager',
            array($this, 'display_plugin_admin_dashboard'),
            $icon,
            26
        );
        
        add_submenu_page(
            'medoffice-manager',
            __('Tableau de bord', 'medoffice-manager'),
            __('Tableau de bord', 'medoffice-manager'),
            'manage_options',
            'medoffice-manager',
            array($this, 'display_plugin_admin_dashboard')
        );
        
        add_submenu_page(
            'medoffice-manager',
            __('Patients', 'medoffice-manager'),
            __('Patients', 'medoffice-manager'),
            'manage_options',
            'medoffice-patients',
            array($this, 'display_plugin_admin_patients')
        );
        
        add_submenu_page(
            'medoffice-manager',
            __('Consultations', 'medoffice-manager'),
            __('Consultations', 'medoffice-manager'),
            'manage_options',
            'medoffice-consultations',
            array($this, 'display_plugin_admin_consultations')
        );
        
        add_submenu_page(
            'medoffice-manager',
            __('Calendrier', 'medoffice-manager'),
            __('Calendrier', 'medoffice-manager'),
            'manage_options',
            'medoffice-calendar',
            array($this, 'display_plugin_admin_calendar')
        );
        
        add_submenu_page(
            'medoffice-manager',
            __('Réglages', 'medoffice-manager'),
            __('Réglages', 'medoffice-manager'),
            'manage_options',
            'medoffice-settings',
            array($this, 'display_plugin_admin_settings')
        );
    }
    
    /**
     * Render the dashboard page.
     */
    public function display_plugin_admin_dashboard() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/dashboard.php';
    }
    
    /**
     * Render the patients page.
     */
    public function display_plugin_admin_patients() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/patients.php';
    }
    
    /**
     * Render the consultations page.
     */
    public function display_plugin_admin_consultations() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/consultations.php';
    }
    
    /**
     * Render the calendar page.
     */
    public function display_plugin_admin_calendar() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/calendar.php';
    }
    
    /**
     * Render the settings page.
     */
    public function display_plugin_admin_settings() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/partials/settings.php';
    }
    
    /**
     * Get dashboard statistics as JSON for AJAX requests.
     */
    public function get_dashboard_stats() {
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        global $wpdb;
        
        // Total patients
        $total_patients = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medoffice_patients");
        
        // Total consultations
        $total_consultations = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}medoffice_consultations");
        
        // Total appointments for today
        $today = date('Y-m-d');
        $total_appointments_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}medoffice_rendezvous 
            WHERE DATE(date_debut) = %s",
            $today
        ));
        
        // Unpaid fees
        $unpaid_fees = $wpdb->get_var(
            "SELECT SUM(total_honoraire) FROM {$wpdb->prefix}medoffice_consultations 
            WHERE est_paye = 0"
        );
        
        // Monthly stats for consultations (last 6 months)
        $months = array();
        $consultation_counts = array();
        
        for ($i = 5; $i >= 0; $i--) {
            $month = date('Y-m', strtotime("-$i months"));
            $months[] = date('M Y', strtotime("-$i months"));
            
            $count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}medoffice_consultations 
                WHERE DATE_FORMAT(date_consultation, '%%Y-%%m') = %s",
                $month
            ));
            
            $consultation_counts[] = $count;
        }
        
        // Recent patients
        $recent_patients = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}medoffice_patients 
            ORDER BY date_creation DESC 
            LIMIT 5"
        );
        
        // Upcoming appointments
        $upcoming_appointments = $wpdb->get_results(
            "SELECT r.*, p.nom, p.prenom 
            FROM {$wpdb->prefix}medoffice_rendezvous r
            JOIN {$wpdb->prefix}medoffice_patients p ON r.patient_id = p.id
            WHERE r.date_debut >= NOW()
            ORDER BY r.date_debut ASC
            LIMIT 5"
        );
        
        $data = array(
            'total_patients' => $total_patients,
            'total_consultations' => $total_consultations,
            'total_appointments_today' => $total_appointments_today,
            'unpaid_fees' => $unpaid_fees ? $unpaid_fees : 0,
            'chart_data' => array(
                'labels' => $months,
                'data' => $consultation_counts
            ),
            'recent_patients' => $recent_patients,
            'upcoming_appointments' => $upcoming_appointments
        );
        
        wp_send_json_success($data);
    }
}
