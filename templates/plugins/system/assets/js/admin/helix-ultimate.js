/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

jQuery(function($){
    "use strict";

    // Swicther
    $('#helix-ultimate-style-form').find('input[type="checkbox"]').each(function( index ) {
        var $this = $(this);
        $this.closest('.control-group').addClass('control-group-checkbox');
    });

    $('.helix-ultimate-fieldset-header-inner').on('click',function(e){
        e.preventDefault();

        if( $(this).closest('.helix-ultimate-fieldset').hasClass('active') ){
            return;
        }

        $('.helix-ultimate-fieldset').removeClass('active');
        $(this).closest('.helix-ultimate-fieldset').addClass('active');
        $('#helix-ultimate-options').removeClass().addClass('active-helix-ultimate-fieldset');
        $('#helix-ultimate').addClass('helix-ultimate-current-fieldset-' + $(this).data('fieldset'));
        $(this).closest('.helix-ultimate-fieldset').find('.helix-ultimate-group-list').find('.helix-ultimate-group-wrap').first().addClass('active-group');
    });

    $('.helix-ultimate-fieldset-toggle-icon').on('click',function(e){
        e.preventDefault();

        $('.helix-ultimate-fieldset').removeClass('active');
        $('#helix-ultimate, #helix-ultimate-options').removeClass();
    });

    $('.helix-ultimate-group-header-box').on('click',function(e){
        e.preventDefault();

        if( $(this).closest('.helix-ultimate-group-wrap').hasClass('active-group') ){
            $(this).closest('.helix-ultimate-group-wrap').removeClass('active-group');
            return;
        }

        $('.helix-ultimate-group-wrap').removeClass('active-group')
        $(this).closest('.helix-ultimate-group-wrap').addClass('active-group');

    });

    $('.helix-ultimate-header-item').on('click',function(e){
        e.preventDefault();

        var $parent = $(this).closest('.helix-ultimate-header-list');

        $parent.find('.helix-ultimate-header-item').removeClass('active')
        $(this).addClass('active');

        var styleName = $(this).data('style'),
            filedName = $parent.data('name');

        $('#' + filedName).val(styleName);
    });

    // Preset
    $(document).ready(function() {
        if($('#custom_style').attr('checked') == 'checked'){
            $('.helix-ultimate-fieldset-presets').find('.helix-ultimate-group-wrap').show();
        } else {
            $('.helix-ultimate-fieldset-presets').find('.helix-ultimate-group-wrap').hide();
        }
    });

    $(document).on('change', '#custom_style', function(e) {
        e.preventDefault();
        if($(this).attr('checked') == 'checked'){
            $('.helix-ultimate-fieldset-presets').find('.helix-ultimate-group-wrap').slideDown();
        } else {
            $('.helix-ultimate-fieldset-presets').find('.helix-ultimate-group-wrap').slideUp();
        }
    });

    $(document).on('click', '.helix-ultimate-preset', function(e){
        e.preventDefault();
        $('.helix-ultimate-preset').removeClass('active');
        $(this).addClass('active');
        //$('.helix-ultimate-input-preset').val($(this).data('preset'));
    });
    $(".helix-responsive-devices span").click( function(){
        if( $(this).hasClass('active') )
            return;
        const parent = $(this).parents('.helix-ultimate-webfont-size')
        parent.find('input').removeClass('active')
        const inputClass = $(this).data('active_class')
        parent.find(inputClass).addClass('active')
        $(this).parent().find('span.active').removeClass('active')
        $(this).addClass('active')
        const device = $(this).data('device')
        updateIframe(device);
    })

    function updateIframe( device ){
        const iframe = $(".helix-ultimate-preview");
        if( device === 'md' ){
            iframe.css({ width: '' })
        }
        if( device === 'sm' ){
            iframe.css({ width: '991px'})
        }
        if( device === 'xs' ){
            iframe.css({ width: '480px'})
        }
    }
    // Save settings
    $('.action-save-template').on('click',function(e){
        e.preventDefault();
        var self = this;

        $('#layout').val( JSON.stringify(getGeneratedLayout()) );
        webfontData();

        $('.helix-ultimate-input-preset').val(JSON.stringify($('.helix-ultimate-preset.active').data()));

        var tmplID = $(this).data('id'),
            tmplView = $(this).data('view'),
            data = $('#helix-ultimate-style-form').serialize();
        
        $.ajax({
            type   : 'POST',
            url    : 'index.php?option=com_ajax&request=task&helix=ultimate&id='+ helixUltimateStyleId +'&action=save-tmpl-style&format=json',
            data   : data,
            beforeSend: function(){
              $(self).find('.fa').removeClass('fa-save').addClass('fa-spinner fa-spin');
              var iframe = $('#helix-ultimate-template-preview').contents();
              iframe.find('body').addClass('helix-ultimate-preloader');
            },
            success: function (response) {
                
                var data = $.parseJSON(response)

                if(data.status){
                    document.getElementById('helix-ultimate-template-preview').contentWindow.location.reload(true);
                }

                $(self).find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-save');
            },
            error: function(){
                alert('Somethings wrong, Try again');
            }

        });
    });
    $('.btn-purge-helix-ultimate-css').on('click',function(e){
        e.preventDefault();
        var self = $(this);
        if( self.hasClass('disable') ){
            return;
        }
        self.addClass('disable')
        $.ajax({
            type   : 'POST',
            url    : 'index.php?option=com_ajax&request=task&helix=ultimate&id='+ helixUltimateStyleId +'&action=purge-css-file&format=json',
            data   : {},
            beforeSend: function(){
                self.append('<span class="fa fa-spinner fa-spin"></span>')
            },
            success: function (response) {
                
                var data = $.parseJSON(response)
                if( data.status ){
                    self.find('span').remove();
                    self.removeClass('disable')
                }

            },
            error: function(){
                alert('Somethings wrong, Try again');
            }

        });
    });
    // Import
    $('#btn-helix-ultimate-import-settings').on( 'click', function( event ) {
        event.preventDefault();

        var $that = $( this ),
            temp_settings = $.trim( $('#input-helix-ultimate-settings').val() );

        if ( temp_settings == '' ) {
          return false;
        }

        if ( confirm( "Warning: It will change all current settings of this Template." ) != true ){
            return false;
        }

        var data = {
            settings : temp_settings
          };

        var request = {
                'action' : 'import-tmpl-style',
                'option' : 'com_ajax',
                'helix' : 'ultimate',
                'request': 'task',
                'data'   : data,
                'format' : 'json'
            };

        $.ajax({
            type   : 'POST',
            data   : request,
            success: function (response) {
                var data = $.parseJSON(response);
                if ( data.status ){
                    window.location.reload();
                }
            },
            error: function(){
                alert('Somethings wrong, Try again');
            }
        });
        return false;
  });

    function webfontData(){
        $('.helix-ultimate-field-webfont').each(function(){
			var $that = $(this),
            
            webfont = {
				'fontFamily' : $that.find('.helix-ultimate-webfont-list').val(),
                'fontSize'	: $that.find('.helix-ultimate-webfont-size-input').val(),
                'fontSize_sm'	: $that.find('.helix-ultimate-webfont-size-input-sm').val(),
				'fontSize_xs'	: $that.find('.helix-ultimate-webfont-size-input-xs').val(),
				'fontWeight' : $that.find('.helix-ultimate-webfont-weight-list').val(),
				'fontStyle' : $that.find('.helix-ultimate-webfont-style-list').val(),
				'fontSubset' : $that.find('.helix-ultimate-webfont-subset-list').val(),
			}

			$that.find('.helix-ultimate-webfont-input').val( JSON.stringify(webfont) )

		});
    }

    function getGeneratedLayout(){
		var item = [];
		$('#helix-ultimate-layout-builder').find('.helix-ultimate-layout-section').each(function(index){
			var $row 		= $(this),
				rowIndex 	= index,
				rowObj 		= $row.data();
			delete rowObj.sortableItem;

			var activeLayout 	= $row.find('.helix-ultimate-column-layout.active'),
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
					colObj 		= $column.data();
				delete colObj.sortableItem;

				item[rowIndex].attr[colIndex] = {
					'type' 				: 'sp_col',
					'settings' 			: colObj
				};

			});
		});

		return item;
    }
    
    /*Option Group*/
    $(document).on('click', '.helix-ultimate-option-group-title', function(event) {
        event.preventDefault();
        $(this).closest('.helix-ultimate-option-group').toggleClass('active').siblings().removeClass('active');
    })
});
