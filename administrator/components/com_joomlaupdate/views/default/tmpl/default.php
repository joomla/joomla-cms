<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       2.5.4
 */

defined('_JEXEC') or die;

$ftpFieldsDisplay = $this->ftp['enabled'] ? '' : 'style = "display: none"';

?>

<?php if (is_null($this->updateInfo['object'])): ?>
<div class="joomla_no_update">
	<h3><?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATES') ?></h3>
	<p>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_DEFAULT_NOUPDATESNOTICE', JVERSION); ?>
	</p>
</div>
<?php else: ?>

<form action="index.php" method="post" id="adminForm">
<input type="hidden" name="option" value="com_joomlaupdate" />
<input type="hidden" name="task" value="update.download" />

<div class="joomla_check">
	<div class="row-fluid">
		<div class="span12">
			<h3>
				<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_UPDATEFOUND') ?>
			</h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-condensed">
				<tbody>
					<tr>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLED') ?>
						</td>
						<td>
							<?php echo $this->updateInfo['installed'] ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_LATEST') ?>
						</td>
						<td>
							<?php echo $this->updateInfo['latest'] ?>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_PACKAGE') ?>
						</td>
						<td>
							<a href="<?php echo $this->updateInfo['object']->downloadurl->_data ?>">
								<?php echo $this->updateInfo['object']->downloadurl->_data ?>
							</a>
						</td>
					</tr>
					<tr>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_METHOD') ?>
						</td>
						<td>
							<?php echo $this->methodSelect ?>
						</td>
					</tr>
					<tr id="row_ftp_hostname" <?php echo $ftpFieldsDisplay ?>>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_HOSTNAME') ?>
						</td>
						<td>
							<input type="text" name="ftp_host" value="<?php echo $this->ftp['host'] ?>" />
						</td>
					</tr>
					<tr id="row_ftp_port" <?php echo $ftpFieldsDisplay ?>>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PORT') ?>
						</td>
						<td>
							<input type="text" name="ftp_port" value="<?php echo $this->ftp['port'] ?>" />
						</td>
					</tr>
					<tr id="row_ftp_username" <?php echo $ftpFieldsDisplay ?>>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_USERNAME') ?>
						</td>
						<td>
							<input type="text" name="ftp_user" value="<?php echo $this->ftp['username'] ?>" />
						</td>
					</tr>
					<tr id="row_ftp_password" <?php echo $ftpFieldsDisplay ?>>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_PASSWORD') ?>
						</td>
						<td>
							<input type="text" name="ftp_pass" value="<?php echo $this->ftp['password'] ?>" />
						</td>
					</tr>
					<tr id="row_ftp_directory" <?php echo $ftpFieldsDisplay ?>>
						<td>
							<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_FTP_DIRECTORY') ?>
						</td>
						<td>
							<input type="text" name="ftp_root" value="<?php echo $this->ftp['directory'] ?>" />
						</td>
					</tr>
					<tr>
						<td>&nbsp;
							
						</td>
						<td>
							<button class="submit" type="submit">
								<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_INSTALLUPDATE') ?>
							</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

</form>
<?php endif; ?>

<div class="download_message" style="display: none">
	<p></p>
	<p class="nowarning"> <?php echo JText::_('COM_JOOMLAUPDATE_VIEW_DEFAULT_DOWNLOAD_IN_PROGRESS'); ?></p>
	<div class="joomlaupdate_spinner"></div>
</div>
<?php if( (isset($this->options) && !empty($this->options)) || (isset($this->settings) && !empty($this->settings)) ): ?>
	<div class="joomla_check">
		<div class="row-fluid">
			<div class="span12">
				<h1><?php echo JText::_('COM_JOOMLAUPDATE_COMPATIBILITY_CHECK'); ?></h1>
				<hr class="hr-condensed" />
			</div>
			<div class="span6">
				<br />
				<h3><?php echo JText::_('COM_JOOMLAUPDATE_PRE_CHECK'); ?></h3>
				<hr class="hr-condensed" />
					<table class="table table-striped table-condensed">
						<tbody>
							<?php if(isset($this->options) && !empty($this->options)): ?>            
								<?php foreach ($this->options as $option): ?>
									<tr>
										<td class="item"> <?php echo $option->label; ?> </td>
										<td>
											<span class="label label-<?php echo ($option->state) ? 'success' : 'important'; ?>">
												<?php echo JText::_(($option->state) ? 'JYES' : 'JNO'); ?>
												<?php if ($option->notice):?>
													<i class="icon-info-sign icon-white hasTooltip" title="<?php echo $option->notice; ?>"></i>
												<?php endif;?>                        
											</span>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php else: ?>
							<tr>
								<td colspan="2"><?php echo JText::_('COM_JOOMLAUPDATE_NO_DATA_AVAILABLE'); ?></td>
							</tr>
							<?php endif; ?>
						</tbody>
					</table>
			</div>
			<!-- close span -->
			<div class="span6">
				<br />
				<h3><?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?></h3>
				<hr class="hr-condensed" />
				<p class="install-text"><?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?></p>
					<table class="table table-striped table-condensed">
						<thead>
							<tr>
								<th> <?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_DIRECTIVE'); ?> </th>
								<th> <?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_RECOMMENDED'); ?> </th>
								<th> <?php echo JText::_('COM_JOOMLAUPDATE_INSTL_PRECHECK_ACTUAL'); ?> </th>
							</tr>
						</thead>
						<tbody>
						<?php if(isset($this->settings) && !empty($this->settings)): ?>            
							<?php foreach ($this->settings as $setting) : ?>
								<tr>
									<td> <?php echo $setting->label; ?> </td>
									<td><span class="label label-success disabled"> <?php echo JText::_(($setting->recommended) ? 'JON' : 'JOFF'); ?> </span></td>
									<td><span class="label label-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>"> <?php echo JText::_(($setting->state) ? 'JON' : 'JOFF'); ?> </span></td>
								</tr>
							<?php endforeach; ?>
						<?php else: ?>
							<tr>
								<td colspan="3"><?php echo JText::_('COM_JOOMLAUPDATE_NO_DATA_AVAILABLE'); ?></td>
							</tr>
						<?php endif; ?>
						</tbody>
					</table>
			</div>
			<!-- close span -->

			<div class="span12">
				<br />
				<h3><?php echo JText::_('COM_JOOMLAUPDATE_EXTENSIONS_PRE_CHECK'); ?></h3>
				<hr class="hr-condensed" />
				<table class="table table-striped table-condensed">
					<thead>
						<tr>
							<th> <?php echo JText::_('COM_JOOMLAUPDATE_EXTENSION_NAME'); ?> </th>
							<th> <?php echo JText::_('COM_JOOMLAUPDATE_COMPATIBLE'); ?> </th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="3"><br />
								<?php echo JText::_('COM_JOOMLAUPDATE_MISSING_TAG_LABEL'); ?> <span class="label label-warning"> <?php echo JText::_('COM_JOOMLAUPDATE_MISSING_TAG_DESCRIPTION'); ?></span><br />
								<br />
								<?php echo JText::_('COM_JOOMLAUPDATE_MARKED_DESCRIPTION_FIRST'); ?> <span class="label label-important"><?php echo JText::_('JNO'); ?></span> or <span class="label label-warning"><?php echo JText::_('COM_JOOMLAUPDATE_MISSING_TAG_MARK'); ?></span> <?php echo JText::_('COM_JOOMLAUPDATE_MARKED_DESCRIPTION_LAST'); ?>
							</td>
						</tr>
					</tfoot>
					<tbody>
						<tr>
							<td> K2 </td>
							<td><span class="label label-success"> <?php echo JText::_('JYES'); ?> </span></td>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- close span --> 
		</div>
		<!-- close row-fluid --> 
	</div>
	<!-- close joomla_check --> 
<?php endif;?>