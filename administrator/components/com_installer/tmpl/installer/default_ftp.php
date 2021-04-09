<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

?>
<fieldset class="option-fieldset options-form">

	<legend><?php echo Text::_('COM_INSTALLER_MSG_DESCFTPTITLE'); ?></legend>

	<div class="alert alert-info">
		<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
		<?php echo Text::_('COM_INSTALLER_MSG_DESCFTP'); ?>
	</div>

	<?php if ($this->ftp instanceof Exception) : ?>
		<p><?php echo Text::_($this->ftp->getMessage()); ?></p>
	<?php endif; ?>

	<div>
		<div class="control-group">
			<div class="control-label">
				<label id="ftp_user-lbl" for="ftp_user"><?php echo Text::_('COM_INSTALLER_FIELD_FTP_USERNAME_LABEL'); ?></label>
			</div>
			<div class="controls">
				<input type="text" name="ftp_user" id="ftp_user" class="form-control">
			</div>
		</div>

		<div class="control-group">
			<div class="control-label">
				<label id="ftp_pass-lbl" for="ftp_pass"><?php echo Text::_('COM_INSTALLER_FIELD_FTP_PASSWORD_LABEL'); ?></label>
			</div>
			<div class="controls">
				<input type="password" name="ftp_pass" id="ftp_pass" class="form-control">
			</div>
		</div>
	</div>
</fieldset>
