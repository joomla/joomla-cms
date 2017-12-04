document.addEventListener('DOMContentLoaded', function(){
	if (Joomla && Joomla.getOptions('com_joomlaupdate')) {
		var options = Joomla.getOptions('com_joomlaupdate'),
			joomlaupdate_password = options.joomlaupdate_password,
			joomlaupdate_totalsize = options.joomlaupdate_totalsize,
			joomlaupdate_ajax_url = options.joomlaupdate_ajax_url,
			joomlaupdate_return_url = options.joomlaupdate_return_url;
	}
	window.pingExtract();
});