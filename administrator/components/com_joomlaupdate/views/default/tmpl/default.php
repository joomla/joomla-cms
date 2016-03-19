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

JHtml::_('jquery.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');
JHtml::script('com_joomlaupdate/default.js', false, true, false);

JFactory::getDocument()->addScriptDeclaration(<<< JS
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

JS
);
?>

<div id="joomlaupdate-wrapper">
	<form enctype="multipart/form-data" action="index.php" method="post" id="adminForm" class="form-horizontal">

	<?php
		if ($this->showUploadAndUpdate)
		{
			echo JHtml::_('bootstrap.startTabSet', 'joomlaupdate-tabs', array('active' => 'online-update'));
			echo JHtml::_('bootstrap.addTab', 'joomlaupdate-tabs', 'online-update', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_ONLINE'));
		}

		if (!$this->updateInfo['hasUpdate'])
		{
			echo $this->loadTemplate('reinstall');

		}
		else
		{
			echo $this->loadTemplate('update');
		}

	?>
		<input type="hidden" name="task" value="update.download" />
		<input type="hidden" name="option" value="com_joomlaupdate" />

		echo JHtml::_('form.token');
	</form>

	<?php
		// Only Super Users have access to the Update & Install for obvious security reasons
		if ($this->showUploadAndUpdate)
		{
			echo JHtml::_('bootstrap.endTab');
			echo JHtml::_('bootstrap.addTab', 'joomlaupdate-tabs', 'upload-update', JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD'));

			echo $this->loadTemplate('upload');

			echo JHtml::_('bootstrap.endTab');
			echo JHtml::_('bootstrap.endTabSet');
		}
	?>

	<div class="download_message" style="display: none">
		<p></p>
		<p class="nowarning">
			<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DOWNLOAD_IN_PROGRESS'); ?>
		</p>
		<div class="joomlaupdate_spinner"></div>
	</div>
	<div id="loading"></div>
</div>