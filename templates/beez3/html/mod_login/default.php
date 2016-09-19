<?php
/**
 * @package     Joomla.Site
 * @subpackage  Templates.beez3
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form" >
	<?php if ($params->get('pretext')) : ?>
		<div class="pretext">
		<p><?php echo $params->get('pretext'); ?></p>
		</div>
	<?php endif; ?>
	<fieldset class="userdata">
		<p id="form-login-username">
			<label for="modlgn-username"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?></label>
			<input id="modlgn-username" type="text" name="username" class="inputbox"  size="18" />
		</p>
		<p id="form-login-password">
			<label for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
			<input id="modlgn-passwd" type="password" name="password" class="inputbox" size="18" />
		</p>
		<?php if (count($twofactormethods) > 1) : ?>
			<div id="form-login-secretkey" class="control-group">
				<div class="controls">
					<?php if (!$params->get('usetext')) : ?>
						<div class="input-prepend input-append">
							<label for="modlgn-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
							<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="input-small" tabindex="0" size="18" />
						</div>
					<?php else: ?>
						<label for="modlgn-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY') ?></label>
						<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="input-small" tabindex="0" size="18" />
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if (count($extraFields)) : ?>
			<?php $extraFieldCounter = 0; ?>
			<?php /** @var JAuthenticationFieldInterface $extraField */ ?>
			<?php foreach ($extraFields as $extraField) : ?>
				<?php if ($extraField->getType() != 'field') continue; ?>
				<div id="form-login-extrafield-<?php echo ++$extraFieldCounter; ?>" class="control-group">
					<div class="controls">
						<?php if (!$params->get('usetext')) : ?>
							<div class="input-prepend input-append">
								<label><?php echo $extraField->getLabel(); ?></label>
								<?php echo $extraField->getInput(); ?>
							</div>
						<?php else : ?>
							<label><?php echo $extraField->getLabel(); ?></label>
							<?php echo $extraField->getInput(); ?>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
			<p id="form-login-remember">
				<label for="modlgn-remember"><?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?></label>
				<input id="modlgn-remember" type="checkbox" name="remember" class="inputbox" value="yes"/>
			</p>
		<?php endif; ?>
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('JLOGIN') ?>" />
		<?php if (count($extraFields)): ?>
			<?php $extraFieldCounter = 0; ?>
			<?php /** @var JAuthenticationFieldInterface $extraField */ ?>
			<?php foreach ($extraFields as $extraField) : ?>
				<?php if ($extraField->getType() != 'button') continue; ?>
				<button type="button" id="form-login-button-<?php echo ++$extraFieldCounter; ?>" href="<?php echo $extraField->getInput() ?>">
					<?php if ($extraField->getIcon()): ?>
						<span class="<?php echo $extraField->getIcon() ?>"></span>
					<?php endif; ?>
					<?php echo $extraField->getLabel(); ?>
				</button>
			<?php endforeach; ?>
		<?php endif; ?>
		<input type="hidden" name="option" value="com_users" />
		<input type="hidden" name="task" value="user.login" />
		<input type="hidden" name="return" value="<?php echo $return; ?>" />
		<?php echo JHtml::_('form.token'); ?>
		<ul>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
				<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
			</li>
			<li>
				<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
				<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
			</li>
			<?php if (JComponentHelper::getParams('com_users')->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
					<?php echo JText::_('MOD_LOGIN_REGISTER'); ?></a>
				</li>
			<?php endif; ?>
			<?php if (count($extraFields)) : ?>
				<?php $extraFieldCounter = 0; ?>
				<?php /** @var JAuthenticationFieldInterface $extraField */ ?>
				<?php foreach ($extraFields as $extraField) : ?>
					<?php if ($extraField->getType() != 'link') continue; ?>
					<li>
						<a id="form-login-link-<?php echo ++$extraFieldCounter; ?>" href="<?php echo $extraField->getInput() ?>">
							<?php echo $extraField->getLabel(); ?>
						</a>
					</li>
				<?php endforeach; ?>
			<?php endif; ?>
		</ul>
		<?php if ($params->get('posttext')) : ?>
			<div class="posttext">
				<p><?php echo $params->get('posttext'); ?></p>
			</div>
		<?php endif; ?>
	</fieldset>
</form>
