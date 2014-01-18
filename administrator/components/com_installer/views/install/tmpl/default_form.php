<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

// no direct access
defined('_JEXEC') or die;

if ($this->showJedAndWebInstaller && !$this->showMessage) {
	$info = '<div style="float: right"><a class="modal" rel="{handler: \'iframe\', size: {x: 875, y: 550}, onClose: function() {}}" href="index.php?option=com_config&view=component&component=com_installer&path=&tmpl=component" class="close hasTooltip" data-dismiss="alert" title="'.str_replace('"', '&quot;', JText::_('COM_INSTALLER_SHOW_JED_INFORMATION_TOOLTIP')).'">&times;</a></div>'
	. preg_replace(
		'#([^<]*)<a>([^<]*)</a>([^<]*)<a>([^<]*)</a>([^<]*)<button>([^<]*)</button>([^<]*)#',
		'\1'
		. '<a href="http://extensions.joomla.org" target="_blank">' . '\2' . '</a>'
		. '\3'
		. '<a href="http://docs.joomla.org/Install_from_Web" target="_blank">' . '\4' . '</a>'
		. '\5'
		. '<input class="btn" type="button" value="' . '\6' . '" onclick="Joomla.submitbuttonInstallWebInstaller()" />'
		. '\7',
		str_replace('"', '&quot;', JText::_('COM_INSTALLER_INSTALL_FROM_WEB_INFORMATION'))
	);
	JFactory::getApplication()->enqueueMessage($info, 'info');
}
?>
<script type="text/javascript">
	Joomla.submitbutton = function() {
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_package.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true); ?>");
		} else {
			form.installtype.value = 'upload';
			form.submit();
		}
	};

	Joomla.submitbutton3 = function() {
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_DIRECTORY', true); ?>");
		} else {
			form.installtype.value = 'folder';
			form.submit();
		}
	};

	Joomla.submitbutton4 = function() {
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true); ?>");
		} else {
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
</script>

<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install');?>" method="post" name="adminForm" id="adminForm">
	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>
	<div class="width-70 fltlft">

		<?php JDispatcher::getInstance()->trigger('onInstallerViewBeforeFirstTab', array()); ?>

		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_INSTALLER_UPLOAD_PACKAGE_FILE'); ?></legend>
			<label for="install_package"><?php echo JText::_('COM_INSTALLER_PACKAGE_FILE'); ?></label>
			<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
			<input class="button" type="button" value="<?php echo JText::_('COM_INSTALLER_UPLOAD_AND_INSTALL'); ?>" onclick="Joomla.submitbutton()" />
		</fieldset>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_DIRECTORY'); ?></legend>
			<label for="install_directory"><?php echo JText::_('COM_INSTALLER_INSTALL_DIRECTORY'); ?></label>
			<input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->state->get('install.directory'); ?>" />			<input type="button" class="button" value="<?php echo JText::_('COM_INSTALLER_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton3()" />
		</fieldset>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_URL'); ?></legend>
			<label for="install_url"><?php echo JText::_('COM_INSTALLER_INSTALL_URL'); ?></label>
			<input type="text" id="install_url" name="install_url" class="input_box" size="70" value="http://" />
			<input type="button" class="button" value="<?php echo JText::_('COM_INSTALLER_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton4()" />
		</fieldset>

		<?php JDispatcher::getInstance()->trigger('onInstallerViewAfterLastTab', array()); ?>

		<input type="hidden" name="type" value="" />
		<input type="hidden" name="installtype" value="upload" />
		<input type="hidden" name="task" value="install.install" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
