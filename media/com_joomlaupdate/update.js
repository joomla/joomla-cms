var joomlaupdate_error_callback = dummy_error_handler;
var	joomlaupdate_stat_inbytes = 0;
var	joomlaupdate_stat_outbytes = 0;
var	joomlaupdate_stat_files = 0;
var joomlaupdate_factory = null;

/**
 * An extremely simple error handler, dumping error messages to screen
 * 
 * @param error The error message string
 */
function dummy_error_handler(error)
{
	alert("ERROR:\n"+error);
}

/**
 * Performs an AJAX request and returns the parsed JSON output.
 * 
 * @param data An object with the query data, e.g. a serialized form
 * @param successCallback A function accepting a single object parameter, called on success
 * @param errorCallback A function accepting a single string parameter, called on failure
 */
function doAjax(data, successCallback, errorCallback)
{
	var json = JSON.stringify(data);
	if( joomlaupdate_password.length > 0 )
	{
		json = AesCtr.encrypt( json, joomlaupdate_password, 128 );
	}
	var post_data = 'json='+encodeURIComponent(json);


	var structure =
	{
		onSuccess: function(msg, responseXML)
		{
			// Initialize
			var junk = null;
			var message = "";

			// Get rid of junk before the data
			var valid_pos = msg.indexOf('###');
			if( valid_pos == -1 ) {
				// Valid data not found in the response
				msg = 'Invalid AJAX data:\n' + msg;
				if(joomlaupdate_error_callback != null)
				{
					joomlaupdate_error_callback(msg);
				}
				return;
			} else if( valid_pos != 0 ) {
				// Data is prefixed with junk
				junk = msg.substr(0, valid_pos);
				message = msg.substr(valid_pos);
			}
			else
			{
				message = msg;
			}
			message = message.substr(3); // Remove triple hash in the beginning

			// Get of rid of junk after the data
			var valid_pos = message.lastIndexOf('###');
			message = message.substr(0, valid_pos); // Remove triple hash in the end
			// Decrypt if required
			if( joomlaupdate_password.length > 0 )
			{
				try {
					var data = JSON.parse(message);
				} catch(err) {
					message = AesCtr.decrypt(message, joomlaupdate_password, 128);
				}
			}

			try {
				var data = JSON.parse(message);
			} catch(err) {
				var msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";
				if(joomlaupdate_error_callback != null)
				{
					joomlaupdate_error_callback(msg);
				}
				return;
			}

			// Call the callback function
			successCallback(data);
		},
		onFailure: function(req) {
			var message = 'AJAX Loading Error: '+req.statusText;
			if(joomlaupdate_error_callback != null)
			{
				joomlaupdate_error_callback(msg);
			}
		}
	};

	var ajax_object = null;
	structure.url = joomlaupdate_ajax_url;
	ajax_object = new Request(structure);
	ajax_object.send(post_data);
}

/**
 * Pings the update script (making sure its executable!!)
 * @return
 */
function pingUpdate()
{
	// Reset variables
	joomlaupdate_stat_files = 0;
	joomlaupdate_stat_inbytes = 0;
	joomlaupdate_stat_outbytes = 0;

	// Do AJAX post
	var post = {task : 'ping'};
	doAjax(post, function(data){
		startUpdate(data);
	});
}

/**
 * Starts the update
 * @return
 */
function startUpdate()
{
	// Reset variables
	joomlaupdate_stat_files = 0;
	joomlaupdate_stat_inbytes = 0;
	joomlaupdate_stat_outbytes = 0;

	var post = { task : 'startRestore' };
	doAjax(post, function(data){
		processUpdateStep(data);
	});
}

/**
 * Steps through the update
 * @param data
 * @return
 */
function processUpdateStep(data)
{
	if(data.status == false)
	{
		if(joomlaupdate_error_callback != null)
		{
			joomlaupdate_error_callback(data.message);
		}
	}
	else
	{
		if(data.done)
		{
			joomlaupdate_factory = data.factory;
			window.location = joomlaupdate_return_url;
		}
		else
		{
			// Add data to variables
			joomlaupdate_stat_inbytes += data.bytesIn;
			joomlaupdate_stat_outbytes += data.bytesOut;
			joomlaupdate_stat_files += data.files;

			// Display data
			document.getElementById('extbytesin').innerHTML = joomlaupdate_stat_inbytes;
			document.getElementById('extbytesout').innerHTML = joomlaupdate_stat_outbytes;
			document.getElementById('extfiles').innerHTML = joomlaupdate_stat_files; 

			// Do AJAX post
			post = {
				task: 'stepRestore',
				factory: data.factory
			};
			doAjax(post, function(data){
				processUpdateStep(data);
			});
		}
	}
}

window.addEvent('domready', function() {
	pingUpdate();
});