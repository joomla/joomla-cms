<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<div id="installer-database">
<form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
<?php if ($this->errorCount === 0) : ?>
	<?php echo JHtml::_('sliders.start', 'database-sliders', array('useCookie' => 1)); ?>

<?php else : ?>
	<?php echo JHtml::_('sliders.start', 'database-sliders', array('useCookie' => 1)); ?>

	<?php $panelName = JText::plural('COM_INSTALLER_MSG_N_DATABASE_ERROR_PANEL', $this->errorCount); ?>
	<?php echo JHtml::_('sliders.panel', $panelName, 'error-panel'); ?>
	<fieldset class="panelform">
		<ul>
			<?php if (!$this->filterParams) : ?>
				<li><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR'); ?>
			<?php endif; ?>

			<?php if ($this->schemaVersion != $this->changeSet->getSchema()) : ?>
				<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $this->schemaVersion, $this->changeSet->getSchema()); ?></li>
			<?php endif; ?>

			<?php if (version_compare($this->updateVersion, JVERSION) != 0) : ?>
				<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATEVERSION_ERROR', $this->updateVersion, JVERSION); ?></li>
			<?php endif; ?>

			<?php foreach ($this->errors as $line => $error) : ?>
				<?php $key = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
				$msgs = $error->msgElements;
				$file = basename($error->file);
				$msg0 = (isset($msgs[0])) ? $msgs[0] : ' ';
				$msg1 = (isset($msgs[1])) ? $msgs[1] : ' ';
				$msg2 = (isset($msgs[2])) ? $msgs[2] : ' ';
				$message = JText::sprintf($key, $file, $msg0, $msg1, $msg2); ?>
				<li><?php echo $message; ?></li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
<?php endif; ?>

<?php echo JHtml::_('sliders.panel', JText::_('COM_INSTALLER_MSG_DATABASE_INFO'), 'furtherinfo-pane'); ?>
	<fieldset class="panelform">
	<ul>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_VERSION', $this->schemaVersion); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATE_VERSION', $this->updateVersion); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_DRIVER', JFactory::getDbo()->name); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($this->results['ok'])); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($this->results['skipped'])); ?></li>
	</ul>
	</fieldset>
<?php echo JHtml::_('sliders.end'); ?>

<div class="clr"> </div>
<div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</div>
</form>
</div>
