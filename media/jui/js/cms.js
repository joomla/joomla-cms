/**
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

/**
 * Sets the HTML of the container-collapse element
 */
Joomla.setcollapse = function(url, name, height) {
    if (!document.getElementById('collapse-' + name)) {
        document.getElementById('container-collapse').innerHTML = '<div class="collapse fade" id="collapse-' + name + '"><iframe class="iframe" src="' + url + '" height="'+ height + '" width="100%"></iframe></div>';
    }
}

if (jQuery) {
	jQuery(document).ready(function($) {
		var elements = {},
			linkedoptions = function(element, target, checkType) {
				var v = element.val(), id = element.attr('id');
				if(checkType && !element.is(':checked'))
					return;
				$('[rel=\"showon_'+target+'\"]').each(function(){
					var i = jQuery(this);
					if (i.hasClass('showon_' + v))
						i.slideDown();
					else
						i.slideUp();
				});
			};
		$('[rel^=\"showon_\"]').each(function(){
			var el = $(this), target = el.attr('rel').replace('showon_', ''), targetEl = $('[name=\"' + target+'\"]');
			if (!elements[target]) {
				var targetType = targetEl.attr('type'), checkType = (targetType == 'checkbox' || targetType == 'radio');
				targetEl.bind('change', function(){
					linkedoptions( $(this), target, checkType);
				}).bind('click', function(){
					linkedoptions( $(this), target, checkType );
				}).each(function(){
					linkedoptions( $(this), target, checkType );
				});
				elements[target] = true;
			}
		});
	});
}
