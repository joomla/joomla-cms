<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('script', 'system/fields/passwordview.min.js', array('version' => 'auto', 'relative' => true));

Text::script('JSHOW');
Text::script('JHIDE');
?>
<form id="<?= $formId ?>" class="mod-login" action="<?= Route::_('index.php', true); ?>" method="post">

	<?php if ($params->get('pretext')) : ?>
		<div class="mod-login__pretext pretext">
			<p><?= $params->get('pretext'); ?></p>
		</div>
	<?php endif; ?>

	<div class="mod-login__userdata userdata">
		<div class="mod-login__username form-group">
			<?php if (!$params->get('usetext', 0)) : ?>
				<div class="input-group">
					<input id="modlgn-username-<?= $module->id ?>" type="text" name="username" class="form-control" placeholder="<?= Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>">
					<span class="input-group-append">
						<label for="modlgn-username-<?= $module->id ?>" class="sr-only"><?= Text::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
						<span class="input-group-text icon-user" title="<?= Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>"></span>
					</span>
				</div>
			<?php else : ?>
				<label for="modlgn-username-<?= $module->id ?>"><?= Text::_('MOD_LOGIN_VALUE_USERNAME'); ?></label>
				<input id="modlgn-username-<?= $module->id ?>" type="text" name="username" class="form-control" placeholder="<?= Text::_('MOD_LOGIN_VALUE_USERNAME'); ?>">
			<?php endif; ?>
		</div>

		<div class="mod-login__password form-group">
			<?php if (!$params->get('usetext', 0)) : ?>
				<div class="input-group">
					<input id="modlgn-passwd-<?= $module->id ?>" type="password" name="password" class="form-control" placeholder="<?= Text::_('JGLOBAL_PASSWORD'); ?>">
					<span class="input-group-append">
						<span class="sr-only"><?= Text::_('JSHOW'); ?></span>
						<span class="input-group-text icon-eye" aria-hidden="true"></span>
					</span>
				</div>
			<?php else : ?>
				<label for="modlgn-passwd-<?= $module->id ?>"><?= Text::_('JGLOBAL_PASSWORD'); ?></label>
				<input id="modlgn-passwd-<?= $module->id ?>" type="password" name="password" class="form-control" placeholder="<?= Text::_('JGLOBAL_PASSWORD'); ?>">
			<?php endif; ?>
		</div>

		<?php if (count($twofactormethods) > 1) : ?>
			<div class="mod-login__twofactor form-group">
				<?php if (!$params->get('usetext', 0)) : ?>
					<div class="input-group">
						<span class="input-group-prepend">
							<span class="input-group-text icon-star" title="<?= Text::_('JGLOBAL_SECRETKEY'); ?>"></span>
							<label for="modlgn-secretkey-<?= $module->id ?>" class="sr-only"><?= Text::_('JGLOBAL_SECRETKEY'); ?></label>
						</span>
						<input id="modlgn-secretkey-<?= $module->id ?>" autocomplete="off" type="text" name="secretkey" class="form-control" placeholder="<?= Text::_('JGLOBAL_SECRETKEY'); ?>">
						<span class="input-group-append" title="<?= Text::_('JGLOBAL_SECRETKEY_HELP'); ?>">
							<span class="input-group-text icon-help"></span>
						</span>
					</div>
				<?php else : ?>
					<label for="modlgn-secretkey-<?= $module->id ?>"><?= Text::_('JGLOBAL_SECRETKEY'); ?></label>
					<input id="modlgn-secretkey-<?= $module->id ?>" autocomplete="off" type="text" name="secretkey" class="form-control" placeholder="<?= Text::_('JGLOBAL_SECRETKEY'); ?>">
					<span class="btn width-auto" title="<?= Text::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<span class="icon-help"></span>
					</span>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
			<div class="mod-login__remember form-group">
				<div id="form-login-remember-<?= $module->id; ?>" class="form-check">
					<label class="form-check-label">
						<input type="checkbox" name="remember" class="form-check-input" value="yes">
						<?= Text::_('MOD_LOGIN_REMEMBER_ME'); ?>
					</label>
				</div>
			</div>
		<?php endif; ?>

		<?php foreach($extraButtons as $button): ?>
			<div class="mod-login__submit form-group">
				<button type="button"
				        class="btn btn-secondary <?= $button['class'] ?? '' ?>"
				        onclick="<?= $button['onclick'] ?>"
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

		<div class="mod-login__submit form-group">
			<button type="submit" name="Submit" class="btn btn-primary"><?= Text::_('JLOGIN'); ?></button>
		</div>

		<?php
			$usersConfig = ComponentHelper::getParams('com_users'); ?>
			<ul class="mod-login__options list-unstyled">
			<?php if ($usersConfig->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?= Route::_('index.php?option=com_users&view=registration'); ?>">
					<?= Text::_('MOD_LOGIN_REGISTER'); ?> <span class="icon-arrow-right"></span></a>
				</li>
			<?php endif; ?>
				<li>
					<a href="<?= Route::_('index.php?option=com_users&view=remind'); ?>">
					<?= Text::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
				</li>
				<li>
					<a href="<?= Route::_('index.php?option=com_users&view=reset'); ?>">
					<?= Text::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
				</li>
			</ul>
		<input type="hidden" name="option" value="com_users">
		<input type="hidden" name="task" value="user.login">
		<input type="hidden" name="return" value="<?= $return; ?>">
		<?= HTMLHelper::_('form.token'); ?>
	</div>
	<?php if ($params->get('posttext')) : ?>
		<div class="mod-login__posttext posttext">
			<p><?= $params->get('posttext'); ?></p>
		</div>
	<?php endif; ?>
</form>
