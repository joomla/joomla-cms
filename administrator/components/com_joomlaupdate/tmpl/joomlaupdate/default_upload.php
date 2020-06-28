<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Utility\Utility;

/** @var JoomlaupdateViewDefault $this */

HTMLHelper::_('behavior.core');
Text::script('COM_INSTALLER_MSG_INSTALL_PLEASE_SELECT_A_PACKAGE', true);
Text::script('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG', true);
Text::script('COM_JOOMLAUPDATE_MSG_WARNINGS_UPLOADFILESUFFIX', true);
Text::script('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE', true);
?>

<div class="alert alert-info">
	<span class="fas fa-info-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('INFO'); ?></span>
	<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPLOAD_INTRO', 'https://downloads.joomla.org/latest'); ?>
</div>

<?php if (count($this->warnings)) : ?>
	<h3><?php echo Text::_('COM_INSTALLER_SUBMENU_WARNINGS'); ?></h3>
	<?php foreach ($this->warnings as $warning) : ?>
		<div class="alert alert-warning">
			<h4 class="alert-heading">
				<span class="fas fa-exclamation-triangle" aria-hidden="true"></span>
				<span class="sr-only"><?php echo Text::_('WARNING'); ?></span>
				<?php echo $warning['message']; ?>
			</h4>
			<p class="mb-0"><?php echo $warning['description']; ?></p>
		</div>
	<?php endforeach; ?>
	<div class="alert alert-info">
		<h4 class="alert-heading">
			<span class="fas fa-info-circle" aria-hidden="true"></span>
			<span class="sr-only"><?php echo Text::_('INFO'); ?></span>
			<?php echo Text::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'); ?>
		</h4>
		<p class="mb-0"><?php echo Text::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC'); ?></p>
	</div>
<?php endif; ?>

<form enctype="multipart/form-data" action="index.php" method="post" id="uploadForm">
	<fieldset class="uploadform options-form">
		<legend><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_TAB_UPLOAD'); ?></legend>
		<table class="table">
			<tbody>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPLOAD_PACKAGE_FILE'); ?>
				</td>
				<td>
					<input class="form-control-file" id="install_package" name="install_package" type="file" size="57" onchange="Joomla.installpackageChange()">
					<?php $maxSizeBytes = Utility::getMaxUploadSize(); ?>
					<?php $maxSize = HTMLHelper::_('number.bytes', $maxSizeBytes); ?>
					<input id="max_upload_size" name="max_upload_size" type="hidden" value="<?php echo $maxSizeBytes; ?>" />
					<small class="form-text text-muted"><?php echo Text::sprintf('JGLOBAL_MAXIMUM_UPLOAD_SIZE_LIMIT', '&#x200E;' . $maxSize); ?></small>
					<small class="form-text text-muted hidden" id="file_size" name="file_size"><?php echo Text::sprintf('JGLOBAL_SELECTED_UPLOAD_FILE_SIZE', '&#x200E;' . ''); ?></small>
					<div class="alert alert-warning hidden" id="max_upload_size_warn">
						<?php echo Text::_('COM_INSTALLER_MSG_WARNINGS_UPLOADFILETOOBIG'); ?>
					</div>
					<div class="alert alert-warning hidden" id="wrong_file_suffix_warn">
						<?php echo Text::_('COM_JOOMLAUPDATE_MSG_WARNINGS_UPLOADFILESUFFIX'); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD'); ?>
				</td>
				<td>
					<?php echo $this->methodSelectUpload; ?>
				</td>
			</tr>
			<tr id="upload_ftp_hostname" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME'); ?>
				</td>
				<td>
					<input class="form-control" type="text" name="ftp_host" value="<?php echo $this->ftp['host']; ?>">
				</td>
			</tr>
			<tr id="upload_ftp_port" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT'); ?>
				</td>
				<td>
					<input class="form-control" type="text" name="ftp_port" value="<?php echo $this->ftp['port']; ?>">
				</td>
			</tr>
			<tr id="upload_ftp_username" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME'); ?>
				</td>
				<td>
					<input class="form-control" type="text" name="ftp_user" value="<?php echo $this->ftp['username']; ?>">
				</td>
			</tr>
			<tr id="upload_ftp_password" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD'); ?>
				</td>
				<td>
					<input class="form-control" type="password" name="ftp_pass" value="<?php echo $this->ftp['password']; ?>">
				</td>
			</tr>
			<tr id="upload_ftp_directory" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_DIRECTORY'); ?>
				</td>
				<td>
					<input class="form-control" type="text" name="ftp_root" value="<?php echo $this->ftp['directory']; ?>">
				</td>
			</tr>
			</tbody>
			<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>
					<button id="uploadButton" class="btn btn-primary" type="button" onclick="Joomla.submitbuttonUpload()"><?php echo Text::_('COM_INSTALLER_UPLOAD_AND_INSTALL'); ?></button>
				</td>
			</tr>
			</tfoot>
		</table>
	</fieldset>

	<input type="hidden" name="task" value="update.upload">
	<input type="hidden" name="option" value="com_joomlaupdate">
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
