<?php
/**
 * Database operations for the plugin.
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 *
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager_DB {

    /**
     * Get a setting value from the database.
     *
     * @since    1.0.0
     * @param    string    $key       The setting key.
     * @param    mixed     $default   Default value if setting is not found.
     * @return   mixed                The setting value or default.
     */
    public static function get_setting($key, $default = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'medoffice_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $table_name WHERE setting_key = %s",
            $key
        ));
        
        return ($value !== null) ? $value : $default;
    }
    
    /**
     * Save a setting value to the database.
     *
     * @since    1.0.0
     * @param    string    $key     The setting key.
     * @param    mixed     $value   The setting value.
     * @return   bool               Success or failure.
     */
    public static function save_setting($key, $value) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'medoffice_settings';
        
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE setting_key = %s",
            $key
        ));
        
        if ($exists) {
            return $wpdb->update(
                $table_name,
                array('setting_value' => $value),
                array('setting_key' => $key)
            );
        } else {
            return $wpdb->insert(
                $table_name,
                array(
                    'setting_key' => $key,
                    'setting_value' => $value
                )
            );
        }
    }
    
    /**
     * Save multiple settings at once.
     *
     * @since    1.0.0
     * @param    array    $settings   Associative array of settings (key => value).
     * @return   bool                 Success or failure.
     */
    public static function save_settings($settings) {
        foreach ($settings as $key => $value) {
            self::save_setting($key, $value);
        }
        return true;
    }
    
    /**
     * Calculate patient age based on birth date.
     *
     * @since    1.0.0
     * @param    string    $birth_date   Birth date in YYYY-MM-DD format.
     * @return   int                     Age in years.
     */
    public static function calculate_age($birth_date) {
        if (empty($birth_date)) {
            return null;
        }
        
        $birth_date = new DateTime($birth_date);
        $today = new DateTime('today');
        $age = $birth_date->diff($today)->y;
        
        return $age;
    }
    
    /**
     * Sanitize and format date time.
     *
     * @since    1.0.0
     * @param    string    $date_time   Date time string.
     * @param    string    $format      Output format (default: Y-m-d H:i:s).
     * @return   string                 Formatted date time or empty string if invalid.
     */
    public static function format_date_time($date_time, $format = 'Y-m-d H:i:s') {
        if (empty($date_time)) {
            return '';
        }
        
        try {
            $date = new DateTime($date_time);
            return $date->format($format);
        } catch (Exception $e) {
            return '';
        }
    }
    
    /**
     * Format a monetary value with currency symbol.
     *
     * @since    1.0.0
     * @param    float     $amount   The amount to format.
     * @return   string              Formatted amount with currency symbol.
     */
    public static function format_money($amount) {
        return number_format($amount, 2, '.', ' ') . ' TND';
    }
}
