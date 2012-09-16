<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<form action="index.php" method="post" id="adminForm" class="form-validate form-horizontal">
	<div class="alert alert-error inlineError" id="theDefaultError" style="display: none">
		<h4 class="alert-heading"><?php echo JText::_('JERROR'); ?></h4>
		<p id="theDefaultErrorMessage"></p>
	</div>
	<div class="alert alert-success">
		<h3><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h3>
	</div>
	<div class="alert">
		<p><?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?></p>
		<input type="button" class="btn btn-warning" name="instDefault" onclick="Install.removeFolder(this);" value="<?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?>" />
	</div>
	<div class="btn-toolbar">
		<div class="btn-group">
			<a class="btn" href="<?php echo JURI::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><i class="icon-eye-open"></i> <?php echo JText::_('JSITE'); ?></a>
		</div>
		<div class="btn-group">
			<a class="btn btn-primary" href="<?php echo JURI::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><i class="icon-lock icon-white"></i> <?php echo JText::_('JADMINISTRATOR'); ?></a>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6">
			<h3><?php echo JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'); ?></h3>
			<hr class="hr-condensed" />
			<table class="table table-striped table-condensed">
				<tbody>
				<tr>
					<td class="item">
						<?php echo JText::_('JEMAIL'); ?>
					</td>
					<td>
						<span class="label"><?php echo $this->options['admin_email']; ?></span>
					</td>
				</tr>
				<tr>
					<td class="item">
						<?php echo JText::_('JUSERNAME'); ?>
					</td>
					<td>
						<span class="label"><?php echo $this->options['admin_user']; ?></span>
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
		<div class="span6">
			<h3><?php echo JText::_('INSTL_COMPLETE_LANGUAGE_1'); ?></h3>
			<hr class="hr-condensed" />
			<a href="http://community.joomla.org/translations/joomla-16-translations.html" target="_blank">
				<?php echo JText::_('INSTL_COMPLETE_LANGUAGE_2'); ?>
			</a>
		</div>
	</div>

	<?php if ($this->config) : ?>
	<div class="alert alert-error">
		<h3 class="alert-heading"><?php echo JText::_('JNOTICE'); ?></h3>
		<p><?php echo JText::_('INSTL_CONFPROBLEM'); ?></p>
		<textarea rows="10" cols="80" style="width: 100%;" name="configcode" onclick="this.form.configcode.focus();this.form.configcode.select();"><?php echo $this->config; ?></textarea>
	</div>
	<?php endif; ?>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
