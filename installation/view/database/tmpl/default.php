<?php
/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewDefault $this */
?>
<?php echo JHtml::_('InstallationHtml.helper.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a class="btn btn-secondary" href="#" onclick="return Install.goToPage('site');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><span class="fa fa-arrow-left"></span> <?php echo JText::_('JPREVIOUS'); ?></a>
			<a class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('JNEXT'); ?></a>
		</div>
	</div>
	<h3><?php echo JText::_('INSTL_DATABASE'); ?></h3>
	<hr class="hr-condensed" />
	<div class="row">
		<div class="col-md-6">
			<div class="form-group">
				<?php echo $this->form->getLabel('db_type'); ?>
				<?php echo $this->form->getInput('db_type'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_DATABASE_TYPE_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('db_host'); ?>
				<?php echo $this->form->getInput('db_host'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_DATABASE_HOST_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('db_user'); ?>
				<?php echo $this->form->getInput('db_user'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_DATABASE_USER_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('db_pass'); ?>
				<?php // Disables autocomplete ?> <input type="password" style="display:none">
				<?php echo $this->form->getInput('db_pass'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_DATABASE_PASSWORD_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('db_name'); ?>
				<?php echo $this->form->getInput('db_name'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_DATABASE_NAME_DESC'); ?></p>
			</div>
		</div>
		<div class="col-md-6">
			<div class="form-group">
				<?php echo $this->form->getLabel('db_prefix'); ?>
				<?php echo $this->form->getInput('db_prefix'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_DATABASE_PREFIX_DESC'); ?></p>
			</div>
			<div class="form-group">
				<?php echo $this->form->getLabel('db_old'); ?>
				<?php echo $this->form->getInput('db_old'); ?>
				<p class="form-text text-muted small"><?php echo JText::_('INSTL_DATABASE_OLD_PROCESS_DESC'); ?></p>
			</div>
		</div>
	</div>
	<div class="btn-toolbar justify-content-end">
		<div class="btn-group">
			<a class="btn btn-secondary" href="#" onclick="return Install.goToPage('site');" rel="prev" title="<?php echo JText::_('JPREVIOUS'); ?>"><span class="fa fa-arrow-left"></span> <?php echo JText::_('JPREVIOUS'); ?></a>
			<a  class="btn btn-primary" href="#" onclick="Install.submitform();" rel="next" title="<?php echo JText::_('JNEXT'); ?>"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('JNEXT'); ?></a>
		</div>
	</div>
	<input type="hidden" name="task" value="database">
	<input type="hidden" name="format" value="json">
	<?php echo JHtml::_('form.token'); ?>
</form>
