<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<form action="<?php echo JRoute::_('index.php?option=com_admin&view=sysinfo'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'site')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'site', JText::_('COM_ADMIN_SYSTEM_INFORMATION')); ?>
			<?php echo $this->loadTemplate('system'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'phpsettings', JText::_('COM_ADMIN_PHP_SETTINGS')); ?>
			<?php echo $this->loadTemplate('phpsettings'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'config', JText::_('COM_ADMIN_CONFIGURATION_FILE')); ?>
			<?php echo $this->loadTemplate('config'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'directory', JText::_('COM_ADMIN_DIRECTORY_PERMISSIONS')); ?>
			<?php echo $this->loadTemplate('directory'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'phpinfo', JText::_('COM_ADMIN_PHP_INFORMATION')); ?>
			<?php echo $this->loadTemplate('phpinfo'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
		<!-- End Content -->
	</div>
</form>
