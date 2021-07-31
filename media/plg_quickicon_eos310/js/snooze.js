/**
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

 (function ($)
 {
	 $(document).ready(function ()
	 {
		 var ajaxData = {
			 'option' : 'com_ajax',
			 'group'  : 'quickicon',
			 'plugin' : 'SnoozeEOS',
			 'format' : 'json'
		 }

		 $('#system-message-container').on('click', '.eosnotify-snooze-btn', function(e)
		 {
			 var button=$(this);
			 $.getJSON('index.php', ajaxData, function(response)
			 {
				 if (response.success)
				 {
					 button.closest('.alert').find('[data-dismiss="alert"]').click();
				 }
			 }
		 );
		 e.preventDefault();
		 });
	 });
 })(jQuery);
