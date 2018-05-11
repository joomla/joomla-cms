<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="installer-warnings" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings'); ?>" method="post" name="adminForm" id="adminForm">
		<div class="row">
			<div id="j-sidebar-container" class="col-md-2">
				<?php echo $this->sidebar; ?>
			</div>
			<div class="col-md-10">
				<div id="j-main-container" class="j-main-container">
					<?php if (count($this->messages)) : ?>
						<?php foreach ($this->messages as $message) : ?>
							<joomla-alert type="warning">
								<h4 class="alert-heading"><?php echo $message['message']; ?></h4>
								<p class="m-b-0"><?php echo $message['description']; ?></p>
							</joomla-alert>
						<?php endforeach; ?>
						<joomla-alert type="info">
							<h4 class="alert-heading"><?php echo JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'); ?></h4>
							<p class="m-b-0"><?php echo JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC'); ?></p>
						</joomla-alert>
						<?php echo JHtml::_('bootstrap.endAccordion'); ?>
					<?php endif; ?>
					<div>
						<input type="hidden" name="boxchecked" value="0">
						<?php echo JHtml::_('form.token'); ?>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
