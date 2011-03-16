<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the component HTML helpers.
JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');

// Load the JavaScript behaviors.
JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('script', 'installation/template/js/installation.js', true, false, false, false);
?>

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

<form action="index.php" method="post" id="adminForm" class="form-validate">
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
					<div class="button1-right"><div class="prev"><a href="index.php?view=database" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="#" onclick="Install.submitform('setup.filesystem');" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
					<div class="button1-right"><div class="prev"><a href="#" onclick="Install.submitform('setup.filesystem');" rel="next" title="<?php echo JText::_('JNext'); ?>"><?php echo JText::_('JNext'); ?></a></div></div>
					<div class="button1-left"><div class="next"><a href="index.php?view=database" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><?php echo JText::_('JPrevious'); ?></a></div></div>
<?php endif; ?>
				</div>
				<span class="step"><?php echo JText::_('INSTL_FTP'); ?></span>
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
					<?php echo JText::_('INSTL_FTP_TITLE'); ?>
				</h2>
				<div class="install-text">
					<?php echo JText::_('INSTL_FTP_DESC'); ?>
				</div>
				<div class="install-body">
					<div class="t">
						<div class="t">
							<div class="t"></div>
						</div>
					</div>
					<div class="m">
						<h3 class="title-smenu" title="<?php echo JText::_('INSTL_BASIC_SETTINGS'); ?>">
							<?php echo JText::_('INSTL_BASIC_SETTINGS'); ?>
						</h3>
						<div class="section-smenu">
							<table class="content2">
								<tr>
									<td>
										<?php echo $this->form->getLabel('ftp_enable'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_enable'); ?>
									</td>
								</tr>
								<tr>
									<td>
										<?php echo $this->form->getLabel('ftp_user'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_user'); ?>
									</td>
									<td>
										<em>
										<?php echo JText::_('INSTL_FTP_USER_DESC'); ?>
										</em>
									</td>	
								</tr>
								<tr>
									<td>
										<?php echo $this->form->getLabel('ftp_pass'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_pass'); ?>
									</td>
									<td>
										<em>
										<?php echo JText::_('INSTL_FTP_PASSWORD_DESC'); ?>
										</em>
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

							<input type="button" id="findbutton" class="button" value="<?php echo JText::_('INSTL_AUTOFIND_FTP_PATH'); ?>" onclick="Install.detectFtpRoot(this);" />
							<input type="button" id="verifybutton" class="button" value="<?php echo JText::_('INSTL_VERIFY_FTP_SETTINGS'); ?>" onclick="Install.verifyFtpSettings(this);" />
							<br /><br />
						</div>

						<h3 class="title-smenu moofx-toggler" title="<?php echo JText::_('INSTL_ADVANCED_SETTINGS'); ?>">
							<a href="#"><?php echo JText::_('INSTL_ADVANCED_SETTINGS'); ?></a>
						</h3>
						<div class="section-smenu moofx-slider">
							<table class="content2">
								<tr id="host">
									<td>
										<?php echo $this->form->getLabel('ftp_host'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_host'); ?>
									</td>
								</tr>
								<tr id="port">
									<td>
										<?php echo $this->form->getLabel('ftp_port'); ?>
									</td>
									<td>
										<?php echo $this->form->getInput('ftp_port'); ?>
									</td>
								</tr>
								<tr>
									<td>
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
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</div>
<div class="clr"></div>
</form>
