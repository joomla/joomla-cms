<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('bootstrap.tooltip');

$spacing = 0;

// Load chosen if we have language selector, ie, more than one administrator language installed and enabled.
if ($langs)
{
	$spacing += 33;
}

if (count($twofactormethods) > 1)
{
	$spacing += 33;
}

if ($spacing > 0)
{
	$marginTop = 240 + $spacing;

	JFactory::getDocument()->addStyleDeclaration('
		.view-login .container {
			margin-top: -' . $marginTop . 'px;
		}
	');
}
?>
<form class="login-initial" action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="form-login">
	<fieldset>

		<div class="form-group">
			<input
				name="username"
				id="mod-login-username"
				type="text"
				class="form-control input-full"
				tabindex="1"
				placeholder="<?php echo JText::_('JGLOBAL_USERNAME'); ?>"
				autofocus
			>
		</div>

		<div class="form-group">
			<input
				name="passwd"
				id="mod-login-password"
				type="password"
				class="form-control input-full"
				placeholder="<?php echo JText::_('JGLOBAL_PASSWORD'); ?>"
			>
		</div>

		<?php if (count($twofactormethods) > 1): ?>
			<div class="form-group">
				<input
					name="secretkey"
					autocomplete="off"
					id="mod-login-secretkey"
					type="text"
					class="form-control input-full"
					placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>"
				>
			</div>
		<?php endif; ?>

		<?php if (!empty($langs)) : ?>
			<div class="form-group">
				<?php echo $langs; ?>
			</div>
		<?php endif; ?>

		<div class="form-group">
			<button class="btn btn-success btn-block btn-lg">
				<span class="icon-lock icon-white"></span> <?php echo JText::_('MOD_LOGIN_LOGIN'); ?>
			</button>
		</div>

		<div class="forgot">
			<div><a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>"><?php echo JText::_('MOD_LOGIN_REMIND'); ?></a></div>
			<div><a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('MOD_LOGIN_RESET'); ?></a></div>
		</div>

		<input type="hidden" name="option" value="com_login"/>
		<input type="hidden" name="task" value="login"/>
		<input type="hidden" name="return" value="<?php echo $return; ?>"/>
		<?php echo JHtml::_('form.token'); ?>
	</fieldset>
</form>
