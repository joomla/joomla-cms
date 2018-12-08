/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Some state variables for the overrider
 */
Joomla.overrider = {
	states: {
		refreshing  : false,
		refreshed   : false,
		counter     : 0,
		searchstring: '',
		searchtype  : 'value'
	}
};

/**
 * Method for refreshing the database cache of known language strings via Ajax
 *
 * @return  void
 *
 * @since   2.5
 */
Joomla.overrider.refreshCache = function()
{
	var $ = jQuery.noConflict(), self = this;
	this.states.refreshing = true;

	$('#refresh-status').slideDown().css('display', 'block');

	$.ajax(
	{
		type: "POST",
		url: 'index.php?option=com_languages&task=strings.refresh&format=json',
		dataType: 'json'
	}).done(function (r)
	{
		if (r.error && r.message)
		{
			alert(r.message);
		}

		if (r.messages)
		{
			Joomla.renderMessages(r.messages);
		}

		$('#refresh-status').slideUp().hide();
		self.states.refreshing = false;
	}).fail(function (xhr)
	{
		alert(Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));
		$('#refresh-status').slideUp().hide();
	});
};

/**
 * Method for searching known language strings via Ajax
 *
 * @param   more  Determines the limit start of the results
 *
 * @return  void
 *
 * @since   2.5
 */
Joomla.overrider.searchStrings = function(more)
{
	var $ = jQuery.noConflict(), self = this;

	// Prevent searching if the cache is refreshed at the moment
	if (this.states.refreshing)
	{
		return;
	}

	// Only update the used searchstring and searchtype if the search button
	// was used to start the search (that will be the case if 'more' is null)
	if (!more)
	{
		this.states.searchstring = $('#jform_searchstring').val();
		this.states.searchtype   = $('#jform_searchtype') !== null ? $('#jform_searchtype').val() : 'value';
	}

	if (!this.states.searchstring)
	{
		$('#jform_searchstring').addClass('invalid');

		return;
	}


	if (more)
	{
		// If 'more' is greater than 0 we have already displayed some results for
		// the current searchstring, so display the spinner at the more link
		$('#more-results').addClass('overrider-spinner');
	}
	else
	{
		// Otherwise it is a new searchstring and we have to remove all previous results first
		$('#more-results').hide();
		var $children = $('#results-container div.language-results');
		$children.remove();
		$('#results-container').addClass('overrider-spinner').slideDown().css('display', 'block');
	}

	$.ajax(
	{
		type: "POST",
		url: 'index.php?option=com_languages&task=strings.search&format=json',
		data: 'searchstring=' + self.states.searchstring + '&searchtype=' + self.states.searchtype + '&more=' + more,
		dataType: 'json'
	}).done(function (r)
	{
		if (r.error && r.message)
		{
			alert(r.message);
		}

		if (r.messages)
		{
			Joomla.renderMessages(r.messages);
		}

		if (r.data)
		{
			if (r.data.results)
			{
				self.insertResults(r.data.results);
			}

			if (r.data.more)
			{
				// If there are more results than the sent ones
				// display the more link
				self.states.more = r.data.more;
				$('#more-results').slideDown().css('display', 'block');
			}
			else
			{
				$('#more-results').hide();
			}
		}

		$('#results-container').removeClass('overrider-spinner');
		$('#more-results').removeClass('overrider-spinner');
	}).fail(function (xhr)
	{
		alert(Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));
		$('#results-container').removeClass('overrider-spinner');
		$('#more-results').removeClass('overrider-spinner');
	});
};

/**
 * Method inserting the received results into the results container
 *
 * @param   results  An array of search result objects
 *
 * @return  void
 *
 * @since   2.5
 */
Joomla.overrider.insertResults = function(results)
{
	var $ = jQuery.noConflict(), self = this;

	// For creating an individual ID for each result we use a counter
	this.states.counter = this.states.counter + 1;

	// Create a container into which all the results will be inserted
	var $results_div = $('<div>', {
		id : 'language-results' + self.states.counter,
		class : 'language-results',
		style : 'display:none;'
	});

	// Create some elements for each result and insert it into the container
	$.each(results, function(index, item) {

		var $div = $('<div>', {
			class: 'result row' + index % 2,
			onclick: 'Joomla.overrider.selectString(' + self.states.counter + index + ');'
		});

		var $key = $('<div>', {
			id:  'override_key' + self.states.counter + index,
			class: 'result-key',
			html: item.constant,
			title: item.file
		});

		var $string = $('<div>',{
			id: 'override_string' + self.states.counter + index,
			class:	'result-string',
			html: item.string
		});

		$key.appendTo($div);
		$string.appendTo($div);
		$div.appendTo($results_div);

	});

	// If there aren't any results display an appropriate message
	if (!results.length)
	{
		var $noresult = $('<div>',{
			html: Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_NO_RESULTS')
		});
		$noresult.appendTo($results_div);
	}

	// Finally insert the container afore the more link and reveal it
	$('#more-results').before($results_div);
	$('#language-results' + this.states.counter).slideDown().css('display','block');
};

/**
 * Inserts a specific constant/value pair into the form and scrolls the page back to the top
 *
 * @param   id  The ID of the element which was selected for insertion
 *
 * @return  void
 *
 * @since   2.5
 */
Joomla.overrider.selectString = function(id)
{
	var $ = jQuery.noConflict();
	$('#jform_key').val($('#override_key' + id).html());
	$('#jform_override').val($('#override_string' + id).html());
	$(window).scrollTop(0);
};
