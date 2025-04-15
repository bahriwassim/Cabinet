<?php
/**
 * Prescriptions management functionality.
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager_Prescriptions {

    /**
     * Save or update a prescription.
     *
     * @since    1.0.0
     */
    public static function save_prescription() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get prescription data
        $consultation_id = isset($_POST['consultation_id']) ? intval($_POST['consultation_id']) : 0;
        $contenu = isset($_POST['contenu']) ? sanitize_textarea_field($_POST['contenu']) : '';
        
        // Validate required fields
        if ($consultation_id <= 0 || empty($contenu)) {
            wp_send_json_error(array('message' => 'Veuillez remplir tous les champs obligatoires.'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'medoffice_ordonnances';
        
        // Check if a prescription already exists for this consultation
        $existing_prescription = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table_name WHERE consultation_id = %d",
            $consultation_id
        ));
        
        if ($existing_prescription) {
            // Update existing prescription
            $result = $wpdb->update(
                $table_name,
                array('contenu' => $contenu),
                array('consultation_id' => $consultation_id)
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Erreur lors de la mise à jour de l\'ordonnance: ' . $wpdb->last_error));
                return;
            }
            
            wp_send_json_success(array('message' => 'Ordonnance mise à jour avec succès', 'prescription_id' => $existing_prescription));
        } else {
            // Insert new prescription
            $result = $wpdb->insert(
                $table_name,
                array(
                    'consultation_id' => $consultation_id,
                    'contenu' => $contenu,
                    'date_creation' => current_time('mysql')
                )
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Erreur lors de l\'ajout de l\'ordonnance: ' . $wpdb->last_error));
                return;
            }
            
            wp_send_json_success(array('message' => 'Ordonnance ajoutée avec succès', 'prescription_id' => $wpdb->insert_id));
        }
    }

    /**
     * Get a prescription by consultation ID.
     *
     * @since    1.0.0
     */
    public static function get_prescription() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get consultation ID
        $consultation_id = isset($_POST['consultation_id']) ? intval($_POST['consultation_id']) : 0;
        
        if ($consultation_id <= 0) {
            wp_send_json_error(array('message' => 'ID consultation invalide.'));
            return;
        }
        
        global $wpdb;
        $ordonnances_table = $wpdb->prefix . 'medoffice_ordonnances';
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $patients_table = $wpdb->prefix . 'medoffice_patients';
        $settings_table = $wpdb->prefix . 'medoffice_settings';
        
        // Get prescription, consultation and patient data
        $prescription = $wpdb->get_row($wpdb->prepare("
            SELECT o.*, c.date_consultation, CONCAT(p.prenom, ' ', p.nom) AS patient_name, p.date_naissance
            FROM $ordonnances_table o
            JOIN $consultations_table c ON o.consultation_id = c.id
            JOIN $patients_table p ON c.patient_id = p.id
            WHERE o.consultation_id = %d
        ", $consultation_id), ARRAY_A);
        
        if (!$prescription) {
            // If no prescription found, get basic consultation and patient data
            $basic_data = $wpdb->get_row($wpdb->prepare("
                SELECT c.id, c.date_consultation, CONCAT(p.prenom, ' ', p.nom) AS patient_name, p.date_naissance
                FROM $consultations_table c
                JOIN $patients_table p ON c.patient_id = p.id
                WHERE c.id = %d
            ", $consultation_id), ARRAY_A);
            
            if (!$basic_data) {
                wp_send_json_error(array('message' => 'Consultation non trouvée.'));
                return;
            }
            
            $prescription = array(
                'id' => 0,
                'consultation_id' => $consultation_id,
                'contenu' => '',
                'date_creation' => current_time('mysql'),
                'date_consultation' => $basic_data['date_consultation'],
                'patient_name' => $basic_data['patient_name'],
                'date_naissance' => $basic_data['date_naissance']
            );
        }
        
        // Calculate patient age
        $patient_age = '';
        if (!empty($prescription['date_naissance'])) {
            $patient_age = MedOffice_Manager_DB::calculate_age($prescription['date_naissance']) . ' ans';
        }
        $prescription['patient_age'] = $patient_age;
        
        // Get cabinet settings
        $settings = array();
        $settings_results = $wpdb->get_results("SELECT setting_key, setting_value FROM $settings_table", ARRAY_A);
        
        foreach ($settings_results as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        // Add settings to prescription data
        $prescription['nom_cabinet'] = $settings['nom_cabinet'] ?? 'Cabinet Médical';
        $prescription['nom_medecin'] = $settings['nom_medecin'] ?? '';
        $prescription['specialite'] = $settings['specialite'] ?? '';
        $prescription['adresse_cabinet'] = $settings['adresse_cabinet'] ?? '';
        $prescription['telephone_cabinet'] = $settings['telephone_cabinet'] ?? '';
        
        wp_send_json_success($prescription);
    }
    
    /**
     * Generate a printable prescription.
     *
     * @since    1.0.0
     * @param    int       $consultation_id    Consultation ID.
     * @return   string                        HTML content of the prescription.
     */
    public static function generate_prescription_html($consultation_id) {
        global $wpdb;
        $ordonnances_table = $wpdb->prefix . 'medoffice_ordonnances';
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $patients_table = $wpdb->prefix . 'medoffice_patients';
        $settings_table = $wpdb->prefix . 'medoffice_settings';
        
        // Get prescription, consultation and patient data
        $prescription = $wpdb->get_row($wpdb->prepare("
            SELECT o.*, c.date_consultation, CONCAT(p.prenom, ' ', p.nom) AS patient_name, p.date_naissance
            FROM $ordonnances_table o
            JOIN $consultations_table c ON o.consultation_id = c.id
            JOIN $patients_table p ON c.patient_id = p.id
            WHERE o.consultation_id = %d
        ", $consultation_id));
        
        if (!$prescription) {
            return '<p>Aucune ordonnance trouvée.</p>';
        }
        
        // Calculate patient age
        $patient_age = '';
        if (!empty($prescription->date_naissance)) {
            $patient_age = MedOffice_Manager_DB::calculate_age($prescription->date_naissance) . ' ans';
        }
        
        // Get cabinet settings
        $settings = array();
        $settings_results = $wpdb->get_results("SELECT setting_key, setting_value FROM $settings_table", ARRAY_A);
        
        foreach ($settings_results as $setting) {
            $settings[$setting['setting_key']] = $setting['setting_value'];
        }
        
        // Format the prescription date
        $prescription_date = date_i18n(get_option('date_format'), strtotime($prescription->date_consultation));
        
        // Generate the HTML
        $html = '
        <div class="prescription-container">
            <div class="prescription-header">
                <div class="doctor-info">
                    <h2>' . esc_html($settings['nom_cabinet'] ?? 'Cabinet Médical') . '</h2>
                    <p>Dr. ' . esc_html($settings['nom_medecin'] ?? '') . '</p>
                    <p>' . esc_html($settings['specialite'] ?? '') . '</p>
                    <p>' . nl2br(esc_html($settings['adresse_cabinet'] ?? '')) . '</p>
                    <p>Tél: ' . esc_html($settings['telephone_cabinet'] ?? '') . '</p>
                </div>
                <div class="prescription-title">
                    <h1>Ordonnance</h1>
                </div>
            </div>
            
            <div class="prescription-info">
                <div class="patient-info">
                    <p>Nom du patient: ' . esc_html($prescription->patient_name) . '</p>
                    <p>Âge: ' . esc_html($patient_age) . '</p>
                </div>
                <div class="prescription-date">
                    <p>Date: ' . esc_html($prescription_date) . '</p>
                </div>
            </div>
            
            <div class="prescription-content">
                ' . nl2br(esc_html($prescription->contenu)) . '
            </div>
            
            <div class="prescription-footer">
                <div class="doctor-signature">
                    <p>Signature</p>
                    <div class="signature-line"></div>
                </div>
            </div>
        </div>
        ';
        
        return $html;
    }
}
