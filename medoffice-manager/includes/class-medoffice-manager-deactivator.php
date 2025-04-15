<?php
/**
 * Fired during plugin deactivation.
 *
 * @since      1.0.0
 * @package    MedOffice_Manager
 * @subpackage MedOffice_Manager/includes
 */

class MedOffice_Manager_Deactivator {

    /**
     * Short Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Nothing to do for now
        // We don't want to delete tables on deactivation
    }
}
