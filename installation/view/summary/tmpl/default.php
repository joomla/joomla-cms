<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewSummaryHtml $this */

// Determine if the configuration file path is writable.
$path = JPATH_CONFIGURATION . '/configuration.php';
$useftp = (file_exists($path)) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');
$prev = $useftp ? 'ftp' : 'database';
?>
<?php echo JHtml::_('installation.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
			<a class="btn" href="#" onclick="return Install.goToPage('<?php echo $prev; ?>');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><i class="icon-arrow-left"></i> <?php echo JText::_('JPrevious'); ?></a>
			<a class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('INSTL_SUMMARY_INSTALL'); ?>"><i class="icon-arrow-right icon-white"></i> <?php echo JText::_('INSTL_SUMMARY_INSTALL'); ?></a>
		</div>
	</div>

	<h3><?php echo JText::_('INSTL_FINALISATION'); ?></h3>
	<hr class="hr-condensed" />

	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('sample_file'); ?>
		</div>
		<div class="controls">
			<div class="help-block">
				<?php echo $this->form->getInput('sample_file'); ?>
			</div>
			<p class="help-block">
				<?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_DESC'); ?>
			</p>
		</div>
	</div>

	<h3><?php echo JText::_('INSTL_STEP_SUMMARY_LABEL'); ?></h3>
	<hr class="hr-condensed" />

	<div class="control-group" id="summary_email">
		<div class="control-label">
			<?php echo $this->form->getLabel('summary_email'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('summary_email'); ?>
			<p class="help-block">
				<?php echo JText::sprintf('INSTL_SUMMARY_EMAIL_DESC', '<span class="label">' . $this->options['admin_email'] . '</span>'); ?>
			</p>
		</div>
	</div>

	<div class="control-group" id="email_passwords" style="display:none;">
		<div class="control-label">
			<?php echo $this->form->getLabel('summary_email_passwords'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('summary_email_passwords'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_SUMMARY_EMAIL_PASSWORDS_DESC'); ?>
			</p>
		</div>
	</div>

	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo JText::_('INSTL_SITE'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-condensed">
				<tbody>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_SITE_NAME_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['site_name']; ?>
						</td>
					</tr>
					<?php if ($this->options['site_metadesc']) : ?>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_SITE_METADESC_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['site_metadesc']; ?>
						</td>
					</tr>
					<?php endif; ?>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_SITE_OFFLINE_LABEL'); ?>
						</td>
						<td>
							<span class="label label-<?php echo ($this->options['site_offline']) ? 'success' : 'important'; ?>">
								<?php echo JText::_(($this->options['site_offline']) ? 'JYES' : 'JNO'); ?>
							</span>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_ADMIN_EMAIL_LABEL'); ?>
						</td>
						<td>
							<span class="label"><?php echo $this->options['admin_email']; ?></span>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_ADMIN_USER_LABEL'); ?>
						</td>
						<td>
							<span class="label"><?php echo $this->options['admin_user']; ?></span>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_ADMIN_PASSWORD_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['admin_password'] ? '***': ''; ?>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2"></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="span6">
			<h3><?php echo JText::_('INSTL_DATABASE'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-condensed">
				<tbody>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_DATABASE_TYPE_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['db_type']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_DATABASE_HOST_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['db_host']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_DATABASE_USER_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['db_user']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_DATABASE_PASSWORD_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['db_pass'] ? '***': ''; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_DATABASE_NAME_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['db_name']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_DATABASE_PREFIX_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['db_prefix']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_DATABASE_OLD_PROCESS_LABEL'); ?>
						</td>
						<td>
							<span class="label label-<?php echo ($this->options['db_old'] == 'remove') ? 'important' : 'success'; ?>">
								<?php echo JText::_(($this->options['db_old'] == 'remove') ? 'INSTL_DATABASE_FIELD_VALUE_REMOVE' : 'INSTL_DATABASE_FIELD_VALUE_BACKUP'); ?>
							</span>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2"></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<?php if ($useftp) : ?>
	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo JText::_('INSTL_FTP'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-condensed">
				<tbody>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_FTP_ENABLE_LABEL'); ?>
						</td>
						<td>
							<span class="label label-<?php echo ($this->options['ftp_enable']) ? 'success' : 'important'; ?>">
								<?php echo JText::_(($this->options['ftp_enable']) ? 'JYES' : 'JNO'); ?>
							</span>
						</td>
					</tr>
					<?php if($this->options['ftp_enable']) : ?>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_FTP_USER_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['ftp_user']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_FTP_PASSWORD_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['ftp_pass'] ? '***': ''; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_FTP_HOST_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['ftp_host']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_FTP_PORT_LABEL'); ?>
						</td>
						<td>
							<?php echo $this->options['ftp_port']; ?>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_FTP_SAVE_LABEL'); ?>
						</td>
						<td>
							<span class="label label-<?php echo ($this->options['ftp_save']) ? 'important' : 'success'; ?>">
								<?php echo JText::_(($this->options['ftp_save']) ? 'JYES' : 'JNO'); ?>
							</span>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
				<tfoot>
				<tr>
					<td colspan="2"></td>
				</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<?php endif; ?>
	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo JText::_('INSTL_PRECHECK_TITLE'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-condensed">
				<tbody>
				<?php foreach ($this->phpoptions as $option) : ?>
					<tr>
						<td class="item">
							<?php echo $option->label; ?>
						</td>
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
				</tbody>
				<tfoot>
					<tr>
						<td colspan="2"></td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div class="span6">
			<h3><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?></h3>
			<hr class="hr-condensed" />
			<p class="install-text">
				<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?>
			</p>
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_DIRECTIVE'); ?>
						</th>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_RECOMMENDED'); ?>
						</th>
						<th>
							<?php echo JText::_('INSTL_PRECHECK_ACTUAL'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->phpsettings as $setting) : ?>
					<tr>
						<td>
							<?php echo $setting->label; ?>
						</td>
						<td>
							<span class="label label-success disabled">
								<?php echo JText::_(($setting->recommended) ? 'JON' : 'JOFF'); ?>
							</span>
						</td>
						<td>
							<span class="label label-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
								<?php echo JText::_(($setting->state) ? 'JON' : 'JOFF'); ?>
							</span>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="3"></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

	<input type="hidden" name="task" value="summary" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	window.addEvent('domready', function() {
		document.getElements('input[name=jform[summary_email]]').each(function(el){
			el.addEvent('click', function(){Install.toggle('email_passwords', 'summary_email', 1);});
		});
		Install.toggle('email_passwords', 'summary_email', 1);
	});
</script>
