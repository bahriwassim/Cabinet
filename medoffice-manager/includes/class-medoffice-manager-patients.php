<?php
/**
 * Patient management functionality.
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager_Patients {

    /**
     * Save or update a patient.
     *
     * @since    1.0.0
     */
    public static function save_patient() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get patient data
        $patient = isset($_POST['patient']) ? $_POST['patient'] : array();
        
        // Validate required fields
        if (empty($patient['nom']) || empty($patient['prenom']) || empty($patient['sexe']) || empty($patient['telephone'])) {
            wp_send_json_error(array('message' => 'Veuillez remplir tous les champs obligatoires.'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'medoffice_patients';
        
        // Sanitize input data
        $patient_data = array(
            'nom' => sanitize_text_field($patient['nom']),
            'prenom' => sanitize_text_field($patient['prenom']),
            'sexe' => sanitize_text_field($patient['sexe']),
            'telephone' => sanitize_text_field($patient['telephone']),
            'email' => sanitize_email($patient['email'] ?? ''),
            'adresse' => sanitize_textarea_field($patient['adresse'] ?? ''),
            'notes' => sanitize_textarea_field($patient['notes'] ?? '')
        );
        
        // Handle date of birth
        if (!empty($patient['date_naissance'])) {
            $patient_data['date_naissance'] = sanitize_text_field($patient['date_naissance']);
        }
        
        if (empty($patient['id']) || $patient['id'] == '0') {
            // Insert new patient
            $patient_data['date_creation'] = current_time('mysql');
            
            $result = $wpdb->insert($table_name, $patient_data);
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Erreur lors de l\'ajout du patient: ' . $wpdb->last_error));
                return;
            }
            
            $patient_id = $wpdb->insert_id;
            wp_send_json_success(array('message' => 'Patient ajouté avec succès', 'patient_id' => $patient_id));
        } else {
            // Update existing patient
            $patient_id = intval($patient['id']);
            
            $result = $wpdb->update(
                $table_name,
                $patient_data,
                array('id' => $patient_id)
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du patient: ' . $wpdb->last_error));
                return;
            }
            
            wp_send_json_success(array('message' => 'Patient mis à jour avec succès', 'patient_id' => $patient_id));
        }
    }

    /**
     * Get patients list.
     *
     * @since    1.0.0
     */
    public static function get_patients() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'medoffice_patients';
        
        // Get filter parameter
        $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
        
        // Build query based on filter
        $where_clause = '';
        $params = array();
        
        switch ($filter) {
            case 'male':
                $where_clause = 'WHERE sexe = %s';
                $params[] = 'Homme';
                break;
            case 'female':
                $where_clause = 'WHERE sexe = %s';
                $params[] = 'Femme';
                break;
            case 'recent':
                $where_clause = 'WHERE date_creation > %s';
                $params[] = date('Y-m-d', strtotime('-30 days'));
                break;
            default:
                // All patients, no filter
                break;
        }
        
        // Prepare and execute query
        $query = "SELECT * FROM $table_name $where_clause ORDER BY id DESC";
        
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }
        
        $patients = $wpdb->get_results($query, ARRAY_A);
        
        wp_send_json_success($patients);
    }

    /**
     * Get a single patient by ID.
     *
     * @since    1.0.0
     */
    public static function get_patient() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get patient ID
        $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
        
        if ($patient_id <= 0) {
            wp_send_json_error(array('message' => 'ID patient invalide.'));
            return;
        }
        
        global $wpdb;
        $patients_table = $wpdb->prefix . 'medoffice_patients';
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $appointments_table = $wpdb->prefix . 'medoffice_rendezvous';
        
        // Check if we need only basic info
        $basic_info = isset($_POST['basic_info']) && $_POST['basic_info'] === 'true';
        
        // Get patient data
        $patient = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $patients_table WHERE id = %d",
            $patient_id
        ), ARRAY_A);
        
        if (!$patient) {
            wp_send_json_error(array('message' => 'Patient non trouvé.'));
            return;
        }
        
        // For basic info requests, just return patient and last consultation date
        if ($basic_info) {
            // Get last consultation date
            $last_consultation = $wpdb->get_var($wpdb->prepare(
                "SELECT date_consultation FROM $consultations_table WHERE patient_id = %d ORDER BY date_consultation DESC LIMIT 1",
                $patient_id
            ));
            
            wp_send_json_success(array(
                'patient' => $patient,
                'last_consultation' => $last_consultation
            ));
            return;
        }
        
        // Get consultations for this patient
        $consultations = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $consultations_table WHERE patient_id = %d ORDER BY date_consultation DESC",
            $patient_id
        ), ARRAY_A);
        
        // Get appointments for this patient
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $appointments_table WHERE patient_id = %d ORDER BY date_debut DESC",
            $patient_id
        ), ARRAY_A);
        
        wp_send_json_success(array(
            'patient' => $patient,
            'consultations' => $consultations,
            'appointments' => $appointments
        ));
    }

    /**
     * Delete a patient.
     *
     * @since    1.0.0
     */
    public static function delete_patient() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get patient ID
        $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
        
        if ($patient_id <= 0) {
            wp_send_json_error(array('message' => 'ID patient invalide.'));
            return;
        }
        
        global $wpdb;
        $patients_table = $wpdb->prefix . 'medoffice_patients';
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $payments_table = $wpdb->prefix . 'medoffice_paiements';
        $prescriptions_table = $wpdb->prefix . 'medoffice_ordonnances';
        $attachments_table = $wpdb->prefix . 'medoffice_attachements';
        $appointments_table = $wpdb->prefix . 'medoffice_rendezvous';
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        // Get all consultations for this patient
        $consultations = $wpdb->get_results($wpdb->prepare(
            "SELECT id FROM $consultations_table WHERE patient_id = %d",
            $patient_id
        ), ARRAY_A);
        
        $consultation_ids = array_column($consultations, 'id');
        
        // Delete associated data if there are consultations
        if (!empty($consultation_ids)) {
            // Convert array to comma-separated string for IN clause
            $placeholders = implode(',', array_fill(0, count($consultation_ids), '%d'));
            
            // Delete payments
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $payments_table WHERE consultation_id IN ($placeholders)",
                $consultation_ids
            ));
            
            // Delete prescriptions
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $prescriptions_table WHERE consultation_id IN ($placeholders)",
                $consultation_ids
            ));
            
            // Get attachments
            $attachments = $wpdb->get_results($wpdb->prepare(
                "SELECT id, url_fichier FROM $attachments_table WHERE consultation_id IN ($placeholders)",
                $consultation_ids
            ), ARRAY_A);
            
            // Delete attachment files from server
            foreach ($attachments as $attachment) {
                $file_path = str_replace(site_url('/'), ABSPATH, $attachment['url_fichier']);
                if (file_exists($file_path)) {
                    @unlink($file_path);
                }
            }
            
            // Delete attachments from database
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $attachments_table WHERE consultation_id IN ($placeholders)",
                $consultation_ids
            ));
            
            // Delete consultations
            $wpdb->query($wpdb->prepare(
                "DELETE FROM $consultations_table WHERE patient_id = %d",
                $patient_id
            ));
        }
        
        // Delete appointments
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $appointments_table WHERE patient_id = %d",
            $patient_id
        ));
        
        // Delete the patient
        $result = $wpdb->delete(
            $patients_table,
            array('id' => $patient_id),
            array('%d')
        );
        
        if ($result === false) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => 'Erreur lors de la suppression du patient: ' . $wpdb->last_error));
            return;
        }
        
        $wpdb->query('COMMIT');
        wp_send_json_success(array('message' => 'Patient supprimé avec succès'));
    }
}
