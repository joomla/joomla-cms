<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('UsersHelperRoute', JPATH_SITE . '/components/com_users/helpers/route.php');

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" class="form-inline login-form">
	<?php if ($params->get('pretext')) : ?>
		<div class="pretext">
			<p><?php echo $params->get('pretext'); ?></p>
		</div>
	<?php endif; ?>
	<div class="userdata">
		<div class="control-group form-login-username">
			<div class="controls">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-prepend">
						<span class="add-on">
							<span class="icon-user hasTooltip" title="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?>"></span>
							<label for="modlgn-username<?php echo $module->id; ?>" class="element-invisible"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
						</span>
						<input id="modlgn-username<?php echo $module->id; ?>" type="text" name="username" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?>" />
					</div>
				<?php else : ?>
					<label for="modlgn-username<?php echo $module->id; ?>"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
					<input id="modlgn-username<?php echo $module->id; ?>" type="text" name="username" class="input-small modlgn-username" tabindex="0" size="18" placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME'); ?>" />
				<?php endif; ?>
			</div>
		</div>
		<div class="control-group form-login-password">
			<div class="controls">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-prepend">
						<span class="add-on">
							<span class="icon-lock hasTooltip" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>">
							</span>
								<label for="modlgn-passwd<?php echo $module->id; ?>" class="element-invisible"><?php echo JText::_('JGLOBAL_PASSWORD'); ?>
							</label>
						</span>
						<input id="modlgn-passwd<?php echo $module->id; ?>" type="password" name="password" class="input-small modlgn-passwd" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" />
					</div>
				<?php else : ?>
					<label for="modlgn-passwd<?php echo $module->id; ?>"><?php echo JText::_('JGLOBAL_PASSWORD'); ?></label>
					<input id="modlgn-passwd<?php echo $module->id; ?>" type="password" name="password" class="input-small modlgn-passwd" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" />
				<?php endif; ?>
			</div>
		</div>
		<?php if (count($twofactormethods) > 1) : ?>
		<div class="control-group form-login-secretkey">
			<div class="controls">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-prepend input-append">
						<span class="add-on">
							<span class="icon-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>">
							</span>
								<label for="modlgn-secretkey<?php echo $module->id; ?>" class="element-invisible"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
							</label>
						</span>
						<input id="modlgn-secretkey<?php echo $module->id; ?>" autocomplete="off" type="text" name="secretkey" class="input-small modlgn-secretkey" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" />
						<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
							<span class="icon-help"></span>
						</span>
				</div>
				<?php else : ?>
					<label for="modlgn-secretkey<?php echo $module->id; ?>"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
					<input id="modlgn-secretkey<?php echo $module->id; ?>" autocomplete="off" type="text" name="secretkey" class="input-small modlgn-secretkey" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" />
					<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<span class="icon-help"></span>
					</span>
				<?php endif; ?>

			</div>
		</div>
		<?php endif; ?>
		<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
		<div class="control-group checkbox form-login-remember">
			<label for="modlgn-remember<?php echo $module->id; ?>" class="control-label"><?php echo JText::_('MOD_LOGIN_REMEMBER_ME'); ?></label>
			<input id="modlgn-remember<?php echo $module->id; ?>" type="checkbox" name="remember" class="inputbox modlgn-remember" value="yes"/>
		</div>
		<?php endif; ?>
		<div class="control-group form-login-submit">
			<div class="controls">
				<button id="modlng-submit<?php echo $module->id; ?>" type="submit" tabindex="0" name="Submit" class="btn btn-primary modlng-submit"><?php echo JText::_('JLOGIN'); ?></button>
			</div>
		</div>
		<?php
			$usersConfig = JComponentHelper::getParams('com_users'); ?>
			<ul class="unstyled">
			<?php if ($usersConfig->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
					<?php echo JText::_('MOD_LOGIN_REGISTER'); ?> <span class="icon-arrow-right"></span></a>
				</li>
			<?php endif; ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
					<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
				</li>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
					<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
				</li>
			</ul>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
	<?php if ($params->get('posttext')) : ?>
		<div class="posttext">
			<p><?php echo $params->get('posttext'); ?></p>
		</div>
	<?php endif; ?>
</form>
