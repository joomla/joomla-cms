<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewDefault $this */
?>
<?php echo JHtml::_('InstallationHtml.helper.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a class="btn btn-secondary" href="#" onclick="return Install.goToPage('database');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><span class="fa fa-arrow-left"></span> <?php echo JText::_('JPREVIOUS'); ?></a>
			<a class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('JNEXT'); ?></a>
		</div>
	</div>
	<h3><?php echo JText::_('INSTL_FTP'); ?></h3>
	<hr class="hr-condensed" />
	<div class="form-group">
		<?php echo $this->form->getLabel('ftp_enable'); ?>
		<?php echo $this->form->getInput('ftp_enable'); ?>
	</div>
	<div class="form-group">
		<?php echo $this->form->getLabel('ftp_user'); ?>
		<?php echo $this->form->getInput('ftp_user'); ?>
		<p class="form-text text-muted small"><?php echo JText::_('INSTL_FTP_USER_DESC'); ?></p>
	</div>
	<div class="form-group">
		<?php echo $this->form->getLabel('ftp_pass'); ?>
		<?php echo $this->form->getInput('ftp_pass'); ?>
		<p class="form-text text-muted small"><?php echo JText::_('INSTL_FTP_PASSWORD_DESC'); ?></p>
	</div>
	<div class="form-group">
		<button id="verifybutton" class="btn btn-success" onclick="Install.verifyFtpSettings(this);"><span class="icon-ok icon-white"></span> <?php echo JText::_('INSTL_VERIFY_FTP_SETTINGS'); ?></button>
	</div>
	<div class="form-group">
		<?php echo $this->form->getLabel('ftp_host'); ?>
		<div class="input-append">
			<?php echo $this->form->getInput('ftp_host'); ?><button id="findbutton" class="btn btn-secondary" onclick="Install.detectFtpRoot(this);"><span class="icon-folder-open"></span> <?php echo JText::_('INSTL_AUTOFIND_FTP_PATH'); ?></button>
		</div>
	</div>
	<div class="form-group">
		<?php echo $this->form->getLabel('ftp_port'); ?>
		<?php echo $this->form->getInput('ftp_port'); ?>
	</div>
	<div class="form-group">
		<?php echo $this->form->getLabel('ftp_save'); ?>
		<?php echo $this->form->getInput('ftp_save'); ?>
	</div>
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a class="btn btn-secondary" href="#" onclick="return Install.goToPage('database');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><span class="fa fa-arrow-left"></span> <?php echo JText::_('JPREVIOUS'); ?></a>
			<a class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('JNEXT'); ?></a>
		</div>
	</div>
	<input type="hidden" name="task" value="ftp" />
	<?php echo JHtml::_('form.token'); ?>
</form>
