<?php 
namespace SimpleLocator\Post;

use SimpleLocator\Repositories\SettingsRepository;

/**
* Add Map and Location data to single view
*/
class Singular 
{
	/**
	* Location Post Type
	* @var string
	*/
	private $post_type;

	/**
	* Location Data
	* @var array
	*/
	private $location_data;

	/**
	* Options
	* @var array
	*/
	private $options;

	/**
	* Settings Repository
	*/
	private $settings_repo;

	public function __construct()
	{
		$this->settings_repo = new SettingsRepository;
		$this->setPostType();
		$this->setOptions();
		$this->filterContent();
	}

	/**
	* Set the Post Type
	*/
	private function setPostType()
	{
		$this->post_type = $this->settings_repo->getLocationPostType();
	}

	/**
	* Set the View Options
	*/
	private function setOptions()
	{
		$this->options['additionalfields'] = false;
	}

	/**
	* Filter the Content
	*/
	private function filterContent()
	{
		add_filter('the_content', [$this, 'addFilteredContent']);
	}

	/**
	* Set the location data for use in map and on page
	*/
	private function setLocationData()
	{
		$this->location_data['additionalfields'] = 'show';
		$this->location_data['title'] = get_the_title();
		$this->location_data['latitude'] = get_post_meta( get_the_id(), get_option('wpsl_lat_field'), true );
		$this->location_data['longitude'] = get_post_meta( get_the_id(), get_option('wpsl_lng_field'), true );
		if ( $this->post_type == 'location' ){
			$this->location_data['address'] = get_post_meta( get_the_id(), 'wpsl_address', true);
			$this->location_data['city'] = get_post_meta( get_the_id(), 'wpsl_city', true);
			$this->location_data['state'] = get_post_meta( get_the_id(), 'wpsl_state', true);
			$this->location_data['zip'] = get_post_meta( get_the_id(), 'wpsl_zip', true);
			$this->location_data['phone'] = get_post_meta( get_the_id(), 'wpsl_phone', true);
			$this->location_data['website'] = get_post_meta( get_the_id(), 'wpsl_website', true);
			$this->location_data['additionalinfo'] = get_post_meta( get_the_id(), 'wpsl_additionalinfo', true);
		}
	}

	/**
	* Apply filter to the content if single view of location post
	*/
	public function addFilteredContent($content)
	{	
		if ( ( is_singular($this->post_type) ) && ( get_option('wpsl_singular_data') == 'true') ){
			if ( !is_main_query() ) return;
			$this->setLocationData();
			$this->enqueueScript();
			$output = $this->addmap();
			$content = $output . $content;
		} 
		return $content;
	}

	/**
	* Add the map to the output
	*/
	private function addmap()
	{
		include( \SimpleLocator\Helpers::view('singular-post') );
		return $out;
	}

	/**
	* Enqueue the simple locator scripts & add localized data to them
	*/
	private function enqueueScript()
	{
		if ( (isset($this->location_data['latitude'])) && (isset($this->location_data['longitude'])) ){
			wp_enqueue_script('google-maps');
			wp_enqueue_script('simple-locator');
		}
	}
}