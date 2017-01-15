/**
 * @package     Joomla.Installation
 * @subpackage  JavaScript
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved.
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
     * Method to submit a form from the installer via AJAX
     *
     * @return {Boolean}
     */
    var submitform = function() {
        var $form = $('#adminForm');

        if (busy) {
            alert(Joomla.JText._('INSTL_PROCESS_BUSY', 'Process is in progress. Please wait...'));
            return false;
        }

        Joomla.loadingLayer("show");
        busy = true;
        Joomla.removeMessages();
        var data = $form.serialize();

        $.ajax({
            type: "POST",
            url: baseUrl,
            data: data,
            dataType: 'json'
        }).done(function (r) {
            Joomla.replaceTokens(r.token);

            if (r.messages) {
                Joomla.renderMessages(r.messages);
            }

            if (r.error) {
                Joomla.renderMessages({'error': [r.message]});
                Joomla.loadingLayer("hide");
                busy = false;
            } else {
                var lang = $('html').attr('lang');

                if (r.lang !== null && lang.toLowerCase() === r.lang.toLowerCase()) {
                    Install.goToPage(r.data.view, true);
                } else {
                    window.location = baseUrl + '?view=' + r.data.view;
                }
            }
        }).fail(function (xhr) {
            Joomla.loadingLayer("hide");
            busy = false;
            try {
                var r = $.parseJSON(xhr.responseText);
                Joomla.replaceTokens(r.token);
                alert(r.message);
            } catch (e) {
            }
        });

        return false;
    }

    /**
     * Method to set the language for the installation UI via AJAX
     *
     * @return {Boolean}
     */
    var setlanguage = function() {
        var $form = $('#languageForm');

        if (busy) {
            alert(Joomla.JText._('INSTL_PROCESS_BUSY', 'Process is in progress. Please wait...'));
            return false;
        }

        Joomla.loadingLayer("show");
        busy = true;
        Joomla.removeMessages();
        var data = $form.serialize();

        $.ajax({
            type: "POST",
            url: baseUrl,
            data: data,
            dataType: 'json'
        }).done(function (r) {
            Joomla.replaceTokens(r.token);

            if (r.messages) {
                Joomla.renderMessages(r.messages);
            }

            if (r.error) {
                Joomla.renderMessages({'error': [r.message]});
                Joomla.loadingLayer("hide");
                busy = false;
            } else {
                var lang = $('html').attr('lang');

                if (lang.toLowerCase() === r.lang.toLowerCase()) {
                    Install.goToPage(r.data.view, true);
                } else {
                    window.location = baseUrl + '?view=' + r.data.view;
                }
            }
        }).fail(function (xhr) {
            Joomla.loadingLayer("hide");
            busy = false;
            try {
                var r = $.parseJSON(xhr.responseText);
                Joomla.replaceTokens(r.token);
                alert(r.message);
            } catch (e) {
            }
        });

        return false;
    }

    /**
     * Method to request a different page via AJAX
     *
     * @param  page        The name of the view to request
     * @param  fromSubmit  Unknown use
     *
     * @return {Boolean}
     */
    var goToPage = function(page, fromSubmit) {
        if (!fromSubmit) {
            Joomla.removeMessages();
            Joomla.loadingLayer("show");
        }

        $.ajax({
            type : "GET",
            url : baseUrl + '?tmpl=body&view=' + page,
            dataType : 'html'
        }).done(function(result) {
            $('#' + container).html(result);
            view = page;

            // Attach JS behaviors to the newly loaded HTML
            pageInit();

            Joomla.loadingLayer("hide");
            busy = false;

            initElements();
        });

        return false;
    }

    /**
     * Executes the required tasks to complete site installation
     *
     * @param tasks       An array of install tasks to execute
     * @param step_width  The width of the progress bar element
     */
    var install = function (tasks, step_width) {
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
        var $form = $('#adminForm');
        var $tr = $('#install_' + task);
        var data = $form.serialize();

        $progress.css('width', parseFloat($progress.get(0).style.width) + step_width + '%');
        $tr.addClass('active');
        Joomla.loadingLayer("show");

        $.ajax({
            type: "POST",
            url: baseUrl + '?format=json&task=Install' + task,
            data: data,
            dataType: 'json'
        }).done(function (r) {
            Joomla.replaceTokens(r.token);

            if (r.error) {
                if (r.messages) {
                    Joomla.renderMessages(r.messages);
                }

                Joomla.renderMessages({'error': [r.message]});

                Install.goToPage('summary', true);
            } else {
                if (r.messages) {
                    Joomla.renderMessages(r.messages);
                    Install.goToPage(r.data.view, true);
                } else {
                    $progress.css('width', parseFloat($progress.get(0).style.width) + (step_width * 10) + '%');
                    $tr.removeClass('active');
                    Joomla.loadingLayer("hide");

                    install(tasks, step_width);
                }
            }
        }).fail(function (xhr) {
            Joomla.renderMessages([['', Joomla.JText._('JLIB_DATABASE_ERROR_DATABASE_CONNECT', 'A Database error occurred.')]]);
            Install.goToPage('summary');

            try {
                var r = $.parseJSON(xhr.responseText);
                Joomla.replaceTokens(r.token);
                alert(r.message);
            } catch (e) {
            }
        });
    }

    /**
     * Method to detect the FTP root via AJAX request.
     *
     * @param el  The page element requesting the event
     */
    var detectFtpRoot = function(el) {
        var $el = $(el), data = $el.closest('form').serialize();

        $el.attr('disabled', 'disabled');
        $.ajax({
            type : "POST",
            url : baseUrl + '?format=json&task=detectftproot',
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
        var $el = $(el), data = $el.closest('form').serialize();

        $el.attr('disabled', 'disabled');

        $.ajax({
            type : "POST",
            url : baseUrl + '?format=json&task=verifyftpsettings',
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
        $ = jQuery.noConflict();
        busy = false;
        container = _container;
        baseUrl = _base;
        view = '';

        pageInit();
    }
    initialize(_container, _base);

    return {
        submitform : submitform,
        setlanguage : setlanguage,
        goToPage : goToPage,
        install : install,
        detectFtpRoot : detectFtpRoot,
        verifyFtpSettings : verifyFtpSettings,
        removeFolder : removeFolder,
        toggle : toggle
    }
}

/**
 * Initializes the elements
 */
function initElements()
{
	(function($){
		$('.hasTooltip').tooltip();

		// Chosen select boxes
		$('select').chosen({
			disable_search_threshold : 10,
			allow_single_deselect : true
		});

		// Turn radios into btn-group
		$('.radio.btn-group label').addClass('btn');

		$('fieldset.btn-group').each(function() {
			var $self = $(this);
			// Handle disabled, prevent clicks on the container, and add disabled style to each button
			if ($self.prop('disabled'))
			{
				$self.css('pointer-events', 'none').off('click');
				$self.find('.btn').addClass('disabled');
			}
		});

		$('.btn-group label:not(.active)').click(function()
		{
			var label = $(this);
			var input = $('#' + label.attr('for'));

			if (!input.prop('checked'))
			{
				label.closest('.btn-group').find('label').removeClass('active btn-success btn-danger btn-primary');

				if (label.closest('.btn-group').hasClass('btn-group-reverse'))
				{
					if (input.val() == '')
					{
						label.addClass('active btn-primary');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-danger');
					}
					else
					{
						label.addClass('active btn-success');
					}
				}
				else
				{
					if (input.val() == '')
					{
						label.addClass('active btn-primary');
					}
					else if (input.val() == 0)
					{
						label.addClass('active btn-success');
					}
					else
					{
						label.addClass('active btn-danger');
					}
				}
				input.prop('checked', true);
			}
		});

		$('.btn-group input[checked="checked"]').each(function()
		{
			var $self  = $(this);
			var attrId = $self.attr('id');

			if ($self.hasClass('btn-group-reverse'))
			{
				if ($self.val() == '')
				{
					$('label[for="' + attrId + '"]').addClass('active btn-primary');
				}
				else if ($self.val() == 0)
				{
					$('label[for="' + attrId + '"]').addClass('active btn-danger');
				}
				else
				{
					$('label[for="' + attrId + '"]').addClass('active btn-success');
				}
			}
			else
			{
				if ($self.val() == '')
				{
					$('label[for="' + attrId + '"]').addClass('active btn-primary');
				}
				else if ($self.val() == 0)
				{
					$('label[for="' + attrId + '"]').addClass('active btn-success');
				}
				else
				{
					$('label[for="' + attrId + '"]').addClass('active btn-danger');
				}
			}
		});
	})(jQuery);
}

// Init on dom content loaded event
document.addEventListener('DOMContentLoaded', function() {

	// Init the elements
	initElements();

	// Init installation
	var installOptions  = Joomla.getOptions('system.installation'),
	    installurl = installOptions.url ? installOptions.url.replace(/&amp;/g, '&') : 'index.php';

	window.Install = new Installation('container-installation', installurl);
});
