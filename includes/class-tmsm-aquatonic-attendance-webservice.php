<?php

/**
 * Aquatonic Attendance web service
 *
 * @since      1.0.0
 *
 * @package    Tmsm_Aquatonic_Attendance
 * @subpackage Tmsm_Aquatonic_Attendance/includes
 */

class Tmsm_Aquatonic_Attendance_Webservice {

	/**
	 * Webservice Namespace
	 *
	 * @access 	const
	 * @since 	1.0.0
	 * @var 	string
	 */
	const WSNAMESPACE = 'http://ws.aquatonic-attendance.com/schemas/planning/2012A';

	/**
	 * Webservice URL
	 *
	 * @access 	private
	 * @since 	1.0.0
	 * @var 	string
	 */
	const URL = 'https://ws.aquatonic-attendance.com/Planning/2012A/PlanningService.asmx?WSDL';

	/**
	 * Webservice Oauth identifiers
	 *
	 * @access 	private
	 * @since 	1.0.0
	 * @var 	array
	 */
	private $oauth_identifiers = [];

	/**
	 * Constructor
	 */
	public function __construct() {

		$options = get_option('tmsm-aquatonic-attendance-options', false);

		$this->set_oauth_identifiers();

	}

	/**
	 * Set oauth identifiers
	 */
	private function set_oauth_identifiers(){
		$options = get_option('tmsm-aquatonic-attendance-options', false);
		$this->oauth_identifiers = [
			'consumerKey'    => $options['consumerkey'],
			'consumerSecret' => $options['consumersecret'],
			'accessToken'    => $options['accesstoken'],
			'accessSecret'   => $options['tokensecret'],
		];
	}

	/**
	 * Get Layout
	 *
	 * @return string
	 */
	private function get_layout(){
		return '<level name="ArticleRate"><property name="Status" /><property name="Price" /><property name="Availability" /><property name="MinimumStayThrough" /></level>';

	}
	/**
	 * Get Filters
	 *
	 * @param null $rateids
	 *
	 * @return string
	 */
	private function get_filters($rateids = null){
		$options = get_option('tmsm-aquatonic-attendance-options', false);

		$option_rateids = $rateids;
		$option_ratecode = null;
		$option_roomids = $options['roomids'];
		$option_groupid = $options['groupid'];
		$option_hotelid = $options['hotelid'];

		// rates
		$option_rateids_array = [];
		if(!empty($option_rateids) ){
			$option_rateids_array = explode(',', $option_rateids);
			foreach($option_rateids_array as &$item){
				$item = trim($item);
			}
		}
		$filters_rateids = '';
		if(!empty($option_rateids_array) && is_array($option_rateids_array) && count($option_rateids_array) > 0){
			$filters_rateids = '<rates default="Excluded">';
			foreach($option_rateids_array as $item){
				$filters_rateids .= '<exception id="'.$item.'"/>';
			}
			$filters_rateids .= '</rates>';
		}

		//rooms
		$option_roomids_array = [];
		if(!empty($option_roomids) ){
			$option_roomids_array = explode(',', $option_roomids);
			foreach($option_roomids_array as &$item){
				$item = trim($item);
			}
		}
		$filters_roomids = '';
		if(!empty($option_roomids_array) && is_array($option_roomids_array) && count($option_roomids_array) > 0){
			$filters_roomids = '<rooms default="Excluded">';
			foreach($option_roomids_array as $item){
				$filters_roomids .= '<exception id="'.$item.'"/>';
			}
			$filters_roomids .= '</rooms>';
		}

		// ratecode
		$filters_ratecode='';
		if(!empty($option_ratecode)){
			$filters_ratecode = 'referenceRateCode="'.$option_ratecode.'"';
		}

		// @TODO include $filters_roomids but it doesn't give any result with it
		// @TODO not hardcode OTABAR
		//referenceRateCode="BARPROM"
		$filters = '
					<ratePlans><ratePlan groupId="'.$option_groupid.'" '.$filters_ratecode.'><hotels default="Excluded"><exception id="'.$option_hotelid.'" /></hotels></ratePlan></ratePlans>'.
	                $filters_rateids.
	                //$filters_roomids.
	                '<currencies default="Excluded"><exception currency="EUR"/></currencies>'.
	                '<status><include status="Available" /><include status="NotAvailable" /></status>'.
		'';

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			error_log('filters:');
			error_log($filters);
		}

		return $filters;

	}

	/**
	 * Get Data from Aquatonic Attendance API call
	 *
	 * @param string $month (YYYY-MM)
	 *
	 * @return array
	 */
	public function get_data($month){


		return [];
	}

	/**
	 * Convert XML results in array
	 *
	 * @param string $xml
	 *
	 * @return array
	 */
	static public function convert_to_array($xml){

		$domObject = new DOMDocument();
		$domObject->loadXML($xml);

		$domXPATH = new DOMXPath($domObject);
		$results = $domXPATH->query("//soap:Body/*");

		$array = [];
		foreach($results as $result)
		{
			$array = json_decode(json_encode(simplexml_load_string($result->ownerDocument->saveXML($result))), true);
		}
		return $array;
	}




}