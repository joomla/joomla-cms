<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_login
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('field.passwordview')
	->registerAndUseScript('mod_login.admin', 'mod_login/admin-login.min.js', [], ['defer' => true], ['core', 'form.validate']);

Text::script('JSHOWPASSWORD');
Text::script('JHIDEPASSWORD');

$task = $params->set('task', 'update.confirm');
//$loginmodule->params->set('task', 'update.finaliseconfirm');

?>
<form class="form-validate" action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login">
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
						<span class="icon-eye icon-fw" aria-hidden="true"></span>
						<span class="sr-only"><?php echo Text::_('JSHOWPASSWORD'); ?></span>
					</button>
				</span>

			</div>
		</div>

		<?php if (count($twofactormethods) > 1): ?>
			<div class="form-group">
				<label for="mod-login-secretkey">
					<span class="label"><?php echo Text::_('JGLOBAL_SECRETKEY'); ?></span>
					<span class="form-control-hint">
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
		<div class="d-flex justify-content-between">
				<a class="btn btn-danger" href="index.php?option=com_joomlaupdate">
					<span class="icon-times icon-white" aria-hidden="true"></span>
					<?php echo Text::_('JCANCEL'); ?>
				</a>
				<button type="submit" class="btn btn-primary">
					<span class="icon-play icon-white" aria-hidden="true"></span>
					<?php echo Text::_($task === 'update.confirm'
							? 'COM_INSTALLER_INSTALL_BUTTON'
							: 'COM_JOOMLAUPDATE_VIEW_UPDATE_FINALISE_CONFIRM_AND_CONTINUE'); ?>
				</button>
		</div>
		<input type="hidden" name="option" value="com_joomlaupdate">
		<input type="hidden" name="task" value="<?php echo $task; ?>">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
