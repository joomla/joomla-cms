<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView;

/** @var HtmlView $this */
?>

<fieldset class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATEFOUND'); ?>
	</legend>
	<p>
		<?php echo Text::sprintf($this->langKey, $this->updateSourceKey); ?>
	</p>

	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLED'); ?>
		</div>
		<div class="controls">
			<?php echo '&#x200E;' . $this->updateInfo['installed']; ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST'); ?>
		</div>
		<div class="controls">
			<?php echo '&#x200E;' . $this->updateInfo['latest']; ?>
		</div>
	</div>

	<div class="control-group">
		<div class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE'); ?>
		</div>
		<div class="controls">
			<?php echo HTMLHelper::link(
				$this->updateInfo['object']->downloadurl->_data,
				$this->updateInfo['object']->downloadurl->_data,
				[
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
					'title'  => Text::sprintf('JBROWSERTARGET_DOWNLOAD', $this->updateInfo['object']->downloadurl->_data)
				]
			); ?>
		</div>
	</div>

	<?php if (isset($this->updateInfo['object']->get('infourl')->_data)
		&& isset($this->updateInfo['object']->get('infourl')->title)) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INFOURL'); ?>
			</div>
			<div class="controls">
				<?php echo HTMLHelper::link(
					$this->updateInfo['object']->get('infourl')->_data,
					$this->updateInfo['object']->get('infourl')->title,
					[
						'target' => '_blank',
						'rel'    => 'noopener noreferrer',
						'title'  => Text::sprintf('JBROWSERTARGET_NEW_TITLE', $this->updateInfo['object']->get('infourl')->title)
					]
				); ?>
			</div>
		</div>
	<?php endif; ?>

	<div class="control-group">
		<label for="extraction_method" class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD'); ?>
		</label>
		<div class="controls">
			<?php echo $this->methodSelect; ?>
		</div>
	</div>

	<div class="control-group" id="row_ftp_hostname" <?php echo $this->ftpFieldsDisplay; ?>>
		<label for="ftp_host" class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME'); ?>
		</label>
		<div class="controls">
			<input type="text" id="ftp_host" name="ftp_host" class="form-control" value="<?php echo $this->ftp['host']; ?>">
		</div>
	</div>

	<div class="control-group" id="row_ftp_port" <?php echo $this->ftpFieldsDisplay; ?>>
		<label for="ftp_port" class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT'); ?>
		</label>
		<div class="controls">
			<input type="text" id="ftp_port" name="ftp_port" class="form-control" value="<?php echo $this->ftp['port']; ?>">
		</div>
	</div>

	<div class="control-group" id="row_ftp_username" <?php echo $this->ftpFieldsDisplay; ?>>
		<label for="ftp_user" class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME'); ?>
		</label>
		<div class="controls">
			<input type="text" id="ftp_user" name="ftp_user" class="form-control" value="<?php echo $this->ftp['username']; ?>">
		</div>
	</div>

	<div class="control-group" id="row_ftp_password" <?php echo $this->ftpFieldsDisplay; ?>>
		<label for="ftp_pass" class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD'); ?>
		</label>
		<div class="controls">
			<input type="password" id="ftp_pass" name="ftp_pass" class="form-control" value="<?php echo $this->ftp['password']; ?>">
		</div>
	</div>

	<div class="control-group" id="row_ftp_directory" <?php echo $this->ftpFieldsDisplay; ?>>
		<label for="ftp_root" class="control-label">
			<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_DIRECTORY'); ?>
		</label>
		<div class="controls">
			<input type="text" id="ftp_root" name="ftp_root" class="form-control" value="<?php echo $this->ftp['directory']; ?>">
		</div>
	</div>

	<hr>

	<div class="control-group">
		<div class="controls">
			<button class="btn btn-warning" type="submit">
				<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE'); ?>
			</button>
		</div>
	</div>
</fieldset>
