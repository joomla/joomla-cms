/**
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
(function ($) {

    let ajaxData = {
        'option': 'com_ajax',
        'group': 'quickicon',
        'plugin': 'SnoozeEOS',
        'format': 'json'
    }

    $('#system-message-container').on('click', '.eosnotify-snooze-btn', function (e) {
        let button = $(this);
        $.getJSON('index.php', ajaxData, function (response) {

                if (response.success) {
                    //let a = button.closest('button.joomla-alert--close').click();
                    const collection = document.getElementsByClassName("joomla-alert--close");
                    for (let i = 0; i < collection.length; i++) {
                        collection[i].click();
                    }
                }
            }
        );
        e.preventDefault();
    });

})(jQuery);







