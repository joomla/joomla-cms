<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// MooTools is loaded for B/C for extensions generating JavaScript in their install scripts, this call will be removed at 4.0
JHtml::_('behavior.framework', true);
JHtml::_('bootstrap.tooltip');

JFactory::getDocument()->addScriptDeclaration("
	Joomla.submitbutton = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_package.value == ''){
			alert('" . JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true) . "');
		}
		else
		{
			form.installtype.value = 'upload';
			form.submit();
		}
	};

	Joomla.submitbutton3 = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_directory.value == ''){
			alert('" . JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_DIRECTORY', true) . "');
		}
		else
		{
		    JoomlaInstaller.showLoading();
			form.installtype.value = 'folder';
			form.submit();
		}
	};

	Joomla.submitbutton4 = function()
	{
		var form = document.getElementById('adminForm');

		// do field validation
		if (form.install_url.value == '' || form.install_url.value == 'http://' || form.install_url.value == 'https://'){
			alert('" . JText::_('COM_INSTALLER_MSG_INSTALL_ENTER_A_URL', true) . "');
		}
		else
		{
			form.installtype.value = 'url';
			form.submit();
		}
	};

	Joomla.submitbuttonInstallWebInstaller = function()
	{
		var form = document.getElementById('adminForm');

		form.install_url.value = 'https://appscdn.joomla.org/webapps/jedapps/webinstaller.xml';

		Joomla.submitbutton4();
	};

	// Add spindle-wheel for installations:
	jQuery(document).ready(function($) {
		var outerDiv = $(\"#installer-install\");
		
		JoomlaInstaller.getLoadingOverlay()
			.css(\"top\", outerDiv.position().top - $(window).scrollTop())
			.css(\"left\", \"0\")
			.css(\"width\", \"100%\")
			.css(\"height\", \"100%\")
			.css(\"display\", \"none\")
			.css(\"margin-top\", \"-10px\");
	});
	
	var JoomlaInstaller = {
		getLoadingOverlay: function () {
			return jQuery(\"#loading\");
		},
		showLoading: function () {
			this.getLoadingOverlay().css(\"display\", \"block\");
		},
		hideLoading: function () {
			this.getLoadingOverlay().css(\"display\", \"none\");
		}
	};
");

JFactory::getDocument()->addStyleDeclaration(
	'
	#loading {
		background: rgba(255, 255, 255, .8) url(\'' . JHtml::_('image', 'jui/ajax-loader.gif', '', null, true, true) . '\') 50% 15% no-repeat;
		position: fixed;
		opacity: 0.8;
		-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity = 80);
		filter: alpha(opacity = 80);
		overflow: hidden;
	}
	'
);
?>
<div id="installer-install" class="clearfix">
    <form enctype="multipart/form-data" action="<?php echo JRoute::_('index.php?option=com_installer&view=install');?>" method="post" name="adminForm" id="adminForm">
    <?php if (!empty( $this->sidebar)) : ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
    <?php endif;?>

        <?php if ($this->showJedAndWebInstaller && !$this->showMessage) : ?>
            <div class="alert j-jed-message" style="margin-bottom: 20px; line-height: 2em; color:#333333; clear:both;">
                <a href="index.php?option=com_config&view=component&component=com_installer&path=&return=<?php echo urlencode(base64_encode(JUri::getInstance())); ?>" class="close hasTooltip icon-options" data-dismiss="alert" title="<?php echo str_replace('"', '&quot;', JText::_('COM_INSTALLER_SHOW_JED_INFORMATION_TOOLTIP')); ?>"></a>
                <p><?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_INFO'); ?>&nbsp;<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_TOS'); ?></p>
                <input class="btn" type="button" value="<?php echo JText::_('COM_INSTALLER_INSTALL_FROM_WEB_ADD_TAB'); ?>" onclick="Joomla.submitbuttonInstallWebInstaller()" />
            </div>
        <?php endif; ?>

        <?php if ($this->ftp) : ?>
            <?php echo $this->loadTemplate('ftp'); ?>
        <?php endif; ?>
        <div class="width-70 fltlft">

            <?php $firstTab = JEventDispatcher::getInstance()->trigger('onInstallerViewBeforeFirstTab', array()); ?>

            <?php // Show installation fieldsets ?>
            <?php $tabs = JEventDispatcher::getInstance()->trigger('onInstallerAddInstallationTab', array()); ?>
            <?php foreach ($tabs as $tab) : ?>
                <fieldset class="uploadform">
                    <?php echo $tab['content']; ?>
                </fieldset>
            <?php endforeach; ?>

            <?php $lastTab = JEventDispatcher::getInstance()->trigger('onInstallerViewAfterLastTab', array()); ?>

            <?php $tabs = array_merge($firstTab, $tabs, $lastTab); ?>
            <?php if (!$tabs) : ?>
                <?php JFactory::getApplication()->enqueueMessage(JText::_('COM_INSTALLER_NO_INSTALLATION_PLUGINS_FOUND'), 'warning'); ?>
            <?php endif; ?>


            <input type="hidden" name="type" value="" />
            <input type="hidden" name="installtype" value="upload" />
            <input type="hidden" name="task" value="install.install" />
            <?php echo JHtml::_('form.token'); ?>
        </div>
    </div>
    </form>
</div>
<div id="loading"></div>
