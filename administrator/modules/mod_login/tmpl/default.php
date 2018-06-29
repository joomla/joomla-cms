<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'system/fields/passwordview.min.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::_('script', 'mod_login/admin-login.min.js', ['version' => 'auto', 'relative' => true]);

Text::script('JSHOW');
Text::script('JHIDE');

?>
<form class="login-initial form-validate" action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
	<fieldset>
		<div class="form-group">
			<label class="text-white" for="mod-login-username">
				<?php echo Text::_('JGLOBAL_USERNAME'); ?>
			</label>
			<div class="input-group">
				<span class="input-group-prepend">
					<span class="input-group-text">
						<span class="fa fa-user" aria-hidden="true"></span>
					</span>
				</span>
				<input
					name="username"
					id="mod-login-username"
					type="text"
					class="form-control input-full"
					required="required"
					autofocus
					tabindex="1"
				>
			</div>
		</div>
		<div class="form-group">
			<label class="text-white" for="mod-login-password">
				<?php echo Text::_('JGLOBAL_PASSWORD'); ?>
			</label>
			<div class="input-group">
				<span class="input-group-prepend">
					<span class="input-group-text">
						<span class="fa fa-lock" aria-hidden="true"></span>
						<span class="sr-only"><?php echo Text::_('JSHOW'); ?></span>
					</span>
				</span>
				<input
					name="passwd"
					id="mod-login-password"
					type="password"
					class="form-control input-full"
					required="required"
					tabindex="2"
				>
			</div>
		</div>

		<?php if (count($twofactormethods) > 1): ?>
			<div class="form-group">
				<label class="text-white" for="mod-login-secretkey">
					<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>
				</label>
				<div class="input-group">
					<span class="input-group-prepend">
						<span class="input-group-text"><span class="fa fa-shield" aria-hidden="true"></span></span>
					</span>
					<input
						name="secretkey"
						autocomplete="off"
						id="mod-login-secretkey"
						type="text"
						class="form-control input-full"
						tabindex="3"
					>
				</div>
			</div>
		<?php endif; ?>
		<?php if (!empty($langs)) : ?>
			<div class="form-group">
				<label class="text-white" for="lang" class="sr-only">
					<?php echo Text::_('MOD_LOGIN_LANGUAGE'); ?>
				</label>
				<?php echo $langs; ?>
			</div>
		<?php endif; ?>
		<div class="form-group">
			<button tabindex="5" class="btn btn-primary btn-block btn-lg mt-4" id="btn-login-submit"><?php echo Text::_('JLOGIN'); ?></button>
		</div>
		<div class="text-center">
			<div>
				<a href="<?php echo Uri::root(); ?>index.php?option=com_users&view=remind">
					<?php echo Text::_('MOD_LOGIN_REMIND'); ?>
				</a>
			</div>
			<div>
				<a href="<?php echo Uri::root(); ?>index.php?option=com_users&view=reset">
					<?php echo Text::_('MOD_LOGIN_RESET'); ?>
				</a>
			</div>
		</div>
		<input type="hidden" name="option" value="com_login">
		<input type="hidden" name="task" value="login">
		<input type="hidden" name="return" value="<?php echo $return; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
