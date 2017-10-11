/**
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
	this.states.refreshing = true;

	var self          = this,
	    refreshStatus = document.getElementById('refresh-status');

	refreshStatus.classList.add('show');

	Joomla.request({
		url: 'index.php?option=com_languages&task=strings.refresh&format=json',
		method: 'POST',
		headers: {'Content-Type': 'application/json'},
		onSuccess: function(response) {
			if (response.error && response.message) {
				alert(response.message);
			}

			if (response.messages) {
				Joomla.renderMessages(response.messages);
			}

			refreshStatus.classList.remove('show');
			self.states.refreshing = false;
		},
		onError: function(xhr) {
			alert(Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));
			refreshStatus.classList.remove('show');
		}
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
	var self             = this,
	    formSearchString = document.getElementById('jform_searchstring'),
	    formSearchType   = document.getElementById('jform_searchtype'),
	    spinner          = document.getElementById('overrider-spinner'),
	    spinnerBtn       = document.getElementById('overrider-spinner-btn'),
	    moreResults      = document.getElementById('more-results'),
	    resultsContainer = document.getElementById('results-container');

	// Prevent searching if the cache is refreshed at the moment
	if (this.states.refreshing) {
		return;
	}

	// Only update the used searchstring and searchtype if the search button
	// was used to start the search (that will be the case if 'more' is null)
	if (!more) {
		this.states.searchstring = formSearchString.value;
		this.states.searchtype   = formSearchType !== null ? formSearchType.value : 'value';

		// Remove the old results
		var oldResults = document.querySelectorAll('.language-results');
		for (var i = 0, l = oldResults.length ; i < l; i++) {
			oldResults[i].parentNode.removeChild(oldResults[i]);
		}
	}

	if (!this.states.searchstring) {
		formSearchString.classList.add('invalid');

		return;
	}

	if (more) {
		// If 'more' is greater than 0 we have already displayed some results for
		// the current searchstring, so display the spinner at the more link
		spinnerBtn.classList.add('show');
	}
	else {
		// Otherwise it is a new searchstring and we have to remove all previous results first
		moreResults.classList.remove('show');

		var children = document.querySelectorAll('#results-container div.language-results');
		for (var i = 0, l = children.length ; i < l; i++) {
			children[i].parentNode.removeChild(children[i]);
		}

		resultsContainer.classList.add('show');
		spinner.classList.add('show');
	}

	Joomla.request({
		url: 'index.php?option=com_languages&task=strings.search&format=json&searchstring=' 
			+ self.states.searchstring + '&searchtype=' + self.states.searchtype + '&more=' + more,
		method: 'POST',
		headers: {'Content-Type': 'application/json'},
		onSuccess: function(response) {

			var response = JSON.parse(response);

			if (response.error && response.message) {
				alert(response.message);
			}

			if (response.messages) {
				Joomla.renderMessages(response.messages);
			}

			if (response.data) {
				if (response.data.results) {
					self.insertResults(response.data.results);
				}

				if (response.data.more) {
					// If there are more results than the sent ones
					// display the more link
					self.states.more = response.data.more;
					moreResults.classList.add('show');
				}
			}

			spinnerBtn.classList.remove('show');
			spinner.classList.remove('show');
		},
		onError: function(xhr) {
			alert(Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_REQUEST_ERROR'));
			moreResults.classList.remove('show');
			resultsContainer.classList.remove('show');
		}
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
	var self = this;

	// For creating an individual ID for each result we use a counter
	this.states.counter = this.states.counter + 1;

	// Create a container into which all the results will be inserted
	var resultsDiv = document.createElement('div'); 
	resultsDiv.setAttribute('id', 'language-results' + self.states.counter);
	resultsDiv.classList.add('language-results');
	resultsDiv.classList.add('list-group');
	resultsDiv.classList.add('mb-2');
	resultsDiv.classList.add('show');

	// Create some elements for each result and insert it into the container
	results.forEach(function(item, index) {

		var a = document.createElement('a'); 
		a.setAttribute('onclick', 'Joomla.overrider.selectString(' + self.states.counter + index + ');');
		a.setAttribute('href', '#');
		a.classList.add('list-group-item');
		a.classList.add('list-group-item-action');
		a.classList.add('flex-column');
		a.classList.add('align-items-start');

		var key = document.createElement('div'); 
		key.setAttribute('id', 'override_key' + self.states.counter + index);
		key.setAttribute('title', item.file);
		key.classList.add('result-key');
		key.innerHTML = item.constant;

		var string = document.createElement('div'); 
		string.setAttribute('id', 'override_string' + self.states.counter + index);
		string.classList.add('result-string');
		string.innerHTML = item.string;

		a.appendChild(key);
		a.appendChild(string);
		resultsDiv.appendChild(a);
	});

	// If there aren't any results display an appropriate message
	if (!results.length) {
		var noresult = document.createElement('div'); 
		noresult.innerHTML = Joomla.JText._('COM_LANGUAGES_VIEW_OVERRIDE_NO_RESULTS');

		resultsDiv.appendChild(noresult);
	}

	// Finally insert the container before the "more" link
	var moreResults = document.getElementById('more-results');
	if (moreResults) {
		moreResults.parentNode.insertBefore(resultsDiv, moreResults);
	}
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
	document.getElementById('jform_key').value = document.getElementById('override_key' + id).innerHTML;
	document.getElementById('jform_override').value = document.getElementById('override_string' + id).innerHTML;
};
