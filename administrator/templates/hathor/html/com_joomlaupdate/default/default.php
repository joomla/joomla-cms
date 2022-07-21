<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('script', 'com_joomlaupdate/default.js', array('version' => 'auto', 'relative' => true));

JText::script('JYES');
JText::script('JNO');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NO_COMPATIBILITY_INFORMATION');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_WARNING_UNKNOWN');
JText::script('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_SERVER_ERROR');

$latestJoomlaVersion  = $this->updateInfo['latest'];
$currentJoomlaVersion = $this->updateInfo['installed'];

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

var joomlaTargetVersion  = '$latestJoomlaVersion';
var joomlaCurrentVersion = '$currentJoomlaVersion';
JS
);

$showPreUpdateCheck = isset($this->updateInfo['object']->downloadurl->_data)
	&& $this->getModel()->isDatabaseTypeSupported();

?>

<div id="joomlaupdate-wrapper">
	<form enctype="multipart/form-data" action="index.php" method="post" id="adminForm" class="form-horizontal">
		<?php echo  JHtml::_('sliders.start', 'joomlaupdate-slider'); ?>
		<?php if($this->shouldDisplayPreUpdateCheck()) : ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_PRE_UPDATE_CHECK'), 'pre-update-check'); ?>
			<?php echo $this->loadTemplate('preupdatecheck'); ?>
		<?php endif; ?>
		<?php if ($this->showUploadAndUpdate) : ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_ONLINE'), 'online-update'); ?>
		<?php endif; ?>

		<?php if ($this->selfUpdate) : ?>
			<?php // If we have a self update notice to install it first! ?>
			<?php JFactory::getApplication()->enqueueMessage(JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALL_SELF_UPDATE_FIRST'), 'error'); ?>
			<?php echo $this->loadTemplate('updatemefirst'); ?>
		<?php else : ?>
			<?php if ((!isset($this->updateInfo['object']->downloadurl->_data)
				&& $this->updateInfo['installed'] < $this->updateInfo['latest'])
				|| !$this->getModel()->isDatabaseTypeSupported()) : ?>
				<?php // If we have no download URL we can't reinstall or update ?>
				<?php echo $this->loadTemplate('nodownload'); ?>
			<?php elseif (!$this->updateInfo['hasUpdate']) : ?>
				<?php // If we have no update we can reinstall the core ?>
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
		<?php echo JHtml::_('sliders.panel', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD'), 'upload-update'); ?>
		<?php echo $this->loadTemplate('upload'); ?>
		<?php echo JHtml::_('sliders.end'); ?>
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
