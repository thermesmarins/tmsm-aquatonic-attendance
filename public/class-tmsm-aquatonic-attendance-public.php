<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/nicomollet
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/public
 * @author     Nicolas Mollet <nico.mollet@gmail.com>
 */
class Tmsm_Aquatonic_Attendance_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of the plugin.
	 * @param      string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Get locale
	 */
	private function get_locale() {
		return (function_exists('pll_current_language') ? pll_current_language() : substr(get_locale(),0, 2));
	}


	/**
	 * Get option
	 * @param string $option_name
	 *
	 * @return null
	 */
	private function get_option($option_name = null){

		$options = get_option($this->plugin_name . '-options');

		if(!empty($option_name)){
			return $options[$option_name] ?? null;
		}
		else{
			return $options;
		}

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquatonic-attendance-public.css', array('theme'), $this->version, 'all' );

		// Define inline css
		$css 			= '';

		// Return CSS
		if ( ! empty( $css ) ) {
			$css = '/* Aquatonic Attendance CSS */'. $css;
			wp_add_inline_style( $this->plugin_name, $css );
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-attendance-public.js', array( 'jquery', 'backbone', 'wp-util' ), $this->version, true );



		// Params
		$params = [
			'ajaxurl'        => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'tmsm-aquatonic-attendance-nonce-action' ),
			'locale'   => $this->get_locale(),
			'timer_period' => 60*5, //seconds
			'page' => get_permalink($this->get_option('pageid')),
			'i18n'     => [
				'attendance'          => __( 'Live Attendance', 'tmsm-aquatonic-attendance' ),
				'moreinfo'          => __( 'More Info About Attendance', 'tmsm-aquatonic-attendance' ),
			],
			'data'     => [
				'realtime' => [],
			],
		];

		wp_localize_script( $this->plugin_name, 'TmsmAquatonicAttendanceApp', $params);
	}

	/**
	 * Register the shortcodes
	 *
	 * @since    1.0.0
	 */
	public function register_shortcodes() {
		add_shortcode( 'tmsm-aquatonic-attendance-badge', array( $this, 'badge_shortcode') );
	}


	/**
	 * Get the current timeslot capacity (if 0, it is closed)
	 *
	 * @since    1.0.0
	 * @return int
	 */
	private function get_timeslot_capacity(){

		$timeslots = $this->get_option('timeslots').PHP_EOL;
		$timeslots_items = preg_split('/\r\n|\r|\n/', esc_attr($timeslots));
		$open = false;
		$capacity = 0;

		foreach($timeslots_items as &$timeslots_item){

			$tmp_timeslots_item = $timeslots_item;
			$tmp_timeslots_item_array = explode('=', $tmp_timeslots_item);

			if ( is_array( $tmp_timeslots_item_array ) && count($tmp_timeslots_item_array) === 3 ) {
				$timeslots_item = [
					'daynumber' => trim($tmp_timeslots_item_array[0]),
					'times' => trim($tmp_timeslots_item_array[1]),
					'capacity' => trim($tmp_timeslots_item_array[2]),
				];

			}
		}
		$timeslots_item = null;

		$current_day = date('w');
		//$current_day = 6;

		foreach($timeslots_items as $timeslots_key => $timeslots_item_to_parse){

			if ( isset( $timeslots_item_to_parse['daynumber'] ) && $timeslots_item_to_parse['daynumber'] == $current_day ) {
				$times = explode(',', $timeslots_item_to_parse['times']);
				foreach($times as $time){
					$hoursminutes = explode('-', $time);
					$before = trim($hoursminutes[0]);
					$after = trim($hoursminutes[1]);
					$current_time = current_time('H:i');
					//$current_time = '13:00';

					if(strtotime($before) <= strtotime($current_time) && strtotime($current_time) <= strtotime($after) ){
						$open = true;
						$capacity = $timeslots_item_to_parse['capacity'];
					}
				}

			}
		}

		return $capacity;
	}

	/**
	 * Send an email to admin if the scheduled cron is not defined
	 */
	public function check_cron_schedule_exists(){

		if ( ! wp_next_scheduled( 'tmsm_aquatonic_attendance_cronaction' ) ) {

			$email = wp_mail(
				get_option( 'admin_email' ),
				wp_specialchars_decode( sprintf( __( 'TMSM Aquatonic Attendance cron is not scheduled on %s', 'tmsm-aquatonic-attendance' ), get_option( 'blogname' ) ) ),
				wp_specialchars_decode( sprintf( __( 'TMSM Aquatonic Attendance cron is not scheduled on %s', 'tmsm-aquatonic-attendance' ) , "\r\n" . get_option( 'siteurl' ) . ' ' . get_option( 'blogname' ) ) )
			);
		}

	}

	/**
	 * Calendar shortcode
	 *
	 * @since    1.0.0
	 */
	public function badge_shortcode($atts) {
		$atts = shortcode_atts( array(
			'size' => 'normal',
			'option' => '',
		), $atts, 'tmsm-aquatonic-attendance-calendar' );

		$output = '
		<div id="tmsm-aquatonic-attendance-badge-select"></div>
		<div id="tmsm-aquatonic-attendance-badge-loading">'.__( 'Loading', 'tmsm-aquatonic-attendance' ).'</div>
		';

		/*
		$theme = wp_get_theme();
		$buttonclass = '';
		if ( 'StormBringer' == $theme->get( 'Name' ) || 'stormbringer' == $theme->get( 'Template' ) ) {
			$buttonclass = 'btn btn-primary';
		}
		if ( 'OceanWP' == $theme->get( 'Name' ) || 'oceanwp' == $theme->get( 'Template' ) ) {
			$buttonclass = 'button';
		}
		*/
		$output = '<div id="tmsm-aquatonic-attendance-badge-container" class="tmsm-aquatonic-attendance-badge-'.$atts['size'].'">'.$output.'</div>';
		return $output;
	}

	/**
	 * Have Voucher Template
	 */
	public function badge_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquatonic-attendance-badge">

			<# if ( data.capacity > 0) { #>
			<a class="progress" data-use="{{ data.use }}" data-count="{{ data.count }}" data-capacity="{{ data.capacity }}" data-percentage="{{ data.percentage}}" data-percentagerounded="{{ data.percentagerounded}}" href="{{ TmsmAquatonicAttendanceApp.page }}" data-toggle="tooltip" data-placement="auto right" title="{{ TmsmAquatonicAttendanceApp.i18n.moreinfo }}">
				<span class="progress-left">
					<span class="progress-bar progress-bar-color-{{ data.color }}"></span>
				</span>
				<span class="progress-right">
					<span class="progress-bar progress-bar-color-{{ data.color }}"></span>
				</span>
				<div class="progress-value">

						<span class="progress-value-text">
						{{ TmsmAquatonicAttendanceApp.i18n.attendance }}
						</span>
					<span class="progress-value-number">
							<b>{{ data.percentage }}%</b>
						</span>



				</div>
			</a>
			<# } #>

		</script>
		<?php
	}


	/**
	 * Get attendance data
	 *
	 * @return array
	 */
	private function get_realtime_data(){

		$count = intval(get_option('tmsm-aquatonic-attendance-count'));
		$aquospercentage = intval(get_option('tmsm-aquatonic-attendance-aquospercentage'));

		$use = 'count';

		if(!empty($aquospercentage)){
			$use = 'aquospercentage';
			$capacity = 100;
			$percentage = $aquospercentage;
		}
		else{
			$capacity = $this->get_timeslot_capacity();

			if(!empty($capacity)){
				$percentage = round( 100 * $count / $capacity );
			}
			else{
				$percentage = 0;
			}
			$percentage = max(0, $percentage);

			$percentage = min($percentage, 100);
		}


		$options = $this->get_option();
		$percentage_tier = 1;

		for ($tier = 1; $tier <= 5; $tier++) {
			if(!empty($options["tier${tier}_value"]) && $percentage > $options["tier${tier}_value"]){
				$percentage_tier = ($tier+1);
			}
		}

		$color = 'blue';

		if(!empty($percentage_tier)){
			$color = $options["tier${percentage_tier}_color"];

		}


		$data = [
			'count' => $count,
			'use' => $use,
			'capacity' => $capacity,
			'color' => $color,
			'percentage' => $percentage,
			'percentagerounded' => round( $percentage, - 1 ),
			];
		return $data;
	}


	/**
	 * Refresh attendance data
	 *
	 * @return array
	 * @throws Exception
	 */
	public function refresh_attendance_data(){
		$count = null;
		$aquospercentage = null;
		$errors = [];

		$settings_webserviceurl = $this->get_option( 'webservicecounturl' );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('tmsm-aquatonic-attendance refresh_attendance_data:');
		}

		// Call web service
		if ( ! empty( $settings_webserviceurl ) ) {

			// Connect with cURL
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			curl_setopt( $ch, CURLOPT_URL, $settings_webserviceurl );
			$result = curl_exec( $ch );
			curl_close( $ch );
			$result_array = [];

			if(empty($result)){
				$errors[] = __( 'Web service is not available', 'tmsm-aquatonic-attendance' );
			}
			else{
				$result_array = json_decode( $result, true );

				if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( var_export( $result_array, true ) );
				}

				if(!empty($result_array['Status']) && $result_array['Status'] == 'true'){

					$count = sanitize_text_field($result_array['Value']);
					$aquospercentage = sanitize_text_field($result_array['Pourcentage']);

					if ( $count === null ) {
						$errors[] = __( 'No data available', 'tmsm-aquatonic-attendance' );
					}
				}
				else{
					if(!empty($result_array['ErrorCode']) && !empty($result_array['ErrorMessage'])){
						$errors[] = sprintf(__( 'Error code %s: %s', 'tmsm-aquatonic-attendance' ), $result_array['ErrorCode'], $result_array['ErrorMessage']);
					}
				}
			}
		}


		// Logging errors
		if ( ! empty( $errors ) ) {
			error_log( 'tmsm-aquatonic-attendance-errors:' );
			error_log( print_r( $errors, true ) );

			$last_error_date = get_option( 'tmsm-aquatonic-attendance-lasterrordate', null );

			$send_error_email = empty( $last_error_date ) || $last_error_date !== date('Y-m-d');

			// Send an email about the error
			if($send_error_email === true) {
				$email = wp_mail(
					get_option( 'admin_email' ),
					wp_specialchars_decode( sprintf( __( 'TMSM Aquatonic Attendance web service is down on %s', 'tmsm-aquatonic-attendance' ), get_option( 'blogname' ) ) ),
					wp_specialchars_decode( sprintf( __( 'TMSM Aquatonic Attendance web service is down on %s with following errors: %s', 'tmsm-aquatonic-attendance' ) , "\r\n" . get_option( 'siteurl' ) . ' ' . get_option( 'blogname' ), "\r\n" . print_r( $errors, true ) ) )
				);
			}

			update_option( 'tmsm-aquatonic-attendance-lasterrordate', date( 'Y-m-d' ) );
			update_option( 'tmsm-aquatonic-attendance-lasterror', $errors );
		}

		// Save Count
		update_option('tmsm-aquatonic-attendance-count', $count);
		update_option('tmsm-aquatonic-attendance-aquospercentage', $aquospercentage);

	}

	/**
	 * Send a response to ajax request, as JSON.
	 *
	 * @param mixed $response
	 */
	private function ajax_return( $response = true ) {
		echo json_encode( $response );
		exit;
	}

	/**
	 * Ajax check nonce security
	 */
	private function ajax_checksecurity(){

		$security = sanitize_text_field( $_REQUEST['nonce'] );

		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquatonic-attendance-nonce-action' ) ) {
			$errors[] = __('Token security is not valid', 'tmsm-aquatonic-attendance');
		}
		else {
		}
		if(check_ajax_referer( 'tmsm-aquatonic-attendance-nonce-action', 'nonce' ) === false){
			$errors[] = __('Ajax referer is not valid', 'tmsm-aquatonic-attendance');
		}
		else{
		}

		if(!empty($errors)){
			wp_send_json($jsondata);
			wp_die();
		}

	}

	/**
	 * Ajax For Products
	 *
	 * @since    1.0.0
	 */
	public function ajax_realtime() {

		$this->ajax_checksecurity();
		$this->ajax_return( $this->get_realtime_data() );

	}

}
