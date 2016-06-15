/**
 * Function to send Permissions via Ajax to Com-Config Application Controller
 */
function sendPermissions(event) {
	// set the icon while storing the values
	var icon = document.getElementById('icon_' + this.id);
	icon.removeAttribute('class');
	icon.setAttribute('style', 'background: url(../media/system/images/modal/spinner.gif); display: inline-block; width: 16px; height: 16px');

	//get values and prepare GET-Parameter
	var asset = 'not';
	var component = getUrlParam('component');
	var extension = getUrlParam('extension');
	var option = getUrlParam('option');
	var view = getUrlParam('view');
	var title = component;
	var value = this.value;

	if (option == 'com_config' && component == false && extension == false)
	{
		asset = 'root.1';
	}
	else if (extension == false && view == 'component'){
		asset = component;
	}
	else if (extension != false && view != false){
		asset = extension + '.' + view + '.' + getUrlParam('id');
		title = document.getElementById('jform_title').value;
	}
	else if (extension == false && view != false){
		asset = option + '.' + view + '.' + getUrlParam('id');
		title = document.getElementById('jform_title').value;
	}

	var id = this.id.replace('jform_rules_', '');
	var lastUnderscoreIndex = id.lastIndexOf('_');
	var action = id.substring(0, lastUnderscoreIndex);
	var rule = id.substring(lastUnderscoreIndex + 1);
	var data = '&comp=' + asset + '&action=' + action + '&rule=' + rule + '&value=' + value + '&title=' + title;
	var url = 'index.php?option=com_config&task=config.store&format=raw' + data;

	// doing ajax request
	jQuery.ajax({
		type: 'GET',
		url: url,
		datatype: 'JSON'
	}).success(function (response) {
		var element = event.target;
		// Parse the response
		var resp = JSON.parse(response);

		// Parse the data
		var data = JSON.parse(resp.data);

		// Check if everything is OK
		if (data.result == true)
		{
			icon.removeAttribute('style');
			icon.setAttribute('class', 'icon-save');

			jQuery(element).parents().next('td').find('span')
				.removeClass().addClass(data.class)
				.html(data.text);
		}
		else
		{
			var msg = { error: [resp.message] };
			Joomla.renderMessages(msg);
			icon.removeAttribute('style');
			icon.setAttribute('class', 'icon-cancel');
		}
	}).fail(function() {
		//set cancel icon on http failure
		var msg = { error: [Joomla.JText._('JLIB_RULES_REQUEST_FAILURE')] };
		Joomla.renderMessages(msg);
		icon.removeAttribute('style');
		icon.setAttribute('class', 'icon-cancel');
	})
}

/**
 * Function to get parameters out of the url
 */
function getUrlParam(variable) {
	var query = window.location.search.substring(1);
	var vars = query.split('&');
	for (var i=0;i<vars.length;i++)
	{
		var pair = vars[i].split('=');
		if (pair[0] == variable)
		{
			return pair[1];
		}
	}
	return false;
}
