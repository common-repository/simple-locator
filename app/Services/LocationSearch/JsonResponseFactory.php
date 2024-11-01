<?php 
namespace SimpleLocator\Services\LocationSearch;

use SimpleLocator\Services\LocationSearch\ResultsInfoPresenter;

/**
* Build the JSON Response Array for a Location Search
*/
class JsonResponseFactory 
{
	/**
	* Response Data
	*/
	private $data;

	/**
	* Request
	*/
	private $request;

	/**
	* Set the Response Data
	*/
	private function setData($results, $total_count = 0)
	{
		$taxonomies = ( isset($this->request['taxonomies']) ) ? $this->request['taxonomies'] : null;
		$address = ( isset($this->request['address']) ) ? sanitize_text_field($this->request['address']) : null;
		$formatted_address = ( isset($this->request['formatted_address']) ) ? sanitize_text_field($this->request['formatted_address']) : null;
		$geolocation = ( isset($this->request['geolocation']) && $this->request['geolocation'] == 'true' ) ? true : false;
		$allow_empty_address = ( isset($this->request['allow_empty_address']) && $this->request['allow_empty_address'] == 'true' ) ? true : false;
		$page = ( isset($this->request['page']) ) ? intval(sanitize_text_field($this->request['page'])) : null;
		$per_page = ( isset($this->request['per_page']) ) ? intval(sanitize_text_field($this->request['per_page'])) : -1;

		// Additional Pagination/etc…
		$search_data = [];
		$search_data['results'] = $results;
		$search_data['total_results'] = $total_count;
		$search_data['max_num_pages'] = ( isset($this->request['per_page']) && $this->request['per_page'] > 0 ) ? ceil($total_count / $this->request['per_page']) : -1;

		$result_info_presenter = new ResultsInfoPresenter($this->request, $search_data);
		$autoload = ( isset($this->request['autoload']) && $this->request['autoload'] ) ? true : false; // for pagination in non-ajax auto location
		$unit = ( isset($this->request['unit']) ) ? sanitize_text_field($this->request['unit']) : get_option('wpsl_measurement_unit');

		$this->data = [
			'address' => $address,
			'formatted_address' => $formatted_address,
			'distance' => isset($this->request['distance']) ? sanitize_text_field($this->request['distance']) : null,
			'latitude' => isset($this->request['latitude']) ? sanitize_text_field($this->request['latitude']) : null,
			'longitude' => isset($this->request['longitude']) ? sanitize_text_field($this->request['longitude']) : null,
			'unit' => $unit,
			'geolocation' => $geolocation,
			'taxonomies' => $taxonomies,
			'allow_empty_address' => $allow_empty_address,
			'page' => $page,
			'per_page' => $per_page,
			'results_header' => $result_info_presenter->resultsHeader(),
			'current_counts' => $result_info_presenter->currentResultCounts(),
			'page_position' => $result_info_presenter->pagePosition(),
			'total_pages' => $search_data['max_num_pages'],
			'back_button' => $result_info_presenter->pagination('back', false, $autoload),
			'next_button' => $result_info_presenter->pagination('next', false, $autoload),
			'page_jump_form' => $result_info_presenter->goToPage(false),
			'loading_spinner' => $result_info_presenter->loadingSpinner()
		];
	}

	/**
	* Build the Response Array
	* @return array
	*/
	public function build($results, $results_count, $total_count = 0, $request = null)
	{
		$this->request = ( $request ) ? $request : $_POST;
		$this->setData($results, $total_count);
		$data = [
			'status' => 'success', 
			'distance'=> $this->data['distance'],
			'latitude' => $this->data['latitude'],
			'longitude' => $this->data['longitude'],
			'unit' => $this->data['unit'],
			'formatted_address' => $this->data['formatted_address'],
			'address' => $this->data['address'],
			'result_count' => $results_count,
			'geolocation' => $this->data['geolocation'],
			'taxonomies' => $this->data['taxonomies'],
			'allow_empty_address' => $this->data['allow_empty_address'],
			'total_count' => $total_count,
			'page' => $this->data['page'],
			'per_page' => $this->data['per_page'],
			'total_pages' => $this->data['total_pages'],
			'results_header' => $this->data['results_header'],
			'current_counts' => $this->data['current_counts'],
			'page_position' => $this->data['page_position'],
			'back_button' => $this->data['back_button'],
			'next_button' => $this->data['next_button'],
			'page_jump_form' => $this->data['page_jump_form'],
			'loading_spinner' => $this->data['loading_spinner'],
			'results' => $results
		];
		return $this->data = apply_filters('simple_locator_results_data', $data);
	}
}