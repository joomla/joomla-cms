<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

$twofactormethods = JAuthenticationHelper::getTwoFactorMethods();

?>

<div class="alert alert-warning">
	<h4 class="alert-heading">
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE_HEAD'); ?>
	</h4>
	<p>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE_HEAD_DESC', JFactory::getConfig()->get('sitename')); ?>
	</p>
</div>

<hr/>

<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login" class="form-inline center">
	<fieldset class="loginform">
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-user hasTooltip" title="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" aria-hidden="true"></span>
						<label for="mod-login-username" class="element-invisible">
							<?php echo JText::_('JGLOBAL_USERNAME'); ?>
						</label>
					</span>
					<input name="username" tabindex="1" id="mod-login-username" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="15" autofocus="true" />
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-lock hasTooltip" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" aria-hidden="true"></span>
						<label for="mod-login-password" class="element-invisible">
							<?php echo JText::_('JGLOBAL_PASSWORD'); ?>
						</label>
					</span>
					<input name="passwd" tabindex="2" id="mod-login-password" type="password" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" size="15"/>
				</div>
			</div>
		</div>
		<?php if (count($twofactormethods) > 1) : ?>
			<div class="control-group">
				<div class="controls">
					<div class="input-prepend input-append">
						<span class="add-on">
							<span class="icon-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" aria-hidden="true"></span>
							<label for="mod-login-secretkey" class="element-invisible">
								<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
							</label>
						</span>
						<input name="secretkey" autocomplete="one-time-code" tabindex="3" id="mod-login-secretkey" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" size="15"/>
						<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
							<span class="icon-help" aria-hidden="true"></span>
						</span>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="controls">
				<div class="btn-group">
					<a tabindex="4" class="btn btn-danger btn-small" href="index.php?option=com_joomlaupdate">
						<span class="icon-cancel icon-white" aria-hidden="true"></span> <?php echo JText::_('JCANCEL'); ?>
					</a>
				</div>
				<div class="btn-group">
					<button tabindex="5" class="btn btn-primary btn-large">
						<span class="icon-play icon-white" aria-hidden="true"></span> <?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE_CONFIRM_AND_CONTINUE'); ?>
					</button>
				</div>
			</div>
		</div>

		<input type="hidden" name="option" value="com_joomlaupdate"/>
		<input type="hidden" name="task" value="update.finaliseconfirm" />
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
