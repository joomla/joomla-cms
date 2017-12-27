<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$js = "
	;(function($)
	{
		$(function()
		{
			var progress = $('#dump-progress');

			$('#installer-dump-modal').on('shown', function()
			{
				generateDump();
			})
			.on('hidden', function()
			{
				progress.width('0%');
				progress.removeClass('bar-success bar-danger').addClass('bar-primary');
			});

			var generateDump = function(hash)
			{
				var link = 'index.php?option=com_installer&view=database&task=database.dump&format=raw';

				if (typeof hash !== 'undefined')
				{
					link += '&hash=' + hash;
				}

				$.getJSON(link)
				.done(function(data)
				{
					if (data.success)
					{
						progress.width(data.data.percent + '%');

						if (!data.data.finished)
						{
							generateDump(data.data.hash);
						}
						else
						{
							progress.removeClass('bar-primary bar-danger').addClass('bar-success');

							location.href = 'index.php?option=com_installer&view=database&task=database.download&format=raw&hash=' + data.data.hash;
						}
					}
				}).
				fail(function()
				{
					$('#installer-dump-modal').modal('hide');

					progress.removeClass('bar-success bar-primary').addClass('bar-danger');

					var deletelink = 'index.php?option=com_installer&view=database&task=database.delete&format=raw';

					if (typeof hash !== 'undefined')
					{
						deletelink += '&hash=' + hash;

						$.getJSON(deletelink);
					}
				});
			};
		});
	})(jQuery);
";

JFactory::getDocument()->addScriptDeclaration($js);

?>
<div class="container-fluid">
	<div class="row-fluid">
		<div class="span12">
			<div class="progress progress-striped active">
				<div id="dump-progress" class="bar bar-primary" style=""></div>
			</div>
		</div>
	</div>
</div>
