/**
 *  @package    AkeebaCMSUpdate
 *  @copyright  Copyright (c)2010-2014 Nicholas K. Dionysopoulos
 *  @license    GNU General Public License version 3, or later
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

var stat_total = 0;
var stat_files = 0;
var stat_inbytes = 0;
var stat_outbytes = 0;

/**
 * An extremely simple error handler, dumping error messages to screen
 *
 * @param error The error message string
 */
function error_callback(error)
{
	alert("ERROR:\n"+error);
}

/**
 * Performs an encrypted AJAX request and returns the parsed JSON output.
 * The window.ajax_url is used as the AJAX proxy URL.
 * If there is no errorCallback, the window.error_callback is used.
 *
 * @param   object    data             An object with the query data, e.g. a serialized form
 * @param   function  successCallback  A function accepting a single object parameter, called on success
 * @param   function  errorCallback    A function accepting a single string parameter, called on failure
 */
doEncryptedAjax = function(data, successCallback, errorCallback)
{
	var json = JSON.stringify(data);
	if( joomlaupdate_password.length > 0 )
	{
		json = AesCtr.encrypt( json, joomlaupdate_password, 128 );
	}
	var post_data = {
		'json':     json
	};

	var structure =
	{
		type: "POST",
		url: joomlaupdate_ajax_url,
		cache: false,
		data: post_data,
		timeout: 600000,

		success: function(msg, responseXML)
		{
			// Initialize
			var junk = null;
			var message = "";

			// Get rid of junk before the data
			var valid_pos = msg.indexOf('###');

			if( valid_pos == -1 )
			{
				// Valid data not found in the response
				msg = 'Invalid AJAX data:\n' + msg;

				if (errorCallback == null)
				{
					if(error_callback != null)
					{
						error_callback(msg);
					}
				}
				else
				{
					errorCallback(msg);
				}

				return;
			}
			else if( valid_pos != 0 )
			{
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
			var data = null;
			if( joomlaupdate_password.length > 0 )
			{
				try
				{
					var data = JSON.parse(message);
				}
				catch(err)
				{
					message = AesCtr.decrypt(message, joomlaupdate_password, 128);
				}
			}

			try
			{
				if (empty(data))
				{
					data = JSON.parse(message);
				}
			}
			catch(err)
			{
				var msg = err.message + "\n<br/>\n<pre>\n" + message + "\n</pre>";

				if (errorCallback == null)
				{
					if (error_callback != null)
					{
						error_callback(msg);
					}
				}
				else
				{
					errorCallback(msg);
				}

				return;
			}

			// Call the callback function
			successCallback(data);
		},

		error: function(req)
		{
			var message = 'AJAX Loading Error: ' + req.statusText;

			if(errorCallback == null)
			{
				if (error_callback != null)
				{
					error_callback(message);
				}
			}
			else
			{
				errorCallback(message);
			}
		}
	};

	jQuery.ajax( structure );
};

/**
 * Pings the update script (making sure its executable)
 */
pingExtract = function()
{
	// Reset variables
	this.stat_files = 0;
	this.stat_inbytes = 0;
	this.stat_outbytes = 0;

	// Do AJAX post
	var post = {task : 'ping'};

	this.doEncryptedAjax(post,
		function(data) {
			startExtract(data);
		});
};

startExtract = function()
{
	// Reset variables
	this.stat_files = 0;
	this.stat_inbytes = 0;
	this.stat_outbytes = 0;

	var post = { task : 'startRestore' };

	this.doEncryptedAjax(post, function(data){
		stepExtract(data);
	});
};

stepExtract = function(data)
{
	if(data.status == false)
	{
		// handle failure
		error_callback(data.message);

		return;
	}

	if( !empty(data.Warnings) )
	{
		// @todo Handle warnings
		/**
		 $.each(data.Warnings, function(i, item){
            $('#warnings').append(
                $(document.createElement('div'))
                    .html(item)
            );
            $('#warningsBox').show('fast');
        });
		 /**/
	}

	if (!empty(data.factory))
	{
		extract_factory = data.factory;
	}

	if(data.done)
	{
		finalizeUpdate();
	}
	else
	{
		// Add data to variables
		stat_inbytes += data.bytesIn;
		stat_percent = (stat_inbytes * 100) / joomlaupdate_totalsize;

		// Update GUI
		stat_inbytes += data.bytesIn;
		stat_outbytes += data.bytesOut;
		stat_files += data.files;

		if (stat_percent < 100)
		{
			jQuery('#progress-bar').css('width', stat_percent + '%').attr('aria-valuenow', stat_percent);
		}
		else if (stat_percent > 100)
		{
			stat_percent = 100;
			jQuery('#progress-bar').css('width', stat_percent + '%').attr('aria-valuenow', stat_percent);
		}
		else
		{
			jQuery('#progress-bar').removeClass('bar-success');
		}

		jQuery('#extpercent').text(stat_percent.toFixed(1) + '%');
		jQuery('#extbytesin').text(stat_inbytes);
		jQuery('#extbytesout').text(stat_outbytes);
		jQuery('#extfiles').text(data.files);

		// Do AJAX post
		post = {
			task: 'stepRestore',
			factory: data.factory
		};
		doEncryptedAjax(post, function(data){
			stepExtract(data);
		});
	}
};

finalizeUpdate = function ()
{
	// Do AJAX post
	var post = { task : 'finalizeRestore', factory: window.factory };
	doEncryptedAjax(post, function(data){
		window.location = joomlaupdate_return_url;
	});
};


/**
 * Is a variable empty?
 *
 * Part of php.js
 *
 * @see  http://phpjs.org/
 *
 * @param   mixed  mixed_var  The variable
 *
 * @returns  boolean  True if empty
 */
function empty (mixed_var)
{
	var key;

	if (mixed_var === "" ||
		mixed_var === 0 ||
		mixed_var === "0" ||
		mixed_var === null ||
		mixed_var === false ||
		typeof mixed_var === 'undefined'
	){
		return true;
	}

	if (typeof mixed_var == 'object')
	{
		for (key in mixed_var)
		{
			return false;
		}

		return true;
	}

	return false;
}

/**
 * Is the variable an array?
 *
 * Part of php.js
 *
 * @see  http://phpjs.org/
 *
 * @param   mixed  mixed_var  The variable
 *
 * @returns  boolean  True if it is an array or an object
 */
function is_array (mixed_var)
{
	var key = '';
	var getFuncName = function (fn) {
		var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);

		if (!name) {
			return '(Anonymous)';
		}

		return name[1];
	};

	if (!mixed_var)
	{
		return false;
	}

	// BEGIN REDUNDANT
	this.php_js = this.php_js || {};
	this.php_js.ini = this.php_js.ini || {};
	// END REDUNDANT

	if (typeof mixed_var === 'object')
	{
		if (this.php_js.ini['phpjs.objectsAsArrays'] &&  // Strict checking for being a JavaScript array (only check this way if call ini_set('phpjs.objectsAsArrays', 0) to disallow objects as arrays)
			(
			(this.php_js.ini['phpjs.objectsAsArrays'].local_value.toLowerCase &&
			this.php_js.ini['phpjs.objectsAsArrays'].local_value.toLowerCase() === 'off') ||
			parseInt(this.php_js.ini['phpjs.objectsAsArrays'].local_value, 10) === 0)
		) {
			return mixed_var.hasOwnProperty('length') && // Not non-enumerable because of being on parent class
			!mixed_var.propertyIsEnumerable('length') && // Since is own property, if not enumerable, it must be a built-in function
			getFuncName(mixed_var.constructor) !== 'String'; // exclude String()
		}

		if (mixed_var.hasOwnProperty)
		{
			for (key in mixed_var) {
				// Checks whether the object has the specified property
				// if not, we figure it's not an object in the sense of a php-associative-array.
				if (false === mixed_var.hasOwnProperty(key)) {
					return false;
				}
			}
		}

		// Read discussion at: http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_is_array/
		return true;
	}

	return false;
}
