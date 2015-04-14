jQuery(function ($) {
	$em = $('#extraction_method');
	if ($em.length) {
		$em.on('change', function () {
			if ($em.val() === 'direct') {
				document.getElementById('row_ftp_hostname').style.display = 'none';
				document.getElementById('row_ftp_port').style.display = 'none';
				document.getElementById('row_ftp_username').style.display = 'none';
				document.getElementById('row_ftp_password').style.display = 'none';
				document.getElementById('row_ftp_directory').style.display = 'none';
			} else {
				document.getElementById('row_ftp_hostname').style.display = 'table-row';
				document.getElementById('row_ftp_port').style.display = 'table-row';
				document.getElementById('row_ftp_username').style.display = 'table-row';
				document.getElementById('row_ftp_password').style.display = 'table-row';
				document.getElementById('row_ftp_directory').style.display = 'table-row';
			}
		});
	}

	$('button.submit').on('click', function() {
		$('div.download_message').show();
		var $el = $('div.joomlaupdate_spinner');
		$el.attr('spinner', {class: 'joomlaupdate_spinner'});
		$el.get(0).spin();
	})
});
