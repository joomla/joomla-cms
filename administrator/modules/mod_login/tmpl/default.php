<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
			<label for="mod-login-username"><?php echo Text::_('JGLOBAL_USERNAME'); ?></label>
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

		<div class="form-group">
			<label for="mod-login-password"><?php echo Text::_('JGLOBAL_PASSWORD'); ?></label>
			<div class="input-group">
				<input
					name="passwd"
					id="mod-login-password"
					type="password"
					class="form-control input-full"
					required="required"
                    tabindex="2"
                >
				<span class="input-group-addon">
					<span class="fa fa-eye" aria-hidden="true"></span>
					<span class="sr-only"><?php echo Text::_('JSHOW'); ?></span>
				</span>
			</div>
		</div>

		<?php if (count($twofactormethods) > 1): ?>
			<label for="mod-login-secretkey"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></label>
			<div class="form-group">
				<input
					name="secretkey"
					autocomplete="off"
					id="mod-login-secretkey"
					type="text"
					class="form-control input-full"
                    tabindex="3"
				>
			</div>
		<?php endif; ?>

		<?php if (!empty($langs)) : ?>
			<div class="form-group">
				<label for="lang" class="sr-only"><?php echo Text::_('JDEFAULTLANGUAGE'); ?></label>
				<?php echo $langs; ?>
			</div>
		<?php endif; ?>

		<div class="form-group">
			<button tabindex="5" class="btn btn-success btn-block btn-lg" id="btn-login-submit">
				<span class="fa fa-lock icon-white" aria-hidden="true"></span> <?php echo Text::_('JLOGIN'); ?>
			</button>
		</div>

		<div class="text-center">
			<div><a href="<?php echo Uri::root(); ?>index.php?option=com_users&view=remind"><?php echo Text::_('MOD_LOGIN_REMIND'); ?></a></div>
			<div><a href="<?php echo Uri::root(); ?>index.php?option=com_users&view=reset"><?php echo Text::_('MOD_LOGIN_RESET'); ?></a></div>
		</div>

		<input type="hidden" name="option" value="com_login">
		<input type="hidden" name="task" value="login">
		<input type="hidden" name="return" value="<?php echo $return; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
