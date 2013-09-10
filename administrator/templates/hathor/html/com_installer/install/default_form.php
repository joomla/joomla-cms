<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('jquery.framework');
$app = JFactory::getApplication();
$installfrom = base64_decode($app->input->get('installfrom', '', 'base64'));
$document = JFactory::getDocument();
$ver = new JVersion;
$min = JFactory::getConfig()->get('debug') ? '' : '.min';
?>
<script type="text/javascript">
	apps_base_url = '<?php echo addslashes($this->appsBaseUrl); ?>';
	apps_installat_url = '<?php echo base64_encode(JURI::current(true) . '?option=com_installer&view=install'); ?>';
	apps_installfrom_url = '<?php echo addslashes($installfrom); ?>';
	apps_product = '<?php echo base64_encode($ver->PRODUCT); ?>';
	apps_release = '<?php echo base64_encode($ver->RELEASE); ?>';
	apps_dev_level = '<?php echo base64_encode($ver->DEV_LEVEL); ?>';

	jQuery(document).ready(function() {
		jQuery('#mywebinstaller').show();
		var link = jQuery('#mywebinstaller a');
		jQuery(link).click(function (event){
			jQuery('<link rel="stylesheet" type="text/css" href="<?php echo addslashes($this->appsBaseUrl . 'jedapps/v1/css/client' . $min . '.css?jversion=' . JVERSION); ?>" />').appendTo("head");
			if (typeof Joomla.apps == 'undefined') {
				jQuery('#mywebinstaller').hide();
				jQuery('#web-loader').show();
				jQuery.ajax({
					url: "<?php echo addslashes($this->appsBaseUrl . 'jedapps/v1/js/client' . $min . '.js?jversion=' . JVERSION); ?>",
					dataType: 'script',
					cache: true,
					jsonpCallback: "jedapps_jsonpcallback",
					timeout: 20000,
					success: function(response) {
						jQuery('<script type="text/javascript">'+response+'</'+'script>').appendTo('head');
						for (var i = 0; i < Joomla.apps.cssfiles.length; i++) {
							jQuery('<link rel="stylesheet" type="text/css" href="<?php echo htmlspecialchars($this->appsBaseUrl); ?>'+Joomla.apps.cssfiles[i]+'" />').appendTo("head");
						}
						if (Joomla.apps.fonturl) {
							jQuery('<link rel="stylesheet" type="text/css" href="'+Joomla.apps.fonturl+'" />').appendTo("head");
						}
						for (var i = 0; i < Joomla.apps.jsfiles.length; i++) {
							jQuery('<script type="text/javascript" src="<?php echo htmlspecialchars($this->appsBaseUrl); ?>'+Joomla.apps.jsfiles[i]+'" />').appendTo("head");
						}
						Joomla.apps.initialize();
					},
					fail: function() {
						jQuery('#web-loader').hide();
						jQuery('#web-loader-error').show();
					},
					error: function(request, status, error) {
						if (request.responseText) {
							jQuery('#web-loader-error').html(request.responseText);
						}
						jQuery('#web-loader').hide();
						jQuery('#web-loader-error').show();
					}
				});
			}
		});
		
		if (apps_installfrom_url != '') {
			jQuery(link).closest('li').trigger('click');
		}
	});

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
	
	Joomla.submitbutton5 = function(pressbutton)
	{
		var form = document.getElementById('adminForm');
		
		// do field validation
		if (form.install_url.value != "" && form.install_url.value != "http://")
		{
			Joomla.submitbutton4();
		}
		else if (form.install_url.value == "")
		{
			alert("<?php echo JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true); ?>");
		}
		else
		{
			form.installtype.value = 'web';
			form.submit();
		}
	}
</script>

<form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install');?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<?php if ($this->ftp) : ?>
		<?php echo $this->loadTemplate('ftp'); ?>
	<?php endif; ?>
	<div class="width-70 fltlft">
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
		<div class="clr"></div>
		<fieldset class="uploadform">
			<legend><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB'); ?></legend>
			<div id="myTabContent">
				<div id="jed-container">
					<div id="mywebinstaller" style="display:none">
						<a href="#"><?php echo JText::_('COM_INSTALLER_LOAD_APPS'); ?></a>
					</div>
					<div class="well" id="web-loader" style="display:none">
						<h2><?php echo JText::_('COM_INSTALLER_INSTALL_WEB_LOADING'); ?></h2>
					</div>
					<div class="alert alert-error" id="web-loader-error" style="display:none">
						<a class="close" data-dismiss="alert">×</a><?php echo JText::_('COM_INSTALLER_INSTALL_WEB_LOADING_ERROR'); ?>
					</div>
				</div>
			</div>
			<fieldset class="uploadform" id="uploadform-web" style="display:none">
				<div class="control-group">
					<strong><?php echo JText::sprintf('COM_INSTALLER_INSTALL_WEB_CONFIRM'); ?></strong><br />
					<span id="uploadform-web-name-label"><?php echo JText::sprintf('COM_INSTALLER_INSTALL_WEB_CONFIRM_NAME'); ?></span> <span id="uploadform-web-name"></span><br />
					<?php echo JText::sprintf('COM_INSTALLER_INSTALL_WEB_CONFIRM_URL'); ?> <span id="uploadform-web-url"></span>
				</div>
				<div class="form-actions">
					<input type="button" class="btn btn-primary" value="<?php echo JText::_('COM_INSTALLER_INSTALL_BUTTON'); ?>" onclick="Joomla.submitbutton<?php echo $installfrom != '' ? 4 : 5; ?>()" />
					<input type="button" class="btn btn-secondary" value="<?php echo JText::_('COM_INSTALLER_CANCEL_BUTTON'); ?>" onclick="Joomla.installfromwebcancel()" />
				</div>
			</fieldset>
		</fieldset>
		<input type="hidden" name="type" value="" />
		<input type="hidden" name="installtype" value="upload" />
		<input type="hidden" name="task" value="install.install" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</div>
</form>
