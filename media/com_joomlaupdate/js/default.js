jQuery(document).ready(function($) {
	$('#extraction_method').change(function(e){
		extractionMethodHandler('#extraction_method', 'row_ftp');
	});
	$('#upload_method').change(function(e){
		extractionMethodHandler('#upload_method', 'upload_ftp');
	});

	$('button.submit').on('click', function() {
		$('div.download_message').show();
	});
});

function extractionMethodHandler(target, prefix)
{
	jQuery(function ($) {
		$em = $(target);
		displayStyle = ($em.val() === 'direct') ? 'none' : 'table-row';

		document.getElementById(prefix + '_hostname').style.display = displayStyle;
		document.getElementById(prefix + '_port').style.display = displayStyle;
		document.getElementById(prefix + '_username').style.display = displayStyle;
		document.getElementById(prefix + '_password').style.display = displayStyle;
		document.getElementById(prefix + '_directory').style.display = displayStyle;
	});
}

	Joomla.submitbuttonUpload = function() {
		var form = document.getElementById('uploadForm');

		// do field validation
		if (form.install_package.value == '') {
			alert(Joomla.JText._('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE'), true);
		}
		else {
			form.submit();
		}
	};

	document.addEventListener('DOMContentLoaded', function() {

		var extractionMethod = document.getElementById('extraction_method'),
		    uploadMethod     = document.getElementById('upload_method'),
		    uploadButton     = document.getElementById('uploadButton'),
		    downloadMsg      = document.getElementById('downloadMessage');

		if (extractionMethod) {
			extractionMethod.addEventListener('change', function(event) {
				Joomla.extractionMethodHandler(extractionMethod, 'row_ftp');
			});
		}

		if (uploadMethod) {
			uploadMethod.addEventListener('change', function(event) {
				Joomla.extractionMethodHandler(uploadMethod, 'upload_ftp');
			});
		}

		if (uploadButton) {
			uploadButton.addEventListener('click', function(event) {
				if (downloadMsg) {
					downloadMsg.style.display = 'block';
				}
			});
		}

	});
