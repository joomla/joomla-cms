<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;
?>
<script language="javascript" type="text/javascript">
<!--
	function submitbutton3(pressbutton) {
		var form = document.adminForm;

		// do field validation
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('Please select a directory', true); ?>");
		} else {
			form.installtype.value = 'folder';
			form.submit();
		}
	}

	function submitbutton4(pressbutton) {
		var form = document.adminForm;

		// do field validation
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('Please enter a URL', true); ?>");
		} else {
			form.installtype.value = 'url';
			form.submit();
		}
	}
//-->
</script>

<form enctype="multipart/form-data" action="index.php" method="post" name="adminForm">

	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>
	<div class="width-70 fltlft">
		<fieldset class="uploadform">
			<legend><?php echo JText::_('Upload Package File'); ?></legend>
			<label for="install_package"><?php echo JText::_('Package File'); ?>:</label>
			<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
			<input class="button" type="button" value="<?php echo JText::_('Upload File'); ?> &amp; <?php echo JText::_('Install'); ?>" onclick="submitbutton()" />
		</fieldset>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('Install from directory'); ?></legend>
			<label for="install_directory"><?php echo JText::_('Install directory'); ?>:</label>
			<input type="text" id="install_directory" name="install_directory" class="input_box" size="70" value="<?php echo $this->state->get('install.directory'); ?>" />
			<input type="button" class="button" value="<?php echo JText::_('Install'); ?>" onclick="submitbutton3()" />
		</fieldset>
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('Install from URL'); ?></legend>
			<label for="install_url"><?php echo JText::_('Install URL'); ?>:</label>
			<input type="text" id="install_url" name="install_url" class="input_box" size="70" value="http://" />
			<input type="button" class="button" value="<?php echo JText::_('Install'); ?>" onclick="submitbutton4()" />
		</fieldset>
	</div>
	<input type="hidden" name="type" value="" />
	<input type="hidden" name="installtype" value="upload" />
	<input type="hidden" name="task" value="doInstall" />
	<input type="hidden" name="option" value="com_installer" />
	<?php echo JHtml::_('form.token'); ?>
</form>