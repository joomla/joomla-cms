/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

jQuery(function($) {

		// Sortable
		$.fn.rowSortable = function(){
			$(this).sortable({
				placeholder: "ui-state-highlight",
				forcePlaceholderSize: true,
				axis: 'x',
				opacity: 0.8,
				tolerance: 'pointer',
	
				start: function(event, ui) {
					$( ".helix-ultimate-layout-section .row" ).find('.ui-state-highlight').addClass( $(ui.item).attr('class') );
					$( ".helix-ultimate-layout-section .row" ).find('.ui-state-highlight').css( 'height', $(ui.item).outerHeight() );
				}
	
			}).disableSelection();
		};
	
		jqueryUiLayout();
	
		function jqueryUiLayout()
		{
			$( "#helix-ultimate-layout-builder" ).sortable({
				placeholder: "ui-state-highlight",
				forcePlaceholderSize: true,
				axis: 'y',
				opacity: 0.8,
				tolerance: 'pointer'
	
			}).disableSelection();
	
			$('.helix-ultimate-layout-section').find('.row').rowSortable();
		}
	
		// setInputValue Callback Function
		$.fn.setInputValue = function(options){
			if (this.attr('type') == 'checkbox') {
				if (options.field == '1') {
					this.attr('checked','checked');
				}else{
					this.removeAttr('checked');
				}
			}else if(this.hasClass('input-select')){
				this.val( options.field );
				this.trigger('liszt:updated');
				this.trigger('chosen:updated');
			}else if(this.hasClass('input-media')){
				if(options.field){
					$imgParent = this.parent('.media');
					$imgParent.find('img.media-preview').each(function() {
						$(this).attr('src',layoutbuilder_base+options.field);
					});
				}
				this.val( options.field );
			}else{
				this.val( options.field );
			}
	
			if (this.data('attrname') == 'column_type'){
				if (this.val() == 'component') {
					$('.form-group.name').hide();
				}
			}
		}
	
		// callback function, return checkbox value
		$.fn.getInputValue = function(){
			if (this.attr('type') == 'checkbox') {
				if (this.attr("checked")) {
					return '1';
				}else{
					return '0';
				}
			}else{
				return this.val();
			}
		}
	
		// color picker initialize
		$.fn.initColorPicker = function(){
			this.find('.minicolors').each(function() {
				$(this).minicolors({
					control: 'hue',
					position: 'bottom',
					theme: 'bootstrap'
				});
			});
		}
	
		// Open Row settings Modal
		$(document).on('click', '.helix-ultimate-row-options', function(event){
			event.preventDefault();
			$(this).helixUltimateOptionsModal({
				flag: 'row-setting',
				title: "<span class='fa fa-cogs'></span> Row Options",
				class: 'helix-ultimate-modal-small'
			});
	
			$('.helix-ultimate-layout-section').removeClass('row-active');
			$parent = $(this).closest('.helix-ultimate-layout-section');
			$parent.addClass('row-active');

			$('#helix-ultimate-row-settings').find('select.helix-ultimate-input').each(function(){
				$(this).chosen('destroy');
			});
	
			var $clone = $('#helix-ultimate-row-settings').clone(true);
			$clone.find('.helix-ultimate-input-color').each(function(){
				$(this).addClass('minicolors');
			});

			$clone.find('select.helix-ultimate-input').each(function(){
				$(this).chosen({width: '100%'});
			});
	
			$clone = $('.helix-ultimate-options-modal-inner').html($clone.removeAttr('id').addClass('helix-ultimate-options-modal-content'));
	
			$clone.find('.helix-ultimate-input').each(function(){
				var $that = $(this),
				attrValue = $parent.data( $that.data('attrname') );
				$that.setInputValue({field: attrValue});
				if($that.hasClass('helix-ultimate-input-media')) {
					if(attrValue) {
						$that.prev('.helix-ultimate-image-holder').html( '<img src="'+ $that.data('baseurl') +  attrValue +'" alt="">' );
					}
				}
			});
	
			$clone.initColorPicker();
	
		});
	
		// Open Column settings Modal
		$(document).on('click', '.helix-ultimate-column-options',function(event) {
			event.preventDefault();
			$(this).helixUltimateOptionsModal({
				flag: 'column-setting',
				title: "<span class='fa fa-cog'></span> Column Options",
				class: 'helix-ultimate-modal-small'
			});
	
			$('.helix-ultimate-layout-column').removeClass('column-active');
			$parent = $(this).closest('.helix-ultimate-layout-column');
			$parent.addClass('column-active');

			$('#helix-ultimate-column-settings').find('select.helix-ultimate-input').each(function(){
				$(this).chosen('destroy');
			});
	
			var $clone = $('#helix-ultimate-column-settings').clone(true);
			$clone.find('.helix-ultimate-input-color').each(function(){
				$(this).addClass('minicolors');
			});
	
			$clone = $('.helix-ultimate-options-modal-inner').html($clone.removeAttr('id').addClass('helix-ultimate-options-modal-content'));
	
			$clone.find('.helix-ultimate-input').each(function(){
				var $that = $(this),
				attrValue = $parent.data( $that.data('attrname'));
				$that.setInputValue({field: attrValue});
			});

			$clone.find('select.helix-ultimate-input').each(function(){
				$(this).chosen({width: '100%'});
			});
	
			$clone.initColorPicker();
		});
	
	
		$('.helix-ultimate-input-column_type').change(function(event) {
	
			var $parent = $(this).closest('.helix-ultimate-modal-content'),
				flag = false;
	
			$('#helix-ultimate-layout-builder').find('.helix-ultimate-layout-column').not( ".column-active" ).each(function(index, val) {
				if ($(this).data('column_type') == '1') {
					flag = true;
					return false;
				}
			});
	
			if (flag) {
				alert('Component Area Taken');
				$(this).prop('checked',false);
				$parent.children('.control-group.name').slideDown('400');
				return false;
			}
	
			if ($(this).attr("checked")) {
				$('.helix-ultimate-layout-column.column-active').find('.helix-ultimate-column').addClass('helix-ultimate-column-component');
				$parent.children('.control-group.name').slideUp('400');
			}else{
				$('#helix-ultimate-layout-builder').find('.helix-ultimate-column-component').removeClass('helix-ultimate-column-component');
				$parent.children('.control-group.name').slideDown('400');
			}
		});
	
		// Save Row Column Settings
		$(document).on('click', '.helix-ultimate-settings-apply', function(event) {
			event.preventDefault();
	
			var flag = $(this).data('flag');
	
			switch(flag){
				case 'row-setting':
					$('.helix-ultimate-options-modal-content').find('.helix-ultimate-input').each(function(){
						var $this = $(this),
							$parent = $('.row-active'),
							$attrname = $this.data('attrname');
						$parent.removeData( $attrname );
		
						if ($attrname == 'name')
						{
							var nameVal = $this.val();
		
							if (nameVal  =='' || nameVal == null) {
								$('.row-active .helix-ultimate-section-title').text('Section Header');
							}else{
								$('.row-active .helix-ultimate-section-title').text($this.val());
							}
						}
		
						$parent.data($attrname, $this.getInputValue());
					});
		
					$('.helix-ultimate-options-modal-overlay, .helix-ultimate-options-modal').remove();
					$('body').removeClass('helix-ultimate-options-modal-open');
					break;
	
				case 'column-setting':
					var component = false;
		
					$('.helix-ultimate-options-modal-content').find('.helix-ultimate-input').each(function(){
		
						var $this = $(this),
							$parent = $('.column-active'),
							$attrname = $this.data('attrname'),
							dataVal = $this.val();

						$parent.removeData( $attrname );
						
		
						if ( $attrname == 'column_type' && $(this).attr("checked") ) {
							component = true;
							$('.column-active .helix-ultimate-column-title').text('Component');
						} else if( $attrname == 'name' && component != true ) {
							if (dataVal == '' || dataVal == undefined) {
								dataVal = 'none';
							}
							$('.column-active .helix-ultimate-column-title').text(dataVal);
						}
		
						$parent.data($attrname, $this.getInputValue());
					});
					$('.helix-ultimate-options-modal-overlay, .helix-ultimate-options-modal').remove();
					$('body').removeClass('helix-ultimate-options-modal-open');
					break;
	
				default:
				alert('You are doing somethings wrongs. Try again');
			}
		});
	
		// Cancel Modal
		$(document).on('click', '.helix-ultimate-settings-cancel, .action-helix-ultimate-options-modal-close', function(event) {
			event.preventDefault();
			$('.helix-ultimate-options-modal-overlay, .helix-ultimate-options-modal').remove();
			$('body').removeClass('helix-ultimate-options-modal-open');
		});
	
		// Column Layout Arrange
		$(document).on('click', '.helix-ultimate-column-layout', function(event) {
			event.preventDefault();
	
			var $that = $(this),
				colType = $that.data('type'), column;
	
			if ($that.hasClass('active') && colType != 'custom' ) {
				return;
			}
	
			if (colType == 'custom') {
				column = prompt('Enter your custom layout like 4+2+2+2+2 as total 12 grid','4+2+2+2+2');
			}
	
			var $parent 		= $that.closest('.helix-ultimate-column-list'),
				$gparent 		= $that.closest('.helix-ultimate-layout-section'),
				oldLayoutData 	= $parent.find('.active').data('layout'),
				oldLayout       = ['12'],
				layoutData 		= $that.data('layout'),
				newLayout 		= ['12'];
	
			if ( oldLayoutData != 12 ) {
				oldLayout = oldLayoutData.split('+');
			}
	
			if(layoutData != 12 ){
				newLayout = layoutData.split('+');
			}
	
			if ( colType == 'custom' ) {
				var error 	= true;
	
				if ( column != null ) {
					var colArray = column.split('+');
	
					var colSum = colArray.reduce(function(a, b) {
						return Number(a) + Number(b);
					});
	
					if ( colSum == 12 ) {
						newLayout = colArray;
						$(this).data('layout', column)
						error = false;
					}
				}
	
				if (error) {
					alert('Error generated. Please correct your column arragnement and try again.');
					return false;
				}
			}
	
			var col = [],
				colAttr = [];
	
			$gparent.find('.helix-ultimate-layout-column').each(function(i,val){
				col[i] = $(this).html();
				var colData = $(this).data();

				if (typeof colData == 'object') {
					colAttr[i] = $(this).data();
				}else{
					colAttr[i] = '';
				}
			});
	
			$parent.find('.active').removeClass('active');
			$that.addClass('active');
	
			var new_item = '';
	
			for(var i=0; i < newLayout.length; i++)
			{
				var dataAttr = '';
				if (typeof colAttr[i] != 'object') {
					colAttr[i] = {
						grid_size : newLayout[i].trim(),
						column_type : 0,
						name : 'none'
					}
				} else {
					colAttr[i].grid_size = newLayout[i].trim();
				}
				$.each(colAttr[i],function(index,value){
					dataAttr += ' data-'+index+'="'+value+'"';
				});
	
				new_item +='<div class="helix-ultimate-layout-column col-md-'+ newLayout[i].trim() +'" '+dataAttr+'>';
				if (col[i]) {
					new_item += col[i];
				}else{
					new_item += '<div class="helix-ultimate-column clearfix">';
					new_item += '<span class="helix-ultimate-column-title">none</span>';
					new_item += '<a class="helix-ultimate-column-options" href="#"><i class="fa fa-gear"></i></a>';
					new_item += '</div>';
				}
				new_item +='</div>';
			}
	
			$old_column = $gparent.find('.helix-ultimate-layout-column');
			$gparent.find('.row.ui-sortable').append( new_item );
	
			$old_column.remove();
			jqueryUiLayout();
		});
	
		// add row
		$(document).on('click', '.helix-ultimate-add-row',function(event){
			event.preventDefault();
	
			var $parent = $(this).closest('.helix-ultimate-layout-section'),
			$rowClone = $('#helix-ultimate-layout-section').clone(true);
	
			$rowClone.addClass('helix-ultimate-layout-section').removeAttr('id');
			$($rowClone).insertAfter($parent);
	
			jqueryUiLayout();
		});
	
		// Remove Row
		$(document).on('click', '.helix-ultimate-remove-row', function(event){
			event.preventDefault();
	
			if ( confirm("Click Ok button to delete Row, Cancel to leave.") == true ) {
				$(this).closest('.helix-ultimate-layout-section').slideUp(500, function(){
					$(this).remove();
				});
			}
		});
	
		// Remove Media
		$(document).on('click','.remove-media',function(){
			var $that = $(this),
			$imgParent = $that.parent('.media');
	
			$imgParent.find('img.media-preview').each(function() {
				$(this).attr('src','');
				$(this).closest('.image-preview').css('display', 'none');
			});
		});
	
		// Generate Layout JSON
		function getGeneratedLayout(){
			var item = [];
			$('#helix-ultimate-layout-builder').find('.helix-ultimate-layout-section').each(function(index){
				var $row 		= $(this),
				rowIndex 	= index,
				rowObj 		= $row.data();
				delete rowObj.sortableItem;
	
				var activeLayout 	= $row.find('.column-layout.active'),
				layoutArray 	= activeLayout.data('layout'),
				layout = 12;
	
				if( layoutArray != 12){
					layout = layoutArray.split(',').join('');
				}
	
				item[rowIndex] = {
					'type'  	: 'row',
					'layout'	: layout,
					'settings' 	: rowObj,
					'attr'		: []
				};
	
				// Find Column Elements
				$row.find('.helix-ultimate-layout-column').each(function(index) {
	
					var $column 	= $(this),
					colIndex 	= index,
					className 	= $column.attr('class'),
					colObj 		= $column.data();
					delete colObj.sortableItem;
	
					item[rowIndex].attr[colIndex] = {
						'type' 				: 'sp_col',
						'className' 		: className,
						'settings' 			: colObj
					};
	
				});
			});
	
			return item;
		}
	});