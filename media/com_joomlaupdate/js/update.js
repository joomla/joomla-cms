/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Original code from Nicholas K. Dionysopoulos
 */

(function() {
	"use strict";

	Joomla = window.Joomla || {};
	Joomla.stat_total = 0;
	Joomla.stat_files = 0;
	Joomla.stat_inbytes = 0;
	Joomla.stat_outbytes = 0;

	/**
	 * An extremely simple error handler, dumping error messages to screen
	 *
	 * @param error The error message string
	 */
	Joomla.error_callback = function(error) {
		Joomla.renderMessages({"ERROR": error});
		throw new Error("ERROR:\n"+error)
	};

	/**
	 * Performs an encrypted AJAX request and returns the parsed JSON output.
	 * The window.ajax_url is used as the AJAX proxy URL.
	 * If there is no errorCallback, the window.error_callback is used.
	 *
	 * @param   {object}    data             An object with the query data, e.g. a serialized form
	 * @param   {function}  successCallback  A function accepting a single object parameter, called on success
	 * @param   {function}  errorCallback    A function accepting a single string parameter, called on failure
	 */
	Joomla.doEncryptedAjax = function(data, successCallback, errorCallback) {
		var json = JSON.stringify(data);
		if( joomlaupdate_password.length > 0 ) {
			json = AesCtr.encrypt( json, joomlaupdate_password, 128 );
		}
		var post_data = {
			'json':     json
		};

		// @TODO use Joomla.request
		var structure = {
				    type: "POST",
				    url: joomlaupdate_ajax_url,
				    cache: false,
				    data: post_data,
				    timeout: 600000,

				    success: function(msg, responseXML) {
					    // Initialize
					    var junk = null;
					    var message = "";

					    // Get rid of junk before the data
					    var valid_pos = msg.indexOf('###');

					    if( valid_pos == -1 ) {
						    // Valid data not found in the response
						    msg = 'Invalid AJAX data:\n' + msg;

						    if (errorCallback == null) {
							    if(Joomla.error_callback != null) {
								    Joomla.error_callback(msg);
							    }
						    } else {
							    errorCallback(msg);
						    }

						    return;
					    } else if( valid_pos != 0 ) {
						    // Data is prefixed with junk
						    junk = msg.substr(0, valid_pos);
						    message = msg.substr(valid_pos);
					    } else {
						    message = msg;
					    }

					    message = message.substr(3); // Remove triple hash in the beginning

					    // Get of rid of junk after the data
					    var valid_pos = message.lastIndexOf('###');

					    message = message.substr(0, valid_pos); // Remove triple hash in the end

					    // Decrypt if required
					    var data = null;
					    if( joomlaupdate_password.length > 0 ) {
						    try {
							    var data = JSON.parse(message);
						    } catch(err) {
							    message = AesCtr.decrypt(message, joomlaupdate_password, 128);
						    }
					    }

					    try {
						    if (empty(data)) {
							    data = JSON.parse(message);
						    }
					    } catch(err) {
						    var msg = err.message + "\n<br>\n<pre>\n" + message + "\n</pre>";

						    if (errorCallback == null) {
							    if (error_callback != null) {
								    error_callback(msg);
							    }
						    } else {
							    errorCallback(msg);
						    }

						    return;
					    }

					    // Call the callback function
					    successCallback(data);
				    },

				    error: function(req) {
					    var message = 'AJAX Loading Error: ' + req.statusText;

					    if(errorCallback == null) {
						    if (error_callback != null) {
							    error_callback(message);
						    }
					    } else {
						    errorCallback(message);
					    }
				    }
			    };

		// @TODO use Joomla.request
		jQuery.ajax( structure );
	};

	/**
	 * Pings the update script (making sure its executable)
	 */
	Joomla.pingExtract = function() {
		// Reset variables
		Joomla.stat_files = 0;
		Joomla.stat_inbytes = 0;
		Joomla.stat_outbytes = 0;

		// Do AJAX post
		var post = {task : 'ping'};

		Joomla.doEncryptedAjax(post,
			function(data) {
				Joomla.startExtract(data);
			});
	};

	Joomla.startExtract = function() {
		console.log("started");
		// Reset variables
		Joomla.stat_files = 0;
		Joomla.stat_inbytes = 0;
		Joomla.stat_outbytes = 0;

		Joomla.doEncryptedAjax({ task : 'startRestore' }, function(data){
			Joomla.stepExtract(data);
		});
	};

	Joomla.stepExtract = function(data) {
		if(data.status == false) {
			// handle failure
			Joomla.error_callback(data.message);

			return;
		}

		if( !empty(data.Warnings) ) {
			// @todo Handle warnings
			/**
			$.each(data.Warnings, function(i, item){
				$('#warnings').append($(document.createElement('div')).html(item));
				$('#warningsBox').show('fast');
			});
			 **/
		}

		if (!empty(data.factory)) {
			extract_factory = data.factory;
		}

		if(data.done) {
			Joomla.finalizeUpdate();
		} else {
			// Add data to variables
			Joomla.stat_inbytes += data.bytesIn;
			Joomla.stat_percent = (stat_inbytes * 100) / joomlaupdate_totalsize;

			// Update GUI
			Joomla.stat_inbytes += data.bytesIn;
			Joomla.stat_outbytes += data.bytesOut;
			Joomla.stat_files += data.files;

			if (Joomla.stat_percent < 100) {
				document.querySelector('#progress-bar').style.width = Joomla.stat_percent + '%';
				document.querySelector('#progress-bar').setAttribute('aria-valuenow', Joomla.stat_percent);
			} else if (stat_percent > 100) {
				Joomla.stat_percent = 100;
				document.querySelector('#progress-bar').style.width = Joomla.stat_percent + '%';
				document.querySelector('#progress-bar').setAttribute('aria-valuenow', Joomla.stat_percent);
			} else {
				document.querySelector('#progress-bar').classList.remove('bar-success');
			}

			document.querySelector('#extpercent').innerText = Joomla.stat_percent.toFixed(1);
			document.querySelector('#extbytesin').innerText = Joomla.stat_inbytes;
			document.querySelector('#extbytesout').innerText = Joomla.stat_outbytes;
			document.querySelector('#extfiles').innerText = data.files;

			// Do AJAX post
			var post = {
				task: 'stepRestore',
				factory: data.factory
			};
			Joomla.doEncryptedAjax(post, function(data){
				Joomla.stepExtract(data);
			});
		}
	};

	finalizeUpdate = function () {
		// Do AJAX post
		var post = { task : 'finalizeRestore', factory: window.factory };
		Joomla.doEncryptedAjax(post, function(data){
			window.location = joomlaupdate_return_url;
		});
	};


	/**
	 * Is a variable empty?
	 *
	 * @param   {mixed}  mixed_var  The variable
	 *
	 * @returns {boolean}  True if empty
	 */
	Joomla.empty = function(mixed_var) {
		var key;

		if (   mixed_var === ""
			|| mixed_var === 0
			|| mixed_var === "0"
			|| mixed_var === null
			|| mixed_var === false
			|| typeof mixed_var === 'undefined'){
			return true;
		}

		if (typeof mixed_var == 'object') {
			for (key in mixed_var) {
				return false;
			}

			return true;
		}

		return false;
	}
});
