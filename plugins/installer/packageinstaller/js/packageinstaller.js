
	Joomla.submitbutton_package = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_package.value == "") 
		{
			alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'));
		}
		else
		{
			jQuery('#loading').css('display', 'block');

			form.installtype.value = 'upload';
			form.submit();
		}
	};