/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 *
 */
(function($) {

	$(document).ready(function (){
		$('.jmoddiv').on({
			click: function(){
				$(this).toggleClass("jmodactive");
			},
			mouseenter: function(){
				var moduleEditUrl = $(this).data('jmodediturl');
				var moduleTip = $(this).data('jmodtip');
				$(this).addClass("jmodinside").prepend('<a class="btn jmodedit" href="#" target="_blank">'
				+ '<i class="icon-edit"></i></a>').children(":first").attr('href', moduleEditUrl).attr('title', moduleTip).tooltip({"container": false});
			},
			mouseleave: function(){
				$(this).removeClass("jmodinside").find('.btn.jmodedit').remove();
			}});
	});

})(jQuery);
