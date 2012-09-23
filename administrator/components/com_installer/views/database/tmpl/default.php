<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>

<div id="installer-database">
	<form action="<?php echo JRoute::_('index.php?option=com_installer&view=warnings');?>" method="post" name="adminForm" id="adminForm">

	<?php if(!empty( $this->sidebar)): ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>

		<?php if ($this->errorCount === 0) : ?>
			<div class="alert alert-info">
				<a class="close" data-dismiss="alert" href="#">&times;</a>
				<?php echo JText::_('COM_INSTALLER_MSG_DATABASE_OK'); ?>
			</div>

			<ul class="nav nav-tabs">
				<li class="active"><a href="#other" data-toggle="tab"><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_INFO');?></a></li>
			</ul>

			<div class="tab-content">
		<?php else : ?>
			<div class="alert alert-error">
				<a class="close" data-dismiss="alert" href="#">&times;</a>
				<?php echo JText::_('COM_INSTALLER_MSG_DATABASE_ERRORS'); ?>
			</div>

			<ul class="nav nav-tabs">
				<li class="active"><a href="#problems" data-toggle="tab"><?php echo JText::plural('COM_INSTALLER_MSG_N_DATABASE_ERROR_PANEL', $this->errorCount);?> <span class="badge badge-info"><?php echo $this->errorCount;?></span></a></li>
				<li><a href="#other" data-toggle="tab"><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_INFO');?></a></li>
			</ul>

			<div class="tab-content">
				<div class="tab-pane active" id="problems">
					<fieldset class="panelform">
						<ul>
						<?php if (!$this->filterParams) : ?>
							<li><?php echo JText::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR'); ?>
						<?php endif; ?>

						<?php if (!(strncmp($this->schemaVersion, JVERSION, 5) === 0)) : ?>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $this->schemaVersion, JVERSION); ?></li>
						<?php endif; ?>

						<?php if (($this->updateVersion != JVERSION)) : ?>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATEVERSION_ERROR', $this->updateVersion, JVERSION); ?></li>
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
						<?php endforeach; ?>
						</ul>
					</fieldset>
				</div>
		<?php endif; ?>
				<div class="tab-pane" id="other">
					<fieldset class="panelform">
						<ul>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_VERSION', $this->schemaVersion); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATE_VERSION', $this->updateVersion); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_DRIVER', JFactory::getDbo()->name); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($this->results['ok'])); ?></li>
							<li><?php echo JText::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($this->results['skipped'])); ?></li>
						</ul>
					</fieldset>
				</div>
			</div>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
