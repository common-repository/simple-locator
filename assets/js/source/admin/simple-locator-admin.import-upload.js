/**
* Import Functionality - Upload/Page One
* @package simple-locator
*/
var SimpleLocatorAdmin = SimpleLocatorAdmin || {};
SimpleLocatorAdmin.ImportUpload = function()
{
	var self = this;
	var $ = jQuery;

	self.selectors = {
		startNewBtn : 'data-simple-locator-import-start-new',
		uploadForm : 'data-simple-locator-import-upload-form',
		previousImportMessage : 'data-simple-locator-import-previous-message',
		postTypeInput : 'data-simple-locator-import-post-type-input',
		toggleHiddenPostTypeCheckbox : 'data-simple-locator-show-non-public-types',
		nonPublicPostType : 'data-non-public-post-type',
		toggleImportDetails : 'data-import-toggle-details',
		undoImportButton : 'data-simple-locator-import-undo-button',
		redoImportButton : 'data-simple-locator-import-redo-button',
		removeImportButton : 'data-simple-locator-import-remove-button',
		toggleTemplateDetails : 'data-import-template-toggle-details',
		removeTemplateButton : 'data-simple-locator-remove-import-template',
		typeRadio : 'data-simple-locator-import-type-radio'
	}

	self.bindEvents = function()
	{
		$(document).ready(function(){
			self.togglePublicPostTypes();
		});	
		$(document).on('change', '[' + self.selectors.toggleHiddenPostTypeCheckbox + ']', function(){
			self.togglePublicPostTypes();
		});
		$(document).on('click', '[' + self.selectors.startNewBtn + ']', function(e){
			e.preventDefault();
			self.startNewImport();
		});
		$(document).on('click', '[' + self.selectors.toggleImportDetails + ']', function(e){
			e.preventDefault();
			self.toggleImportDetails($(this));
		});
		$(document).on('click', '[' + self.selectors.undoImportButton + ']', function(e){
			e.preventDefault();
			var id = $(this).attr(self.selectors.undoImportButton);
			self.undoImport(id);
		});
		$(document).on('click', '[' + self.selectors.redoImportButton + ']', function(e){
			e.preventDefault();
			var id = $(this).attr(self.selectors.redoImportButton);
			self.redoImport(id);
		});
		$(document).on('click', '[' + self.selectors.removeImportButton + ']', function(e){
			e.preventDefault();
			var id = $(this).attr(self.selectors.removeImportButton);
			self.removeImport(id);
		});
		$(document).on('click', '[' + self.selectors.toggleTemplateDetails + ']', function(e){
			e.preventDefault();
			self.toggleTemplateDetails($(this));
		});
		$(document).on('click', '[' + self.selectors.removeTemplateButton + ']', function(e){
			e.preventDefault();
			var id = $(this).attr(self.selectors.removeTemplateButton);
			self.removeTemplate(id);
		});
		$(document).on('change', '[' + self.selectors.typeRadio + ']', function(){
			self.toggleImportType();
		});
	}

	/**
	* Cancel a previous import and start new
	*/
	self.startNewImport = function()
	{
		$('[' + self.selectors.uploadForm + ']').show();
		$('[' + self.selectors.previousImportMessage + ']').hide();
	}

	/**
	* Toggle non-public post types in the post type field
	*/
	self.togglePublicPostTypes = function()
	{
		var nonPublic = $('[' + self.selectors.nonPublicPostType + ']');
		var checked = ( $('[' + self.selectors.toggleHiddenPostTypeCheckbox + ']').is(':checked') );
		if ( checked ){
			$(nonPublic).show();
			return;
		}
		$(nonPublic).hide();
	}

	/**
	* Toggle previous import details
	*/
	self.toggleImportDetails = function(button)
	{
		$(button).parents('.import').find('.import-body').toggle();
	}

	/**
	* Toggle Template Details
	*/
	self.toggleTemplateDetails = function(button)
	{
		$(button).parents('.import-template ').find('.details').toggle();
	}

	/**
	* Toggle the import type
	* (new vs template)
	*/
	self.toggleImportType = function()
	{
		var selectedType = $('[' + self.selectors.typeRadio + ']:checked').val();
		var fields = $('[data-import-type]');
		console.log(selectedType);
		$(fields).hide();
		$('[data-import-type="' + selectedType + '"]').show();
	}

	/**
	* Undo a previous import
	*/
	self.undoImport = function(id)
	{
		if ( !confirm(wpsl_locator.confirm_undo) ) return;
		$('#undo_import_id').val(id);
		$('[data-undo-import-form]').submit();
	}

	/**
	* Redo a previous import
	*/
	self.redoImport = function(id)
	{
		if ( !confirm(wpsl_locator.confirm_redo) ) return;
		$('#redo_import_id').val(id);
		$('[data-redo-import-form]').submit();
	}

	/**
	* Remove a previous import's record
	*/
	self.removeImport = function(id)
	{
		if ( !confirm(wpsl_locator.confirm_remove) ) return;
		$('#remove_import_id').val(id);
		$('[data-remove-import-form]').submit();
	}

	/**
	* Remove a previous import's record
	*/
	self.removeTemplate = function(id)
	{
		if ( !confirm(wpsl_locator.confirm_remove_template) ) return;
		$('#template_remove_id').val(id);
		$('[data-remove-import-template]').submit();
	}

	return self.bindEvents();
}