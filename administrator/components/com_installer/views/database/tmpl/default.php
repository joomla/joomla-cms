<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		2.5
 */

// no direct access
defined('_JEXEC') or die;


?>
<form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings');?>" method="post" name="adminForm" id="adminForm">
<?php $errors = count($this->errors); ?>
<?php if (!strstr($this->schemaVersion, JVERSION)) : ?>
		<?php $errors++; ?>
<?php endif; ?>
<?php if ($errors === 0) : ?>
	<p class="nowarning"><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_OK'); ?></p>
<?php else : ?>
	<p class="warning"><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_ERRORS'); ?></p>
	<?php echo JHtml::_('sliders.start', 'database-sliders', array('useCookie'=>1)); ?>
	<?php $panelName = JText::plural('COM_INSTALLER_MSG_N_DATABASE_ERROR_PANEL', $errors); ?>
	<?php echo JHtml::_('sliders.panel', $panelName, 'error-panel'); ?>
	<ul>
	<?php if (!strstr($this->schemaVersion, JVERSION)) : ?>
		<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $this->schemaVersion, JVERSION); ?></li>
	<?php endif; ?>
	<?php foreach($this->errors as $line => $error) : ?>
	<?php $key = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
		$msgs = $error->msgElements;
		$file = basename($error->file);
		$msg0 = (isset($msgs[0])) ? $msgs[0] : ' '; 
		$msg1 = (isset($msgs[1])) ? $msgs[1] : ' ';
		$msg2 = (isset($msgs[2])) ? $msgs[2] : ' ';
		$message = JText::sprintf($key, $file, $msg0, $msg1, $msg2); ?>
		<li><?php echo $message; ?></li>
	<?php endforeach; ?></ul>
<?php endif; ?>

<?php echo JHtml::_('sliders.panel', JText::_('COM_INSTALLER_MSG_DATABASE_INFO'),'furtherinfo-pane'); ?>
<ul>
	<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_VERSION', $this->schemaVersion); ?></li>
	<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_DRIVER', JFactory::getDbo()->name); ?></li>
	<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($this->results['ok'])); ?></li>
	<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($this->results['skipped'])); ?></li>
</ul>
<?php echo JHtml::_('sliders.end'); ?>

<div class="clr"> </div>
<div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
</div>
</form>
