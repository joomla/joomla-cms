<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

HTMLHelper::_('behavior.keepalive');

$twofactormethods = AuthenticationHelper::getTwoFactorMethods();

?>

<div class="alert alert-warning">
	<h4 class="alert-heading">
		<?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPLOAD_CAPTIVE_INTRO_HEAD'); ?>
	</h4>
	<p>
		<?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_UPLOAD_CAPTIVE_INTRO_BODY', Factory::getApplication()->get('sitename')); ?>
	</p>
</div>

<hr>

<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login" class="text-center">
	<fieldset class="loginform">
		<legend><?php echo Text::_('COM_JOOMLAUPDATE_LOGIN'); ?></legend>
		<div class="control-group">
			<div class="controls">
				<div class="input-group">
					<input name="username" id="mod-login-username" type="text" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_USERNAME'); ?>" size="15" autofocus="true">
					<span class="input-group-append">
						<span class="input-group-text">
							<span class="fas fa-user" aria-hidden="true"></span>
							<label for="mod-login-username" class="sr-only">
								<?php echo Text::_('JGLOBAL_USERNAME'); ?>
							</label>
						</span>
					</span>
				</div>
			</div>
		</div>
		<div class="control-group">
			<div class="controls">
				<div class="input-group">
					<input name="passwd" id="mod-login-password" type="password" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>" size="15">
					<span class="input-group-append">
						<span class="input-group-text">
							<span class="fas fa-lock" aria-hidden="true"></span>
							<label for="mod-login-password" class="sr-only">
								<?php echo Text::_('JGLOBAL_PASSWORD'); ?>
							</label>
						</span>
					</span>
				</div>
			</div>
		</div>
		<?php if (count($twofactormethods) > 1) : ?>
			<div class="control-group">
				<div class="controls">
					<div class="input-group">
						<input name="secretkey" autocomplete="one-time-code" id="mod-login-secretkey" type="text" class="form-control" placeholder="<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>" size="15">
						<span class="input-group-append">
							<span class="input-group-text" title="<?php echo Text::_('JGLOBAL_SECRETKEY_HELP'); ?>">
								<span class="fas fa-star" aria-hidden="true"></span>
								<label for="mod-login-secretkey" class="sr-only">
									<?php echo Text::_('JGLOBAL_SECRETKEY'); ?>
								</label>
							</span>
						</span>
					</div>
				</div>
			</div>
		<?php endif; ?>
		<div class="control-group">
			<div class="controls">
				<div class="btn-group">
					<a class="btn btn-danger" href="index.php?option=com_joomlaupdate">
						<span class="fas fa-times icon-white" aria-hidden="true"></span> <?php echo Text::_('JCANCEL'); ?>
					</a>
					<button type="submit" class="btn btn-primary">
						<span class="fas fa-play icon-white" aria-hidden="true"></span> <?php echo Text::_('COM_INSTALLER_INSTALL_BUTTON'); ?>
					</button>
				</div>
			</div>
		</div>

		<input type="hidden" name="option" value="com_joomlaupdate">
		<input type="hidden" name="task" value="update.confirm">
		<?php echo HTMLHelper::_('form.token'); ?>
	</fieldset>
</form>
