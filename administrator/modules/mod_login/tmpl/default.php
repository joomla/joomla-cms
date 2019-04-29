<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

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
			<label for="mod-login-username">
				<?php echo Text::_('JGLOBAL_USERNAME'); ?>
			</label>
			<div class="input-group">

				<input
					name="username"
					id="mod-login-username"
					type="text"
					class="form-control input-full"
					required="required"
					autofocus
				>
			</div>
		</div>
		<div class="form-group">
			<label for="mod-login-password">
				<?php echo Text::_('JGLOBAL_PASSWORD'); ?>
			</label>
			<div class="input-group">

				<input
					name="passwd"
					id="mod-login-password"
					type="password"
					class="form-control input-full"
					required="required"
				>
				<span class="input-group-append ml-2">
					<span class="sr-only"><?php echo Text::_('JSHOW'); ?></span>
					<button type="button" class="input-group-text icon-eye input-password-toggle" aria-hidden="true" ></button>
				</span>
			</div>
		</div>

		<?php if (count($twofactormethods) > 1): ?>
			<div class="form-group">
				<label for="mod-login-secretkey">
					<span class="label"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></span>
                    <span class="text-right">
                        <?php echo Text::_('COM_LOGIN_TWOFACTOR'); ?>
                    </span>
				</label>
				<div class="input-group">

					<input
						name="secretkey"
						autocomplete="off"
						id="mod-login-secretkey"
						type="text"
						class="form-control input-full"
					>
				</div>
			</div>
		<?php endif; ?>
		<?php if (!empty($langs)) : ?>
			<div class="form-group">
				<label for="lang">
					<?php echo Text::_('MOD_LOGIN_LANGUAGE'); ?>
				</label>
				<?php echo $langs; ?>
			</div>
		<?php endif; ?>
		<div class="form-group">
			<button class="btn btn-primary btn-block btn-lg mt-4" id="btn-login-submit"><?php echo Text::_('JLOGIN'); ?></button>
		</div>
		<input type="hidden" name="option" value="com_login">
		<input type="hidden" name="task" value="login">
		<input type="hidden" name="return" value="<?php echo $return; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
<div class="text-center">
    <div>
        <a href="<?php echo Text::_('MOD_LOGIN_CREDENTIALS_LINK'); ?>" target="_blank" rel="nofollow">
			<?php echo Text::_('MOD_LOGIN_CREDENTIALS'); ?>
        </a>
    </div>
</div>
