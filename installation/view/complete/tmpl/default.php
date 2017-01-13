<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/* @var InstallationViewCompleteHtml $this */
?>
<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div class="col-md-12">
		<div class="alert alert-danger inlineError" id="theDefaultError" style="display: none">
			<h4 class="alert-heading"><?php echo JText::_('JERROR'); ?></h4>
			<p id="theDefaultErrorMessage"></p>
		</div>
		<div class="row">
			<div class="alert alert-success">
				<h3><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h3>
			</div>
		</div>
		<div class="row">
			<h3><?php echo JText::_('INSTL_COMPLETE_LANGUAGE_1'); ?></h3>
			<hr>
			<div class="row">	
				<div class="col-md-6">
					<p><?php echo JText::_('INSTL_COMPLETE_LANGUAGE_DESC'); ?></p>
					<p><a href="#" class="btn btn-primary" id="instLangs" onclick="return Install.goToPage('languages');"><span class="fa fa-arrow-right icon-white"></span> <?php echo JText::_('INSTL_COMPLETE_INSTALL_LANGUAGES'); ?></a></p>
				</div>
				<div class="col-md-6">
					<div class="alert alert-info">
						<p><?php echo JText::_('INSTL_COMPLETE_LANGUAGE_DESC2'); ?></p>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="alert alert-info">
				<p><?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?></p>
				<input type="button" class="btn btn-warning" name="instDefault" onclick="Install.removeFolder(this);" value="<?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?>" />
			</div>
		</div>
		<div class="row">
			<div class="col-md-6">
				<div class="btn-group">
					<a class="btn btn-secondary" href="<?php echo JUri::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><span class="fa fa-eye"></span> <?php echo JText::_('JSITE'); ?></a>
				</div>
				<div class="btn-group">
					<a class="btn btn-primary" href="<?php echo JUri::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><span class="fa fa-lock"></span> <?php echo JText::_('JADMINISTRATOR'); ?></a>
				</div>
			</div>
			<div class="col-md-6">
				<h3><?php echo JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'); ?></h3>
				<hr>
				<table class="table table-striped table-sm">
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
							<td class="notice">
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
		</div>
		<?php if ($this->config) : ?>
		<div class="alert alert-danger">
			<h3 class="alert-heading"><?php echo JText::_('JNOTICE'); ?></h3>
			<p><?php echo JText::_('INSTL_CONFPROBLEM'); ?></p>
			<textarea rows="10" cols="80" style="width: 100%;" name="configcode" onclick="this.form.configcode.focus();this.form.configcode.select();"><?php echo $this->config; ?></textarea>
		</div>
		<?php endif; ?>
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
