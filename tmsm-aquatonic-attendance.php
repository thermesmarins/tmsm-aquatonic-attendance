<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/nicomollet
 * @since             1.0.0
 * @package           Tmsm_Aquatonic_Attendance
 *
 * @wordpress-plugin
 * Plugin Name:       TMSM Aquatonic Attendance
 * Plugin URI:        https://github.com/thermesmarins/tmsm-aquatonic-attendance
 * Description:       Display Aquatonic Attendance daily prices (best price) in a calendar view
 * Version:           1.1.5
 * Author:            Nicolas Mollet
 * Author URI:        https://github.com/nicomollet
 * Requires PHP:      5.6
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       tmsm-aquatonic-attendance
 * Domain Path:       /languages
 * Github Plugin URI: https://github.com/thermesmarins/tmsm-aquatonic-attendance
 * Github Branch:     master
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TMSM_AQUATONIC_ATTENDANCE_VERSION', '1.1.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-tmsm-aquatonic-attendance-activator.php
 */
function activate_tmsm_aquatonic_attendance() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-attendance-activator.php';
	Tmsm_Aquatonic_Attendance_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-tmsm-aquatonic-attendance-deactivator.php
 */
function deactivate_tmsm_aquatonic_attendance() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-attendance-deactivator.php';
	Tmsm_Aquatonic_Attendance_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_tmsm_aquatonic_attendance' );
register_deactivation_hook( __FILE__, 'deactivate_tmsm_aquatonic_attendance' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-tmsm-aquatonic-attendance.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_tmsm_aquatonic_attendance() {

	$plugin = new Tmsm_Aquatonic_Attendance();
	$plugin->run();

}
run_tmsm_aquatonic_attendance();
