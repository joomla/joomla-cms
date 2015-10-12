/**
 * @package     Joomla.Installation
 * @subpackage  JavaScript
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

var Installation = function(_container, _base) {
    var $, container, busy, spinner, baseUrl, view;

    /**
     * Initializes JavaScript events on each request, required for AJAX
     */
    var pageInit = function() {
        // Attach the validator
        $('form.form-validate').each(function(index, form) {
            document.formvalidator.attachToForm(form);
        });
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

        spinner.show(true);
        busy = true;
        Joomla.removeMessages();
        var data = 'format: json&' + $form.serialize();

        $.ajax({
            type : "POST",
            url : baseUrl,
            data : data,
            dataType : 'json'
        }).done(function(r) {
            Joomla.replaceTokens(r.token);
            if (r.messages) {
                Joomla.renderMessages(r.messages);
            }
            var lang = $('html').attr('lang');
            if (r.lang !== null && lang.toLowerCase() === r.lang.toLowerCase()) {
                Install.goToPage(r.data.view, true);
            } else {
                window.location = baseUrl + '?view=' + r.data.view;
            }
        }).fail(function(xhr) {
            spinner.hide(true);
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

        spinner.show(true);
        busy = true;
        Joomla.removeMessages();
        var data = 'format: json&' + $form.serialize();

        $.ajax({
            type : "POST",
            url : baseUrl,
            data : data,
            dataType : 'json'
        }).done(function(r) {
            Joomla.replaceTokens(r.token);
            if (r.messages) {
                Joomla.renderMessages(r.messages);
            }
            var lang = $('html').attr('lang');
            if (lang.toLowerCase() === r.lang.toLowerCase()) {
                Install.goToPage(r.data.view, true);
            } else {
                window.location = baseUrl + '?view=' + r.data.view;
            }
        }).fail(function(xhr) {
            spinner.hide(true);
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
            spinner.show(true);
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

            spinner.hide(true);
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
        var $form = $('#adminForm');
        var $tr = $('#install_' + task);
        var $taskSpinner = $tr.find('.spinner');
        var data = 'format: json&' + $form.serialize();

        $progress.css('width', parseFloat($progress.get(0).style.width) + step_width + '%');
        $tr.addClass('active');
        $taskSpinner.css('visibility', 'visible');

        $.ajax({
            type : "POST",
            url : baseUrl + '?task=Install' + task,
            data : data,
            dataType : 'json'
        }).done(function(r) {

            Joomla.replaceTokens(r.token);
            if (r.messages) {
                Joomla.renderMessages(r.messages);
                Install.goToPage(r.data.view, true);
            } else {
                $progress.css('width', parseFloat($progress.get(0).style.width) + (step_width * 10) + '%');
                $tr.removeClass('active');
                $taskSpinner.css('visibility', 'hidden');

                install(tasks, step_width);
            }

        }).fail(function(xhr) {
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
        $ = jQuery.noConflict();
        busy = false;
        container = _container;
        spinner = new Spinner(container);
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
