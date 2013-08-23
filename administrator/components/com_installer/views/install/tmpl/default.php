<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton)
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_package.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true); ?>");
		}
		else
		{
			form.installtype.value = 'upload';
			form.submit();
		}
	}

	Joomla.submitbutton3 = function(pressbutton)
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_DIRECTORY', true); ?>");
		}
		else
		{
			form.installtype.value = 'folder';
			form.submit();
		}
	}

	Joomla.submitbutton4 = function(pressbutton)
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true); ?>");
		}
		else
		{
			form.installtype.value = 'url';
			form.submit();
		}
	}
	
	Joomla.loadweb = function(url) {
		if ('' == url) { return false; }
		
		jQuery.get(url, function(data) {
			response = JSON.parse(data);
			jQuery('#web-loader').hide();
			jQuery('#jed-container').html(response.data);
		}).fail(function() { 
			jQuery('#web-loader').hide();
			jQuery('#web-loader-error').show();
		});
	}
	
	Joomla.installfromweb = function(install_url, name) {
		if ('' == install_url) {
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_WEB_INVALID_URL', true); ?>");
			return false;
		}
		jQuery('#install_url').val(install_url);
		jQuery('#uploadform-web-url').text(install_url);
		jQuery('#uploadform-web-name').text(name);
		jQuery('#jed-container').slideUp(300);
		jQuery('#uploadform-web').show();
	}
	
	Joomla.installfromwebcancel = function() {
		jQuery('#uploadform-web').hide();
		jQuery('#jed-container').slideDown(300);
	}
	
	jQuery(document).ready(function() { 
		Joomla.loadweb('index.php?option=com_installer&task=install.installfromweb');
	});
	
</script>

<div id="installer-install">
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
	<?php endif; ?>

	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'upload')); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'upload', JText::_('COM_INSTALLER_UPLOAD_PACKAGE_FILE', true)); ?>
			<fieldset class="uploadform">
				<legend><?php echo JText::_('COM_INSTALLER_UPLOAD_PACKAGE_FILE'); ?></legend>
				<div class="control-group">
					<label for="install_package" class="control-label"><?php echo JText::_('COM_INSTALLER_PACKAGE_FILE'); ?></label>
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

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'web', JText::_('COM_INSTALLER_INSTALL_FROM_WEB', true)); ?>
			<div id="jed-container">
				<div class="well" id="web-loader">
					<h2><?php echo JText::_('COM_INSTALLER_INSTALL_WEB_LOADING'); ?></h2>
				</div>
				<div class="alert alert-error" id="web-loader-error" style="display:none">
					<a class="close" data-dismiss="alert">×</a><?php echo JText::_('COM_INSTALLER_INSTALL_WEB_LOADING_ERROR'); ?>
				</div>
			</div>

			<fieldset class="uploadform" id="uploadform-web" style="display:none">
				<div class="control-group">
					<strong><?php echo JText::sprintf('COM_INSTALLER_INSTALL_WEB_CONFIRM'); ?></strong><br />
					<?php echo JText::sprintf('COM_INSTALLER_INSTALL_WEB_CONFIRM_NAME'); ?> <span id="uploadform-web-name"></span><br />
					<?php echo JText::sprintf('COM_INSTALLER_INSTALL_WEB_CONFIRM_URL'); ?> <span id="uploadform-web-url"></span>
				</div>
				<div class="form-actions">
					<input type="button" class="btn btn-primary" value="<?php echo JText::_('COM_INSTALLER_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton4()" />
					<input type="button" class="btn btn-secondary" value="<?php echo JText::_('COM_INSTALLER_CANCEL_BUTTON'); ?>" onclick="Joomla.installfromwebcancel()" />
				</div>
			</fieldset>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

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
