<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewDefault $this */
?>
<?php echo JHtml::_('InstallationHtml.helper.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
			<a class="btn" href="#" onclick="return Install.goToPage('database');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><span class="icon-arrow-left"></span> <?php echo JText::_('JPrevious'); ?></a>
			<a  class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><span class="icon-arrow-right icon-white"></span> <?php echo JText::_('JNext'); ?></a>
		</div>
	</div>
	<h3><?php echo JText::_('INSTL_FTP'); ?></h3>
	<hr class="hr-condensed" />
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('ftp_enable'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('ftp_enable'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('ftp_user'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('ftp_user'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_FTP_USER_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('ftp_pass'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('ftp_pass'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_FTP_PASSWORD_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
		</div>
		<div class="controls">
			<button id="verifybutton" class="btn btn-success" onclick="Install.verifyFtpSettings(this);"><span class="icon-ok icon-white"></span> <?php echo JText::_('INSTL_VERIFY_FTP_SETTINGS'); ?></button>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('ftp_host'); ?>
		</div>
		<div class="controls">
			<div class="input-append">
				<?php echo $this->form->getInput('ftp_host'); ?><button id="findbutton" class="btn" onclick="Install.detectFtpRoot(this);"><span class="icon-folder-open"></span> <?php echo JText::_('INSTL_AUTOFIND_FTP_PATH'); ?></button>
			</div>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('ftp_port'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('ftp_port'); ?>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('ftp_save'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('ftp_save'); ?>
		</div>
	</div>
	<div class="row-fluid">
		<div class="btn-toolbar">
			<div class="btn-group pull-right">
				<a class="btn" href="#" onclick="return Install.goToPage('database');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><span class="icon-arrow-left"></span> <?php echo JText::_('JPrevious'); ?></a>
				<a  class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><span class="icon-arrow-right icon-white"></span> <?php echo JText::_('JNext'); ?></a>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="ftp" />
	<?php echo JHtml::_('form.token'); ?>
</form>
