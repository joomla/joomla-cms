/**
 * Function to send Permissions via Ajax to Com-Config Application Controller
 */
function sendPermissions(event) {
	// set the icon while storing the values
	var icon = document.getElementById('icon_' + this.id);
	icon.removeAttribute('class');
	icon.setAttribute('style', 'background: url(../media/system/images/modal/spinner.gif); display: inline-block; width: 16px; height: 16px');

	//get values and prepare GET-Parameter
	var id = this.id.split('_');
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

	var data = '&comp=' + asset + '&action=' + id[2] + '&rule=' + id[3] + '&value=' + value + '&title=' + title;
	var url = 'index.php?option=com_config&task=config.store&format=raw' + data;

	// doing ajax request
	jQuery.ajax({
		type: 'GET',
		url: url,
		datatype: 'JSON'
	}).success(function (response) {
		var element = event.target;
		var resp = JSON.parse(response);
		if (resp.data == 'true')
		{
			icon.removeAttribute('style');
			icon.setAttribute('class', 'icon-save');
			if (value == '1')
			{
				jQuery(element).parents().next('td').find('span')
					.removeClass('label label-important').addClass('label label-success')
					.html('Allowed');
			}
			else
			{
				jQuery(element).parents().next('td').find('span')
					.removeClass('label label-success').addClass('label label-important')
					.html('Not Allowed.');
			}
		}
		else
		{
			var msg = { error: [Joomla.JText._('JLIB_RULES_DATABASE_FAILURE ')] };
			Joomla.renderMessages(msg);
			icon.removeAttribute('style');
			icon.setAttribute('class', 'icon-cancel');
		}
		if (resp.message == 0)
		{
			var msg = { error: [Joomla.JText._('JLIB_RULES_SAVE_BEFORE_CHANGE_PERMISSIONS')] };
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
