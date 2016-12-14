<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

// Load chosen if we have language selector, ie, more than one administrator language installed and enabled.
if ($langs)
{
	JHtml::_('formbehavior.chosen', '.advancedSelect');
}
?>
<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="form-login" class="form-inline">
	<fieldset class="loginform">
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-user hasTooltip" title="<?php echo JText::_('JGLOBAL_USERNAME'); ?>"></span>
						<label for="mod-login-username" class="element-invisible">
							<?php echo JText::_('JGLOBAL_USERNAME'); ?>
						</label>
					</span>
					<input name="username" tabindex="1" id="mod-login-username" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>" size="15" autofocus="true" />
					<a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=remind" class="btn width-auto hasTooltip" title="<?php echo JText::_('MOD_LOGIN_REMIND'); ?>">
						<span class="icon-help"></span>
					</a>
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-lock hasTooltip" title="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>"></span>
						<label for="mod-login-password" class="element-invisible">
							<?php echo JText::_('JGLOBAL_PASSWORD'); ?>
						</label>
					</span>
					<input name="passwd" tabindex="2" id="mod-login-password" type="password" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>" size="15"/>
					<a href="<?php echo JUri::root(); ?>index.php?option=com_users&view=reset" class="btn width-auto hasTooltip" title="<?php echo JText::_('MOD_LOGIN_RESET'); ?>">
						<span class="icon-help"></span>
					</a>
				</div>
			</div>
		</div>
		<?php if (count($twofactormethods) > 1): ?>
		<div class="control-group">
			<div class="controls">
				<div class="input-prepend input-append">
					<span class="add-on">
						<span class="icon-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>"></span>
						<label for="mod-login-secretkey" class="element-invisible">
							<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>
						</label>
					</span>
					<input name="secretkey" autocomplete="off" tabindex="3" id="mod-login-secretkey" type="text" class="input-medium" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>" size="15"/>
					<span class="btn width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<span class="icon-help"></span>
					</span>
				</div>
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
							<div class="input-prepend">
								<span class="add-on">
									<span class="<?php echo $extraField->getIcon(); ?> hasTooltip" title="<?php echo $extraField->getLabel(); ?>"></span>
									<label class="element-invisible"><?php echo $extraField->getLabel(); ?></label>
								</span>
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
		<?php if (!empty($langs)) : ?>
			<div class="control-group">
				<div class="controls">
					<div class="input-prepend">
						<span class="add-on">
							<span class="icon-comment hasTooltip" title="<?php echo JHtml::tooltipText('MOD_LOGIN_LANGUAGE'); ?>"></span>
							<label for="lang" class="element-invisible">
								<?php echo JText::_('MOD_LOGIN_LANGUAGE'); ?>
							</label>
						</span>
						<?php echo $langs; ?>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="controls">
				<div class="btn-group">
					<button tabindex="3" class="btn btn-primary btn-block btn-large">
						<span class="icon-lock icon-white"></span> <?php echo JText::_('MOD_LOGIN_LOGIN'); ?>
					</button>
					<?php if (count($extraFields)) : ?>
						<?php $extraFieldCounter = 0; ?>
						<?php /** @var JAuthenticationFieldInterface $extraField */ ?>
						<?php foreach ($extraFields as $extraField) : ?>
							<?php if ($extraField->getType() != 'link') continue; ?>
							<a id="form-login-button-<?php echo ++$extraFieldCounter; ?>" class="btn btn-default" href="<?php echo $extraField->getInput() ?>">
								<?php if ($extraField->getIcon()) : ?>
									<span class="<?php echo $extraField->getIcon(); ?>"></span>
								<?php endif; ?>
								<?php echo $extraField->getLabel(); ?>
							</a>
						<?php endforeach; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<input type="hidden" name="option" value="com_login"/>
		<input type="hidden" name="task" value="login"/>
		<input type="hidden" name="return" value="<?php echo $return; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
