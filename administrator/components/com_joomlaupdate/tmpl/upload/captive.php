<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');

$twofactormethods = \Joomla\CMS\Helper\AuthenticationHelper::getTwoFactorMethods();

?>

<joomla-alert type="warning">
	<h4 class="alert-heading">
		<?php echo JText::_('COM_JOOMLAUPDATE_VIEW_UPLOAD_CAPTIVE_INTRO_HEAD'); ?>
	</h4>
	<p>
		<?php echo JText::sprintf('COM_JOOMLAUPDATE_VIEW_UPLOAD_CAPTIVE_INTRO_BODY', JFactory::getConfig()->get('sitename')); ?>
	</p>
</joomla-alert>

<hr>

<form action="<?php echo JRoute::_('index.php', true); ?>" method="post" id="form-login" class="text-center">
	<fieldset class="loginform">
		<div class="control-group">
			<div class="controls">
				<div class="input-group">
					<input name="username" tabindex="1" id="mod-login-username" type="text" class="form-control" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="15" autofocus="true">
					<span class="input-group-append">
						<span class="input-group-text">
							<span class="fa fa-user" aria-hidden="true"></span>
							<label for="mod-login-username" class="sr-only">
								<?php echo JText::_('JGLOBAL_USERNAME'); ?>
							</label>
						</span>
					</span>
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<div class="input-group">
					<input name="passwd" tabindex="2" id="mod-login-password" type="password" class="form-control" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" size="15">
					<span class="input-group-append">
						<span class="input-group-text">
							<span class="fa fa-lock" aria-hidden="true"></span>
							<label for="mod-login-password" class="sr-only">
								<?php echo JText::_('JGLOBAL_PASSWORD'); ?>
							</label>
						</span>
					</span>
				</div>
			</div>
		</div>
		<?php if (count($twofactormethods) > 1) : ?>
			<div class="control-group">
				<div class="controls">
					<div class="input-group">
						<input name="secretkey" autocomplete="off" tabindex="3" id="mod-login-secretkey" type="text" class="form-control" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" size="15">
						<span class="input-group-append">
							<span class="input-group-text hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
								<span class="fa fa-star" aria-hidden="true"></span>
								<label for="mod-login-secretkey" class="sr-only">
									<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
								</label>
							</span>
						</span>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="controls">
				<div class="btn-group">
					<a tabindex="4" class="btn btn-danger" href="index.php?option=com_joomlaupdate">
						<span class="icon-cancel icon-white" aria-hidden="true"></span> <?php echo JText::_('JCANCEL'); ?>
					</a>
					<button tabindex="5" class="btn btn-primary">
						<span class="icon-play icon-white" aria-hidden="true"></span> <?php echo JText::_('COM_INSTALLER_INSTALL_BUTTON'); ?>
					</button>
				</div>
			</div>
		</div>

		<input type="hidden" name="option" value="com_joomlaupdate">
		<input type="hidden" name="task" value="update.confirm">
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
