<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/admin/partials
 */
?>
<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

<form method="post" action="options.php"><?php
	settings_fields( $this->plugin_name . '-options' );
	do_settings_sections( $this->plugin_name );
	submit_button( __( 'Save options', 'tmsm-aquatonic-attendance' ));

	do_action( 'tmsm_aquatonic_attendance_cronaction' );
	?></form>