<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<?php echo JHtml::_('installation.stepbar'); ?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="alert alert-error inlineError" id="theDefaultError" style="display: none">
		<h4 class="alert-heading"><?php echo JText::_('JERROR'); ?></h4>
		<p id="theDefaultErrorMessage"></p>
	</div>
	<div class="alert">
		<h4 class="alert-heading"><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h4>
		<p><?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?></p>
		<button class="btn btn-warning" name="instDefault" onclick="Install.removeFolder(this);"><i class="icon-ban-circle icon-white"></i> <?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?></button>
	</div>
	<div class="btn-toolbar">
		<div class="btn-group">
			<a class="btn" href="<?php echo JURI::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><i class="icon-eye-open"></i> <?php echo JText::_('JSITE'); ?></a>
		</div>
		<div class="btn-group">
			<a class="btn btn-primary" href="<?php echo JURI::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><i class="icon-lock icon-white"></i> <?php echo JText::_('JADMINISTRATOR'); ?></a>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'); ?>
		</div>
		<div class="controls">
			<?php echo JText::_('JUSERNAME'); ?> : <span class="label"><?php echo $this->options['admin_user']; ?></span>
		</div>
	</div>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('INSTL_COMPLETE_LANGUAGE_1'); ?>
		</div>
		<div class="controls">
			<a href="http://community.joomla.org/translations/joomla-16-translations.html" target="_blank">
				<?php echo JText::_('INSTL_COMPLETE_LANGUAGE_2'); ?>
			</a>
		</div>
	</div>
	<?php if ($this->config) : ?>
	<div class="control-group">
		<div class="control-label">
			<?php echo JText::_('INSTL_CONFPROBLEM'); ?>
		</div>
		<div class="controls">
			<div class="alert alert-error">
				<textarea rows="5" cols="49" name="configcode" onclick="this.form.configcode.focus();this.form.configcode.select();"><?php echo $this->config; ?></textarea>
			</div>
		</div>
	</div>
	<?php endif; ?>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
