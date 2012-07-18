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
<script type="text/javascript">
	Joomla.submitbutton = function(pressbutton) {
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_package.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true); ?>");
		} else {
			form.installtype.value = 'upload';
			form.submit();
		}
	}

	Joomla.submitbutton3 = function(pressbutton) {
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_directory.value == ""){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_DIRECTORY', true); ?>");
		} else {
			form.installtype.value = 'folder';
			form.submit();
		}
	}

	Joomla.submitbutton4 = function(pressbutton) {
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_url.value == "" || form.install_url.value == "http://"){
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true); ?>");
		} else {
			form.installtype.value = 'url';
			form.submit();
		}
	}
</script>

<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install');?>" method="post" name="adminForm" id="adminForm" class="form-horizontal">
	<!-- Begin Sidebar -->
	<div id="sidebar" class="span2">
		<div class="sidebar-nav">
			<?php
				// Display the submenu position modules
				$this->modules = JModuleHelper::getModules('submenu');
				foreach ($this->modules as $module)
				{
					$output = JModuleHelper::renderModule($module);
					$params = new JRegistry;
					$params->loadString($module->params);
					echo $output;
				}
			?>
		</div>
	</div>
	<!-- End Sidebar -->
	<!-- Begin Content -->
	<div class="span10">
		<!-- Render messages set by extension install scripts here -->
		<?php if ($this->showMessage) : ?>
		<?php echo $this->loadTemplate('message'); ?>
		<?php endif; ?>
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#upload" data-toggle="tab"><?php echo JText::_('COM_INSTALLER_UPLOAD_PACKAGE_FILE'); ?></a></li>
		  <li><a href="#directory" data-toggle="tab"><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_DIRECTORY'); ?></a></li>
		  <li><a href="#url" data-toggle="tab"><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_URL'); ?></a></li>
		  <?php if ($this->ftp) : ?>
			<li><a href="#ftp" data-toggle="tab"><?php echo JText::_('COM_INSTALLER_INSTALL_FTP'); ?></a></li>
		  <?php endif; ?>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active" id="upload">
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
		 	 </div>
			<div class="tab-pane" id="directory">
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
			</div>
			<div class="tab-pane" id="url">
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
			</div>
			<?php if ($this->ftp) : ?>
			<div class="tab-pane" id="ftp">
				<?php echo $this->loadTemplate('ftp'); ?>
			</div>
			<?php endif; ?>
		</div>
		<input type="hidden" name="type" value="" />
		<input type="hidden" name="installtype" value="upload" />
		<input type="hidden" name="task" value="install.install" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<!-- End Content -->
</form>
