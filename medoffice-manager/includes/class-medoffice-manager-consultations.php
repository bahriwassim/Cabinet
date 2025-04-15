<?php
/**
 * Consultations management functionality.
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager_Consultations {

    /**
     * Save or update a consultation.
     *
     * @since    1.0.0
     */
    public static function save_consultation() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get consultation data
        $consultation_id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $patient_id = isset($_POST['patient_id']) ? intval($_POST['patient_id']) : 0;
        $date_consultation = isset($_POST['date_consultation']) ? sanitize_text_field($_POST['date_consultation']) : '';
        $motif = isset($_POST['motif']) ? sanitize_text_field($_POST['motif']) : '';
        $diagnostic = isset($_POST['diagnostic']) ? sanitize_textarea_field($_POST['diagnostic']) : '';
        $traitement = isset($_POST['traitement']) ? sanitize_textarea_field($_POST['traitement']) : '';
        $ordonnance = isset($_POST['ordonnance']) ? sanitize_textarea_field($_POST['ordonnance']) : '';
        $total_honoraire = isset($_POST['total_honoraire']) ? floatval($_POST['total_honoraire']) : 0;
        $est_paye = isset($_POST['est_paye']) ? intval($_POST['est_paye']) : 0;
        $notes_interne = isset($_POST['notes_interne']) ? sanitize_textarea_field($_POST['notes_interne']) : '';
        
        // Validate required fields
        if ($patient_id <= 0 || empty($date_consultation)) {
            wp_send_json_error(array('message' => 'Veuillez remplir tous les champs obligatoires.'));
            return;
        }
        
        global $wpdb;
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $ordonnances_table = $wpdb->prefix . 'medoffice_ordonnances';
        $attachments_table = $wpdb->prefix . 'medoffice_attachements';
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        // Convert date to MySQL format if needed
        $date_consultation = MedOffice_Manager_DB::format_date_time($date_consultation);
        
        $consultation_data = array(
            'patient_id' => $patient_id,
            'date_consultation' => $date_consultation,
            'motif' => $motif,
            'diagnostic' => $diagnostic,
            'traitement' => $traitement,
            'total_honoraire' => $total_honoraire,
            'est_paye' => $est_paye,
            'notes_interne' => $notes_interne
        );
        
        if ($consultation_id <= 0) {
            // Insert new consultation
            $consultation_data['date_creation'] = current_time('mysql');
            
            $result = $wpdb->insert($consultations_table, $consultation_data);
            
            if ($result === false) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(array('message' => 'Erreur lors de l\'ajout de la consultation: ' . $wpdb->last_error));
                return;
            }
            
            $consultation_id = $wpdb->insert_id;
        } else {
            // Update existing consultation
            $result = $wpdb->update(
                $consultations_table,
                $consultation_data,
                array('id' => $consultation_id)
            );
            
            if ($result === false) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(array('message' => 'Erreur lors de la mise à jour de la consultation: ' . $wpdb->last_error));
                return;
            }
        }
        
        // Handle prescription
        if (!empty($ordonnance)) {
            // Check if a prescription already exists
            $existing_prescription = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $ordonnances_table WHERE consultation_id = %d",
                $consultation_id
            ));
            
            if ($existing_prescription) {
                // Update existing prescription
                $result = $wpdb->update(
                    $ordonnances_table,
                    array('contenu' => $ordonnance),
                    array('consultation_id' => $consultation_id)
                );
            } else {
                // Insert new prescription
                $result = $wpdb->insert(
                    $ordonnances_table,
                    array(
                        'consultation_id' => $consultation_id,
                        'contenu' => $ordonnance,
                        'date_creation' => current_time('mysql')
                    )
                );
            }
            
            if ($result === false) {
                $wpdb->query('ROLLBACK');
                wp_send_json_error(array('message' => 'Erreur lors de l\'enregistrement de l\'ordonnance: ' . $wpdb->last_error));
                return;
            }
        }
        
        // Handle attachments
        if (!empty($_FILES['attachments'])) {
            $attachments = $_FILES['attachments'];
            $uploaded = array();
            
            // Set up WordPress upload directory
            $upload_dir = wp_upload_dir();
            $base_dir = $upload_dir['basedir'] . '/medoffice-attachments/' . $consultation_id;
            $base_url = $upload_dir['baseurl'] . '/medoffice-attachments/' . $consultation_id;
            
            // Create directory if it doesn't exist
            if (!file_exists($base_dir)) {
                wp_mkdir_p($base_dir);
            }
            
            // Loop through each file
            for ($i = 0; $i < count($attachments['name']); $i++) {
                if ($attachments['error'][$i] === 0) {
                    $filename = sanitize_file_name($attachments['name'][$i]);
                    $tmp_name = $attachments['tmp_name'][$i];
                    $type = $attachments['type'][$i];
                    $size = $attachments['size'][$i];
                    
                    // Generate unique filename
                    $unique_filename = wp_unique_filename($base_dir, $filename);
                    $file_path = $base_dir . '/' . $unique_filename;
                    $file_url = $base_url . '/' . $unique_filename;
                    
                    // Move the file to the uploads directory
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // Insert attachment record in database
                        $attachment_data = array(
                            'consultation_id' => $consultation_id,
                            'nom_fichier' => $filename,
                            'url_fichier' => $file_url,
                            'type_fichier' => $type,
                            'taille_fichier' => $size,
                            'date_creation' => current_time('mysql')
                        );
                        
                        $result = $wpdb->insert($attachments_table, $attachment_data);
                        
                        if ($result) {
                            $uploaded[] = array(
                                'id' => $wpdb->insert_id,
                                'name' => $filename,
                                'url' => $file_url
                            );
                        }
                    }
                }
            }
        }
        
        $wpdb->query('COMMIT');
        
        wp_send_json_success(array(
            'message' => $consultation_id > 0 ? 'Consultation mise à jour avec succès' : 'Consultation ajoutée avec succès',
            'consultation_id' => $consultation_id,
            'uploaded_attachments' => $uploaded ?? array()
        ));
    }

    /**
     * Get consultations list.
     *
     * @since    1.0.0
     */
    public static function get_consultations() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $patients_table = $wpdb->prefix . 'medoffice_patients';
        
        // Get filter parameter
        $filter = isset($_POST['filter']) ? sanitize_text_field($_POST['filter']) : 'all';
        $date_start = isset($_POST['date_start']) ? sanitize_text_field($_POST['date_start']) : '';
        $date_end = isset($_POST['date_end']) ? sanitize_text_field($_POST['date_end']) : '';
        
        // Build query based on filter
        $where_clause = '';
        $params = array();
        
        switch ($filter) {
            case 'paid':
                $where_clause = 'WHERE c.est_paye = 1';
                break;
            case 'unpaid':
                $where_clause = 'WHERE c.est_paye = 0';
                break;
            case 'today':
                $where_clause = 'WHERE DATE(c.date_consultation) = %s';
                $params[] = date('Y-m-d');
                break;
            case 'this-week':
                $where_clause = 'WHERE YEARWEEK(c.date_consultation, 1) = YEARWEEK(NOW(), 1)';
                break;
            case 'this-month':
                $where_clause = 'WHERE YEAR(c.date_consultation) = YEAR(NOW()) AND MONTH(c.date_consultation) = MONTH(NOW())';
                break;
            case 'custom':
                if (!empty($date_start) && !empty($date_end)) {
                    $where_clause = 'WHERE DATE(c.date_consultation) BETWEEN %s AND %s';
                    $params[] = $date_start;
                    $params[] = $date_end;
                } elseif (!empty($date_start)) {
                    $where_clause = 'WHERE DATE(c.date_consultation) >= %s';
                    $params[] = $date_start;
                } elseif (!empty($date_end)) {
                    $where_clause = 'WHERE DATE(c.date_consultation) <= %s';
                    $params[] = $date_end;
                }
                break;
            default:
                // All consultations, no filter
                break;
        }
        
        // Prepare and execute query
        $query = "
            SELECT c.*, p.nom as patient_nom, p.prenom as patient_prenom
            FROM $consultations_table c
            JOIN $patients_table p ON c.patient_id = p.id
            $where_clause
            ORDER BY c.date_consultation DESC
        ";
        
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }
        
        $consultations = $wpdb->get_results($query, ARRAY_A);
        
        wp_send_json_success($consultations);
    }

    /**
     * Get a single consultation by ID.
     *
     * @since    1.0.0
     */
    public static function get_consultation() {
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
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $patients_table = $wpdb->prefix . 'medoffice_patients';
        $ordonnances_table = $wpdb->prefix . 'medoffice_ordonnances';
        $attachments_table = $wpdb->prefix . 'medoffice_attachements';
        $payments_table = $wpdb->prefix . 'medoffice_paiements';
        
        // Get consultation data
        $consultation = $wpdb->get_row($wpdb->prepare("
            SELECT c.*, o.contenu as ordonnance
            FROM $consultations_table c
            LEFT JOIN $ordonnances_table o ON c.id = o.consultation_id
            WHERE c.id = %d
        ", $consultation_id), ARRAY_A);
        
        if (!$consultation) {
            wp_send_json_error(array('message' => 'Consultation non trouvée.'));
            return;
        }
        
        // Get patient data
        $patient = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $patients_table WHERE id = %d
        ", $consultation['patient_id']), ARRAY_A);
        
        // Get attachments
        $attachments = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $attachments_table WHERE consultation_id = %d
        ", $consultation_id), ARRAY_A);
        
        // Get payments if requested
        $include_payments = isset($_POST['include_payments']) && $_POST['include_payments'] === 'true';
        $payments = array();
        
        if ($include_payments) {
            $payments = $wpdb->get_results($wpdb->prepare("
                SELECT * FROM $payments_table WHERE consultation_id = %d ORDER BY date_paiement ASC
            ", $consultation_id), ARRAY_A);
        }
        
        wp_send_json_success(array(
            'consultation' => $consultation,
            'patient' => $patient,
            'attachments' => $attachments,
            'payments' => $payments
        ));
    }

    /**
     * Delete a consultation.
     *
     * @since    1.0.0
     */
    public static function delete_consultation() {
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
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $payments_table = $wpdb->prefix . 'medoffice_paiements';
        $ordonnances_table = $wpdb->prefix . 'medoffice_ordonnances';
        $attachments_table = $wpdb->prefix . 'medoffice_attachements';
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        // Delete payments
        $wpdb->delete($payments_table, array('consultation_id' => $consultation_id));
        
        // Delete prescriptions
        $wpdb->delete($ordonnances_table, array('consultation_id' => $consultation_id));
        
        // Get attachments
        $attachments = $wpdb->get_results($wpdb->prepare("
            SELECT id, url_fichier FROM $attachments_table WHERE consultation_id = %d
        ", $consultation_id), ARRAY_A);
        
        // Delete attachment files from server
        foreach ($attachments as $attachment) {
            $file_path = str_replace(site_url('/'), ABSPATH, $attachment['url_fichier']);
            if (file_exists($file_path)) {
                @unlink($file_path);
            }
        }
        
        // Delete attachments from database
        $wpdb->delete($attachments_table, array('consultation_id' => $consultation_id));
        
        // Delete the consultation
        $result = $wpdb->delete(
            $consultations_table,
            array('id' => $consultation_id),
            array('%d')
        );
        
        if ($result === false) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => 'Erreur lors de la suppression de la consultation: ' . $wpdb->last_error));
            return;
        }
        
        $wpdb->query('COMMIT');
        wp_send_json_success(array('message' => 'Consultation supprimée avec succès'));
    }
    
    /**
     * Save a payment for a consultation.
     *
     * @since    1.0.0
     */
    public static function save_payment() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get payment data
        $payment = isset($_POST['payment']) ? $_POST['payment'] : array();
        
        // Validate required fields
        if (empty($payment['consultation_id']) || empty($payment['montant']) || empty($payment['date_paiement'])) {
            wp_send_json_error(array('message' => 'Veuillez remplir tous les champs obligatoires.'));
            return;
        }
        
        // Validate consultation ID
        $consultation_id = intval($payment['consultation_id']);
        if ($consultation_id <= 0) {
            wp_send_json_error(array('message' => 'ID consultation invalide.'));
            return;
        }
        
        global $wpdb;
        $consultations_table = $wpdb->prefix . 'medoffice_consultations';
        $payments_table = $wpdb->prefix . 'medoffice_paiements';
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        // Get the consultation
        $consultation = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM $consultations_table WHERE id = %d
        ", $consultation_id), ARRAY_A);
        
        if (!$consultation) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => 'Consultation non trouvée.'));
            return;
        }
        
        // Get total honoraire
        $total_honoraire = floatval($consultation['total_honoraire']);
        
        // Get already paid amount
        $paid_amount = $wpdb->get_var($wpdb->prepare("
            SELECT SUM(montant) FROM $payments_table WHERE consultation_id = %d
        ", $consultation_id));
        
        $paid_amount = floatval($paid_amount ?: 0);
        
        // Add new payment amount
        $new_payment_amount = floatval($payment['montant']);
        $total_paid = $paid_amount + $new_payment_amount;
        
        // Check if this payment will fully pay the consultation
        $fully_paid = ($total_paid >= $total_honoraire);
        
        // Insert the payment
        $result = $wpdb->insert(
            $payments_table,
            array(
                'consultation_id' => $consultation_id,
                'montant' => $new_payment_amount,
                'date_paiement' => sanitize_text_field($payment['date_paiement']),
                'methode_paiement' => sanitize_text_field($payment['methode_paiement'] ?? 'Espèces'),
                'notes' => sanitize_textarea_field($payment['notes'] ?? ''),
                'date_creation' => current_time('mysql')
            )
        );
        
        if ($result === false) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(array('message' => 'Erreur lors de l\'enregistrement du paiement: ' . $wpdb->last_error));
            return;
        }
        
        // Update consultation status if fully paid
        if ($fully_paid) {
            $wpdb->update(
                $consultations_table,
                array('est_paye' => 1),
                array('id' => $consultation_id)
            );
        }
        
        $wpdb->query('COMMIT');
        
        wp_send_json_success(array(
            'message' => 'Paiement enregistré avec succès',
            'payment_id' => $wpdb->insert_id,
            'fully_paid' => $fully_paid
        ));
    }
}
