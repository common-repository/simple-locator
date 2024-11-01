<?php 
namespace SimpleLocator\Repositories;

/**
* Settings Repo
*/
class SettingsRepository 
{
	/**
	* Get the Post Type for Locations
	*/
	public function getPostType()
	{
		$option = get_option('wpsl_post_type');
		return ( !isset($option) || $option == '' ) ? 'locations' : $option;
	}

	/**
	* Get the map service
	*/
	public function mapService()
	{
		$option = get_option('wpsl_map_service');
		return ( !isset($option) || $option == '' ) ? 'google' : $option;
	}

	/**
	* Include the map library?
	*/
	public function includeMapLibrary($location = 'public')
	{
		if ( $location == 'public' ){
			$option = get_option('wpsl_gmaps_api');
			return ( isset($option) && $option == 'true' ) ? true : false;
		}
		$option = get_option('wpsl_gmaps_api_admin');
		return ( isset($option) && $option == 'true' ) ? true : false;
	}

	/**
	* Include plugin CSS?
	*/
	public function includeCss()
	{
		$option = get_option('wpsl_output_css');
		return ( isset($option) && $option == 'true' ) ? true : false;
	}

	/**
	* Get Geo Button Options
	* @param $return string 
	*/
	public function geoButton($return = 'enabled')
	{
		$option = get_option('wpsl_geo_button');
		if ( $return == 'enabled' ){
			return ( !isset($option['enabled']) || $option['enabled'] == "" ) ? false : 'true';
		}
		return ( !isset($option['text']) || $option['text'] == "" ) ? __('Use my location', 'simple-locator') : $option['text'];
	}

	/**
	* Output the Maps API Key
	* @return boolean
	*/
	public function outputMapsApi()
	{
		$option = get_option('wpsl_gmaps_api');
		return ( $option == 'true' ) ? true : false;
	}

	/**
	* Output the Google Maps API in the Admin
	* @return boolean
	*/
	public function outputGMapsAdmin()
	{
		$option = get_option('wpsl_gmaps_api_admin');
		return ( $option == 'true' ) ? true : false;
	}

	/**
	* Show a default map?
	* @return boolean
	*/
	public function showDefaultMap()
	{
		$option = get_option('wpsl_default_map');
		return ( isset($option['show']) && $option['show'] == "true" ) ? true : false;
	}

	/**
	* Default map coordinates & zoom
	* @return string
	* @param string - what field to return
	*/
	public function defaultMap($return = 'latitude')
	{
		$option = get_option('wpsl_default_map');
		if ( $return == 'latitude' ) return $option['latitude'];
		if ( $return == 'longitude' ) return $option['longitude'];
		if ( $return == 'zoom' ) return $option['zoom'];
		if ( $return == 'user_location' ){
			return ( isset($option['user_location']) && $option['user_location'] == 'true' ) ? 'true' : 'false';	
		} 
	}

	/**
	* Unit of Measurement
	*/
	public function measurementUnit()
	{
		$option = get_option('wpsl_measurement_unit');
		return ( $option == 'miles' || $option == 'Miles' ) ? 'miles' : 'kilometers';
	}

	/**
	* Results Limit
	*/
	public function resultsLimit()
	{
		$option = get_option('wpsl_results_fields_formatted');
		return ( isset($option['limit']) ) ? $option['limit'] : -1;
	}

	/**
	* Get the results fields from the formatted option
	* @return array
	*/
	public function getResultsFieldArray()
	{
		$exclude = ['post_title','distance','post_excerpt','post_permalink','show_on_map','post_thumbnail'];
		$resultoutput = get_option('wpsl_results_fields_formatted');
		$resultoutput = $resultoutput['output'];
		preg_match_all("/\[([^\]]*)\]/", $resultoutput, $matches);
		return array_diff(array_unique($matches[1]), $exclude);
	}

	/**
	* Get results field formatting option
	*/
	public function resultsFormatting($type = 'search')
	{
		if ( $type == 'search' ) $option = get_option('wpsl_results_fields_formatted');
		if ( $type == 'default' ) $option = get_option('wpsl_results_fields_formatted_default');
		return $option['output'];
	}

	/**
	* Get the Location Post Type
	* @since 1.1.0
	* @return string
	*/
	public function getLocationPostType()
	{
		return get_option('wpsl_post_type');
	}

	/**
	* Get the distance unit
	* @since 1.1.0
	* @return string
	*/
	public function getDistanceUnit()
	{
		$unit = get_option('wpsl_measurement_unit');
		if ( $unit == "" || $unit == 'miles' ) return 'miles';
		return 'kilometers';
	}

	/**
	* Get the localized distance unit
	* @since 1.1.0
	* @return string
	*/
	public function getDistanceUnitLocalized()
	{
		$unit = get_option('wpsl_measurement_unit');
		if ( $unit == "" || $unit == 'miles' ) return __('Miles', 'simple-locator');
		return __('Kilometers', 'simple-locator');
	}

	/**
	* Get Geocode Field
	*/
	public function getGeoField($field = 'lat')
	{
		return ( $field == 'lat' ) ? get_option('wpsl_lat_field') : get_option('wpsl_lng_field');
	}

	/**
	* Get the ACF map field if exists
	*/
	public function acfMapField()
	{
		$option = get_option('wpsl_acf_map_field');
		return ( $option !== '' ) ? $option : false;
	}

	/**
	* Is Autocomplete enabled?
	* @return boolean
	*/
	public function autocomplete()
	{
		$option = get_option('wpsl_enable_autocomplete');
		return ( $option == 'true' ) ? true : false;
	}

	/**
	* Are custom map options being used?
	*/
	public function customMapOptions()
	{
		$option = get_option('wpsl_custom_map_options');
		return ( $option && $option == '1' ) ? true : false;
	}

	/**
	* Get JS Map options
	*/ 
	public function mapOptions()
	{
		$option = get_option('wpsl_map_options');
		if ( $option ) return $option;
		include( dirname(dirname(__FILE__)) . '/Migrations/map_options/map-options.php' );
		return $default;
	}

	/**
	* Are custom autocomplete options being used?
	*/
	public function customAutocompleteOptions()
	{
		$option = get_option('wpsl_custom_autocomplete_options');
		return ( $option && $option == '1' ) ? true : false;
	}

	/**
	* Get JS Map options
	*/ 
	public function autocompleteOptions()
	{
		$option = get_option('wpsl_autocomplete_options');
		if ( $option ) return $option;
		include( dirname(dirname(__FILE__)) . '/Migrations/map_options/autocomplete-options.php' );
		return $default;
	}

	/**
	* Is JS debugging enabled?
	*/
	public function jsDebug()
	{
		$option = get_option('wpsl_js_debug');
		if ( $option && $option == 'true' ) return true;
		return false;
	}

	/**
	* Include the user location as a pin in map?
	*/
	public function includeUserPin()
	{
		$option = get_option('wpsl_include_user_pin');
		return ( !$option || $option !== 'true' ) ? false : true;
	}

	/**
	* Is marker clustering enabled?
	*/
	public function useMarkerClusters()
	{
		$option = get_option('wpsl_marker_clusters');
		return ( !$option || $option !== 'true' ) ? false : true;
	}

	/**
	* Is a custom cluster interface being used?
	*/
	public function useClusterRenderer()
	{
		$option = get_option('wpsl_marker_cluster_renderer_enabled');
		return ( !$option || $option !== '1' ) ? false : true;
	}

	/**
	* Custom Renderer interface for the MarkerClusterer
	*/
	public function clusterRenderer()
	{
		$option = get_option('wpsl_marker_cluster_renderer');
		return ( $option && $option !== '' ) ? $option : false;
	}

	/**
	* Get the Map Pin
	*/
	public function mapPin($style = 'standard')
	{
		if ( $style == 'standard' ){
			$pin = get_option('wpsl_map_pin');
			if ( !$pin && $pin == '' ) $pin = \SimpleLocator\Helpers::plugin_url() . '/assets/images/map-marker.svg';
			return $pin;
		}
		$pin = get_option('wpsl_map_pin_user');
		if ( !$pin && $pin == '' ) $pin = \SimpleLocator\Helpers::plugin_url() . '/assets/images/map-marker-blue.svg';
		return apply_filters('simple_locator_map_pin_user', $pin);
	}

	/**
	* Are non-ajax results disabled from the content
	*/
	public function resultsInContent()
	{
		$option = get_option('wpsl_results_content_disabled');
		return ( isset($option) && $option == 'true' ) ? false : true;
	}

	/**
	* Include the admin post listing map?
	*/
	public function includeAdminListMap()
	{
		$option = get_option('wpsl_display_admin_table_map');
		return ( !$option || $option !== 'true' ) ? false : true;
	}

	/**
	* Get the ACF tab to place location meta in
	*/ 
	public function acfTab()
	{
		if ( !function_exists('get_fields') ) return '';
		$option = get_option('wpsl_acf_tab');
		return ( !$option || $option == '' ) ? '' : $option;
	}
} 