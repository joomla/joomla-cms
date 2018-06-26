<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

$usersConfig = ComponentHelper::getParams('com_users');

?>
<div class="com-users-login login">
	<?php if ($this->params->get('show_page_heading')) : ?>
	<div class="page-header">
		<h1>
			<?php echo $this->escape($this->params->get('page_heading')); ?>
		</h1>
	</div>
	<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	<div class="com-users-login__description login-description">
	<?php endif; ?>

		<?php if ($this->params->get('logindescription_show') == 1) : ?>
			<?php echo $this->params->get('login_description'); ?>
		<?php endif; ?>

		<?php if ($this->params->get('login_image') != '') : ?>
			<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="com-users-login__image login-image" alt="<?php echo Text::_('COM_USERS_LOGIN_IMAGE_ALT'); ?>">
		<?php endif; ?>

	<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
	</div>
	<?php endif; ?>

	<form action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="com-users-login__form form-validate form-horizontal well">

		<fieldset>
			<?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
				<?php if (!$field->hidden) : ?>
					<div class="com-users-login__input control-group">
						<div class="control-label">
							<?php echo $field->label; ?>
						</div>
						<div class="controls">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php endif; ?>
			<?php endforeach; ?>

			<?php if ($this->tfa) : ?>
				<div class="com-users-login__secretkey control-group">
					<div class="control-label">
						<?php echo $this->form->getField('secretkey')->label; ?>
					</div>
					<div class="controls">
						<?php echo $this->form->getField('secretkey')->input; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
				<div  class="com-users-login__remember control-group">
					<div class="control-label">
						<label for="remember">
							<?php echo Text::_('COM_USERS_LOGIN_REMEMBER_ME'); ?>
						</label>
					</div>
					<div class="controls">
						<input id="remember" type="checkbox" name="remember" class="inputbox" value="yes">
					</div>
				</div>
			<?php endif; ?>

			<div class="com-users-login__submit control-group">
				<div class="controls">
					<button type="submit" class="btn btn-primary">
						<?php echo Text::_('JLOGIN'); ?>
					</button>
				</div>
			</div>

			<?php $return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem'))); ?>
			<input type="hidden" name="return" value="<?php echo base64_encode($return); ?>">
			<?php echo HTMLHelper::_('form.token'); ?>
		</fieldset>
	</form>
</div>
<div>
	<div class="com-users-login__options list-group">
		<a class="com-users-login__reset list-group-item" href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
			<?php echo Text::_('COM_USERS_LOGIN_RESET'); ?>
		</a>
		<a class="com-users-login__remind list-group-item" href="<?php echo Route::_('index.php?option=com_users&view=remind'); ?>">
			<?php echo Text::_('COM_USERS_LOGIN_REMIND'); ?>
		</a>
		<?php if ($usersConfig->get('allowUserRegistration')) : ?>
			<a class="com-users-login__register list-group-item" href="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo Text::_('COM_USERS_LOGIN_REGISTER'); ?>
			</a>
		<?php endif; ?>
	</div>
</div>
