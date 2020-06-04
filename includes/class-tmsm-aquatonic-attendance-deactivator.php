<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Attendance_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook('tmsm_aquatonic_attendance_cronaction');
	}

}
