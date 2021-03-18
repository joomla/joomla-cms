<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Updater\Update;

/** @var \Joomla\Component\Joomlaupdate\Administrator\View\Joomlaupdate\HtmlView $this */
?>
<fieldset class="options-form">
	<legend>
		<?php echo Text::_('COM_JOOMLAUPDATE_UPDATE_CHECK'); ?>
	</legend>
	<p class="alert alert-info">
		<span class="fas fa-info-circle" aria-hidden="true"></span>
		<span class="sr-only"><?php echo Text::_('NOTICE'); ?></span>
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATES'); ?>
	</p>
	<p><?php echo Text::sprintf($this->langKey, $this->updateSourceKey); ?></p>
	<p class="alert alert-success">
		<span class="fas fa-check-circle" aria-hidden="true"></span><span class="sr-only"><?php echo Text::_('NOTICE'); ?></span>
		<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATESNOTICE', '&#x200E;' . JVERSION); ?>
	</p>
	<?php if (is_object($this->updateInfo['object']) && ($this->updateInfo['object'] instanceof Update)) : ?>
	<div>
		<table class="table">
			<tbody>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE_REINSTALL'); ?>
				</td>
				<td>
					<a href="<?php echo $this->updateInfo['object']->downloadurl->_data; ?>" target="_blank" rel="noopener noreferrer"
						title="<?php echo TEXT::sprintf('JBROWSERTARGET_NEW_TITLE', $this->updateInfo['object']->downloadurl->_data); ?>">
						<?php echo $this->updateInfo['object']->downloadurl->_data; ?>
					</a>
				</td>
			</tr>
			<?php if (isset($this->updateInfo['object']->get('infourl')->_data)
				&& isset($this->updateInfo['object']->get('infourl')->title)) : ?>
				<tr>
					<td>
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INFOURL'); ?>
					</td>
					<td>
						<a href="<?php echo $this->updateInfo['object']->get('infourl')->_data; ?>" target="_blank" rel="noopener noreferrer"
							title="<?php echo TEXT::sprintf('JBROWSERTARGET_NEW_TITLE', $this->updateInfo['object']->get('infourl')->title); ?>">
							<?php echo $this->updateInfo['object']->get('infourl')->title; ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD'); ?>
				</td>
				<td>
					<?php echo $this->methodSelect; ?>
				</td>
			</tr>
			<tr id="row_ftp_hostname" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME'); ?>
				</td>
				<td>
					<input class="form-control" class="form-control" type="text" name="ftp_host" value="<?php echo $this->ftp['host']; ?>">
				</td>
			</tr>
			<tr id="row_ftp_port" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT'); ?>
				</td>
				<td>
					<input class="form-control" type="text" name="ftp_port" value="<?php echo $this->ftp['port']; ?>">
				</td>
			</tr>
			<tr id="row_ftp_username" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME'); ?>
				</td>
				<td>
					<input class="form-control" type="text" name="ftp_user" value="<?php echo $this->ftp['username']; ?>">
				</td>
			</tr>
			<tr id="row_ftp_password" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD'); ?>
				</td>
				<td>
					<input class="form-control" type="password" name="ftp_pass" value="<?php echo $this->ftp['password']; ?>">
				</td>
			</tr>
			<tr id="row_ftp_directory" <?php echo $this->ftpFieldsDisplay; ?>>
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
					<button class="btn btn-warning" type="submit">
						<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLAGAIN'); ?>
					</button>
				</td>
			</tr>
			</tfoot>
		</table>
	</div>
	<?php endif; ?>

</fieldset>
