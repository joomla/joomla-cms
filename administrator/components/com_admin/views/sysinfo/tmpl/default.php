<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Add specific helper files for html generation
JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<form action="<?php echo JRoute::_('index.php'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="span12">
			<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'site')); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'site', JText::_('COM_ADMIN_SYSTEM_INFORMATION', true)); ?>
			<?php echo $this->loadTemplate('system'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'phpsettings', JText::_('COM_ADMIN_PHP_SETTINGS', true)); ?>
			<?php echo $this->loadTemplate('phpsettings'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'config', JText::_('COM_ADMIN_CONFIGURATION_FILE', true)); ?>
			<?php echo $this->loadTemplate('config'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'directory', JText::_('COM_ADMIN_DIRECTORY_PERMISSIONS', true)); ?>
			<?php echo $this->loadTemplate('directory'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'phpinfo', JText::_('COM_ADMIN_PHP_INFORMATION', true)); ?>
			<?php echo $this->loadTemplate('phpinfo'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
		<!-- End Content -->
	</div>
</form>
