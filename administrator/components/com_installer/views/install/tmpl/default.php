<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// MooTools is loaded for B/C for extensions generating JavaScript in their install scripts, this call will be removed at 4.0
JHtml::_('behavior.framework', true);
JHtml::_('bootstrap.tooltip');
?>
<script type="text/javascript">
	Joomla.submitbutton = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_package.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true); ?>");
		}
		else
		{
			jQuery('#loading').css('display', 'block');

			form.installtype.value = 'upload';
			form.submit();
		}
	};

	Joomla.submitbutton3 = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_DIRECTORY', true); ?>");
		}
		else
		{
			jQuery('#loading').css('display', 'block');

			form.installtype.value = 'folder';
			form.submit();
		}
	};

	Joomla.submitbutton4 = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true); ?>");
		}
		else
		{
			jQuery('#loading').css('display', 'block');

			form.installtype.value = 'url';
			form.submit();
		}
	};

	Joomla.submitbuttonInstallWebInstaller = function()
	{
		var form = document.getElementById('adminForm');

		form.install_url.value = 'http://appscdn.joomla.org/webapps/jedapps/webinstaller.xml';

		Joomla.submitbutton4();
	};

	// Add spindle-wheel for installations:
	jQuery(document).ready(function($) {
		var outerDiv = $('#installer-install');

		$('<div id="loading"></div>')
			.css("background", "rgba(255, 255, 255, .8) url('../media/jui/img/ajax-loader.gif') 50% 15% no-repeat")
			.css("top", outerDiv.position().top - $(window).scrollTop())
			.css("left", outerDiv.position().left - $(window).scrollLeft())
			.css("width", outerDiv.width())
			.css("height", outerDiv.height())
			.css("position", "fixed")
			.css("opacity", "0.80")
			.css("-ms-filter", "progid:DXImageTransform.Microsoft.Alpha(Opacity = 80)")
			.css("filter", "alpha(opacity = 80)")
			.css("display", "none")
			.appendTo(outerDiv);
	});

</script>

<div id="installer-install" class="clearfix">
<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install');?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<?php if (!empty( $this->sidebar)) : ?>
		<div id="j-sidebar-container" class="span2">
			<?php echo $this->sidebar; ?>
		</div>
		<div id="j-main-container" class="span10">
	<?php else : ?>
		<div id="j-main-container">
	<?php endif;?>

	<!-- Render messages set by extension install scripts here -->
	<?php if ($this->showMessage) : ?>
		<?php echo $this->loadTemplate('message'); ?>
	<?php elseif ($this->showJedAndWebInstaller) : ?>
		<div class="alert alert-info j-jed-message" style="margin-bottom: 40px; line-height: 2em; color:#333333;">
			<a href="index.php?option=com_config&view=component&component=com_installer&path=&return=<?php echo urlencode(base64_encode(JUri::getInstance())); ?>" class="close hasTooltip" data-dismiss="alert" title="<?php echo str_replace('"', '&quot;', JText::_('COM_INSTALLER_SHOW_JED_INFORMATION_TOOLTIP')); ?>">&times;</a>
			<p><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_INFO'); ?>&nbsp;&nbsp;<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_TOS'); ?></p>
			<input class="btn" type="button" value="<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_ADD_TAB'); ?>" onclick="Joomla.submitbuttonInstallWebInstaller()" />
		</div>
	<?php endif; ?>

	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'upload')); ?>

			<?php JEventDispatcher::getInstance()->trigger('onInstallerViewBeforeFirstTab', array()); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'upload', JText::_('COM_INSTALLER_UPLOAD_PACKAGE_FILE', true)); ?>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('COM_INSTALLER_UPLOAD_INSTALL_JOOMLA_EXTENSION'); ?></legend>
				<div class="control-group">
					<label for="install_package" class="control-label"><?php echo JText::_('COM_INSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
					<div class="controls">
						<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
					</div>
				</div>
				<div class="form-actions">
					<input class="btn btn-primary" type="button" value="<?php echo JText::_('COM_INSTALLER_UPLOAD_AND_INSTALL'); ?>" onclick="Joomla.submitbutton()" />
				</div>
			</fieldset>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'directory', JText::_('COM_INSTALLER_INSTALL_FROM_DIRECTORY', true)); ?>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_DIRECTORY'); ?></legend>
				<div class="control-group">
					<label for="install_directory" class="control-label"><?php echo JText::_('COM_INSTALLER_INSTALL_DIRECTORY'); ?></label>
					<div class="controls">
						<input type="text" id="install_directory" name="install_directory" class="span5 input_box" size="70" value="<?php echo $this->state->get('install.directory'); ?>" />
					</div>
				</div>
				<div class="form-actions">
					<input type="button" class="btn btn-primary" value="<?php echo JText::_('COM_INSTALLER_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton3()" />
				</div>
			</fieldset>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'url', JText::_('COM_INSTALLER_INSTALL_FROM_URL', true)); ?>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_URL'); ?></legend>
				<div class="control-group">
					<label for="install_url" class="control-label"><?php echo JText::_('COM_INSTALLER_INSTALL_URL'); ?></label>
					<div class="controls">
						<input type="text" id="install_url" name="install_url" class="span5 input_box" size="70" value="http://" />
					</div>
				</div>
				<div class="form-actions">
					<input type="button" class="btn btn-primary" value="<?php echo JText::_('COM_INSTALLER_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton4()" />
				</div>
			</fieldset>

		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php JEventDispatcher::getInstance()->trigger('onInstallerViewAfterLastTab', array()); ?>

		<?php if ($this->ftp) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'ftp', JText::_('COM_INSTALLER_MSG_DESCFTPTITLE', true)); ?>
				<?php echo $this->loadTemplate('ftp'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

	<input type="hidden" name="type" value="" />
	<input type="hidden" name="installtype" value="upload" />
	<input type="hidden" name="task" value="install.install" />
	<?php echo JHtml::_('form.token'); ?>

	<?php echo JHtml::_('bootstrap.endTabSet'); ?>
</form>
</div>
