/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

(function ($) {

	//Remove Chosen
	$.fn.rowSortable = function(){
		$(this).sortable({
			placeholder: "ui-state-highlight",
			forcePlaceholderSize: true,
			axis: 'x',
			opacity: 0.8,
			tolerance: 'pointer',

			start: function(event, ui) {
				$( ".layoutbuilder-section .row" ).find('.ui-state-highlight').addClass( $(ui.item).attr('class') );
				$( ".layoutbuilder-section .row" ).find('.ui-state-highlight').css( 'height', $(ui.item).outerHeight() );
			}

		}).disableSelection();
	};

	//Random number
	function random_number() {
		return randomFromInterval(1, 1e6)
	}

	function randomFromInterval(e, t) {
		return Math.floor(Math.random() * (t - e + 1) + e)
	}

	$.fn.randomIds = function()
	{
		//Media
		$(this).find('.media').each(function(){
			var $id = random_number();

			$(this).find('.input-media').attr('id', 'media-' + $id);

			//Preview
			$(this).find('.image-preview').attr('id', 'media-' + $id + '_preview_img');
			$(this).find('.image-preview').find('img').attr('id', 'media-' + $id + '_preview');

			$(this).find('a.modal').attr('href', 'index.php?option=com_media&view=images&tmpl=component&fieldid=' + 'media-' + $id);
			$(this).find('a.remove-media').attr('onClick', "jInsertFieldValue('', 'media-" + $id + "');return false;");

			$(this).find('a.remove-media').on('click', function(){
				$(this).closest('.media').find('.input-media').val('');
			});
		});

		//Re-initialize modal
		SqueezeBox.assign( $(this).find('a.modal') , {
			parse: 'rel'
		});
	}

	//remove ids
	$.fn.cleanRandomIds = function(){

		$(this).find('select').chosen('destroy');

		//Media
		$(this).find('.media').each(function(){
			$(this).find('.input-media').removeAttr('id');
			//Preview
			$(this).find('.image-preview').removeAttr('id');
			$(this).find('.image-preview').find('img').removeAttr('id');
			$(this).find('a.modal').removeAttr('href');
			$(this).find('a.remove-media').removeAttr('onClick');
		});

		return $(this);

	}

})(jQuery);
