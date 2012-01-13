/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Some state variables for the overrider
 */
Joomla.overrider = {
	states : {
		refreshing: false,
		refreshed: false,
		counter: 0,
		searchstring: '',
		searchtype: 'value'
	}
};

/**
 * Method for refreshing the database cache of known language strings via Ajax
 *
 * @return	void
 *
 * @since		2.5
 */
Joomla.overrider.refreshCache = function()
{
	var req = new Request.JSON({
		method: 'post',
		url: 'index.php?option=com_languages&task=strings.refresh&format=json',
		onRequest: function()
		{
			this.states.refreshing = true;
			document.id('refresh-status').reveal();
		}.bind(this),
		onSuccess: function(r)
		{
			if (r.error && r.message)
			{
				alert(r.message);
			}
			if (r.messages)
			{
				Joomla.renderMessages(r.messages);
			}
			document.id('refresh-status').dissolve();
			this.states.refreshing = false;
		}.bind(this),
		onFailure: function(xhr)
		{
			alert(Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));
			document.id('refresh-status').dissolve();
		}.bind(this),
		onError: function(text, error)
		{
			alert(error + "\n\n" + text);
			document.id('refresh-status').dissolve();
		}.bind(this)
	});
	req.post();
};

/**
 * Method for searching known language strings via Ajax
 *
 * @param		int		 	more	Determines the limit start of the results
 *
 * @return	void
 *
 * @since		2.5
 */
Joomla.overrider.searchStrings = function(more)
{
	// Prevent searching if the cache is refreshed at the moment
	if (this.states.refreshing)
	{
		return;
	}

	// Only update the used searchstring and searchtype if the search button
	// was used to start the search (that will be the case if 'more' is null)
	if (!more)
	{
		this.states.searchstring 	= document.id('jform_searchstring').value;
		this.states.searchtype		= 'value';
		if (document.id('jform_searchtype0').checked)
		{
			this.states.searchtype 	= 'constant';
		}
	}

	if (!this.states.searchstring)
	{
		document.id('jform_searchstring').addClass('invalid');

		return;
	}

	var req = new Request.JSON({
		method: 'post',
		url: 'index.php?option=com_languages&task=strings.search&format=json',
		onRequest: function()
		{
			if (more)
			{
				// If 'more' is greater than 0 we have already displayed some results for
				// the current searchstring, so display the spinner at the more link
				document.id('more-results').addClass('overrider-spinner');
			}
			else
			{
				// Otherwise it is a new searchstring and we have to remove all previous results first
				document.id('more-results').set('style', 'display:none;');
				var children = $$('#results-container div.language-results');
				children.destroy();
				document.id('results-container').addClass('overrider-spinner').reveal();
			}
		}.bind(this),
		onSuccess: function(r) {
			if (r.error && r.message)
			{
				alert(r.message);
			}
			if (r.messages)
			{
				Joomla.renderMessages(r.messages);
			}
			if(r.data)
			{
				if(r.data.results)
				{
					this.insertResults(r.data.results);
				}
				if(r.data.more)
				{
					// If there are more results than the sent ones
					// display the more link
					this.states.more = r.data.more;
					document.id('more-results').reveal();
				}
				else
				{
					document.id('more-results').set('style', 'display:none;');
				}
			}
			document.id('results-container').removeClass('overrider-spinner');
			document.id('more-results').removeClass('overrider-spinner');
		}.bind(this),
		onFailure: function(xhr)
		{
			alert(Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));
			document.id('results-container').removeClass('overrider-spinner');
			document.id('more-results').removeClass('overrider-spinner');
		}.bind(this),
		onError: function(text, error)
		{
			alert(error + "\n\n" + text);
			document.id('results-container').removeClass('overrider-spinner');
			document.id('more-results').removeClass('overrider-spinner');
		}.bind(this)
	});
	req.post('searchstring=' + this.states.searchstring + '&searchtype=' + this.states.searchtype + '&more=' + more);
};

/**
 * Method inserting the received results into the results container
 *
 * @param		array results An array of search result objects
 *
 * @return	void
 *
 * @since		2.5
 */
Joomla.overrider.insertResults = function(results)
{
	// For creating an individual ID for each result we use a counter
	this.states.counter = this.states.counter + 1;

	// Create a container into which all the results will be inserted
	var results_div = new Element('div', {
		id: 'language-results' + this.states.counter,
		'class': 'language-results',
		style: 'display:none;'
	});

	// Create some elements for each result and insert it into the container
	Array.each(results, function (item, index) {
		var div = new Element('div', {
			'class':	'result row' + index%2,
			onclick:	'Joomla.overrider.selectString(' + this.states.counter + index + ');',
		});
		var key = new Element('div', {
			id:				'override_key' + this.states.counter + index,
			'class':	'result-key',
			html:			item.constant,
			title:		item.file
		});
		key.inject(div);
		var string = new Element('div', {
			id:				'override_string' + this.states.counter + index,
			'class':	'result-string',
			html:			item.string
		});
		string.inject(div);
		div.inject(results_div);
	}, this);

	// If there aren't any results display an appropriate message
	if(!results.length)
	{
		var noresult = new Element('div', {
			html: Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_NO_RESULTS')
		});
		noresult.inject(results_div);
	}

	// Finally insert the container afore the more link and reveal it
	results_div.inject(document.id('more-results'), 'before');
	document.id('language-results' + this.states.counter).reveal();
};

/**
 * Inserts a specific constant/value pair into the form and scrolls the page back to the top
 *
 * @param		int		id	The ID of the element which was selected for insertion
 *
 * @return	void
 *
 * @since		2.5
 */
Joomla.overrider.selectString = function(id)
{
	document.id('jform_key').value = document.id('override_key' + id).get('html');
	document.id('jform_override').value = document.id('override_string' + id).get('html');
	new Fx.Scroll(window).toTop();
};