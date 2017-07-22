Joomla.submitbuttonUpload = function() {
	var form = document.getElementById("uploadForm");

	// do field validation
	if (form.install_package.value == "") {
		alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'));
	}
	else
	{
		document.getElementById("loading").style.display = "block";

		form.submit();
	}
};

// Add spindle-wheel for installations:
document.addEventListener('DOMContentLoaded', function() {
	var outerDiv = document.getElementById("joomlaupdate-wrapper"),
	    el = document.getElementById("loading"),
		position = outerDiv.getBoundingClientRect();

	el.style.top = position.top;
	el.style.left = 0;
	el.style.width = '100%';
	el.style.height = '100%';
	el.style.display = 'none';
	el.style.marginTop = '-10px';
});