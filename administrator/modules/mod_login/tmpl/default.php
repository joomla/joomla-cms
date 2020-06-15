<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('core')
	->useScript('form.validate')
	->useScript('keepalive')
	->useScript('field.passwordview')
	->registerAndUseScript('mod_login.admin', 'mod_login/admin-login.min.js', [], ['defer' => true], ['core', 'form.validate']);

Text::script('JSHOWPASSWORD');
Text::script('JHIDEPASSWORD');
// Load JS message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');
?>
<form class="login-initial hidden form-validate" action="<?php echo Route::_('index.php', true); ?>" method="post"
	id="form-login">
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
					class="form-control"
					required="required"
					autofocus
					autocomplete="username"
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
					autocomplete="current-password"
				>
				<span class="input-group-append">
					<button type="button" class="btn btn-secondary input-password-toggle">
						<span class="fas fa-eye" aria-hidden="true"></span>
						<span class="sr-only"><?php echo Text::_('JSHOWPASSWORD'); ?></span>
					</button>
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
						autocomplete="one-time-code"
						id="mod-login-secretkey"
						type="text"
						class="form-control"
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
		<?php foreach ($extraButtons as $button) : ?>
		<div class="form-group">
			<button type="button"
			        class="btn btn-secondary btn-block mt-4 <?= $button['class'] ?? '' ?>"
			        data-webauthn-form="<?= $button['data-webauthn-form'] ?>"
			        data-webauthn-url="<?= $button['data-webauthn-url'] ?>"
			        title="<?= Text::_($button['label']) ?>"
			        id="<?= $button['id'] ?>"
			>
				<?php if (!empty($button['icon'])): ?>
					<span class="<?= $button['icon'] ?>"></span>
				<?php elseif (!empty($button['image'])): ?>
					<?= HTMLHelper::_('image', $button['image'], Text::_('PLG_SYSTEM_WEBAUTHN_LOGIN_DESC'), [
						'class' => 'icon',
					], true) ?>
				<?php endif; ?>
				<?= Text::_($button['label']) ?>
			</button>
		</div>
		<?php endforeach; ?>
		<div class="form-group">
			<button class="btn btn-primary btn-block btn-lg mt-4"
				id="btn-login-submit"><?php echo Text::_('JLOGIN'); ?></button>
		</div>
		<input type="hidden" name="option" value="com_login">
		<input type="hidden" name="task" value="login">
		<input type="hidden" name="return" value="<?php echo $return; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
<div class="text-center">
	<div>
		<a href="<?php echo Text::_('MOD_LOGIN_CREDENTIALS_LINK'); ?>" target="_blank" rel="nofollow"
			title="<?php echo Text::sprintf('JBROWSERTARGET_NEW_TITLE', Text::_('MOD_LOGIN_CREDENTIALS')); ?>">
			<?php echo Text::_('MOD_LOGIN_CREDENTIALS'); ?>
		</a>
	</div>
</div>
