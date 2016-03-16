<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */

$errSelectPackage = JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true);
JFactory::getDocument()->addScriptDeclaration( <<< JS
	Joomla.submitbuttonUpload = function() {
		var form = document.getElementById("adminForm");

		// do field validation
		if (form.install_package.value == "") {
			alert("$errSelectPackage");
		}
		else
		{
			jQuery("#loading").css("display", "block");

			form.installtype.task = "update.upload";
			form.submit();
		}
	};

	// Add spindle-wheel for installations:
	jQuery(document).ready(function($) {
		var outerDiv = $("#joomlaupdate-wrapper");

		$("#loading")
		.css("top", outerDiv.position().top - $(window).scrollTop())
		.css("left", outerDiv.position().left - $(window).scrollLeft())
		.css("width", outerDiv.width())
		.css("height", outerDiv.height())
		.css("display", "none")
	});

JS
);

$ajaxLoaderImage = JHtml::_('image', 'jui/ajax-loader.gif', '', null, true, true);
JFactory::getDocument()->addStyleDeclaration( <<< CSS
	#loading {
		background: rgba(255, 255, 255, .8) url('$ajaxLoaderImage') 50% 15% no-repeat;
		position: fixed;
		opacity: 0.8;
		-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity = 80);
		filter: alpha(opacity = 80);
		margin: -10px -50px 0 -50px;
		overflow: hidden;
	}
CSS
);
?>

<div class="alert alert-info">
	<p>
		<span class="icon icon-info"></span>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPLOAD_INTRO', 'https://www.joomla.org/download.html') ?>
	</p>
</div>

<?php if (count($this->warnings)): ?>
<fieldset>
	<legend>
		<?php echo JText::_('COM_INSTALLER_SUBMENU_WARNINGS') ?>
	</legend>

	<?php echo JHtml::_('bootstrap.startAccordion', 'warnings', array('active' => 'warning0')); ?>
	<?php $i = 0; ?>
	<?php foreach($this->warnings as $message) : ?>
		<?php echo JHtml::_('bootstrap.addSlide', 'warnings', $message['message'], 'warning' . ($i++)); ?>
		<?php echo $message['description']; ?>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.addSlide', 'warnings', JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'), 'furtherinfo'); ?>
	<?php echo JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC'); ?>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php echo JHtml::_('bootstrap.endAccordion'); ?>
</fieldset>
<?php endif;?>

<fieldset class="uploadform">
	<legend><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD'); ?></legend>
	<div class="control-group">
		<label for="install_package" class="control-label"><?php echo JText::_('COM_INSTALLER_EXTENSION_PACKAGE_FILE'); ?></label>
		<div class="controls">
			<input class="input_box" id="install_package" name="install_package" type="file" size="57" />
		</div>
	</div>
	<div class="form-actions">
		<button class="btn btn-primary" type="button" onclick="Joomla.submitbuttonUpload()"><?php echo JText::_('COM_INSTALLER_UPLOAD_AND_INSTALL'); ?></button>
	</div>
</fieldset>