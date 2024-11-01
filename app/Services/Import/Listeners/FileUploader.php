<?php 
namespace SimpleLocator\Services\Import\Listeners;

use League\Csv\Reader;
use SimpleLocator\Repositories\ImportRepository;

/**
* Upload a File
*/
class FileUploader extends ImportListenerBase 
{
	/**
	* Import Repo
	* @var obj
	*/
	private $import_repo;

	/**
	* Import Template
	* @var post obj
	*/
	private $template = null;

	public function __construct()
	{
		$this->import_repo = new ImportRepository;
		parent::__construct();
		$this->copyFile();
	}

	/**
	* Copy the file to the uploads folder
	* @see SimpleLocator\WPData\UploadFilter for uploads filter
	*/
	private function copyFile()
	{
		if ( $_FILES['file']['name'] == "" ) return $this->error('Please include a file.');
		if ( !$this->isCsv(sanitize_text_field($_FILES['file']['type'])) ) return $this->error('File must be CSV format. This file\'s format is ' . sanitize_text_field($_FILES['file']['type']));
		$file = $_FILES['file'];
		$upload_overrides = [ 'test_form' => false ];
		$movefile = wp_handle_upload($file, $upload_overrides);
		if ( isset($movefile['error']) ) return $this->error($movefile['error']);
		
		$this->setTransient($movefile['file']);
		if ( $this->template ) return $this->success('3');
		$this->success('2');
	}

	/**
	* Set the transient data to use in the remaining import steps
	* @var string path and name of file, as returned by wp_handle_upload
	*/
	private function setTransient($file)
	{
		$mac = ( isset($_POST['mac_formatted']) ) ? true : false;
		$rowcount = $this->rowCount($file, $mac);
		$template = false;
		if ( isset($_POST['import_type']) && $_POST['import_type'] == 'template' ){
			$this->getImportTemplate();
			$template = $this->template;
		}
		$post_type = ( $this->template ) ? $this->template->import_post_type : $this->setPostType();
		$transient = [
			'file' => $file, // full path to file
			'mac' => $mac, // is mac formatted?
			'row_count' => $rowcount, // total rows in CSV file
			'post_type' => $post_type, // post type to import to
			'filename' => sanitize_text_field($_FILES['file']['name']), // filename for display purposes
			'complete_rows' => '0',
			'error_rows' => array(), // Rows with import or geocoding errors,
			'last_imported' => 0,
			'lat' => get_option('wpsl_lat_field'), // Field to save latitude to
			'lng' => get_option('wpsl_lng_field'), // Field to save longitude to
			'import_type' => sanitize_text_field($_FILES['file']['type']),
			'post_ids' => array(),
			'complete' => false,
			'using_template' => intval(sanitize_text_field($_POST['import_template']))
		];
		if ( $template ) $transient = $this->saveTemplateData($transient);
		set_transient('wpsl_import_file', $transient, 1 * YEAR_IN_SECONDS);
	}

	/**
	* Set the Post Type
	*/
	private function setPostType()
	{
		return ( isset($_POST['import_post_type']) ) ? sanitize_text_field($_POST['import_post_type']) : 'location';
	}

	/**
	* Get total row count
	*/
	private function rowCount($file, $mac)
	{
		if ($mac && !ini_get("auto_detect_line_endings")) {
			ini_set("auto_detect_line_endings", '1');
		}
		$csv = Reader::createFromPath($file);
		$count = $csv->each(function(){
    		return true;
		}); 
		return $count;
	}

	/**
	* Check if the uploaded file is a CSV
	*/
	private function isCsv($type)
	{
		$csv_mimetypes = [
			'text/csv',
			'text/plain',
			'application/csv',
			'text/comma-separated-values',
			'application/excel',
			'application/vnd.ms-excel',
			'application/vnd.msexcel',
			'text/anytext',
			'application/octet-stream',
			'application/txt',
		];
		if (in_array($type, $csv_mimetypes)) {
			return true;
		}
		return false;
	}

	/**
	* Get the import template
	*/
	private function getImportTemplate()
	{
		$this->template = $this->import_repo->getImportTemplates(sanitize_text_field($_POST['import_template']));
	}

	/**
	* Save the template data to the transient for the current import
	*/
	private function saveTemplateData($data)
	{
		$data['columns'] = $this->template->import_columns;
		$data['import_status'] = $this->template->import_status;
		$data['skip_first'] = $this->template->import_skip_first;
		$data['skip_geocode'] = $this->template->import_skip_geocode;
		$data['duplicate_handling'] = $this->template->import_duplicate_handling;
		$data['taxonomy_separator'] = $this->template->import_taxonomy_separator;
		return $data;
	}

}