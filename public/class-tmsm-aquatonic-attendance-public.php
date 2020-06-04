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
	 * Engine URL
	 *
	 * @since 		1.0.0
	 */
	const ENGINE_URL = 'https://www.secure-hotel-booking.com/';

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/tmsm-aquatonic-attendance-public.css', array(), $this->version, 'all' );

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/tmsm-aquatonic-attendance-public.js', array( 'jquery' ), $this->version, true );


		// Params
		$params = [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'locale'   => $this->get_locale(),
			'security' => wp_create_nonce( 'security' ),
			'i18n'     => [
				//'fromprice'          => _x( 'From', 'price', 'tmsm-aquatonic-attendance' ),
			],
			'options'  => [
				//'currency' => $this->get_option( 'currency' ),
			],
			'data'     => $this->get_attendance_data(),
		];

		wp_localize_script( $this->plugin_name, 'tmsm_aquatonic_attendance_params', $params);
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

		$output = 'Testing';

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
		$output = '<div id="tmsm-aquatonic-attendance-container">'.$output.'</div>';
		return $output;
	}




	/**
	 * Get attendance data
	 *
	 * @return array
	 * @throws Exception
	 */
	private function get_attendance_data(){

		$data = [];

		return $data;
	}


	/**
	 * Refresh attendance data
	 *
	 * @return array
	 * @throws Exception
	 */
	public function refresh_attendance_data(){

		error_log('refresh_attendance_data');
		$data = [];

		return $data;
	}


}
