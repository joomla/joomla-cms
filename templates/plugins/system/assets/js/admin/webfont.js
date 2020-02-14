/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

jQuery(function($) {

	//Web Fonts
	$(document).on('change', '.helix-ultimate-webfont-list', function(event) {
		event.preventDefault();

		var $that = $(this),
			fontName = $that.val();

		var systemFonts = [
			'Arial',
			'Tahoma',
			'Verdana',
			'Helvetica',
			'Times New Roman',
			'Trebuchet MS',
			'Georgia'
		];

		if($.inArray(fontName, systemFonts) !== -1) {
			$that.closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-subset-list').html('').trigger("liszt:updated");
		} else {
			var data = {
				fontName : fontName
			};

			var request = {
				'action' : 'fontVariants',
				'option' : 'com_ajax',
				'helix'  : 'ultimate',
				'request': 'task',
				'data'   : data,
				'format' : 'json'
			};

			$.ajax({
				type   : 'POST',
				data   : request,
				success: function (response) {
					var font = $.parseJSON(response);
					$that.closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-subset-list').html(font.subsets).trigger("liszt:updated");
				}
			});

			var font = $that.val().replace(" ", "+");
			$('head').append("<link href='//fonts.googleapis.com/css?family="+ font +":100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic' rel='stylesheet' type='text/css'>");
		}
		
        $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-preview').fadeIn().css('font-family', $(this).val());

        return false;
	});
	
	// Font Size
	$(document).on('change', '.helix-ultimate-webfont-size-input', function(event) {
		event.preventDefault();

        $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-preview').fadeIn().css({
        	'font-family': $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-list').val(),
        	'font-size': $(this).val() + 'px'
		});
	});

	// Font Weight
	$(document).on('change', '.helix-ultimate-webfont-weight-list', function(event) {
		event.preventDefault();

        $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-preview').fadeIn().css({
        	'font-family': $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-list').val(),
        	'font-size': $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-size-input').val() + 'px',
        	'font-weight': $(this).val()
		});
	});
	
	// Font Style
	$(document).on('change', '.helix-ultimate-webfont-style-list', function(event) {
		event.preventDefault();

        $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-preview').fadeIn().css({
			'font-family': $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-list').val(),
			'font-size': $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-size-input').val() + 'px',
        	'font-weight': $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-weight-list').val(),
        	'font-style': $(this).val()
		});
    });

	//Font Subset
	$('.list-font-subset').on('change', function(event) {
		event.preventDefault();

		var font = $(this).closest('.helix-ultimate-field-webfont').find('.helix-ultimate-webfont-list').val().replace(" ", "+");
		$('head').append("<link href='//fonts.googleapis.com/css?family="+ font +":100,100italic,200,200italic,300,300italic,400,400italic,500,500italic,600,600italic,700,700italic,800,800italic,900,900italic&subset="+ $(this).val() +"' rel='stylesheet' type='text/css'>");
    });

    //Update Fonts list
    $('.btn-update-helix-ultimate-fonts').on('click', function(event){
        event.preventDefault();

        var $that   = $(this);
        var request = {
			'action' : 'update-font-list',
            'option' : 'com_ajax',
			'helix'  : 'ultimate',
			'request': 'task',
            'data'   : {},
            'format' : 'json'
        };

        $.ajax({
            type   : 'POST',
            data   : request,
            beforeSend: function(){
                $that.prepend('<i class="fa fa-spinner fa-spin"></i> ');
            },
            success: function (response) {
				var data = $.parseJSON(response);
				if (data.status){
					$that.after(data.message);
					$that.find('.fa-spinner').remove();
					$that.next().delay(1000).fadeOut(300, function(){
						$(this).remove();
					});
				} else {
					$that.after("<p class='font-update-failed'>Unexpected error occurs. Please make sure that, you have inserted Google Font API key.</p>");
					$that.find('.fa-spinner').remove();
				}
            }
        });

        return false;

    });

});