<?php 
namespace SimpleLocator\Services\LocationSearch;

use SimpleLocator\Repositories\SettingsRepository;
use SimpleLocator\Helpers;

/**
* Formats a result to match defined format
*/
class LocationResultPresenter 
{
	/**
	* Result
	* @var object - WP SQL result
	*
	* Available Properties include: 
	* title, id, wpsl_address, wpsl_city, wpsl_state, wpsl_zip, wpsl_phone, wpsl_website, latitude, longitude, distance 
	* 
	*/
	private $result;

	/**
	* Count of this result
	* @var int
	*/
	private $count;

	/**
	* Optional build options
	* @var array
	*/
	private $options;

	/**
	* Results Fields from Settings
	* @var array
	*/
	private $results_fields;

	/**
	* Settings Repository
	*/
	private $settings_repo;

	/**
	* Formatted Output from Settings
	*/
	private $output;

	/**
	* Unit of Measurement
	*/
	private $distance_unit;

	/**
	* The Location Post Type
	* @var obj
	*/
	private $post_type;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		$this->output = $this->settings_repo->resultsFormatting();
		$this->results_fields = $this->settings_repo->getResultsFieldArray();
		$this->distance_unit = $this->settings_repo->measurementUnit();
	}

	/**
	* Primary Presenter Method
	* @return array
	*/
	public function present($result, $count, $options = [])
	{
		$this->result = $result;
		if ( !isset($this->result->distance) ) $this->output = $this->settings_repo->resultsFormatting('default');
		$this->count = $count;
		$this->options = $options;
		return $this->setData();
	}

	/**
	* Set the primary result data
	* @return array
	*/
	private function setData()
	{
		$location = [
			'id' => $this->result->id,
			'title' => $this->result->title,
			'permalink' => get_permalink($this->result->id),
			'latitude' => $this->result->latitude,
			'longitude' => $this->result->longitude,
			'output' => $this->formatOutput(),
			'infowindow' => $this->formatInfoWindow(),
			'mappin' => $this->mapPin(),
			'result_data' => $this->result
		];
		return apply_filters('simple_locator_result_location_data', $location);
	}

	/**
	* Set the formatted output
	*/
	private function formatOutput()
	{
		$output = $this->output;
		$output = $this->replacePostFields($output);
		foreach($this->results_fields as $field){
			$found = $this->result->$field; // WP result object property
			$output = str_replace('[' . $field . ']', $found, $output);
		}
		$output = $this->removeEmptyTags($output);
		$output = Helpers::replaceURLs($output);
		$output = wpautop($output);
		$output = apply_filters('simple_locator_result', $output, $this->result, $this->count);
		return $output;
	}

	/**
	* Render the info window output
	* @return str html
	*/
	private function formatInfoWindow()
	{
		$this->post_type = get_post_type_object($this->settings_repo->getLocationPostType());
		$infowindow = '<div data-result="' . $this->count . '"><h4>[post_title]</h4><p><a href="[post_permalink]" data-location-id="'.$this->result->id.'">' . $this->post_type->labels->view_item . '</a></p></div>';
		$infowindow = $this->replacePostFields($infowindow);
		$infowindow = apply_filters('simple_locator_infowindow', $infowindow, $this->result, $this->count);
		return $infowindow;
	}

	/**
	* Replace post fields from settings
	*/
	private function replacePostFields($output)
	{
		if ( isset($this->result->distance) ) $output = str_replace('[distance]', round($this->result->distance, 2) . ' ' . $this->distance_unit, $output);
		
		$output = str_replace('[post_title]', $this->result->title, $output);

		if ( strpos($output, '[post_permalink]') !== false ){
			$output = str_replace('[post_permalink]', get_permalink($this->result->id), $output);
		}
		if ( strpos($output, '[post_excerpt]') !== false ){
			$output = str_replace('[post_excerpt]', Helpers::excerptByID($this->result->id), $output);
		}
		if ( strpos($output, '[post_thumbnail_') !== false ){
			$output = $this->addThumbnail($output);
		}

		// Show on Map Link
		$maplink = '<a href="#" class="infowindow-open map-link" data-simple-locator-open-infowindow="' . $this->count . '">' . __('Show on Map', 'simple-locator') . '</a>';
		$output = str_replace('[show_on_map]', $maplink, $output);

		return $output;
	}

	/**
	* Remove empty tags
	*/
	private function removeEmptyTags($output)
	{
		$output = preg_replace("/<p[^>]*><\\/p[^>]*>/", '', $output); // empty p tags
		$output = str_replace('<a href="http://">http://</a>', '', $output); // remove empty links
		$output = str_replace('<a href=""></a>', '', $output);
		$output = str_replace("\r\n\r\n", "\n", $output);
		return $output;
	}

	/**
	* Add the post thumbnail
	*/
	private function addThumbnail($output)
	{
		$sizes = get_intermediate_image_sizes();
		foreach ( $sizes as $size ){
			if ( strpos($output, '[post_thumbnail_' . $size) !== false ){
				$output = str_replace('[post_thumbnail_' . $size . ']', $this->getThumbnail($size), $output);
			}
		}
		return $output;		
	}

	/**
	* Add the map pin (provides functionality to customize pin for each result)
	*/
	private function mapPin()
	{
		$custom_pin = $this->settings_repo->mapPin();
		return apply_filters('simple_locator_map_pin', $custom_pin, $this->result);
	}

	/**
	* Get thumbnail
	*/
	private function getThumbnail($size)
	{
		return ( has_post_thumbnail($this->result->id) )
			? get_the_post_thumbnail($this->result->id, $size)
			: ' ';
	}
}