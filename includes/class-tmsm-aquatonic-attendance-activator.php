<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/includes
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Attendance_Activator {

	/**
	 * Activate
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		if ( ! wp_next_scheduled( 'tmsm_aquatonic_attendance_cronaction' ) ) {
			wp_schedule_event( time(), 'tmsm_aquatonic_attendance_refresh_schedule', 'tmsm_aquatonic_attendance_cronaction' );
		}
	}

}
