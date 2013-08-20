/**
 * Admin Modal Repeat Editor
 *
 * @copyright: Copyright (C) 2005-2013, fabrikar.com - All rights reserved.
 * @license:   GNU/GPL http://www.gnu.org/copyleft/gpl.html
 */

(function ($) {
	$.JRepeatable = function (elid, names, field) {
		var field = null, win = null, el = null, tmpl, names, origContent,
		button = $(elid + '_button'),
		cancelled = false,
		origContainer = null,
		mask = $('<div>');
		mask.css({'background-color': '#000', 'opacity': 0.4, 'z-index': 9998, 'position': 'fixed', 'left': 0, 'top': 0, 'height': '100%', 'width': '100%'}).hide();
		mask.appendTo('body');   
		
		/**
		 * Open the window
		 */
		openWindow = function () {
			if (!win) {
				makeWin();
				el.prependTo(win);
			}
			el.show();
			win.show();
			resizeWin();
			mask.show();
			
			// Set original content for cancel
			origContent = getTrs().clone();
		};

		/**
		 * Build the window
		 */
		makeWin = function () {
			if (win) {
				return;
			}
			win = $('<div/>');
			win.css({'padding': '5px', 'background-color': '#fff', 'display': 'none', 'z-index': 9999, 'position': 'fixed', 'left': '50%', 'top': '50%'});
			win.appendTo('body');
			
			var applyButton = $('<button class="btn button btn-primary"/>').text(Joomla.JText._('JAPPLY'));
			applyButton.on('click', function (e) {
				e.stopPropagation();
				store();
				origContainer.find('table').replaceWith(el);
				close();
			});
			
			var cancelButton = $('<button class="btn button btn-link"/>').text(Joomla.JText._('JCANCEL'));
			cancelButton.on('click', function (e) {
				cancelled = true;
				e.stopPropagation();
				$(el).find('tbody tr').replaceWith(origContent);
				origContainer.find('table').replaceWith(el);
				close();
				win = null;
			});
				
			var controls = $('<div class="controls form-actions"/>').css({'text-align': 'right', 'margin-bottom': 0}).append([cancelButton, applyButton]);

			win.append(el);
			
			win.append(controls);
			if (!cancelled) {
				build();
			}
			watchButtons();
		};
		
		/**
		 * Re-center the window
		 */
		resizeWin = function () {
			var l = -1 * (win.width() / 2);
			var t = -1 * (win.height() / 2);
			win.css({'margin-left': l, 'margin-top': t});
		};
		
		/**
		 * Close the window
		 */
		close = function () {
			el.hide();
			win.hide();
			mask.hide();
		};
		
		/**
		 * Parse for radio values
		 * 
		 * @return   array  Radio values.
		 */
		getRadioValues = function () {
			var radiovals = [];
			$.each(getTrs(), function (i, tr) {
				var sel = $(tr).find('input[type="radio"]:checked');
				var v = (sel.length > 0) ? sel.val() : v = '';
				radiovals.push(v);
			});
			return radiovals;
		};

		/**
		 * Reapply radio button selections
		 * 
		 * @param   array  values  Radio element values.
		 */
		setRadioValues = function (values) {
			$.each(getTrs(), function (i, tr) {
				var r = $(tr).find('input[type="radio"][value="' + values[i] + '"]');
				if (r.length > 0) {
					r.attr('checked', 'checked');
				}
			});
		};
		
		/**
		 * Delegate window add/remove events
		 */
		watchButtons = function () {
			win.on('click', 'a.add', function (e) {
				if (tr = findTr(e)) {

					// Store radio button selections
					var radiovals = getRadioValues();

					var body = $(tr).closest('table').find('tbody');
					var clone = tmpl.clone(true, true);
					clone.appendTo(body);
					renameInputs();

					// Reapply values as renaming radio buttons
					setRadioValues(radiovals);
					resizeWin();
					resetChosen(clone);
				}
				//win.position();
				return false;
			}.bind(this));
			win.on('click', 'a.remove', function (e) {
				if (tr = findTr(e)) {
					tr.remove();
				}
				resizeWin();
				return false;
			}.bind(this));
		};
		
		resetChosen = function (clone) {

			// Chosen reset
			clone.find('select').removeClass('chzn-done').show();

			// Assign random id
			$.each(clone.find('select'), function (index, c) {
				c.id = c.id + '_' + (Math.random() * 10000000).toInt();
			});
			clone.find('.chzn-container').remove();

			$('select').chosen({
				disable_search_threshold : 10,
				allow_single_deselect : true
			});
		};

		/**
		 * Get the pop-up windows <tr>s
		 * 
		 * @return  JQuery object containing dom nodes.
		 */
		getTrs = function () {
			return win.find('tbody tr');
		};
		
		/**
		 * Ensure checkboxes and radio buttons (and their labels) have unique names & ids.
		 */
		renameInputs = function () {
			var id, label, shortName, i, chx,
			trs = getTrs();
			regex = /\[\]/;
			for (i = 0; i < trs.length; i ++) {
				chx = $(trs[i]).find('input[type="radio"], input[type="checkbox"]');
				$.each(chx, function (index, r) {
					if (r.name.match(regex) === null) {
						r.name += '[' + i + ']';
					} else {
						r.name = r.name.replace(regex, '[' + i + ']');
						r.name += '[]';
					}
					shortName = r.name.split('][');
					shortName = shortName[shortName.length - 3];
					id = r.id.split('_')
					id[id.length - 1] = shortName + index;
					id.push(i);
					r.id = id.join('_');
					label = $(this).next('label');
					label.attr('for', r.id);
				});
			}
		};

		/**
		 * Create <tr>'s from the hidden fields JSON and the template HTML
		 */
		build = function () {
			var clone, a, tr, keys, newrow, rowcount, trs, type;
			a = JSON.decode($(field).val());
			if (typeOf(a) === 'null') {
				a = {};
			}
			tr = win.find('tbody tr');
			keys = Object.keys(a);
			newrow = keys.length === 0 || a[keys[0]].length === 0 ? true : false;
			rowcount = newrow ? 1 : a[keys[0]].length;

			// Build the rows from the json object
			for (var i = 1; i < rowcount; i ++) {
				clone = tr.clone();
				clone.insertAfter(tr);
				resetChosen(clone);
			}
			renameInputs();
			trs = getTrs();

			// Populate the cloned fields with the json values
			for (i = 0; i < rowcount; i++) {
				$.each(keys, function (index, k) {
					console.log($(trs[i]).find('*[name*="' + this + '"]'));
					$(trs[i]).find('*[name*="' + this + '"]').each(function (index, f) {
						type = $(f).attr('type');
						if (type === 'radio' || type === 'checkbox') {
							if (f.value === a[k][i]) {
								$(f).attr('checked', 'checked');
							}
						} else {
							// Works for input,select and textareas
							$(f).val(a[k][i]);
							if ($(f).prop('tagName') === 'SELECT') {

								// Manually fire chosen dropdown update
								$(f).trigger('liszt:updated');
							}
						}
					});
				});
			}
			tmpl = tr;
			if (newrow) {
				tr.remove();
			}
		};
		
		/**
		 * Get the <tr> from the event
		 * 
		 * @param   Event  e  click event for add/remove
		 * 
		 * @return  DOM Node <tr> or false
		 */
		findTr = function (e) {
			var tr = e.target.getParents().filter(function (p) {
				return p.get('tag') === 'tr';
			});
			return (tr.length === 0) ? false : tr[0];
		};

		/**
		 * Save the window fields back to the hidden element field (stored as JSON)
		 */
		store = function () {
			var i, n, fields, type, json = {};

			// Get the current values
			for (i = 0; i < names.length; i++) {
				n = names[i];
				fields = el.find('*[name*="' + n + '"]');
				json[n] = [];
				$.each(fields, function () {
					type = $(this).attr('type');
					if (type === 'radio' || type === 'checkbox') {
						if ($(this).attr('checked') === 'checked') {
							json[n].push($(this).val());
						}
					} else {
						json[n].push($(this).val());
					}
				});
			}
			// Store them in the parent field.
			field.val(JSON.encode(json));
			return true;
		};
		
		/**
		 * Main click event on 'Select' button to open the window.
		 */
		$(document).on('click', '*[data-modal="' + elid + '"]', function (e, target) {
			field = $(this).next('input');
			origContainer = $(this).closest('div.control-group');
			if (!el) {
				el = origContainer.find('table');
			}
			openWindow();
			return false;
		});
	}
	
})(jQuery);