<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager_Activator {

    /**
     * Create the necessary database tables for the plugin.
     *
     * @since    1.0.0
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // Patients table
        $table_name = $wpdb->prefix . 'medoffice_patients';
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            nom varchar(100) NOT NULL,
            prenom varchar(100) NOT NULL,
            sexe varchar(10) NOT NULL,
            date_naissance date DEFAULT NULL,
            telephone varchar(20) NOT NULL,
            email varchar(100) DEFAULT '',
            adresse text DEFAULT '',
            notes text DEFAULT '',
            date_creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        // Consultations table
        $table_consultations = $wpdb->prefix . 'medoffice_consultations';
        $sql .= "CREATE TABLE $table_consultations (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            date_consultation datetime NOT NULL,
            motif text DEFAULT '',
            diagnostic text DEFAULT '',
            traitement text DEFAULT '',
            notes_interne text DEFAULT '',
            total_honoraire decimal(10,2) DEFAULT 0.00,
            est_paye tinyint(1) DEFAULT 0,
            date_creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id)
        ) $charset_collate;";
        
        // Paiements table
        $table_paiements = $wpdb->prefix . 'medoffice_paiements';
        $sql .= "CREATE TABLE $table_paiements (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            consultation_id mediumint(9) NOT NULL,
            montant decimal(10,2) NOT NULL,
            date_paiement date NOT NULL,
            methode_paiement varchar(50) DEFAULT 'Espèces',
            notes text DEFAULT '',
            date_creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY consultation_id (consultation_id)
        ) $charset_collate;";
        
        // Ordonnances table
        $table_ordonnances = $wpdb->prefix . 'medoffice_ordonnances';
        $sql .= "CREATE TABLE $table_ordonnances (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            consultation_id mediumint(9) NOT NULL,
            contenu text NOT NULL,
            date_creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY consultation_id (consultation_id)
        ) $charset_collate;";
        
        // Attachements table
        $table_attachements = $wpdb->prefix . 'medoffice_attachements';
        $sql .= "CREATE TABLE $table_attachements (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            consultation_id mediumint(9) NOT NULL,
            nom_fichier varchar(255) NOT NULL,
            url_fichier varchar(255) NOT NULL,
            type_fichier varchar(100) DEFAULT '',
            taille_fichier int(11) DEFAULT 0,
            date_creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY consultation_id (consultation_id)
        ) $charset_collate;";
        
        // Rendez-vous table
        $table_rendezvous = $wpdb->prefix . 'medoffice_rendezvous';
        $sql .= "CREATE TABLE $table_rendezvous (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            patient_id mediumint(9) NOT NULL,
            date_debut datetime NOT NULL,
            date_fin datetime NOT NULL,
            titre varchar(255) NOT NULL,
            description text DEFAULT '',
            status varchar(50) DEFAULT 'confirmé',
            date_creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY patient_id (patient_id)
        ) $charset_collate;";
        
        // Settings table
        $table_settings = $wpdb->prefix . 'medoffice_settings';
        $sql .= "CREATE TABLE $table_settings (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_key varchar(100) NOT NULL,
            setting_value text NOT NULL,
            date_creation datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
        
        // Insert default settings
        $default_settings = array(
            array('nom_cabinet', 'Cabinet Médical'),
            array('adresse_cabinet', ''),
            array('telephone_cabinet', ''),
            array('email_cabinet', ''),
            array('whatsapp_numero', ''),
            array('logo_cabinet', ''),
            array('couleur_theme', '#4e73df'),
        );
        
        foreach ($default_settings as $setting) {
            $wpdb->insert(
                $table_settings,
                array(
                    'setting_key' => $setting[0],
                    'setting_value' => $setting[1]
                )
            );
        }
    }
}
