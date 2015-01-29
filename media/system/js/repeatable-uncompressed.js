/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
//
//
(function ($)
{
	"use strict";

	$.JRepeatable = function (id, names, maximum)
	{
		var $input = $('#' + id),
			$modal = $('#' + id + '_modal'),
			$button = $('#' + id + '_modal_button'),
			$tmpl = $modal.find('tbody tr').first().detach(),
			$win = $('<div>'),
			$mask = $('<div>');

		// Clean up any chosens that happen to be on the template.
		$tmpl.find('.chzn-container').remove();
		$tmpl.find('select').removeClass('chzn-done').show();

		// Setup the mask
		$mask.css(
			{
				backgroundColor: '#000',
				opacity: 0.4,
				zIndex: 9998,
				position: 'fixed',
				left: 0,
				top: 0,
				height: '100%',
				width: '100%'
			})
			.hide()
			.appendTo('body');

		// Setup the win
		$win.css(
			{
				padding: '5px',
				backgroundColor: '#fff',
				display: 'none',
				zIndex: 9999,
				position: 'absolute',
				left: '50%',
				top: $(document).scrollTop() + ($(window).height() * 0.1)
			})
			.appendTo('body');

		// Start watching the + and - buttons even though they don't exist yet!
		watchButtons();

		/**
		 * Main click event on 'Select' button to open the window.
		 */
		$button.on('click', function (e, target)
		{
			e.stopPropagation();
			e.preventDefault();

			openWindow();

			return false;
		});

		/**
		 * Open the window
		 */
		function openWindow()
		{
			makeWin();
			$modal.prependTo($win);

			$modal.show();
			$win.show();
			centerWin();
			$mask.show();
		}

		/**
		 * Build the window
		 */
		function makeWin()
		{
			var $applyButton, $cancelButton, $controls;

			function makeHandler(save)
			{
				return function (e)
				{
					e.stopPropagation();
					e.preventDefault();

					if (save)
					{
						store();
					}

					$modal.detach()
						.find('tbody tr').remove();

					$win.empty();

					close();
				};
			}

			$win.css('top', $(document).scrollTop() + ($(window).height() * 0.1));

			$applyButton = $('<button class="btn button btn-primary"/>')
				.text(Joomla.JText._('JAPPLY'))
				.on('click', makeHandler(true));

			$cancelButton = $('<button class="btn button btn-link"/>')
				.text(Joomla.JText._('JCANCEL'))
				.on('click', makeHandler(false));

			$controls = $('<div class="controls form-actions"/>')
				.css(
				{
					textAlign: 'right',
					marginBottom: 0
				})
				.append($cancelButton, $applyButton);

			$win.append($modal, $controls);

			build();
		}

		/**
		 * Re-center the window.
		 */
		function centerWin()
		{
			$win.css('margin-left', $win.width() * -0.5);
		}

		/**
		 * Close the window
		 */
		function close()
		{
			$modal.hide();
			$win.hide();
			$mask.hide();
		}

		/**
		 * Delegate window add/remove events
		 */
		function watchButtons()
		{
			$win.on('click', 'a.add', add)
				.on('click', 'a.remove', remove);

			function add(e)
			{
				e.preventDefault();
				e.stopPropagation();

				var $tr = $(e.target).parents('tr').first();

				if (!$tr.length)
				{
					return false;
				}

				var rowcount = $modal.find('tbody tr').length;

				// Don't allow a new row to be added if we're at the maximum value
				if (rowcount >= maximum)
				{
					return false;
				}

				// Store radio button selections
				var $body = $modal.find('tbody').first();

				$tmpl.clone()
					.appendTo($body)
					.find('select').chosen(
					{
						disable_search_threshold: 10,
						allow_single_deselect: true
					});

				rowcount = $modal.find('tbody tr').length;

				// 'Disable' the new button if we are at the maximum value
				if (rowcount >= maximum)
				{
					$win.find(".add")
						.removeClass("btn-success")
						.addClass("disabled");
				}

				renameInputs();

				centerWin();

				return false;
			}

			function remove(e)
			{
				e.preventDefault();
				e.stopPropagation();

				var $tr = $(e.target).parents('tr').first();

				if (!$tr.length)
				{
					return false;
				}

				$tr.remove();

				var rowcount = $modal.find('tbody tr').length;

				// Unstyle disabled add buttons
				if (rowcount < maximum)
				{
					$win.find(".add")
						.removeClass("disabled")
						.addClass("btn-success");
				}

				centerWin();

				return false;
			}
		}

		/**
		 * Ensure checkboxes and radio buttons (and their labels) have unique names & ids.
		 */
		function renameInputs()
		{
			$modal.find('tbody tr').each(function (index, tr)
			{
				renameInputRow(tr, index);
			});
		}

		/**
		 * Single row manipulation for radio / chx and the labels.
		 */
		function renameInputRow(tr, i)
		{
			var regex = /(\[[\d]+\])?(\[\])?$/;

			$(tr).find('input, textarea, select').each(function (index, field)
			{
				if (field.name.match(regex) === null)
				{
					field.name += '[' + i + ']';
				}
				else
				{
					field.name = field.name.replace(regex, '[' + i + ']$2');
				}

				field.id = field.name.replace(/\[|\]/gi, '_').replace(/_+/gi, '_') + index;

				$(field)
					.next('label')
					.attr('for', field.id);
			});
		}

		/**
		 * Create <tr>'s from the hidden fields JSON and the template HTML
		 */
		function build()
		{
			var a = JSON.parse($input.val()) ||
				{},
				hasData = !!(names.length > 0 && a[names[0]] && a[names[0]].length > 0),
				rowcount = hasData ? a[names[0]].length : 1,
				$row,
				rows = [],
				i;

			function processRow(index, key)
			{
				$row.find('input, textarea, select')
					.filter('[name$="[' + key + '][' + i + ']"], [name$="[' + key + '][' + i + '][]"]')
					.each(function (index, field)
					{
						if (typeof a[key] === 'undefined' || typeof a[key][i] === 'undefined')
						{
							return;
						}

						var $field = $(field),
							type = $field.attr('type'),
							subValues = a[key][i] instanceof Array ? a[key][i] : [];

						if ($.inArray(type, ['radio', 'checkbox']) !== -1 && $.inArray(field.value, subValues) !== -1)
						{
							$field.prop('checked', true);
						}
						else
						{
							// Works for input,select and textareas
							$field.val(a[key][i]);
						}
					});
			}

			// Populate the cloned fields with the json values
			for (i = 0; i < rowcount; i++)
			{
				$row = $tmpl.clone();

				renameInputRow($row, i);

				if (hasData)
				{
					$.each(names, processRow);
				}

				rows.push($row);
			}

			$modal
				.find('tbody').append(rows)
				.find('select').chosen(
				{
					disable_search_threshold: 10,
					allow_single_deselect: true
				});
		}

		/**
		 * Save the window fields back to the hidden element field (stored as JSON)
		 */
		function store()
		{
			var json = {},
				regex = /\[([\w\s]+)\]\[([\d]+)\](\[\])?/;

			function processField(index, field)
			{
				var $field = $(field),
					type = $field.attr('type'),
					value = $field.val(),
					matches = field.name.match(regex),
					name, row;

				if (!matches)
				{
					return;
				}

				name = matches[1];
				row = matches[2];

				if (typeof json[name] === 'undefined')
				{
					json[name] = [];
				}

				if (type === 'radio' || type === 'checkbox')
				{
					if (typeof json[name][row] === 'undefined')
					{
						json[name][row]	= [];
					}

					if ($field.prop('checked'))
					{
						json[name][row].push(value);
					}
				}
				else
				{
					json[name][row]	= value;
				}
			}

			$modal.find('tbody').find('input, textarea, select').each(processField);

			// Store them in the parent field.
			$input.val(JSON.stringify(json));

			return true;
		}
	};

})(jQuery);