<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */

$errSelectPackage = JText::_('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true);
$errPackageTooBig = JText::_('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG', true);
$txtPackageSize   = JText::_('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE', true);
$js               = <<< JS
	Joomla.submitbuttonUpload = function() {
		var form = document.getElementById("uploadForm");

		// do field validation
		if (form.install_package.value == "") {
			alert("$errSelectPackage");
		}
		else if (form.install_package.files[0].size > form.max_upload_size.value) {
			alert("$errPackageTooBig");
		}
		else
		{
			jQuery("#loading").css("display", "block");

			form.submit();
		}
	};

	Joomla.installpackageChange = function() {
		var form = document.getElementById('uploadForm');
		var fileSize = form.install_package.files[0].size;
		var fileSizeMB = fileSize * 1.0 / 1024.0 / 1024.0;
		var fileSizeText = "$txtPackageSize";
		var fileSizeElement = document.getElementById('file_size');
		var warningElement  = document.getElementById('max_upload_size_warn');

		if (form.install_package.value == '') {
			fileSizeElement.classList.add('hidden');
			warningElement .classList.add('hidden');
		}
		else if (fileSize) {
			fileSizeElement.classList.remove('hidden');
			fileSizeElement.innerHTML = fileSizeText.replace('%s', fileSizeMB.toFixed(2) + ' MB');

			if (fileSize > form.max_upload_size.value) {
				warningElement .classList.remove('hidden');
			} else {
				warningElement .classList.add('hidden');
			}
		}
	};

	// Add spindle-wheel for installations:
	jQuery(document).ready(function($) {
		var outerDiv = $("#joomlaupdate-wrapper");

		$("#loading")
		.css("top", outerDiv.position().top - $(window).scrollTop())
		.css("left", "0")
		.css("width", "100%")
		.css("height", "100%")
		.css("display", "none")
		.css("margin-top", "-10px");
	});

JS;

JFactory::getDocument()->addScriptDeclaration($js);

$ajaxLoaderImage = JHtml::_('image', 'jui/ajax-loader.gif', '', null, true, true);
$css             = <<< CSS
	#loading {
		background: rgba(255, 255, 255, .8) url('$ajaxLoaderImage') 50% 15% no-repeat;
		position: fixed;
		opacity: 1;
		-ms-filter: progid:DXImageTransform.Microsoft.Alpha(Opacity = 80);
		filter: alpha(opacity = 80);
		overflow: hidden;
	}
CSS;
JFactory::getDocument()->addStyleDeclaration($css);
?>

<div class="alert alert-info">
	<p>
		<span class="icon icon-info" aria-hidden="true"></span>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPLOAD_INTRO', 'https://downloads.joomla.org/latest'); ?>
	</p>
</div>

<?php if (count($this->warnings)) : ?>
<fieldset>
	<legend>
		<?php echo JText::_('COM_INSTALLER_SUBMENU_WARNINGS'); ?>
	</legend>

	<?php $i = 0; ?>
	<?php echo JHtml::_('bootstrap.startAccordion', 'warnings', array('active' => 'warning' . $i)); ?>
	<?php foreach ($this->warnings as $message) : ?>
		<?php echo JHtml::_('bootstrap.addSlide', 'warnings', $message['message'], 'warning' . ($i++)); ?>
		<?php echo $message['description']; ?>
		<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php endforeach; ?>
	<?php echo JHtml::_('bootstrap.addSlide', 'warnings', JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'), 'furtherinfo'); ?>
	<?php echo JText::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC'); ?>
	<?php echo JHtml::_('bootstrap.endSlide'); ?>
	<?php echo JHtml::_('bootstrap.endAccordion'); ?>
</fieldset>
<?php endif; ?>

<form enctype="multipart/form-data" action="index.php" method="post" id="uploadForm" class="form-horizontal">
	<fieldset class="uploadform">
		<legend><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD'); ?></legend>
		<table class="table table-striped">
			<tbody>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPLOAD_PACKAGE_FILE'); ?>
				</td>
				<td>
					<input class="input_box" id="install_package" name="install_package" type="file" size="57" accept=".zip,application/zip" onchange="Joomla.installpackageChange()" /><br>
					<?php $maxSizeBytes = JUtility::getMaxUploadSize(); ?>
					<?php $maxSize = JHtml::_('number.bytes', $maxSizeBytes); ?>
					<input id="max_upload_size" name="max_upload_size" type="hidden" value="<?php echo $maxSizeBytes; ?>" />
					<div class="small"><?php echo JText::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', '&#x200E;' . $maxSize); ?></div>
					<div class="small hidden" id="file_size" name="file_size"><?php echo JText::sprintf('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE', '&#x200E;' . ''); ?></div>
					<div class="alert alert-warning hidden" id="max_upload_size_warn">
						<?php echo JText::_('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG'); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD'); ?>
				</td>
				<td>
					<?php echo $this->methodSelectUpload; ?>
				</td>
			</tr>
			<tr id="upload_ftp_hostname" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME'); ?>
				</td>
				<td>
					<input type="text" name="ftp_host" value="<?php echo $this->ftp['host']; ?>" />
				</td>
			</tr>
			<tr id="upload_ftp_port" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT'); ?>
				</td>
				<td>
					<input type="text" name="ftp_port" value="<?php echo $this->ftp['port']; ?>" />
				</td>
			</tr>
			<tr id="upload_ftp_username" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME'); ?>
				</td>
				<td>
					<input type="text" name="ftp_user" value="<?php echo $this->ftp['username']; ?>" />
				</td>
			</tr>
			<tr id="upload_ftp_password" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD'); ?>
				</td>
				<td>
					<input type="password" name="ftp_pass" value="<?php echo $this->ftp['password']; ?>" />
				</td>
			</tr>
			<tr id="upload_ftp_directory" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_DIRECTORY'); ?>
				</td>
				<td>
					<input type="text" name="ftp_root" value="<?php echo $this->ftp['directory']; ?>" />
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td>
					&nbsp;
				</td>
				<td>
					<button class="btn btn-primary" type="button" onclick="Joomla.submitbuttonUpload()"><?php echo JText::_('COM_INSTALLER_UPLOAD_AND_INSTALL'); ?></button>
				</td>
			</tr>
			</tfoot>
		</table>
	</fieldset>

	<input type="hidden" name="task" value="update.upload" />
	<input type="hidden" name="option" value="com_joomlaupdate" />
	<?php echo JHtml::_('form.token'); ?>

</form>
