<?php
/**
 * Appointments management functionality.
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager_Appointments {

    /**
     * Save or update an appointment.
     *
     * @since    1.0.0
     */
    public static function save_appointment() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get appointment data
        $appointment = isset($_POST['appointment']) ? $_POST['appointment'] : array();
        
        // Validate required fields
        if (empty($appointment['patient_id']) || empty($appointment['date_debut']) || 
            empty($appointment['date_fin']) || empty($appointment['titre'])) {
            wp_send_json_error(array('message' => 'Veuillez remplir tous les champs obligatoires.'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'medoffice_rendezvous';
        
        // Sanitize and prepare data
        $appointment_data = array(
            'patient_id' => intval($appointment['patient_id']),
            'date_debut' => sanitize_text_field($appointment['date_debut']),
            'date_fin' => sanitize_text_field($appointment['date_fin']),
            'titre' => sanitize_text_field($appointment['titre']),
            'description' => sanitize_textarea_field($appointment['description'] ?? ''),
            'status' => sanitize_text_field($appointment['status'] ?? 'confirmé')
        );
        
        // Format dates if needed
        $appointment_data['date_debut'] = MedOffice_Manager_DB::format_date_time($appointment_data['date_debut']);
        $appointment_data['date_fin'] = MedOffice_Manager_DB::format_date_time($appointment_data['date_fin']);
        
        // Check if dates are valid
        if (strtotime($appointment_data['date_fin']) <= strtotime($appointment_data['date_debut'])) {
            wp_send_json_error(array('message' => 'La date de fin doit être postérieure à la date de début.'));
            return;
        }
        
        if (empty($appointment['id']) || intval($appointment['id']) <= 0) {
            // Insert new appointment
            $appointment_data['date_creation'] = current_time('mysql');
            
            $result = $wpdb->insert($table_name, $appointment_data);
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Erreur lors de l\'ajout du rendez-vous: ' . $wpdb->last_error));
                return;
            }
            
            $appointment_id = $wpdb->insert_id;
            wp_send_json_success(array('message' => 'Rendez-vous ajouté avec succès', 'appointment_id' => $appointment_id));
        } else {
            // Update existing appointment
            $appointment_id = intval($appointment['id']);
            
            $result = $wpdb->update(
                $table_name,
                $appointment_data,
                array('id' => $appointment_id)
            );
            
            if ($result === false) {
                wp_send_json_error(array('message' => 'Erreur lors de la mise à jour du rendez-vous: ' . $wpdb->last_error));
                return;
            }
            
            wp_send_json_success(array('message' => 'Rendez-vous mis à jour avec succès', 'appointment_id' => $appointment_id));
        }
    }

    /**
     * Get appointments list.
     *
     * @since    1.0.0
     */
    public static function get_appointments() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        global $wpdb;
        $appointments_table = $wpdb->prefix . 'medoffice_rendezvous';
        $patients_table = $wpdb->prefix . 'medoffice_patients';
        
        // Get date range parameters
        $start = isset($_POST['start']) ? sanitize_text_field($_POST['start']) : '';
        $end = isset($_POST['end']) ? sanitize_text_field($_POST['end']) : '';
        
        // Build query
        $query = "
            SELECT r.*, CONCAT(p.prenom, ' ', p.nom) as patient_name
            FROM $appointments_table r
            JOIN $patients_table p ON r.patient_id = p.id
        ";
        
        $params = array();
        
        // Add date range filter if provided
        if (!empty($start) && !empty($end)) {
            $query .= " WHERE r.date_debut >= %s AND r.date_debut <= %s";
            $params[] = $start;
            $params[] = $end;
        }
        
        $query .= " ORDER BY r.date_debut ASC";
        
        // Prepare the query if we have parameters
        if (!empty($params)) {
            $query = $wpdb->prepare($query, $params);
        }
        
        // Execute query
        $appointments = $wpdb->get_results($query, ARRAY_A);
        
        wp_send_json_success($appointments);
    }

    /**
     * Delete an appointment.
     *
     * @since    1.0.0
     */
    public static function delete_appointment() {
        // Check nonce for security
        check_ajax_referer('medoffice_nonce', 'nonce');
        
        // Check if user has permission
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Permission denied'));
            return;
        }
        
        // Get appointment ID
        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
        
        if ($appointment_id <= 0) {
            wp_send_json_error(array('message' => 'ID rendez-vous invalide.'));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'medoffice_rendezvous';
        
        // Delete the appointment
        $result = $wpdb->delete(
            $table_name,
            array('id' => $appointment_id),
            array('%d')
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => 'Erreur lors de la suppression du rendez-vous: ' . $wpdb->last_error));
            return;
        }
        
        wp_send_json_success(array('message' => 'Rendez-vous supprimé avec succès'));
    }
    
    /**
     * Convert appointments to FullCalendar events.
     *
     * @since    1.0.0
     * @param    array     $appointments    Array of appointment objects.
     * @return   array                      Array of events formatted for FullCalendar.
     */
    public static function to_fullcalendar_events($appointments) {
        $events = array();
        
        foreach ($appointments as $appointment) {
            $events[] = array(
                'id' => $appointment->id,
                'title' => $appointment->titre,
                'start' => $appointment->date_debut,
                'end' => $appointment->date_fin,
                'extendedProps' => array(
                    'patientId' => $appointment->patient_id,
                    'patientName' => $appointment->patient_name,
                    'description' => $appointment->description,
                    'status' => $appointment->status
                ),
                'backgroundColor' => self::get_status_color($appointment->status),
                'borderColor' => self::get_status_color($appointment->status)
            );
        }
        
        return $events;
    }
    
    /**
     * Get color based on appointment status.
     *
     * @since    1.0.0
     * @param    string    $status    Appointment status.
     * @return   string               Hex color code.
     */
    private static function get_status_color($status) {
        switch ($status) {
            case 'confirmé':
                return '#4e73df'; // blue
            case 'en attente':
                return '#f6c23e'; // yellow
            case 'annulé':
                return '#e74a3b'; // red
            default:
                return '#4e73df'; // default to blue
        }
    }
}
