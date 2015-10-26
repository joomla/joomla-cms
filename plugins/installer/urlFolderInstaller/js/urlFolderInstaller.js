Joomla.submitbuttonall = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation 
		if (form.install_all.value == "")
		{
			alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'));
		}
		// test if it is an https url or http url 
		else if ((form.install_all.value.startsWith("http") == true) || (form.install_all.value.startsWith("https") == true))
		{
			jQuery('#loading').css('display', 'block');
			form.install_url.value = form.install_all.value;
			form.installtype.value = 'url';
			form.submit();
		}
		else
		{
			jQuery('#loading').css('display', 'block');
	
			form.install_directory.value = form.install_all.value;
			form.installtype.value = 'folder';
			form.submit();
		}
	};