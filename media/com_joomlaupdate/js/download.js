(function($, document, window) {
	var JoomlaUpdateDownload = {
		ajaxUrl: null,
		returnUrl: null,
		nextFrag: -1,
		minWait: 3000,
	};

	JoomlaUpdateDownload.run = function()
	{
		var options = Joomla.getOptions('com_joomlaupdate');
		JoomlaUpdateDownload.ajaxUrl = options.ajaxUrl;
		JoomlaUpdateDownload.returnUrl = options.returnUrl;

		document.getElementById('download-error').style.display = 'none';

		var progressBar         = document.getElementById('progress-bar');
		progressBar.classList.remove('active', 'progress-striped', 'bar-success', 'bar-danger');
		progressBar.style.width = '0%';
		progressBar.attributes['aria-valuenow'] = '0';
		progressBar.innerText = '0%';
		document.getElementById('dlpercent').innerText = '0%';
		document.getElementById('dlbytesin').innerText = '0';
		document.getElementById('dlbytestotal').innerText = '0';

		JoomlaUpdateDownload.nextFrag = -1;

		window.setTimeout(JoomlaUpdateDownload.step, 50);
	}

	JoomlaUpdateDownload.error = function(message)
	{
		var progressBar         = document.getElementById('progress-bar');
		progressBar.classList.remove('active', 'progress-striped', 'bar-success', 'bar-danger');
		progressBar.classList.add('bar-danger');

		document.getElementById('download-error').style.display = '';
		document.getElementById('dlerror').innerHTML = message;
	}

	JoomlaUpdateDownload.step = function()
	{
		var startTime = new Date();

		$.ajax({
			type: 'GET',
			url: JoomlaUpdateDownload.ajaxUrl + '&frag=' + JoomlaUpdateDownload.nextFrag,
			cache: false,
			timeout: 600000,
			success: function(msg, responseXML)
					 {
						 var message = '';
						 var progressBar = document.getElementById('progress-bar');

						 try
						 {
							 var data = JSON.parse(msg);
						 }
						 catch (e)
						 {
							 message = e.message + "\n<br/>\n<pre>\n" + msg + "\n</pre>";

							 JoomlaUpdateDownload.error(message);
						 }

						 if (data.error)
						 {
							 message = data.message ?? 'Error';

							 JoomlaUpdateDownload.error(message);
						 }

						 if (data.done)
						 {
							 progressBar.classList.remove('active', 'progress-striped', 'bar-success', 'bar-danger');
							 progressBar.classList.add('bar-success');
							 progressBar.style.width                 = '100%';
							 progressBar.attributes['aria-valuenow'] = '100';
							 progressBar.innerText                   = '100%';

							 window.location = JoomlaUpdateDownload.returnUrl;
						 }

						 JoomlaUpdateDownload.nextFrag = data.frag;
						 var downloaded = data.downloaded * 1;
						 var total = data.totalSize * 1;
						 var percentage = (total > 0) ? (100 * downloaded / total) : null;

						 if (percentage)
						 {
							 progressBar.style.width = percentage.toFixed(1) + '%';
							 progressBar.attributes['aria-valuenow'] = percentage.toFixed(1) + '';
							 progressBar.innerText = percentage.toFixed(1) + '%';
							 document.getElementById('dlpercent').innerText = percentage.toFixed(1) + '%';
							 document.getElementById('dlbytestotal').innerText = total + '';
						 }
						 else
						 {
							 progressBar.style.width = '100%';
							 progressBar.innerText = '';
							 document.getElementById('dlpercent').innerText = '';
							 document.getElementById('dlbytestotal').innerText = '';
						 }

						 document.getElementById('dlbytesin').innerText = downloaded + '';

						 var endTime = new Date();
						 var timeDiff = endTime - startTime;
						 var waitFor = JoomlaUpdateDownload.minWait - timeDiff;

						 if (waitFor < 50)
						 {
							 waitFor = 50;
						 }

						 setTimeout(JoomlaUpdateDownload.step, waitFor);
					 },
			error: function (req)
				   {
					   JoomlaUpdateDownload.error('AJAX Loading Error: ' + req.statusText);
				   }
		});
	}

	// Run on document ready
	document.addEventListener( "DOMContentLoaded", function() {
		document.getElementById('dlrestart').addEventListener('click', JoomlaUpdateDownload.run);
		document.getElementById('dlcancel').addEventListener('click', function() {
			window.location = 'index.php?option=com_joomlaupdate'
		});
		JoomlaUpdateDownload.run();
	} );
})(jQuery, document, window);
