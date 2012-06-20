<?php
/**
 * @package    Joomla.Installation
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="far-right">
<?php if ($this->document->direction == 'ltr') : ?>
		<div class="button1-left"><div class="site"><a href="<?php echo JURI::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><?php echo JText::_('JSITE'); ?></a></div></div>
		<div class="button1-left"><div class="admin"><a href="<?php echo JURI::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><?php echo JText::_('JADMINISTRATOR'); ?></a></div></div>
<?php elseif ($this->document->direction == 'rtl') : ?>
		<div class="button1-left"><div class="admin"><a href="<?php echo JURI::root(); ?>administrator/" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><?php echo JText::_('JADMINISTRATOR'); ?></a></div></div>
		<div class="button1-left"><div class="site"><a href="<?php echo JURI::root(); ?>" title="<?php echo JText::_('JSITE'); ?>"><?php echo JText::_('JSITE'); ?></a></div></div>
<?php endif; ?>
	</div>
	<h2><?php echo JText::_('INSTL_COMPLETE'); ?></h2>
</div>

<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div id="installer">
		<div class="m">
			<h3><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h3>
			<div class="install-text">
				<p><?php echo JText::_('INSTL_COMPLETE_DESC1'); ?></p>
				<p><?php echo JText::_('INSTL_COMPLETE_DESC2'); ?></p>
				<p><?php echo JText::_('INSTL_COMPLETE_DESC3'); ?></p>
			</div>
			<div class="install-body">
				<div class="m">
					<fieldset>
						<table class="final-table">
							<tr>
								<td class="error">
									<?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?>
								</td>
							</tr>
							<tr>
								<td><input class="button" type="button" name="instDefault" value="<?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?>" onclick="Install.removeFolder(this);"/></td>
							</tr>
							<tr class="message inlineError" id="theDefaultError" style="display: none">
								<td>
									<dl>
										<dt class="error"><?php echo JText::_('JERROR'); ?></dt>
										<dd id="theDefaultErrorMessage"></dd>
									</dl>
								</td>
							<tr>
							<tr>
								<td>
									<h3>
									<?php echo JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'); ?>
									</h3>
								</td>
							</tr>
							<tr>
								<td class="notice">
									<?php echo JText::_('JUSERNAME'); ?> : <strong><?php echo $this->options['admin_user']; ?></strong>
								</td>
							</tr>
							<tr>
								<td>&#160;</td>
							</tr>
							<tr>
								<td class="notice">
									<div id="cpanel">
										<div>
											<div class="icon">
												<p>
													<a href="http://community.joomla.org/translations/joomla-16-translations.html" target="_blank">
													<b><?php echo JText::_('INSTL_COMPLETE_LANGUAGE_1'); ?></b>
													<br />
													<?php echo JText::_('INSTL_COMPLETE_LANGUAGE_2'); ?>
													</a>
												</p>
											</div>
										</div>
									</div>
								</td>
							</tr>
							<tr>
								<td>&#160;</td>
							</tr>
							<?php if ($this->config) : ?>
							<tr>
								<td class="small">
									<?php echo JText::_('INSTL_CONFPROBLEM'); ?>
								</td>
							</tr>
							<tr>
								<td>
									<textarea rows="5" cols="49" name="configcode" onclick="this.form.configcode.focus();this.form.configcode.select();" ><?php echo $this->config; ?></textarea>
								</td>
							</tr>
							<?php endif; ?>
						</table>
					</fieldset>
				</div>
			</div>
			<div class="clr"></div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
