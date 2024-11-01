/**
* The Primary Form Object
* @package simple-locator
*/
var SimpleLocator = SimpleLocator || {};
SimpleLocator.Form = function()
{
	var self = this;
	var $ = jQuery;

	self.activeForm;
	self.activeFormContainer;
	self.isWidget = false;
	self.mapContainer;
	self.resultsContainer;
	self.formData;
	self.isAjax = false;
	self.page = 1;
	self.formIndex;

	self.bindEvents = function()
	{
		$(document).on('click', '[' + SimpleLocator.selectors.submitButton + ']', function(e){
			e.preventDefault();
			self.activeForm = $(this).parents('[' + SimpleLocator.selectors.form + ']');
			self.activeFormContainer = $(this).parents('[' + SimpleLocator.selectors.formContainer + ']');
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputGeocode + ']').val('');
			self.setAjax()
			active_form = self.activeForm; // Deprecated
			wpsl_before_submit(self.activeForm); // Deprecated
			$(document).trigger('simple-locator-before-submit', [self.activeForm]);
			self.processForm();
		});
		// Programmatic submit form
		$(document).on('simple-locator-submit-form', function(e, form){
			self.activeForm = form;
			self.activeFormContainer = $(form).parents('[' + SimpleLocator.selectors.formContainer + ']');
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputGeocode + ']').val('');
			self.setAjax()
			active_form = self.activeForm; // Deprecated
			wpsl_before_submit(self.activeForm); // Deprecated
			$(document).trigger('simple-locator-before-submit', [self.activeForm]);
			self.processForm();
		});
		// Runs on geolocation success, whether by clicking button or auto-load
		$(document).on('simple-locator-geolocation-success', function(e, form, results){
			self.activeForm = $(form);
			self.activeFormContainer = $(form).parents('[' + SimpleLocator.selectors.formContainer + ']');
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputLatitude + ']').val(results.latitude);
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputLongitude + ']').val(results.longitude);
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputGeocode + ']').val('1');
			self.setAjax();
			wpsl_before_submit(self.activeForm); // Deprecated
			$(self.activeFormContainer).removeClass('has-error');
			$(document).trigger('simple-locator-before-submit', [self.activeForm]);
			self.setResultsContainers();
			self.setFormData();
			self.submitForm();
		});
		// Runs after a form has been manually submitted and geocoded
		$(document).on('simple-locator-address-geocoded', function(e, results, form){
			self.toggleLoading(true, true);
			if ( typeof self.activeForm === 'undefined' ) {
				self.activeForm = form;
				self.activeFormContainer = $(form).parents('[' + SimpleLocator.selectors.formContainer + ']');
				self.setAjax();
			}
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputGeocode + ']').val('');
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputLatitude + ']').val(results.latitude);
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputLongitude + ']').val(results.longitude);
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputFormattedLocation + ']').val(results.formatted_address);
			self.setFormData();
			self.submitForm();
		});
		$(document).on('click', '[' + SimpleLocator.selectors.paginationButton + ']', function(e){
			if ( !self.activeForm ) return;
			if ( $(self.activeFormContainer).hasClass('has-geolocation') && !self.isAjax ) return;
			e.preventDefault();
			$(self.activeFormContainer).addClass('loading');
			self.paginate($(this));
		});
		$(document).on('simple-locator-autocomplete-changed', function(e, place, form){
			self.activeForm = $(form);
			self.activeFormContainer = $(form).parents('[' + SimpleLocator.selectors.formContainer + ']');
			self.setAjax();
			self.toggleLoading(true, true);
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputGeocode + ']').val('');
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputLatitude + ']').val(place.geometry.location.lat());
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputLongitude + ']').val(place.geometry.location.lng());
			$(self.activeForm).find('[' + SimpleLocator.selectors.inputFormattedLocation + ']').val(place.formatted_address);
			if ( self.page > 1 ) self.page = 1;
			self.setFormData();
			self.submitForm();
		});
		$(document).on('submit', '[' + SimpleLocator.selectors.pageJumpForm + ']', function(e){
			var container = $(this).parents('[' + SimpleLocator.selectors.resultsWrapper + ']');
			if ( $(container).hasClass('non-ajax') ) return;
			e.preventDefault();
			self.jumpToPage($(this));
		});
	}

	/**
	* Set whether the active form is ajax or not
	*/
	self.setAjax = function()
	{
		var ajax = $(self.activeForm).attr(SimpleLocator.selectors.ajaxForm);
		self.isAjax = ( typeof ajax === 'undefined' || ajax !== 'true' ) ? false : true;
	}

	/**
	* Process the form submission
	*/
	self.processForm = function(geocode)
	{
		$(self.activeFormContainer).removeClass('has-error');
		self.toggleLoading(true, true);
		self.setResultsContainers();
		var geocoder = new SimpleLocator.Geocoder();
		geocoder.getCoordinates(self.activeForm);
	}

	/**
	* Set the appropriate containers for results
	*/
	self.setResultsContainers = function()
	{
		if ( $(self.activeForm).siblings('#widget').length > 0 ) self.isWidget = true;	
		if ( typeof wpsl_locator_options === 'undefined' || wpsl_locator_options === '' ) wpsl_locator_options = '';
		self.mapContainer = ( wpsl_locator_options.mapcont === '' || self.isWidget )
			? $(self.activeFormContainer).find('[' + SimpleLocator.selectors.map + ']')
			: $(wpsl_locator_options.mapcont);
		
		self.resultsContainer = ( wpsl_locator_options.resultscontainer === '' || self.isWidget )
			? (self.activeFormContainer).find('[' + SimpleLocator.selectors.results + ']')
			: $(wpsl_locator_options.resultscontainer);
		return;
	}

	/**
	* Set the form data for processing
	*/
	self.setFormData = function()
	{
		var allow_empty_address = $(self.activeForm).attr('data-simple-locator-form-allow-empty');
		allow_empty_address = ( typeof allow_empty_address === 'undefined' || allow_empty_address === '' ) ? false : true;

		var address = $(self.activeForm).find('[' + SimpleLocator.selectors.inputAddress + ']');
		address = ( typeof address === 'undefined' ) ? false : $(address).val();

		var distance = $(self.activeForm).find('[' + SimpleLocator.selectors.inputDistance + ']');
		distance = ( typeof distance === 'undefined' ) ? false : $(distance).val();

		var geolocation = $(self.activeForm).find('[' + SimpleLocator.selectors.inputGeocode + ']').val();
		geolocation = ( geolocation === '' || geolocation === 'false' ) ? false : true;	

		var limit = $(self.activeForm).find('[' + SimpleLocator.selectors.inputLimit + ']').val();
		limit = ( limit === '' ) ? null : limit;

		self.formData = {
			address : address,
			formatted_address : $(self.activeForm).find('[' + SimpleLocator.selectors.inputFormattedLocation + ']').val(),
			distance : distance,
			latitude : $(self.activeForm).find('[' + SimpleLocator.selectors.inputLatitude + ']').val(),
			longitude :  $(self.activeForm).find('[' + SimpleLocator.selectors.inputLongitude + ']').val(),
			unit : $(self.activeForm).find('[' + SimpleLocator.selectors.inputUnit + ']').val(),
			geolocation : geolocation,
			allow_empty_address : allow_empty_address,
			ajax : self.isAjax,
			per_page : limit,
			page : self.page
		}

		self.setTaxonomies();

		// Custom Input Data (for SQL filter availability)
		if ( wpsl_locator.postfields.length == 0 ) return
		for ( var i = 0; i < wpsl_locator.postfields.length; i++ ){
			var field = wpsl_locator.postfields[i];
			self.formData[field] = $('*[name=' + field + ']').val();
		}
	}

	/**
	* Set taxonomies in the form data if applicable
	*/
	self.setTaxonomies = function()
	{
		var taxonomyCheckboxes = $(self.activeForm).find('[data-simple-locator-taxonomy-checkbox]:checked');
		var taxonomySelect = $(self.activeForm).find('[data-simple-locator-taxonomy-select]');
		var taxonomies = {};
	
		// Select Menus
		$.each(taxonomySelect, function(i, v){
			if ( $(this).val() === "" ) return;
			taxonomies[$(this).attr('data-simple-locator-taxonomy-select')] = [$(this).val()];
		});

		// Checkboxes
		$.each(taxonomyCheckboxes, function(i, v){
			var tax_name = $(this).attr('data-simple-locator-taxonomy-checkbox');
			if ( !(taxonomies[tax_name] instanceof Array) ) taxonomies[tax_name] = [];
			taxonomies[tax_name].push(parseInt($(this).val()));
		});

		self.formData.taxfilter = taxonomies;
	}

	/**
	* Set Autoload
	* Adds necessary form data for non-ajax forms on auto-located user-centered forms
	*/
	self.setAutoload = function()
	{
		var autoload = $(self.activeFormContainer).hasClass('has-geolocation');
		self.formData.autoload = autoload;
		self.formData.formmethod = $(self.activeForm).attr('method');
		self.formData.resultspage = $(self.activeForm).find('input[name="results_page"]').val();
		self.formData.mapheight = $(self.activeForm).find('input[name="mapheight"]').val();
		self.formData.search_page = $(self.activeForm).find('input[name="search_page"]').val();
	}

	/**
	* Set the form index
	*/
	self.setFormIndex = function()
	{
		var forms = $('[' + SimpleLocator.selectors.form + ']');
		self.formIndex = $(self.activeForm).index(forms);
	}

	/**
	* Submit the form
	*/
	self.submitForm = function()
	{
		self.setFormIndex();
		self.setAutoload();
		if ( !self.formData.ajax && !self.formData.autoload ){
			$(self.activeForm).submit();
			return;
		}
		self.formData.page = self.page;
		$.ajax({
			url : SimpleLocator.endpoints.search,
			type: 'GET',
			datatype: 'jsonp',
			data: self.formData,
			beforeSend : function(i, v){
				if ( wpsl_locator.jsdebug === '1' ){
					console.log('Form Data');
					console.log(self.formData);
					console.log('URL');
					console.log(v.url);
				}
			},
			success: function(data){
				SimpleLocator.formData[self.formIndex] = data;
				SimpleLocator.formData[self.formIndex].allLocations = false;
				if ( wpsl_locator.jsdebug === '1' ){
					console.log('Form Response');
					console.log(data);
				}
				if (data.status === 'error'){
					$(document).trigger('simple-locator-error', [self.activeForm, data.message]);
					self.toggleLoading(false, true);
					return;
				}
				if ( data.result_count === 0 ){
					var message = wpsl_locator.nolocationserror + ' ' + data.formatted_address;
					$(document).trigger('simple-locator-error', [self.activeForm, message, self.formData]);
					wpsl_no_results(self.formData.formatted_address, self.activeForm); // Deprecated
					self.toggleLoading(false, true);
					return;
				}
				$(document).trigger('simple-locator-form-success', [data, self.activeForm]);
				wpsl_success(data.result_count, data.results, self.activeForm); // Deprecated
			},
			error: function(data){
				if ( wpsl_locator.jsdebug === '1' ){
					console.log('Form Response Error');
					console.log(data.responseText);
				}
			}
		});
	}

	/**
	* Pagination Action
	*/
	self.paginate = function(button)
	{
		var container = $(button).parents('[' + SimpleLocator.selectors.formContainer + ']');
		var formIndex = $(container).index('[' + SimpleLocator.selectors.formContainer + ']');
		if ( SimpleLocator.formData[formIndex].allLocations ) return;
		var direction = $(button).attr(SimpleLocator.selectors.paginationButton);
		if ( direction === 'next' ){
			self.page = self.page + 1;
			self.submitForm();
			return;
		}
		self.page = self.page - 1;
		self.submitForm();
	}

	/**
	* Jump to a page
	*/
	self.jumpToPage = function(pageForm)
	{
		var page = parseInt($(pageForm).find('input[type="tel"]').val());
		if ( isNaN(page) ) return;

		self.activeFormContainer = $(pageForm).parents('[' + SimpleLocator.selectors.formContainer + ']');
		self.activeForm = $(self.activeFormContainer).find('[' + SimpleLocator.selectors.form + ']');
		self.formIndex = $(self.activeFormContainer).index('[' + SimpleLocator.selectors.formContainer + ']');

		var totalPages = SimpleLocator.formData[self.formIndex].total_pages;
		if ( typeof totalPages !== 'undefined' && page > totalPages ) return;

		if ( typeof SimpleLocator.formData[self.formIndex] === 'undefined' || SimpleLocator.formData[self.formIndex] === '' ) return;
		if ( SimpleLocator.formData[self.formIndex].allLocations ) return;

		$(self.activeFormContainer).addClass('loading');
		
		self.page = page;
		self.submitForm();
	}

	/**
	* Toggle Loading
	*/
	self.toggleLoading = function(loading, clearvalues)
	{
		$(document).trigger('simple-locator-form-loading', [loading]);
		var results = $(self.activeFormContainer).find('[' + SimpleLocator.selectors.results + ']');
		if ( loading ){
			if ( clearvalues ){
				$('[' + SimpleLocator.selectors.inputLatitude + ']').val('');
				$('[' + SimpleLocator.selectors.inputLongitude + ']').val('');
				$('[' + SimpleLocator.selectors.inputGeocode + ']').val('');
				$('[' + SimpleLocator.selectors.inputFormattedLocation + ']').val('');
			}
			$(self.activeFormContainer).addClass('loading');
			$(self.activeFormContainer).find('[' + SimpleLocator.selectors.formError + ']').hide();
			$(results).empty();
			return;
		}
		$(self.activeFormContainer).removeClass('loading');
	}

	return self.bindEvents();
}