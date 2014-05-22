<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewDefault $this */
?>
<?php echo JHtml::_('installation.stepbar'); ?>
<div class="btn-toolbar">
	<div class="btn-group pull-right">
		<a href="#" class="btn btn-primary" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNext'); ?>"><i class="icon-arrow-right icon-white"></i> <?php echo JText::_('JNext'); ?></a>
	</div>
</div>
<form action="index.php" method="post" id="languageForm" class="form-horizontal">
	<div class="control-group">
		<label for="jform_language" class="control-label"><?php echo JText::_('INSTL_SELECT_LANGUAGE_TITLE'); ?></label>
		<div class="controls">
			<?php echo $this->form->getInput('language'); ?>
		</div>
	</div>
	<input type="hidden" name="task" value="setlanguage" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<h3><?php echo JText::_('INSTL_SITE'); ?></h3>
	<hr class="hr-condensed" />

	<div class="row-fluid">
		<div class="span6">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('site_name'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('site_name'); ?>
					<p class="help-block"><?php echo JText::_('INSTL_SITE_NAME_DESC'); ?></p>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('site_metadesc'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('site_metadesc'); ?>
					<p class="help-block">
						<?php echo JText::_('INSTL_SITE_METADESC_TITLE_LABEL'); ?>
					</p>
				</div>
			</div>
		</div>
		<div class="span6">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('admin_email'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('admin_email'); ?>
					<p class="help-block"><?php echo JText::_('INSTL_ADMIN_EMAIL_DESC'); ?></p>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('admin_user'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('admin_user'); ?>
					<p class="help-block"><?php echo JText::_('INSTL_ADMIN_USER_DESC'); ?></p>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('admin_password'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('admin_password'); ?>
					<p class="help-block"><?php echo JText::_('INSTL_ADMIN_PASSWORD_DESC'); ?></p>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('admin_password2'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('admin_password2'); ?>
				</div>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span12">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('site_offline'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('site_offline'); ?>
					<p class="help-block">
						<?php echo JText::_('INSTL_SITE_OFFLINE_TITLE_LABEL'); ?>
					</p>
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="site" />
	<?php echo JHtml::_('form.token'); ?>
</form>
