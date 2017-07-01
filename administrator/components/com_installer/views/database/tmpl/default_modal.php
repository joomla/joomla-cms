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
							generateZip(data.data.hash);
						}
					}
				}).
				fail(function()
				{
					$('#installer-dump-modal').modal('hide');

					// @TODO delete files
				});
			};

			var generateZip = function(hash)
			{
				if (typeof hash == 'undefined')
				{
					$('#installer-dump-modal').modal('hide');

					return false;
				}

				var link = 'index.php?option=com_installer&view=database&task=database.zip&format=raw&hash=' + hash;
				var download = 'index.php?option=com_installer&view=database&task=database.download&format=raw&hash=' + hash;

				$.getJSON(link)
				.done(function(data)
				{
					if (data.success)
					{
						$('#installer-dump-modal').modal('hide');

						location.href = download;
					}
				}).
				fail(function()
				{
					$('#installer-dump-modal').modal('hide');

					// @TODO delete files
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
				<div id="dump-progress" class="bar bar-success" style=""></div>
			</div>
		</div>
	</div>
</div>
