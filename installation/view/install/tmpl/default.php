<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewInstallHtml $this */
?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<h3><?php echo JText::_('INSTL_INSTALLING'); ?></h3>
	<hr class="hr-condensed" />
	<div class="progress progress-striped active" id="install_progress">
		<div class="bar" style="width: 0%;"></div>
	</div>
	<table class="table">
		<tbody>
		<?php foreach ($this->tasks as $task) : ?>
			<tr id="install_<?php echo $task; ?>">
				<td class="item" nowrap="nowrap" width="10%">
				<?php if ($task === 'Email') : ?>
					<?php echo JText::sprintf('INSTL_INSTALLING_EMAIL', '<span class="label">' . $this->options['admin_email'] . '</span>'); ?>
				<?php else : ?>
					<?php echo JText::_('INSTL_INSTALLING_' . strtoupper($task)); ?>
				<?php endif; ?>
				</td>
				<td>
					<div class="spinner spinner-img" style="visibility: hidden;"></div>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="2"></td>
			</tr>
		</tfoot>
	</table>
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	jQuery(function()
	{
		doInstall();
	});
	function doInstall() {
		if(document.getElementById('install_progress') != null) {
			Install.install(['<?php echo implode("','", $this->tasks); ?>']);
		} else {
			(function(){doInstall();}).delay(500);
		}
	}
</script>
