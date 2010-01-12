<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the JavaScript behaviors.
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>

<script language="JavaScript" type="text/javascript">
<!--
	function validateForm(frm, task) {
		Joomla.submitform(task);
	}
// -->
</script>

<div id="stepbar">
	<div class="t">
		<div class="t">
			<div class="t"></div>
		</div>
	</div>
	<div class="m">
		<?php echo JHtml::_('installation.stepbar', 5); ?>
		<div class="box"></div>
  	</div>
	<div class="b">
		<div class="b">
			<div class="b"></div>
		</div>
	</div>
</div>

<form action="index.php" method="post" name="adminForm" class="form-validate">
<div id="right">
	<div id="rightpad">
		<div id="step">
			<div class="t">
				<div class="t">
					<div class="t"></div>
				</div>
			</div>
			<div class="m">
				<div class="far-right">
<?php if ($this->document->direction == 'ltr') : ?>
					<div class="button1-right"><div class="prev"><a href="index.php?view=database" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a onclick="validateForm(adminForm, 'setup.filesystem');" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a onclick="validateForm(adminForm, 'setup.filesystem');" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=database" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('FTP_CONFIGURATION'); ?></span>
			</div>
			<div class="b">
				<div class="b">
					<div class="b"></div>
				</div>
			</div>
		</div>
		<div id="installer">
			<div class="t">
				<div class="t">
					<div class="t"></div>
				</div>
			</div>
			<div class="m">
				<h2>
					<?php echo JText::_('FTP_CONFIGURATION'); ?>:
				</h2>
				<div class="install-text">
					<?php echo JText::_('
						<p>Due to filesystem permission restrictions and PHP Safe Mode restrictions.
						For all users to utilize the Joomla! installers an FTP layer exists to handle
						filesystem manipulation.
						<br />
						<br />
						Enter an FTP username and password with access to the Joomla! root directory,
						this will be the FTP account that handles all filesystem operations when Joomla!
						requires FTP access to complete a task.
						<br />
						<br />
						For security reasons, it is best if a separate FTP user account is created with
						access only to the Joomla! installation.</p>
					'); ?>
				</div>
				<div class="install-body">
					<div class="t">
						<div class="t">
							<div class="t"></div>
						</div>
					</div>
					<div class="m">
						<h3 class="title-smenu" title="<?php echo JText::_('Basic'); ?>">
							<?php echo JText::_('BASIC_SETTINGS'); ?>
						</h3>
						<div class="section-smenu">
							<table class="content2">
								<tr>
									<td width="100">
										<?php echo $this->form->getLabel('ftp_enable'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_enable'); ?>
									</td>
								</tr>
								<tr>
									<td width="100">
										<?php echo $this->form->getLabel('ftp_user'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_user'); ?>
									</td>
								</tr>
								<tr>
									<td>
										<?php echo $this->form->getLabel('ftp_pass'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_pass'); ?>
									</td>
								</tr>
								<tr id="rootPath">
									<td>
										<?php echo $this->form->getLabel('ftp_root'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_root'); ?>
									</td>
								</tr>
							</table>

							<input type="button" id="findbutton" class="button" value="<?php echo JText::_('AUTOFIND_FTP_PATH'); ?>" onclick="Install.detectFtpRoot(this);" />
							<input type="button" id="verifybutton" class="button" value="<?php echo JText::_('VERIFY_FTP_SETTINGS'); ?>" onclick="Install.verifyFtpSettings(this);" />
							<br /><br />
						</div>

						<h3 class="title-smenu moofx-toggler" title="<?php echo JText::_('Advanced'); ?>">
							<?php echo JText::_('ADVANCED_SETTINGS'); ?>
						</h3>
						<div class="section-smenu moofx-slider">
							<table class="content2">
								<tr id="host">
									<td width="100">
										<?php echo $this->form->getLabel('ftp_host'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_host'); ?>
									</td>
								</tr>
								<tr id="port">
									<td width="100">
										<?php echo $this->form->getLabel('ftp_port'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_port'); ?>
									</td>
								</tr>
								<tr>
									<td width="100">
										<?php echo $this->form->getLabel('ftp_save'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_save'); ?>
									</td>
								</tr>
							</table>
						</div>
						<div class="clr"></div>
					</div>
					<div class="b">
						<div class="b">
							<div class="b"></div>
						</div>
					</div>
					<div class="clr"></div>
				</div>
				<div class="clr"></div>
			</div>
			<div class="b">
				<div class="b">
					<div class="b"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
