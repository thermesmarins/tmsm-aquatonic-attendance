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
	private function get_option($option_name){

		$options = get_option($this->plugin_name . '-options');

		if(empty($options[$option_name])){
			return null;
		}
		return $options[$option_name];
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
			'i18n'     => [
				'attendance'          => __( 'Live Attendance', 'tmsm-aquatonic-attendance' ),
				//'fromprice'          => _x( 'From', 'price', 'tmsm-aquatonic-attendance' ),
			],
			'options'  => [
				//'currency' => $this->get_option( 'currency' ),
			],
			'data'     => [
				'products' => [],
				'realtime' => $this->get_realtime_data(),
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
	 * Send an email to admin if the scheduled cron is not defined
	 */
	public function check_cron_schedule_exists(){

		if ( ! wp_next_scheduled( 'tmsm_aquatonic_attendance_cronaction' ) ) {

			$email = wp_mail(
				get_option( 'admin_email' ),
				wp_specialchars_decode( sprintf( __('TMSM Aquatonic Attendance cron is not scheduled on %s', 'tmsm-aquatonic-attendance'), get_option( 'blogname' ) ) ),
				wp_specialchars_decode( sprintf( __('TMSM Aquatonic Attendance cron is not scheduled on %s', 'tmsm-aquatonic-attendance'), get_option( 'blogname' ) ) )
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
		$output = '<div id="tmsm-aquatonic-attendance-badge-container">'.$output.'</div>';
		return $output;
	}

	/**
	 * Have Voucher Template
	 */
	public function badge_template(){
		?>

		<script type="text/html" id="tmpl-tmsm-aquatonic-attendance-badge">
			aaa {{ data.count }} bbb

			<div class="progress" data-percentage="{{ data.occupation_rounded }}">
				<span class="progress-left">
					<span class="progress-bar progress-bar-color-{{ data.color }}"></span>
				</span>
				<span class="progress-right">
					<span class="progress-bar progress-bar-color-{{ data.color }}"></span>
				</span>
				<div class="progress-value">
					<p class="progress-value-text">
						{{ TmsmAquatonicAttendanceApp.i18n.attendance }}
					</p>
					<p class="progress-value-number">
						<b>{{ data.occupation }}%</b>
					</p>

				</div>
			</div>

		</script>
		<?php
	}


	/**
	 * Get attendance data
	 *
	 * @return array
	 */
	private function get_realtime_data(){

		$count = get_option('tmsm-aquatonic-attendance-count');
		$occupation = absint( 100 * $count / 60 );

		$color = 'blue';
		if($occupation > 65){
			$color = 'orange';
		}
		if($occupation > 85){
			$color = 'red';
		}

		$data = [
			'count' => $count,
			'capacity' => 60,
			'color' => $color,
			'occupation' => $occupation,
			'occupation_rounded' => round( $occupation, - 1 ),
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
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log( 'refresh_attendance_data' );
		}
		$count = null;
		$errors = [];

		// Call web service
		$settings_webserviceurl = $this->get_option( 'webservicecounturl' );
		if ( ! empty( $settings_webserviceurl ) ) {

			// Connect with cURL
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
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
					if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
						error_log( 'count: '.$count );
					}

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

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! empty( $errors ) ) {
			error_log('$errors:');
			error_log(print_r($errors, true));
		}

		// Save Count to Options
		update_option('tmsm-aquatonic-attendance-count', $count);

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
		error_log('ajax_checksecurity');

		error_log(print_r($_REQUEST, true));
		$security = sanitize_text_field( $_REQUEST['nonce'] );

		error_log('security: '.$security);
		$errors = array(); // Array to hold validation errors
		$jsondata   = array(); // Array to pass back data

		// Check security
		if ( empty( $security ) || ! wp_verify_nonce( $security, 'tmsm-aquatonic-attendance-nonce-action' ) ) {
			$errors[] = __('Token security is not valid', 'tmsm-aquatonic-attendance');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Token security is not valid');
			}
		}
		else {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Token security is valid' );
			}
		}
		if(check_ajax_referer( 'tmsm-aquatonic-attendance-nonce-action', 'nonce' ) === false){
			$errors[] = __('Ajax referer is not valid', 'tmsm-aquatonic-attendance');
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log('Ajax referer is not valid');
			}
		}
		else{
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( 'Ajax referer is valid' );
			}
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
