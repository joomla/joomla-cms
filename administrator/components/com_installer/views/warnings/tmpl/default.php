<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="installer-warnings" class="clearfix">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif; ?>
		<?php if (count($this->messages)) : ?>
			<?php echo JHtml::_('bootstrap.startAccordion', 'warnings', array('active' => 'warning0')); ?>
				<?php $i = 0; ?>
				<?php foreach ($this->messages as $message) : ?>
					<?php echo JHtml::_('bootstrap.addSlide', 'warnings', $message['message'], 'warning' . ($i++)); ?>
						<?php echo $message['description']; ?>
					<?php echo JHtml::_('bootstrap.endSlide'); ?>
				<?php endforeach; ?>
					<?php echo JHtml::_('bootstrap.addSlide', 'warnings', JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'), 'furtherinfo'); ?>
						<?php echo JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC'); ?>
					<?php echo JHtml::_('bootstrap.endSlide'); ?>
			<?php echo JHtml::_('bootstrap.endAccordion'); ?>
		<?php endif; ?>
			<div>
				<input type="hidden" name="boxchecked" value="0" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</div>
	</form>
</div>
