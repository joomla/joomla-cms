/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Voting plugin JavaScript behavior
 *
 * @package     Joomla
 * @since       __DEPLOY_VERSION__
 * @version     1.0
 */

(function ($, Joomla) {
    $(document).ready(function () {
        var form = $('#vote-submission');
        form.submit(function (e) {
            $.ajax({
                type: form.attr('method'),
                url: form.attr('action'),
                data: form.serialize(),
                success: function (data) {
                    var messageContainer = $('#vote-message'),
                        closeButton = $('<button>').attr('type', 'button').attr('class', 'close').attr('data-dismiss', 'alert').attr('aria-label', 'Close'),
                        closeX = $('<span>').attr('aria-hidden', 'true').text('×');

                    closeButton.append(closeX);

                    if (data.success) {
                        var response = data.data[0],
                            alertType = response.success ? 'success' : 'error',
                            alertMessage = response.message;

                        Joomla.replaceTokens(response.token);
                    } else {
                        var alertType = 'error',
                            alertMessage = data.message;
                    }

                    var alertDiv = jQuery('<div>').attr('class', 'alert alert-' + alertType).attr('role', 'alert');

                    alertDiv.append(closeButton).append(alertMessage);
                    messageContainer.append(alertDiv);
                },
                error: function (jqXHR, textStatus, error) {
                    Joomla.renderMessages(Joomla.ajaxErrorsMessages(jqXHR, textStatus, error));
                }
            });

            e.preventDefault();
        });
    });
})(jQuery, Joomla);
