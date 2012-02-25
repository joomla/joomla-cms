<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<div id="step">
	<div class="buttons">
		<div class="button"><a href="<?php echo JURI::root(); ?>" class="site" title="<?php echo JText::_('JSITE'); ?>"><?php echo JText::_('JSITE'); ?></a></div>
		<div class="button"><a href="<?php echo JURI::root(); ?>administrator/" class="admin" title="<?php echo JText::_('JADMINISTRATOR'); ?>"><?php echo JText::_('JADMINISTRATOR'); ?></a></div>
	</div>
	<h2><?php echo JText::_('INSTL_COMPLETE'); ?></h2>
</div>

<form action="index.php" method="post" id="adminForm" class="form-validate">
	<div id="installer">
		<h3><?php echo JText::_('INSTL_COMPLETE_TITLE'); ?></h3>
		<div class="install-text">
			<p><?php echo JText::_('INSTL_COMPLETE_DESC1'); ?></p>
			<p><?php echo JText::_('INSTL_COMPLETE_DESC2'); ?></p>
			<p><?php echo JText::_('INSTL_COMPLETE_DESC3'); ?></p>
		</div>
		<div class="install-body installcomplete">
			<fieldset>
				<p class="error">
					<?php echo JText::_('INSTL_COMPLETE_REMOVE_INSTALLATION'); ?>
				</p>
				<div>
					<input class="button" type="button" name="instDefault" value="<?php echo JText::_('INSTL_COMPLETE_REMOVE_FOLDER'); ?>" onclick="Install.removeFolder(this);"/>
				</div>
				<div class="message inlineError" id="theDefaultError" style="display: none">
					<dl>
						<dt class="error"><?php echo JText::_('JERROR'); ?></dt>
						<dd id="theDefaultErrorMessage"></dd>
					</dl>
				</div>
				<h3><?php echo JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'); ?></h3>
				<dl>
					<dt><?php echo JText::_('JUSERNAME'); ?>:</dt>
					<dd><strong><?php echo $this->options['admin_user']; ?></strong></dd>
				</dl>
				<p>
					<a href="http://community.joomla.org/translations/joomla-16-translations.html" target="_blank">
					<b><?php echo JText::_('INSTL_COMPLETE_LANGUAGE_1'); ?></b>
					<br />
					<?php echo JText::_('INSTL_COMPLETE_LANGUAGE_2'); ?>
					</a>
				</p>
				<?php if ($this->config) : ?>
				<div>
					<h3>
						<?php echo JText::_('INSTL_CONFPROBLEM'); ?>
					</h3>
					<textarea rows="5" cols="49" name="configcode" onclick="this.form.configcode.focus();this.form.configcode.select();" ><?php echo $this->config; ?></textarea>
				</div>
				<?php endif; ?>
			</fieldset>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
