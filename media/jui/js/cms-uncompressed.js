/**
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

!(function (document, Joomla) {
	"use strict";

/**
 * Sets the HTML of the container-collapse element
 */
Joomla.setcollapse = function(url, name, height) {
    if (!document.getElementById('collapse-' + name)) {
        document.getElementById('container-collapse').innerHTML = '<div class="collapse fade" id="collapse-' + name + '"><iframe class="iframe" src="' + url + '" height="'+ height + '" width="100%"></iframe></div>';
    }
};

/**
 * IE8 polyfill for indexOf()
 */
if (!Array.prototype.indexOf)
{
	Array.prototype.indexOf = function(elt)
	{
		var len = this.length >>> 0;

		var from = Number(arguments[1]) || 0;
		from = (from < 0) ? Math.ceil(from) : Math.floor(from);

		if (from < 0)
		{
			from += len;
		}

		for (; from < len; from++)
		{
			if (from in this && this[from] === elt)
			{
				return from;
			}
		}
		return -1;
	};
}
	/**
	 * JField 'showon' feature.
	 */
	window.jQuery && (function ($) {

		/**
		 * Method to check condition and change the target visibility
		 * @param {jQuery}  target
		 * @param {Boolean} animate
		 */
		function linkedoptions (target, animate) {
			var showfield = true,
				jsondata  = target.data('showon') || [],
				itemval, condition, fieldName, $fields;

			// Check if target conditions are satisfied
			for (var j = 0, lj = jsondata.length; j < lj; j++) {
				condition  = jsondata[j] || {};
				fieldName  = condition.field;
				$fields    = $('[name="' + fieldName + '"], [name="' + fieldName + '[]"]');

				condition['valid'] = 0;

				// Test in each of the elements in the field array if condition is valid
				$fields.each(function() {
					var $field = $(this);

					// If checkbox or radio box the value is read from proprieties
					if (['checkbox','radio'].indexOf($field.attr('type')) !== -1)
					{
						itemval = $field.prop('checked') ? $field.val() : '';
					}
					else
					{
						itemval = $field.val();
					}

					// Convert to array to allow multiple values in the field (e.g. type=list multiple)
					// and normalize as string
					if (!(typeof itemval === 'object'))
					{
						itemval = JSON.parse('["' + itemval + '"]');
					}

					// Test if any of the values of the field exists in showon conditions
					for (var i in itemval)
					{
						// ":" Equal to one or more of the values condition
						if (jsondata[j]['sign'] == '=' && jsondata[j]['values'].indexOf(itemval[i]) !== -1)
						{
							jsondata[j]['valid'] = 1;
						}
						// "!:" Not equal to one or more of the values condition
						if (jsondata[j]['sign'] == '!=' && jsondata[j]['values'].indexOf(itemval[i]) === -1)
						{
							jsondata[j]['valid'] = 1;
						}
					}
				});

				// Verify conditions
				// First condition (no operator): current condition must be valid
				if (condition['op'] === '')
				{
					if (condition['valid'] === 0)
					{
						showfield = false;
					}
				}
				// Other conditions (if exists)
				else
				{
					// AND operator: both the previous and current conditions must be valid
					if (condition['op'] === 'AND' && condition['valid'] + jsondata[j-1]['valid'] < 2)
					{
						showfield = false;
					}
					// OR operator: one of the previous and current conditions must be valid
					if (condition['op'] === 'OR'  && condition['valid'] + jsondata[j-1]['valid'] > 0)
					{
						showfield = true;
					}
				}
			}

			// If conditions are satisfied show the target field(s), else hide
			if (animate) {
				(showfield) ? target.slideDown() : target.slideUp();
			} else {
				target.toggle(showfield);
			}
		}

		/**
		 * Method for setup the 'showon' feature, for the fields in given container
		 * @param {HTMLElement} container
		 */
		function setUpShowon (container) {
			container = container || document;

			var $showonFields = $(container).find('[data-showon]');

			// Setup each 'showon' field
			for (var is = 0, ls = $showonFields.length; is < ls; is++) {
				// Use anonymous function to capture arguments
				(function () {
					var $target = $($showonFields[is]), jsondata = $target.data('showon') || [],
						field, $fields = $();

					// Collect an all referenced elements
					for (var ij = 0, lj = jsondata.length; ij < lj; ij++) {
						field   = jsondata[ij]['field'];
						$fields = $fields.add($('[name="' + field + '"], [name="' + field + '[]"]'));
					}

					// Check current condition for element
					linkedoptions($target);

					// Attach events to referenced element, to check condition on change
					$fields.on('change', function() {
						linkedoptions($target, true);
					});
				})();
			}
		}

		/**
		 * Initialize 'showon' feature
		 */
		$(document).ready(function() {
			setUpShowon();

			// Setup showon feature in the subform field
			$(document).on('subform-row-add', function(event, row){
				var $row = $(row),
					$elements = $row.find('[data-showon]'),
					baseName  = $row.data('baseName'),
					group     = $row.data('group'),
					search    = new RegExp('\\[' + baseName + '\\]\\[' + baseName + 'X\\]', 'g'),
					replace   = '[' + baseName + '][' + group + ']',
					$elm, showon;

				// Fix showon field names in a current group
				for (var i = 0, l = $elements.length; i < l; i++) {
					$elm   = $($elements[i]);
					showon = $elm.attr('data-showon').replace(search, replace);

					$elm.attr('data-showon', showon);
				}

				setUpShowon(row);
			});
		});

	})(jQuery);


})(document, Joomla);
