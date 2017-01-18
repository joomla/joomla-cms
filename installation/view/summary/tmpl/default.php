<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewSummaryHtml $this */

// Determine if the configuration file path is writable.
$path = JPATH_CONFIGURATION . '/configuration.php';
$useftp = file_exists($path) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');
$prev = $useftp ? 'ftp' : 'database';
?>
<?php echo JHtml::_('InstallationHtml.helper.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a class="btn btn-secondary" href="#" onclick="return Install.goToPage('<?php echo $prev; ?>');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><span class="fa fa-arrow-left"></span> <?php echo JText::_('JPREVIOUS'); ?></a>
			<a class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('INSTL_SUMMARY_INSTALL'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('INSTL_SUMMARY_INSTALL'); ?></a>
		</div>
	</div>

	<h3><?php echo JText::_('INSTL_FINALISATION'); ?></h3>
	<hr class="hr-condensed" />

	<div class="form-group">
		<?php echo $this->form->getLabel('sample_file'); ?>
		<div class="form-text text-muted small">
			<?php echo $this->form->getInput('sample_file'); ?>
		</div>
		<p class="form-text text-muted small"><?php echo JText::_('INSTL_SITE_INSTALL_SAMPLE_DESC'); ?></p>
	</div>

	<h3><?php echo JText::_('INSTL_STEP_SUMMARY_LABEL'); ?></h3>
	<hr class="hr-condensed" />

	<div class="form-group" id="summary_email">
		<?php echo $this->form->getLabel('summary_email'); ?>
		<?php echo $this->form->getInput('summary_email'); ?>
		<p class="form-text text-muted small">
			<?php echo JText::sprintf('INSTL_SUMMARY_EMAIL_DESC', '<span class="badge badge-default">' . $this->options['admin_email'] . '</span>'); ?>
		</p>
	</div>

	<div class="form-group" id="email_passwords" style="display:none;">
		<?php echo $this->form->getLabel('summary_email_passwords'); ?>
		<?php echo $this->form->getInput('summary_email_passwords'); ?>
		<p class="form-text text-muted small"><?php echo JText::_('INSTL_SUMMARY_EMAIL_PASSWORDS_DESC'); ?></p>
	</div>

	<div class="row">
		<div class="col-md-6">
			<h3><?php echo JText::_('INSTL_SITE'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-sm">
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
							<span class="badge badge-<?php echo $this->options['site_offline'] ? 'success' : 'important'; ?>">
								<?php echo JText::_($this->options['site_offline'] ? 'JYES' : 'JNO'); ?>
							</span>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_ADMIN_EMAIL_LABEL'); ?>
						</td>
						<td>
							<span class="badge badge-default"><?php echo $this->options['admin_email']; ?></span>
						</td>
					</tr>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_ADMIN_USER_LABEL'); ?>
						</td>
						<td>
							<span class="badge badge-default"><?php echo $this->options['admin_user']; ?></span>
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
		<div class="col-md-6">
			<h3><?php echo JText::_('INSTL_DATABASE'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-sm">
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
							<span class="badge badge-<?php echo ($this->options['db_old'] == 'remove') ? 'important' : 'success'; ?>">
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
	<div class="row">
		<div class="col-md-6">
			<h3><?php echo JText::_('INSTL_FTP'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-sm">
				<tbody>
					<tr>
						<td class="item">
							<?php echo JText::_('INSTL_FTP_ENABLE_LABEL'); ?>
						</td>
						<td>
							<span class="badge badge-<?php echo $this->options['ftp_enable'] ? 'success' : 'important'; ?>">
								<?php echo JText::_($this->options['ftp_enable'] ? 'JYES' : 'JNO'); ?>
							</span>
						</td>
					</tr>
					<?php if ($this->options['ftp_enable']) : ?>
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
							<span class="badge badge-<?php echo $this->options['ftp_save'] ? 'important' : 'success'; ?>">
								<?php echo JText::_($this->options['ftp_save'] ? 'JYES' : 'JNO'); ?>
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
	<div class="row">
		<div class="col-md-6">
			<h3><?php echo JText::_('INSTL_PRECHECK_TITLE'); ?></h3>
			<hr class="hr-condensed" />
			<p class="install-text">
				<?php echo JText::_('INSTL_PRECHECK_DESC'); ?>
			</p>
			<table class="table table-striped table-sm">
				<tbody>
				<?php foreach ($this->phpoptions as $option) : ?>
					<tr>
						<td class="item">
							<?php echo $option->label; ?>
						</td>
						<td>
							<span class="badge badge-<?php echo $option->state ? 'success' : 'important'; ?>">
								<?php echo JText::_($option->state ? 'JYES' : 'JNO'); ?>
								<?php if ($option->notice): ?>
									<span class="icon-info-sign icon-white hasTooltip" title="<?php echo $option->notice; ?>"></span>
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
		<div class="col-md-6">
			<h3><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_TITLE'); ?></h3>
			<hr class="hr-condensed" />
			<p class="install-text"><?php echo JText::_('INSTL_PRECHECK_RECOMMENDED_SETTINGS_DESC'); ?></p>
			<table class="table table-striped table-sm">
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
							<span class="badge badge-success disabled">
								<?php echo JText::_($setting->recommended ? 'JON' : 'JOFF'); ?>
							</span>
						</td>
						<td>
							<span class="badge badge-<?php echo ($setting->state === $setting->recommended) ? 'success' : 'warning'; ?>">
								<?php echo JText::_($setting->state ? 'JON' : 'JOFF'); ?>
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
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a class="btn btn-secondary" href="#" onclick="return Install.goToPage('<?php echo $prev; ?>');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><span class="fa fa-arrow-left"></span> <?php echo JText::_('JPREVIOUS'); ?></a>
			<a class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('INSTL_SUMMARY_INSTALL'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('INSTL_SUMMARY_INSTALL'); ?></a>
		</div>
	</div>

	<input type="hidden" name="task" value="summary" />
	<?php echo JHtml::_('form.token'); ?>
</form>

<script type="text/javascript">
	jQuery('input[name="jform[summary_email]"]').each(function(index, el) {
        jQuery(el).parent().on('click', function() {
            Install.toggle('email_passwords', 'summary_email', 0);
        });
        Install.toggle('email_passwords', 'summary_email', 1);
    });
</script>
