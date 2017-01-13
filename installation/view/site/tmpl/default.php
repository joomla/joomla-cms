<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewDefault $this */
?>
<?php echo JHtml::_('InstallationHtml.helper.stepbar'); ?>
<div class="btn-toolbar justify-content-end">
	<div class="btn-group">
		<a href="#" class="btn btn-primary" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('JNEXT'); ?></a>
	</div>
</div>
<form action="index.php" method="post" id="languageForm">
	<div class="form-group">
		<label for="jform_language"><?php echo JText::_('INSTL_SELECT_LANGUAGE_TITLE'); ?></label>
		<?php echo $this->form->getInput('language'); ?>
	</div>
	<input type="hidden" name="task" value="setlanguage" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<h3><?php echo JText::_('INSTL_SITE'); ?></h3>
	<hr class="hr-condensed" />
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<?php echo $this->form->getLabel('site_name'); ?>
				<?php echo $this->form->getInput('site_name'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_SITE_NAME_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('site_metadesc'); ?>
				<?php echo $this->form->getInput('site_metadesc'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_SITE_METADESC_TITLE_LABEL'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('site_offline'); ?>
				<?php echo $this->form->getInput('site_offline'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_SITE_OFFLINE_TITLE_LABEL'); ?></p>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<?php echo $this->form->getLabel('admin_email'); ?>
				<?php echo $this->form->getInput('admin_email'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_ADMIN_EMAIL_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('admin_user'); ?>
				<?php echo $this->form->getInput('admin_user'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_ADMIN_USER_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('admin_password'); ?>
				<?php // Disables autocomplete ?> <input type="password" style="display:none">
				<?php echo $this->form->getInput('admin_password'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_ADMIN_PASSWORD_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('admin_password2'); ?>
				<?php // Disables autocomplete ?> <input type="password" style="display:none">
				<?php echo $this->form->getInput('admin_password2'); ?>
			</div>
		</div>
	</div>
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a href="#" class="btn btn-primary" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('JNEXT'); ?></a>
		</div>
	</div>
	<input type="hidden" name="task" value="site" />
	<?php echo JHtml::_('form.token'); ?>
</form>
