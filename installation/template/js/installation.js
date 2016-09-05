/**
 * @package     Joomla.Installation
 * @subpackage  JavaScript
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

var Installation = function(_container, _base) {
	var $, container, busy, baseUrl, view;

	/**
	 * Initializes JavaScript events on each request, required for AJAX
	 */
	var pageInit = function() {
		// Attach the validator
		$('form.form-validate').each(function(index, form) {
			document.formvalidator.attachToForm(form);
		});

		// Create and append the loading layer.
		Joomla.loadingLayer("load");
	}

	/**
	 * Method to check if the installation process is busy.
	 *
	 * @return  boolean  True if busy, false otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	var isBusy = function() {
		if (busy)
		{
			// @todo move to the installation language.
			Joomla.JText._('INSTL_PROCESS_BUSY', 'Process is in progress. Please wait...')

			// Render message.
			Joomla.renderMessages({'notice': [Joomla.JText._('INSTL_PROCESS_BUSY')]});
			window.scrollTo(0, 0);

			return true;
		}

		busy = true;

		return false;
	}

	/**
	 * Method to use if ajax request failed.
	 *
	 * @param   object  xhr         xhr object.
	 * @param   string  textStatus  Type of error that occurred.
	 * @param   string  error       Textual portion of the HTTP status.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	var ajaxFail = function(xhr, textStatus, error) {
		// Hide the Joomla loading layer.
		Joomla.loadingLayer('hide');

		// Render messages, if any.
		Joomla.renderMessages(Joomla.ajaxErrorsMessages(xhr, textStatus, error));
		window.scrollTo(0, 0);

		busy = false;

		try
		{
			var response = $.parseJSON(xhr.responseText);

			Joomla.replaceTokens(response.token);

			// Render message.
			Joomla.renderMessages({'warning': [response.message]});
			window.scrollTo(0, 0);
		}
		catch (e)
		{
			// Do nothing.
		}
	}

	/**
	 * Method to use if ajax request succeded.
	 *
	 * @param   object  response  The response object.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	var ajaxDone = function(response) {
		// Hide the Joomla loading layer.
		Joomla.loadingLayer('hide');

		// Replace the tokens.
		Joomla.replaceTokens(response.token);

		// Render messages, if any.
		if (typeof response.messages == 'object' && response.messages !== null)
		{
			Joomla.renderMessages(response.messages);
			window.scrollTo(0, 0);
		}

		busy = false;
	}

	/**
	 * Method to submit a form from the installer via AJAX
	 *
	 * @return {Boolean}
	 */
	var submitform = function() {
		// Check if installation process is busy.
		if (isBusy())
		{
			return false;
		}

		// Remove js messages, if they exist.
		Joomla.removeMessages();

		// Show the Joomla loading layer.
		Joomla.loadingLayer('show');

		$.ajax({
			type     : 'POST',
			url      : baseUrl,
			data     : 'format=json&' + $(document.getElementById('adminForm')).serialize(),
			dataType : 'json'
		})
		.done(function(response) {
			// Treat ajax success response.
			ajaxDone(response);

			// Redirect to page.
			var lang = document.querySelector('html').getAttribute('lang');

			if (response.lang !== null && lang.toLowerCase() === response.lang.toLowerCase())
			{
				Install.goToPage(response.data.view, true);
			}
			else
			{
				window.location = baseUrl + '?view=' + response.data.view;
			}
		})
		.fail(function(xhr, textStatus, error) {
			// Treat ajax fail response.
			ajaxFail(xhr, textStatus, error);
		});

		return false;
	}

	/**
	 * Method to set the language for the installation UI via AJAX
	 *
	 * @return  boolean
	 */
	var setlanguage = function() {
		// Check if installation process is busy.
		if (isBusy())
		{
			return false;
		}

		// Remove js messages, if they exist.
		Joomla.removeMessages();

		// Show the Joomla loading layer.
		Joomla.loadingLayer('show');

		$.ajax({
			type     : 'POST',
			url      : baseUrl,
			data     : 'format=json&' + $(document.getElementById('languageForm')).serialize(),
			dataType : 'json'
		})
		.done(function(response) {
			// Treat ajax success response.
			ajaxDone(response);

			// Redirect to page.
			var lang = document.querySelector('html').getAttribute('lang');

			if (response.lang !== null && lang.toLowerCase() === response.lang.toLowerCase())
			{
				Install.goToPage(response.data.view, true);
			}
			else
			{
				window.location = baseUrl + '?view=' + response.data.view;
			}
		})
		.fail(function(xhr, textStatus, error) {
			// Treat ajax fail response.
			ajaxFail(xhr, textStatus, error);
		});

		return false;
	}

	/**
	 * Method to request a different page via AJAX
	 *
	 * @param  page        The name of the view to request
	 * @param  fromSubmit  True when the call is from form submit.
	 *
	 * @return boolean
	 */
	var goToPage = function(page, fromSubmit) {

		if (!fromSubmit)
		{
			// Remove js messages, if they exist.
			Joomla.removeMessages();

			// Show the Joomla loading layer.
			Joomla.loadingLayer('show');
		}

		$.ajax({
			type     : 'GET',
			url      : baseUrl,
			data     : 'tmpl=body&view=' + page,
			dataType : 'html'
		})
		.done(function(response) {
			// Treat ajax success response.
			ajaxDone(response);

			$('#' + container).html(response);
			view = page;

			// Attach JS behaviors to the newly loaded HTML
			pageInit();
			initElements();
		})
		.fail(function(xhr, textStatus, error) {
			// Treat ajax fail response.
			ajaxFail(xhr, textStatus, error);
		});

		return false;
	}

	/**
	 * Executes the required tasks to complete site installation
	 *
	 * @param tasks       An array of install tasks to execute
	 * @param step_width  The width of the progress bar element
	 */
	var install = function(tasks, step_width) {
		var $progress = $('#install_progress').find('.bar');

		if (!tasks.length) {
			$progress.css('width', parseFloat($progress.get(0).style.width) + (step_width * 3) + '%');
			goToPage('complete');
			return;
		}

		if (!step_width) {
			var step_width = (100 / tasks.length) / 11;
		}

		var task = tasks.shift();
		var $tr = $('#install_' + task);

		$progress.css('width', parseFloat($progress.get(0).style.width) + step_width + '%');
		$tr.addClass('active');

		// Show the Joomla loading layer.
		Joomla.loadingLayer('show');

		$.ajax({
			type     : 'POST',
			url      : baseUrl,
			data     : 'format=json&task=Install' + task + '&' + $('#adminForm').serialize(),
			dataType : 'json'
		})
		.done(function(response) {
			// Treat ajax success response.
			ajaxDone(response);

			// If there are messages go to page.
			if (response.messages)
			{
				Install.goToPage(response.data.view, true);
			}
			// Else continue with install process.
			else
			{
				$progress.css('width', parseFloat($progress.get(0).style.width) + (step_width * 10) + '%');
				$tr.removeClass('active');
				install(tasks, step_width);
			}

		})
		.fail(function(xhr, textStatus, error) {
			// Treat ajax fail response.
			ajaxFail(xhr, textStatus, error);

			// Return to summary page.
			Install.goToPage('summary');
		});
	}

	/**
	 * Method to detect the FTP root via AJAX request.
	 *
	 * @param el  The page element requesting the event
	 */
	var detectFtpRoot = function(el) {
		var $el = $(el), data = 'format: json&' + $el.closest('form').serialize();

		$el.attr('disabled', 'disabled');
		$.ajax({
			type : "POST",
			url : baseUrl + '?task=detectftproot',
			data : data,
			dataType : 'json'
		}).done(function(r) {
			if (r) {
				Joomla.replaceTokens(r.token)
				if (r.error == false) {
					$('#jform_ftp_root').val(r.data.root);
				} else {
					alert(r.message);
				}
			}
			$el.removeAttr('disabled');
		}).fail(function(xhr) {
			try {
				var r = $.parseJSON(xhr.responseText);
				Joomla.replaceTokens(r.token);
				alert(xhr.status + ': ' + r.message);
			} catch (e) {
				alert(xhr.status + ': ' + xhr.statusText);
			}
		});
	}

	/**
	 * Method to verify the supplied FTP settings are valid via AJAX request.
	 *
	 * @param el  The page element requesting the event
	 */
	var verifyFtpSettings = function(el) {
		// make the ajax call
		var $el = $(el), data = 'format: json&' + $el.closest('form').serialize();

		$el.attr('disabled', 'disabled');

		$.ajax({
			type : "POST",
			url : baseUrl + '?task=verifyftpsettings',
			data : data,
			dataType : 'json'
		}).done(function(r) {
			if (r) {
				Joomla.replaceTokens(r.token)
				if (r.error == false) {
					alert(Joomla.JText._('INSTL_FTP_SETTINGS_CORRECT', 'Settings correct'));
				} else {
					alert(r.message);
				}
			}
			$el.removeAttr('disabled');
		}).fail(function(xhr) {
			try {
				var r = $.parseJSON(xhr.responseText);
				Joomla.replaceTokens(r.token);
				alert(xhr.status + ': ' + r.message);
			} catch (e) {
				alert(xhr.status + ': ' + xhr.statusText);
			}
		});
	}

	/**
	 * Method to remove the installation Folder after a successful installation.
	 *
	 * @param el  The page element requesting the event
	 */
	var removeFolder = function(el) {
		var $el = $(el), $languages = $("#languages"), $defaultError = $('#theDefaultError'), $defualtErrorMessage = $('#theDefaultErrorMessage'), data = 'format: json&' + $el.closest('form').serialize();

		if ($languages.length) {
			$languages.fadeOut();
		}

		$el.attr('disabled', 'disabled');
		$defaultError.hide();

		$.ajax({
			type : "POST",
			url : baseUrl + '?task=removefolder',
			data : data,
			dataType : 'json'
		}).done(function(r) {
			if (r) {
				Joomla.replaceTokens(r.token);
				if (r.error === false) {
					$el.val(r.data.text);
					$el.attr('onclick', '').unbind('click');
					$el.attr('disabled', 'disabled');
					// Stop keep alive requests
					window.keepAlive = function() {
					};
				} else {
					$defaultError.show();
					$defualtErrorMessage.html(r.message);
					$el.removeAttr('disabled');
				}
			} else {
				$defaultError.show();
				$defualtErrorMessage.html(r);
				$el.attr('disabled', 'disabled');
			}
		}).fail(function(xhr) {
			try {
				var r = $.parseJSON(xhr.responseText);
				Joomla.replaceTokens(r.token);
				$('#theDefaultError').show();
				$('#theDefaultErrorMessage').html(r.message);
			} catch (e) {
			}
			$el.removeAttr('disabled');
		});
	}

	var toggle = function(id, el, value) {
		var val = $('input[name="jform[' + el + ']"]:checked').val(), $id = $('#' + id);
		if (val === value.toString()) {
			$id.show();
		} else {
			$id.hide();
		}
	}

	/**
	 * Initializes the Installation class
	 *
	 * @param _container  The name of the container which the view is rendered in
	 * @param _base       The URL of the current page
	 */
	var initialize = function(_container, _base) {
		$         = jQuery.noConflict();
		busy      = false;
		container = _container;
		baseUrl   = _base;
		view      = '';

		pageInit();
	}
	initialize(_container, _base);

	return {
		submitform        : submitform,
		setlanguage       : setlanguage,
		goToPage          : goToPage,
		install           : install,
		detectFtpRoot     : detectFtpRoot,
		verifyFtpSettings : verifyFtpSettings,
		removeFolder      : removeFolder,
		toggle            : toggle
	}
}
