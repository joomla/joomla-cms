<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('bootstrap.popover');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('script', 'com_joomlaupdate/default.js', array('version' => 'auto', 'relative' => true));

JText::script('JYES');
JText::script('JNO');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_DESC');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_LIST');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_POTENTIALLY_DANGEROUS_PLUGIN_CONFIRM_MESSAGE');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_HELP');

$latestJoomlaVersion = $this->updateInfo['latest'];
$currentJoomlaVersion = isset($this->updateInfo['installed']) ? $this->updateInfo['installed'] : JVERSION;

JFactory::getDocument()->addScriptDeclaration(
<<<JS
jQuery(document).ready(function($) {
	$('#extraction_method').change(function(e){
		extractionMethodHandler('#extraction_method', 'row_ftp');
	});
	$('#upload_method').change(function(e){
		extractionMethodHandler('#upload_method', 'upload_ftp');
	});

	$('button.submit').on('click', function() {
		$('div.download_message').show();
	});
});

var joomlaTargetVersion = '$latestJoomlaVersion';
var joomlaCurrentVersion = '$currentJoomlaVersion';
JS
);

?>

<div id="joomlaupdate-wrapper">
	<?php if ($this->showUploadAndUpdate) : ?>
		<?php echo JHtml::_('bootstrap.startTabSet', 'joomlaupdate-tabs', array('active' => $this->shouldDisplayPreUpdateCheck() ? 'pre-update-check' : 'online-update')); ?>
		<?php if ($this->shouldDisplayPreUpdateCheck()) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'joomlaupdate-tabs', 'pre-update-check', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_PRE_UPDATE_CHECK')); ?>
			<?php echo $this->loadTemplate('preupdatecheck'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		<?php echo JHtml::_('bootstrap.addTab', 'joomlaupdate-tabs', 'online-update', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_ONLINE')); ?>
	<?php endif; ?>

	<form enctype="multipart/form-data" action="index.php" method="post" id="adminForm" class="form-horizontal">

		<?php if ($this->selfUpdate) : ?>
			<?php // If we have a self update notice to install it first! ?>
			<?php JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALL_SELF_UPDATE_FIRST'), 'error'); ?>
			<?php echo $this->loadTemplate('updatemefirst'); ?>
		<?php else : ?>
			<?php if ((!isset($this->updateInfo['object']->downloadurl->_data)
				&& !$this->updateInfo['hasUpdate'])
				|| !$this->getModel()->isDatabaseTypeSupported()
				|| !$this->getModel()->isPhpVersionSupported()) : ?>
		<?php // If we have no download URL or our PHP version or our DB type is not supported we can't reinstall or update ?>
				<?php echo $this->loadTemplate('noupdate'); ?>
			<?php elseif (!isset($this->updateInfo['object']->downloadurl->_data)) : ?>
				<?php echo $this->loadTemplate('nodownload'); ?>
			<?php elseif (!$this->updateInfo['hasUpdate']) : ?>
				<?php // If we have no update but we have a downloadurl we can reinstall the core ?>
				<?php echo $this->loadTemplate('reinstall'); ?>
			<?php else : ?>
				<?php // Ok let's show the update template ?>
				<?php echo $this->loadTemplate('update'); ?>
			<?php endif; ?>
		<?php endif; ?>

		<input type="hidden" name="task" value="update.download" />
		<input type="hidden" name="option" value="com_joomlaupdate" />

		<?php echo JHtml::_('form.token'); ?>
	</form>

	<?php // Only Super Users have access to the Update & Install for obvious security reasons ?>
	<?php if ($this->showUploadAndUpdate) : ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.addTab', 'joomlaupdate-tabs', 'upload-update', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD')); ?>
		<?php echo $this->loadTemplate('upload'); ?>
		<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	<?php endif; ?>

	<div class="download_message" style="display: none">
		<p></p>
		<p class="nowarning">
			<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DOWNLOAD_IN_PROGRESS'); ?>
		</p>
		<div class="joomlaupdate_spinner"></div>
	</div>
	<div id="loading"></div>
</div>
