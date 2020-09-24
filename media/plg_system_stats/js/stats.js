/**
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * stats javascript behavior
 *
 * To allow users to accept & configure stats sending
 *
 * @package     Joomla
 * @since       3.5.0
 * @version  1.0
 */

(function ($) {
	$(document).ready(function () {
		var ajaxData = {
			'option' : 'com_ajax',
			'group'  : 'system',
			'plugin' : 'renderStatsMessage',
			'format' : 'raw'
			},
			messageContainer = $('#system-message-container');

		/**
		 * Initialise events for the message container
		 *
		 * @return  void
		 */
		function initStatsEvents()
		{
			var globalContainer = messageContainer.find('.js-pstats-alert'),
				detailsContainer = messageContainer.find('.js-pstats-data-details');

			// Show details about the information being sent
			messageContainer.on('click', '.js-pstats-btn-details', function(e){
				detailsContainer.toggle(200);
				e.preventDefault();
			});

			// Always allow
			messageContainer.on('click', '.js-pstats-btn-allow-always', function(e){

				// Remove message
				globalContainer.hide(200);
				detailsContainer.remove();
				ajaxData.plugin = 'sendAlways';

				$.getJSON('index.php', ajaxData, function(response){});
				e.preventDefault();
			});

			// Allow once
			messageContainer.on('click', '.js-pstats-btn-allow-once', function(e){

				// Remove message
				globalContainer.hide(200);
				detailsContainer.remove();

				ajaxData.plugin = 'sendOnce';

				$.getJSON('index.php', ajaxData, function(response){});
				e.preventDefault();
			});

			// Never allow
			messageContainer.on('click', '.js-pstats-btn-allow-never', function(e){

				// Remove message
				globalContainer.hide(200);
				detailsContainer.remove();

				ajaxData.plugin = 'sendNever';

				$.getJSON('index.php', ajaxData, function(response){});
				e.preventDefault();
			});
		}

		ajaxData.plugin = 'sendStats';

		$.getJSON('index.php', ajaxData, function(response){
			if (response && response.html) {
				messageContainer
					.append(response.html)
					.find('.js-pstats-alert').show(200);

				initStatsEvents();
			}
		});
	});
})(jQuery);
