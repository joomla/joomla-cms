<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// MooTools is loaded for B/C for extensions generating JavaScript in their install scripts, this call will be removed at 4.0
JHtml::_('behavior.framework', true);
JHtml::_('bootstrap.tooltip');
JHtml::_('script', 'com_installer/install.js', false, true);

JFactory::getDocument()->addScriptDeclaration("
	// Keep for B/C. required for webInstaller updates
	Joomla.submitbuttonInstallWebInstaller = function() {
		Joomla.installer.installWebInstaller();
	};
");
JFactory::getDocument()->addStyleDeclaration('
	.control-label { display: block; min-width: 140px; float: left }
	.form-action { padding-left: 138px; margin-top: 5px; }
');
?>
<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

	<?php if ($this->state->get('install.show_jed_info') && !$this->showMessage) : ?>
		<div class="alert j-jed-message" style="margin-bottom: 20px; line-height: 2em; color:#333333; clear:both;">
			<a href="index.php?option=com_config&view=component&component=com_installer&path=&return=<?php echo urlencode(base64_encode(JUri::getInstance())); ?>"
			   class="close hasTooltip" data-dismiss="alert" title="<?php echo str_replace('"', '&quot;', JText::_('COM_INSTALLER_SHOW_JED_INFORMATION_TOOLTIP')); ?>">&times;</a>
			<p><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_INFO'); ?>&nbsp;&nbsp;<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_TOS'); ?></p>
			<input class="btn" type="button" value="<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_ADD_TAB'); ?>" onclick="Joomla.installer.installWebInstaller()" />
		</div>
	<?php endif; ?>

	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>

	<div class="width-70 fltlft">
		<?php foreach ($this->installTypes as $installType) : ?>
			<?php if (isset($installType->form) && $installType->form instanceof JForm): ?>
				<div class="clr"></div>
				<fieldset class="uploadform">
					<legend><?php echo $installType->title; ?></legend>
					<?php foreach ($installType->form->getFieldset() as $field): ?>
						<div class="control-label"><?php echo $field->label; ?></div>
						<div class="control-input"><?php echo $field->input; ?></div>
					<?php endforeach; ?>
					<div class="form-action"><button type="button" class="btn btn-primary"
					        onclick="Joomla.installer.submit('<?php echo $installType->name ?>');"><?php echo $installType->button; ?></button></div>
				</fieldset>
			<?php else: // B/C for older plugins before Joomla 3.6.0 that echo html directly ?>
				<?php echo $installType->html; ?>
			<?php endif; ?>
		<?php endforeach; ?>

		<input type="hidden" name="type" value="" />
		<input type="hidden" name="installtype" value="upload" />
		<input type="hidden" name="task" value="install.install" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>
