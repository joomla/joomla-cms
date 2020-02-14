/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

jQuery(function($) {

	$(document).ready(function(){

		$(this).find('select').each(function(){
			$(this).chosen('destroy');
		});

	});//end ready

	/* ----------   Load existing template   ------------- */
	$('.form-horizontal').on('click', '.layout-del-action', function(event) {
		event.preventDefault();

		var $that = $(this),
		layoutName = $(".layoutlist select").val(),
		data = {
			action : $that.data('action'),
			layoutName : layoutName
		};

		if ( confirm("Click Ok button to delete "+layoutName+", Cancel to leave.") != true ){
			return false;
		}

		if ( data.action != 'remove' ){
			alert('You are doing somethings wrong.');
		}

		var request = {
			'option' : 'com_ajax',
			'plugin' : 'helix3',
			'data'   : data,
			'format' : 'json'
		};

		$.ajax({
			type   : 'POST',
			data   : request,
			beforeSend: function(){
				$('.layout-del-action .fa-spin').show();
			},
			success: function (response) {
				var data = $.parseJSON(response.data),
				layouts = data.layout,
				tplHtml = '';

				$('#jform_params_layoutlist').find('option').remove();
				if (layouts.length) {
					for (var i = 0; i < layouts.length; i++) {
						tplHtml += '<option value="'+ layouts[i] +'">'+ layouts[i].replace('.json','')+'</option>';
					}

					$('#jform_params_layoutlist').html(tplHtml);
				}

				$('.layout-del-action .fa-spin').fadeOut('fast');
			},
			error: function(){
				alert('Somethings wrong, Try again');
				$('.layout-del-action .fa-spin').fadeOut('fast');
			}
		});
		return false;
	});

	// Save new copy of layout
	$('.form-horizontal').on('click', '.layout-save-action', function(event) {
		$('#layout-modal').find('.sp-modal-body').empty();
		$('#layout-modal .sp-modal-title').text('Save New Layout');
		$('#layout-modal #save-settings').data('flag', 'save-layout');

		var $clone = $('.save-box').clone(true);
		$('#layout-modal').find('.sp-modal-body').append( $clone );

		$('#layout-modal').spmodal();
	});

	// load layout from file

	$(".layoutlist select").chosen().change(function(){
		var $that = $(this),
		layoutName = $that.val(),
		data = {
			action : 'load',
			layoutName : layoutName
		};

		if ( layoutName == '' || layoutName == ' ' ){
			alert('You are doing somethings wrong.');
		}

		var request = {
			'option' : 'com_ajax',
			'plugin' : 'helix3',
			'data'   : data,
			'format' : 'raw'
		};

		$.ajax({
			type   : 'POST',
			data   : request,
			dataType: "html",
			beforeSend: function(){
			},
			success: function (response) {
				$('#helix-layout-builder').empty();
				$('#helix-layout-builder').append(response).fadeIn('normal');
				jqueryUiLayout();
			}
		});
		return false;
	});

	/*********   Lyout Builder JavaScript   **********/

	jqueryUiLayout();

	function jqueryUiLayout()
	{
		$( "#helix-layout-builder" ).sortable({
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			axis: 'y',
			opacity: 0.8,
			tolerance: 'pointer'

		}).disableSelection();

		$('.layoutbuilder-section').find('.row').rowSortable();
	}

	// setInputValue Callback Function
	$.fn.setInputValue = function(options){
		if (this.attr('type') == 'checkbox') {
			if (options.filed == '1') {
				this.attr('checked','checked');
			}else{
				this.removeAttr('checked');
			}
		}else if(this.hasClass('input-media')){
			if(options.filed){
				$imgParent = this.parent('.media');
				console.log($imgParent);
				$imgParent.find('img.media-preview').each(function() {
					$(this).attr('src',layoutbuilder_base+options.filed);
				});
			}
			this.val( options.filed );
		}else{
			this.val( options.filed );
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
	$(document).on('click', '.row-ops-set', function(event){
		event.preventDefault();

		$('.layoutbuilder-section').removeClass('row-active');
		$parent = $(this).closest('.layoutbuilder-section');
		$parent.addClass('row-active');

		$('#layout-modal').find('.sp-modal-body').empty();
		$('#layout-modal .sp-modal-title').text('Row Settings');
		$('#layout-modal #save-settings').data('flag', 'row-setting');

		var $clone = $('.row-settings').clone(true);
		$clone.find('.sppb-color').each(function(){
			$(this).addClass('minicolors');
		});

		$clone = $('#layout-modal').find('.sp-modal-body').append( $clone );

		$clone.find('.addon-input').each(function(){
			var $that = $(this),
			attrValue = $parent.data( $that.data('attrname'));
			$that.setInputValue({filed: attrValue});
		});

		$clone.initColorPicker();

		$('#layout-modal').randomIds();

		$clone.find('select').chosen({
			allow_single_deselect: true
		});

		$('#layout-modal').spmodal();
	});

	// Open Column settings Modal
	$(document).on('click','.col-ops-set',function(event) {
		event.preventDefault();

		$('.layout-column').removeClass('column-active');
		$parent = $(this).closest('.layout-column');
		$parent.addClass('column-active');

		$('#layout-modal').find('.sp-modal-body').empty();
		$('#layout-modal .sp-modal-title').text('Column Settings');
		$('#layout-modal #save-settings').data('flag', 'col-setting');

		var $clone = $('.column-settings').clone(true);
		$clone.find('.sppb-color').each(function(){
			$(this).addClass('minicolors');
		});

		$clone = $('#layout-modal').find('.sp-modal-body').append( $clone );
		var comFlug = false;
		$clone.find('.addon-input').each(function(){
			var $that = $(this),
			$attrname = $that.data('attrname'),
			attrValue = $parent.data($attrname);

			if ( $attrname == 'column_type' && attrValue == '1' ) {
				comFlug = true;
			}else if($attrname == 'name' && comFlug == true){
				$that.closest('.form-group').slideUp('fast');
			}

			$that.setInputValue({filed: attrValue});
		});

		$clone.initColorPicker();

		$clone.find('select').chosen({
			allow_single_deselect: true
		});

		$('#layout-modal').randomIds();
		$('#layout-modal').spmodal();
	});


	$('.input-column_type').change(function(event) {

		var $parent = $(this).closest('.column-settings'),
		flag = false;

		$('#helix-layout-builder').find('.layout-column').not( ".column-active" ).each(function(index, val) {
			if ($(this).data('column_type') == '1') {
				flag = true;
				return false;
			}
		});

		if (flag) {
			alert('Component Area Taken');
			$(this).prop('checked',false);
			$parent.children('.form-group.name').slideDown('400');
			return false;
		}

		if ($(this).attr("checked")) {
			$parent.children('.form-group.name').slideUp('400');
		}else{
			$parent.children('.form-group.name').slideDown('400');
		}
	});

	// Save Row Column Settings
	$(document).on('click', '#save-settings', function(event) {
		event.preventDefault();

		var flag = $(this).data('flag');

		switch(flag){
			case 'row-setting':
			$('#layout-modal').find('.addon-input').each(function(){
				var $this = $(this),
				$parent = $('.row-active'),
				$attrname = $this.data('attrname');
				$parent.removeData( $attrname );

				if ($attrname == 'name') {
					var nameVal = $this.val();

					if (nameVal  !='' || $this.val() != null) {
						$('.row-active .section-title').text($this.val());
					}else{
						$('.row-active .section-title').text('Section Header');
					}
				}

				$parent.attr('data-' + $attrname, $this.getInputValue());
			});
			break;

			case 'col-setting':
			var component = false;

			$('#layout-modal').find('.addon-input').each(function(){

				var $this = $(this),
				$parent = $('.column-active'),
				$attrname = $this.data('attrname');
				$parent.removeData( $attrname ),
				dataVal = $this.val();

				if ( $attrname == 'column_type' && $(this).attr("checked") ) {
					component = true;
					$('.column-active .col-title').text('Component');
				}else if( $attrname == 'name' && component != true ) {
					if (dataVal == '' || dataVal == undefined) {
						dataVal = 'none';
					}
					$('.column-active .col-title').text(dataVal);
				}

				$parent.attr('data-' + $attrname, $this.getInputValue());
			});
			break;

			case 'save-layout':
			var layoutName = $('#layout-modal .addon-input').val(),
			data = {
				action : 'save',
				layoutName : layoutName,
				content: JSON.stringify(getGeneratedLayout())
			};

			if (layoutName =='' || layoutName ==' ') {
				alert("Without Name Layout Can't be save");
				return false;
			}

			var request = {
				'option' : 'com_ajax',
				'plugin' : 'helix3',
				'data'   : data,
				'format' : 'json'
			};

			$.ajax({
				type   : 'POST',
				data   : request,
				beforeSend: function(){
				},
				success: function (response) {
					var data = $.parseJSON(response.data),
					layouts = data.layout,
					tplHtml = '';

					$('#jform_params_layoutlist').find('option').remove();
					if (layouts.length) {
						for (var i = 0; i < layouts.length; i++) {
							tplHtml += '<option value="'+ layouts[i] +'">'+ layouts[i].replace('.json','')+'</option>';
						}

						$('#jform_params_layoutlist').html(tplHtml);
					}
				},
				error: function(){
					alert('Somethings wrong, Try again');
				}

			});
			break;

			default:
			alert('You are doing somethings wrongs. Try again');
		}
	});

	// Column Layout Arrange
	$(document).on('click', '.column-layout', function(event) {
		event.preventDefault();

		var $that = $(this),
		colType = $that.data('type'), column;

		if ($that.hasClass('active') && colType != 'custom' ) {
			return;
		};



		if (colType == 'custom') {
			column = prompt('Enter your custom layout like 4,2,2,2,2 as total 12 grid','4,2,2,2,2');
		}

		var $parent 		= $that.closest('.column-list'),
		$gparent 		= $that.closest('.layoutbuilder-section'),
		oldLayoutData 	= $parent.find('.active').data('layout'),
		oldLayout       = ['12'],
		layoutData 		= $that.data('layout'),
		newLayout 		= ['12'];

		if (oldLayoutData !=12) {
			oldLayout = oldLayoutData.split(',');
		}

		if(layoutData != 12 ){
			newLayout = layoutData.split(',');
		}

		if ( colType == 'custom' ) {
			var error 	= true;

			if ( column != null ) {
				var colArray = column.split(',');

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

		$gparent.find('.layout-column').each(function(i,val){
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
			if (typeof colAttr[i] == 'object') {
				$.each(colAttr[i],function(index,value){
					dataAttr += ' data-'+index+'="'+value+'"';
				});
			}

			new_item +='<div class="layout-column col-sm-'+ newLayout[i].trim() +'" '+dataAttr+'>';
			if (col[i]) {
				new_item += col[i];
			}else{
				new_item += '<div class="column"> <h6 class="col-title pull-left">None</h6> <a class="col-ops-set pull-right" href="#" ><i class="fa fa-gears"></i></a></div>';
			}
			new_item +='</div>';
		}

		$old_column = $gparent.find('.layout-column');
		$gparent.find('.row.ui-sortable').append( new_item );

		$old_column.remove();
		jqueryUiLayout();
	});

	// add row
	$(document).on('click','.add-row',function(event){
		event.preventDefault();

		var $parent = $(this).closest('.layoutbuilder-section'),
		$rowClone = $('#layoutbuilder-section').clone(true);

		$rowClone.addClass('layoutbuilder-section').removeAttr('id');
		$($rowClone).insertAfter($parent);

		jqueryUiLayout();
	});

	// Remove Row
	$(document).on('click', '.remove-row', function(event){
		event.preventDefault();

		if ( confirm("Click Ok button to delete Row, Cancel to leave.") == true )
		{
			$(this).closest('.layoutbuilder-section').slideUp(500, function(){
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
		$('#helix-layout-builder').find('.layoutbuilder-section').each(function(index){
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
			$row.find('.layout-column').each(function(index) {

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

	//On Submit
	document.adminForm.onsubmit = function(event){

		//Webfonts
		$('.webfont').each(function(){
			var $that = $(this),
			webfont = {
				'fontFamily' : $that.find('.list-font-families').val(),
				'fontWeight' : $that.find('.list-font-weight').val(),
				'fontSubset' : $that.find('.list-font-subset').val(),
				'fontSize'	: $that.find('.webfont-size').val()
			}

			$that.find('.input-webfont').val( JSON.stringify(webfont) )

		});

		//Generate Layout
		$('#jform_params_layout').val( JSON.stringify(getGeneratedLayout()) );
	}

});
