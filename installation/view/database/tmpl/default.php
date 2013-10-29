<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewDefault $this */
?>
<?php echo JHtml::_('installation.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="btn-toolbar">
		<div class="btn-group pull-right">
			<a class="btn" href="#" onclick="return Install.goToPage('site');" rel="prev" title="<?php echo JText::_('JPrevious'); ?>"><i class="icon-arrow-left"></i> <?php echo JText::_('JPrevious'); ?></a>
			<a  class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><i class="icon-arrow-right icon-white"></i> <?php echo JText::_('JNext'); ?></a>
		</div>
	</div>
	<h3><?php echo JText::_('INSTL_DATABASE'); ?></h3>
	<hr class="hr-condensed" />
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('db_type'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('db_type'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DATABASE_TYPE_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('db_host'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('db_host'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DATABASE_HOST_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('db_user'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('db_user'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DATABASE_USER_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('db_pass'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('db_pass'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DATABASE_PASSWORD_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('db_name'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('db_name'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DATABASE_NAME_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('db_prefix'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('db_prefix'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DATABASE_PREFIX_DESC'); ?>
			</p>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo $this->form->getLabel('db_old'); ?>
		</div>
		<div class="controls">
			<?php echo $this->form->getInput('db_old'); ?>
			<p class="help-block">
				<?php echo JText::_('INSTL_DATABASE_OLD_PROCESS_DESC'); ?>
			</p>
		</div>
	</div>

	<input type="hidden" name="task" value="database" />
	<?php echo JHtml::_('form.token'); ?>
</form>
