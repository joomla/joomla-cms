<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var JoomlaupdateViewDefault $this */
?>
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
				<?php echo '&#x200E;' . $this->updateInfo['installed']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST'); ?>
			</td>
			<td>
				<?php echo '&#x200E;' . $this->updateInfo['latest']; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE'); ?>
			</td>
			<td>
				<a href="<?php echo $this->updateInfo['object']->downloadurl->_data; ?>" target="_blank" rel="noopener noreferrer">
					<?php echo $this->updateInfo['object']->downloadurl->_data; ?>
					<span class="icon-out-2" aria-hidden="true"></span>
					<span class="element-invisible"><?php echo JText::_('JBROWSERTARGET_NEW'); ?></span>
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
					<a href="<?php echo $this->updateInfo['object']->get('infourl')->_data; ?>" target="_blank" rel="noopener noreferrer">
						<?php echo $this->updateInfo['object']->get('infourl')->title; ?>
						<span class="icon-out-2" aria-hidden="true"></span>
						<span class="element-invisible"><?php echo JText::_('JBROWSERTARGET_NEW'); ?></span>
					</a>
				</td>
			</tr>
		<?php endif; ?>
		<?php // Hide FTP settings when updating to Joomla 4 given that the supporting code has been dropped there ?>
		<?php if (version_compare($this->updateInfo['latest'], '4', '<')) : ?>
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
		<?php endif; ?>
		</tbody>
		<tfoot>
		<tr id="preupdateCheckWarning">
			<td colspan="2">
				<div class="alert">
					<h4 class="alert-heading">
						<?php echo JText::_('WARNING'); ?>
					</h4>
					<div class="alert-message">
						<div class="preupdateCheckIncomplete">
							<?php echo JText::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_NOT_COMPLETE'); ?>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<tr id="preupdateCheckCompleteProblems" class="hidden">
			<td colspan="2">
				<div class="alert">
					<h4 class="alert-heading">
						<?php echo JText::_('WARNING'); ?>
					</h4>
					<div class="alert-message">
						<div class="preupdateCheckComplete">
							<?php echo JText::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_COMPLETED_YOU_HAVE_DANGEROUS_PLUGINS'); ?>
						</div>
					</div>
				</div>
			</td>
		</tr>
		<tr id="preupdateconfirmation" >
			<td colspan="2">
				<label  class="preupdateconfirmation_label label label-warning span12">
					<h3>
						<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NON_CORE_PLUGIN_BEING_CHECKED'); ?>
					</h3>
				</label>
			</td>
		</tr>
		<tr id="preupdatecheckheadings">
			<td colspan="2">
				<table class="table table-striped">
					<thead>
						<th>
							<?php echo JText::_('COM_INSTALLER_TYPE_PLUGIN'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_INSTALLER_TYPE_PACKAGE'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_INSTALLER_AUTHOR_INFORMATION'); ?>
						</th>
						<th>
							<?php echo JText::_('COM_JOOMLAUPDATE_PREUPDATE_CHECK_EXTENSION_AUTHOR_URL'); ?>
						</th>
					</thead>
					<tbody>
						<?php foreach ($this->nonCoreCriticalPlugins as $nonCoreCriticalPlugin) : ?>
							<tr id='plg_<?php echo $nonCoreCriticalPlugin->extension_id ?>'>
								<td>
									<?php echo JText::_($nonCoreCriticalPlugin->name); ?>
								</td>
								<?php if ($nonCoreCriticalPlugin->package_id > 0) : ?>
									<?php foreach ($this->nonCoreExtensions as $nonCoreExtension) : ?>
										<?php if ($nonCoreCriticalPlugin->package_id == $nonCoreExtension->extension_id) : ?>
											<td>
												<?php echo $nonCoreExtension->name; ?>
											</td>
										<?php endif; ?>
									<?php endforeach; ?>
								<?php else : ?>
									<td/>
								<?php endif; ?>
								<td>
									<?php if (isset($nonCoreCriticalPlugin->manifest_cache->author)) : ?>
									<?php echo JText::_($nonCoreCriticalPlugin->manifest_cache->author); ?>
									<?php elseif ($nonCoreCriticalPlugin->package_id > 0) : ?>
									<?php foreach ($this->nonCoreExtensions as $nonCoreExtension) : ?>
										<?php if ($nonCoreCriticalPlugin->package_id == $nonCoreExtension->extension_id) : ?>
										<td>
											<?php echo $nonCoreExtension->name; ?>
										</td>
										<?php endif; ?>
									<?php endforeach; ?>
									<?php endif; ?>
								</td>
								<td>
									<?php $authorURL = ''; ?>
									<?php if (isset($nonCoreCriticalPlugin->manifest_cache->authorUrl)) : ?>
										<?php $authorURL = $nonCoreCriticalPlugin->manifest_cache->authorUrl; ?>
									<?php elseif ($nonCoreCriticalPlugin->package_id > 0) : ?>
										<?php foreach ($this->nonCoreExtensions as $nonCoreExtension) : ?>
											<?php if ($nonCoreCriticalPlugin->package_id == $nonCoreExtension->extension_id) : ?>
												<?php $authorURL = $nonCoreExtension->manifest_cache->authorUrl; ?>
											<?php endif; ?>
										<?php endforeach; ?>
									<?php endif; ?>
									<?php if (!empty($authorURL)) : ?>
										<a href="<?php echo $authorURL; ?>" target="_blank">
											<?php echo $authorURL; ?>
											<span class="icon-out-2" aria-hidden="true"></span>
											<span class="element-invisible">
												<?php echo JText::_('JBROWSERTARGET_NEW'); ?>
											</span>
										</a>
									<?php endif;?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</td>
		</tr>
		<tr id="preupdatecheckbox">
			<td>
				<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NON_CORE_PLUGIN_CONFIRMATION'); ?>
			</td>
			<td>
				<input type="checkbox" id="noncoreplugins" name="noncoreplugins" value="1" required aria-required="true" />
			</td>
		</tr>

		<tr>
			<td>
				&nbsp;
			</td>
			<td>
				<button class="btn btn-primary disabled submitupdate" type="submit" disabled>
					<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE'); ?>
				</button>
			</td>
		</tr>
		</tfoot>
	</table>
</fieldset>
