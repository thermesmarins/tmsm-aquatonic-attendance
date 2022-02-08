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
			'ajaxurl'      => admin_url( 'admin-ajax.php' ),
			'nonce'        => wp_create_nonce( 'tmsm-aquatonic-attendance-nonce-action' ),
			'locale'       => $this->get_locale(),
			'timer_period' => 60 * 5, //seconds
			'page'         => get_permalink( $this->get_option( 'pageid' ) ),
			'i18n'         => [
				'attendance'      => __( 'Live Attendance', 'tmsm-aquatonic-attendance' ),
				'moreinfo'        => __( 'More Info About Attendance', 'tmsm-aquatonic-attendance' ),
				'nodata'          => __( 'No information at this moment', 'tmsm-aquatonic-attendance' ),
				'usedplaces'      => __( 'Used Places', 'tmsm-aquatonic-attendance' ),
				'remainingplaces' => __( 'remaining places', 'tmsm-aquatonic-attendance' ),
				'remainingplace'  => __( 'remaining place', 'tmsm-aquatonic-attendance' ),
				'complete'        => __( 'Full', 'tmsm-aquatonic-attendance' ),
			],
			'data'         => [
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
	public function badge_shortcode($atts): string {
		$atts = shortcode_atts( array(
			'camera_name' => '',
			'size' => 'normal',
			'option' => '',
		), $atts, 'tmsm-aquatonic-attendance-calendar' );

		// Camera name needs to be setup
		if( empty( $atts['camera_name']) ) {
			return '';
		}

		// Generate output
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

		return '<div id="tmsm-aquatonic-attendance-badge-container" data-camera="' . esc_attr( $atts['camera_name']) . '" class="tmsm-aquatonic-attendance-badge-' . esc_attr( $atts['size']) . ' tmsm-aquatonic-attendance-badge-' . esc_attr( $atts['camera_name']) . '">' . $output . '</div>';
	}

	/**
	 * Badge Template (bassin)
	 */
	public function badge_template_bassin(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquatonic-attendance-badge-bassin">

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
	 * Badge Template (brouillard)
	 */
	public function badge_template_brouillard(){
		?>
		<script type="text/html" id="tmpl-tmsm-aquatonic-attendance-badge-brouillard">
			<# if ( data.remaining !== null ) { #>
				<# if ( data.remaining === 0 ) { #>
					<span class="count-text count-text-complete">{{ TmsmAquatonicAttendanceApp.i18n.complete }}</span>
				<# } else { #>
					<span class="count-number"><b>{{ data.remaining }}</b></span>
					<span class="count-text">
						<# if ( data.remaining === 1 ) { #>
						{{ TmsmAquatonicAttendanceApp.i18n.remainingplace }}
						<# } else { #>
						{{ TmsmAquatonicAttendanceApp.i18n.remainingplaces }}
						<# } #>
					</span>
				<# } #>

			<# } else { #>
			{{ TmsmAquatonicAttendanceApp.i18n.nodata }}
			<# } #>
		</script>
		<?php
	}


	/**
	 * Get attendance data
	 *
	 * @param string $camera
	 *
	 * @return array
	 */
	private function get_realtime_data( string $camera ): array {

		$realtime_data = get_option( 'tmsm-aquatonic-attendance-data' );

		// Camera name must be setup
		if ( empty( $camera ) ) {
			return [];
		}
		$data = [];

		// Browse all camera data
		foreach ( $realtime_data as $data_camera ) {
			$use = 'count';
			$count = $data_camera->count;
			$count = max( 0, $count ); // is superior to 0
			if ( ! empty( $data_camera->pourcentage ) ) {
				$use        = 'aquospercentage';
				$capacity   = 100;
				$percentage = $data_camera->pourcentage;
			} else {
				$capacity = $this->get_timeslot_capacity();

				if ( ! empty( $capacity ) ) {
					$percentage = round( 100 * $data_camera->number / $capacity );
				} else {
					$percentage = 0;
				}
				$percentage = max( 0, $percentage );

				$percentage = min( $percentage, 100 );
			}

			$options         = $this->get_option();
			$percentage_tier = 1;

			for ( $tier = 1; $tier <= 5; $tier ++ ) {
				if ( ! empty( $options["tier${tier}_value"] ) && $percentage > $options["tier${tier}_value"] ) {
					$percentage_tier = ( $tier + 1 );
				}
			}

			$color = 'blue';

			if ( ! empty( $percentage_tier ) ) {
				$color = $options["tier${percentage_tier}_color"];
			}

			$remaining = null;

			if ( $data_camera->camera_name === 'brouillard' ) {
				$capacity = $this->get_option( 'mistcapacity' );
				$remaining = $capacity - $count;
				$remaining = max( 0, $remaining);
			}


			$data[ $data_camera->camera_name ] = [
				'remaining'         => $remaining,
				'count'             => $count,
				'camera'            => $data_camera->camera_name,
				'use'               => $use,
				'capacity'          => $capacity,
				'color'             => $color,
				'percentage'        => $percentage,
				'percentagerounded' => round( $percentage, - 1 ),
			];
		}

		return $data[ $camera ] ?? [];
	}

	/**
	 * Refresh attendance data
	 *
	 * @throws Exception
	 */
	public function refresh_attendance_data() {
		$data = [];
		$errors = [];

		$settings_webserviceurl = $this->get_option( 'webservicecounturl' );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('tmsm-aquatonic-attendance refresh_attendance_data:');
		}

		// Call web service
		if ( ! empty( $settings_webserviceurl ) ) {

			$headers = [
				'Content-Type' => 'application/json; charset=utf-8',
				'Cache-Control' => 'no-cache',
			];

			$response = wp_remote_get(
				$settings_webserviceurl,
				array(
					'headers'     => $headers,
					'timeout' => 10,
				)
			);
			$response_code = wp_remote_retrieve_response_code( $response );
			$response_data = json_decode( wp_remote_retrieve_body( $response ) );

			// Parsing response
			if(empty($response)){
				error_log( __( 'Web service is not available', 'tmsm-aquatonic-attendance' ) );
				$errors[] = __( 'Web service is not available', 'tmsm-aquatonic-attendance' );
			}
			else{
				if ( $response_code >= 400 ) {
					error_log( sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquatonic-attendance' ), absint( $response_code ) ) );
					$errors[] = sprintf( __( 'Error: Delivery URL returned response code: %s', 'tmsm-aquatonic-attendance' ), absint( $response_code ) );
				}

				if ( is_wp_error( $response ) ) {
					error_log('Error message: '. $response->get_error_message());
					$errors[] = sprintf( __( 'Error message: %s', 'tmsm-aquatonic-course-booking' ), $response->get_error_message() );
				}


				// No errors, success
				if ( ! empty( $response_data->status ) && ($response_data->status === 'true' ||  $response_data->status === true ) ) {
					$data = $response_data->data;
				}
				// Some error detected
				else{
					if ( ! empty( $response_data->error ) ) {
						$errors[] = sprintf( __( 'Error %s', 'tmsm-aquatonic-attendance' ), $response_data->error );
					} else {
						$errors[] = __( 'Unknown error', 'tmsm-aquatonic-attendance' );
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
				wp_mail(
					get_option( 'admin_email' ),
					wp_specialchars_decode( sprintf( __( 'TMSM Aquatonic Attendance web service is down on %s', 'tmsm-aquatonic-attendance' ), get_option( 'blogname' ) ) ),
					wp_specialchars_decode( sprintf( __( 'TMSM Aquatonic Attendance web service is down on %s with following errors: %s', 'tmsm-aquatonic-attendance' ) , "\r\n" . get_option( 'siteurl' ) . ' ' . get_option( 'blogname' ), "\r\n" . print_r( $errors, true ) ) )
				);
			}

			update_option( 'tmsm-aquatonic-attendance-lasterrordate', date( 'Y-m-d' ) );
			update_option( 'tmsm-aquatonic-attendance-lasterror', $errors );
		}

		// Save Count
		update_option('tmsm-aquatonic-attendance-data', $data);

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
		if(check_ajax_referer( 'tmsm-aquatonic-attendance-nonce-action', 'nonce' ) === false){
			$errors[] = __('Ajax referer is not valid', 'tmsm-aquatonic-attendance');
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

		$camera = sanitize_text_field($_REQUEST['camera']);

		$this->ajax_checksecurity();
		$this->ajax_return( $this->get_realtime_data($camera) );

	}

}
