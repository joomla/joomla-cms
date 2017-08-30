<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */
?>

<script>
	var joomlaTargetVersion = "<?php echo $this->updateInfo['latest']; ?>";
</script>

<fieldset>
	<legend>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATEFOUND'); ?>
	</legend>
	<p>
		<?php echo JText::sprintf($this->langKey, $this->updateSourceKey); ?>
	</p>

	<table class="table table-striped">
		<tbody>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLED'); ?>
				</td>
				<td>
					<?php echo $this->updateInfo['installed']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST'); ?>
				</td>
				<td>
					<?php echo $this->updateInfo['latest']; ?>
				</td>
			</tr>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE'); ?>
				</td>
				<td>
					<a href="<?php echo $this->updateInfo['object']->downloadurl->_data; ?>">
						<?php echo $this->updateInfo['object']->downloadurl->_data; ?>
					</a>
				</td>
			</tr>
			<?php if (isset($this->updateInfo['object']->get('infourl')->_data)
				&& isset($this->updateInfo['object']->get('infourl')->title)) : ?>
				<tr>
					<td>
						<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INFOURL'); ?>
					</td>
					<td>
						<a href="<?php echo $this->updateInfo['object']->get('infourl')->_data; ?>">
							<?php echo $this->updateInfo['object']->get('infourl')->title; ?>
						</a>
					</td>
				</tr>
			<?php endif; ?>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD'); ?>
				</td>
				<td>
					<?php echo $this->methodSelect; ?>
				</td>
			</tr>
			<tr id="row_ftp_hostname" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME'); ?>
				</td>
				<td>
					<input type="text" name="ftp_host" value="<?php echo $this->ftp['host']; ?>" />
				</td>
			</tr>
			<tr id="row_ftp_port" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT'); ?>
				</td>
				<td>
					<input type="text" name="ftp_port" value="<?php echo $this->ftp['port']; ?>" />
				</td>
			</tr>
			<tr id="row_ftp_username" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME'); ?>
				</td>
				<td>
					<input type="text" name="ftp_user" value="<?php echo $this->ftp['username']; ?>" />
				</td>
			</tr>
			<tr id="row_ftp_password" <?php echo $this->ftpFieldsDisplay; ?>>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD'); ?>
				</td>
				<td>
					<input type="password" name="ftp_pass" value="<?php echo $this->ftp['password']; ?>" />
				</td>
			</tr>
			<tr id="row_ftp_directory" <?php echo $this->ftpFieldsDisplay; ?>>
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
					<button class="btn btn-primary" type="submit">
						<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE'); ?>
					</button>
				</td>
			</tr>
		</tfoot>
	</table>
</fieldset>

<h2>
	<?php printf(JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_COMPATIBILITY_CHECK'), $this->updateInfo['latest']); ?>
</h2>

<fieldset class="col">
	<legend>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PREUPDATE_CHECK'); ?>
	</legend>
	<table class="table">
		<tbody>
			<?php foreach ($this->phpOptions as $option) : ?>
				<tr>
					<td>
						<?php echo $option->label; ?>
					</td>
					<td>
						<span class="label label-<?php echo $option->state ? 'success' : 'important'; ?>">
							<?php echo JText::_($option->state ? 'JYES' : 'JNO'); ?>
							<?php if ($option->notice) : ?>
								<span class="icon-info icon-white hasTooltip" title="<?php echo $option->notice; ?>"></span>
							<?php endif; ?>
						</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>

<fieldset class="col">
	<legend>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS'); ?>
	</legend>
	<p>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED_SETTINGS_DESC'); ?>
	</p>

	<table class="table">
		<thead>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DIRECTIVE'); ?>
				</td>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_RECOMMENDED'); ?>
				</td>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_ACTUAL'); ?>
				</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->phpSettings as $setting) : ?>
				<tr>
					<td>
						<?php echo $setting->label; ?>
					</td>
					<td>
						<?php echo JText::_($setting->recommended ? 'JON' : 'JOFF'); ?>
					</td>
					<td>
						<span class="label label-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
							<?php echo JText::_($setting->state ? 'JON' : 'JOFF'); ?>
						</span>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>

<fieldset class="col">
	<legend>
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSIONS'); ?>
	</legend>

	<table class="table">
		<thead>
			<tr>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_NAME'); ?>
				</td>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_COMPATIBLE'); ?>
				</td>
				<td>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_EXTENSION_INSTALLED_VERSION'); ?>
				</td>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->nonCoreExtensions as $extension) : ?>
				<tr>
					<td>
						<?php echo JText::_($extension->name); ?>
					</td>
					<td class="extension-check"
						data-extension-id="<?php echo $extension->extension_id; ?>"
						data-extension-current-version="<?php echo $extension->version; ?>">
						<img src="../media/system/images/mootree_loader.gif" />
					</td>
					<td>
						<?php echo $extension->version; ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</fieldset>

<p><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_BREAK'); ?></p>
<p><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_MISSING_TAG'); ?></p>
<p><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DESCRIPTION_UPDATE_REQUIRED'); ?></p>
