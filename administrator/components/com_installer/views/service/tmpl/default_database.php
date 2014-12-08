<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<?php if ($this->errorCount > 0) : ?>
	<?php echo JHtml::_('sliders.panel', JText::plural('COM_INSTALLER_MSG_N_SERVICE_DATABASE_ERROR_PANEL', $this->errorCount), str_replace(' ', '', JText::plural('COM_INSTALLER_MSG_N_SERVICE_DATABASE_ERROR_PANEL', $this->errorCount))); ?>
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
<?php endif; ?>
<?php echo JHtml::_('sliders.panel', JText::_('COM_INSTALLER_MSG_SERVICE_DATABASE_INFO', true), str_replace(' ', '', JText::_('COM_INSTALLER_MSG_SSERVICE_DATABASE_INFO', true))); ?>
	<?php if ($this->errorCount === 0) : ?>
		<div class="alert alert-info">
			<a class="close" data-dismiss="alert" href="#">&times;</a>
			<?php echo JText::_('COM_INSTALLER_MSG_DATABASE_OK'); ?>
		</div>
	<?php endif; ?>
	<ul>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_VERSION', $this->schemaVersion); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATE_VERSION', $this->updateVersion); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_DRIVER', JFactory::getDbo()->name); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($this->results['ok'])); ?></li>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($this->results['skipped'])); ?></li>
	</ul>