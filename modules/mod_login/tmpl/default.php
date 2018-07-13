<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

JLoader::register('UsersHelperRoute', JPATH_SITE . '/components/com_users/helpers/route.php');

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'system/fields/passwordview.min.js', array('version' => 'auto', 'relative' => true));

Text::script('JSHOW');
Text::script('JHIDE');
?>
<form id="login-form" class="mod-login" action="<?php echo Route::_('index.php', true); ?>" method="post">

	<?php if ($params->get('pretext')) : ?>
		<div class="mod-login__pretext pretext">
			<p><?php echo $params->get('pretext'); ?></p>
		</div>
	<?php endif; ?>

	<div class="mod-login__userdata userdata">
		<div class="mod-login__username form-group">
			<?php if (!$params->get('usetext')) : ?>
				<div class="input-group">
					<input id="modlgn-username" type="text" name="username" class="form-control" placeholder="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>">
					<span class="input-group-append">
						<label for="modlgn-username" class="sr-only"><?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
						<span class="input-group-text icon-user hasTooltip" title="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>"></span>
					</span>
				</div>
			<?php else : ?>
				<label for="modlgn-username"><?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
				<input id="modlgn-username" type="text" name="username" class="form-control" placeholder="<?php echo Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>">
			<?php endif; ?>
		</div>

		<div class="mod-login__password form-group">
			<?php if (!$params->get('usetext')) : ?>
				<div class="input-group">
					<input id="modlgn-passwd" type="password" name="password" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>">
					<span class="input-group-append">
						<span class="sr-only"><?php echo Text::_('JSHOW'); ?></span>
						<span class="input-group-text icon-eye" aria-hidden="true"></span>
					</span>
				</div>
			<?php else : ?>
				<label for="modlgn-passwd"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
				<input id="modlgn-passwd" type="password" name="password" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>">
			<?php endif; ?>
		</div>

		<?php if (count($twofactormethods) > 1) : ?>
			<div class="mod-login__twofactor form-group">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-group">
						<span class="input-group-prepend">
							<span class="input-group-text icon-star hasTooltip" title="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>"></span>
							<label for="modlgn-secretkey" class="sr-only"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
						</span>
						<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>">
						<span class="input-group-append hasTooltip" title="<?php echo Text::_('JGLOBAL_SECRETKEY_HELP'); ?>">
							<span class="input-group-text icon-help"></span>
						</span>
					</div>
				<?php else : ?>
					<label for="modlgn-secretkey"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
					<input id="modlgn-secretkey" autocomplete="off" type="text" name="secretkey" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>">
					<span class="btn width-auto hasTooltip" title="<?php echo Text::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<span class="icon-help"></span>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
			<div class="mod-login__remember form-group">
				<div id="form-login-remember" class="form-check">
					<label class="form-check-label">
						<input type="checkbox" name="remember" class="form-check-input" value="yes">
						<?php echo Text::_('MOD_LOGIN_REMEMBER_ME'); ?>
					</label>
				</div>
			</div>
		<?php endif; ?>

		<div class="mod-login__submit form-group">
			<button type="submit" name="Submit" class="btn btn-primary"><?php echo Text::_('JLOGIN'); ?></button>
		</div>

		<?php
			$usersConfig = ComponentHelper::getParams('com_users'); ?>
			<ul class="mod-login__options list-unstyled">
			<?php if ($usersConfig->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>">
					<?php echo Text::_('MOD_LOGIN_REGISTER'); ?> <span class="icon-arrow-right"></span></a>
				</li>
			<?php endif; ?>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
					<?php echo Text::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
				</li>
				<li>
					<a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
					<?php echo Text::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
				</li>
			</ul>
		<input type="hidden" name="option" value="com_users">
		<input type="hidden" name="task" value="user.login">
		<input type="hidden" name="return" value="<?php echo $return; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
	<?php if ($params->get('posttext')) : ?>
		<div class="mod-login__posttext posttext">
			<p><?php echo $params->get('posttext'); ?></p>
		</div>
	<?php endif; ?>
</form>
