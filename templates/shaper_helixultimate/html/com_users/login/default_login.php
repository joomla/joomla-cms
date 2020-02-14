<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

$usersConfig = JComponentHelper::getParams('com_users');

?>
<div class="login<?php echo $this->pageclass_sfx; ?>">
	<div class="row justify-content-center">
		<div class="col-md-4">
			<?php if ($this->params->get('show_page_heading')) : ?>
				<div class="page-header">
					<h1>
						<?php echo $this->escape($this->params->get('page_heading')); ?>
					</h1>
				</div>
			<?php endif; ?>

			<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
				<div class="login-description">
				<?php endif; ?>

				<?php if ($this->params->get('logindescription_show') == 1) : ?>
					<?php echo $this->params->get('login_description'); ?>
				<?php endif; ?>

				<?php if ($this->params->get('login_image') != '') : ?>
					<img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image" alt="<?php echo JText::_('COM_USERS_LOGIN_IMAGE_ALT'); ?>">
				<?php endif; ?>

				<?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
				</div>
			<?php endif; ?>

			<form action="<?php echo JRoute::_('index.php?option=com_users&task=user.login'); ?>" method="post" class="form-validate">

				<?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
					<?php if (!$field->hidden) : ?>
						<div class="form-group">
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if ($this->tfa) : ?>
					<div class="form-group">
						<?php echo $this->form->getField('secretkey')->label; ?>
						<?php echo $this->form->getField('secretkey')->input; ?>
					</div>
				<?php endif; ?>

				<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
					<div class="form-check form-group">
						<label class="form-check-label">
							<input class="form-check-input" type="checkbox" name="remember" id="remember" class="inputbox" value="yes">
							<?php echo JText::_('COM_USERS_LOGIN_REMEMBER_ME') ?>
						</label>
					</div>
				<?php endif; ?>

				<div class="form-group">
					<button type="submit" class="btn btn-primary btn-lg btn-block">
						<?php echo JText::_('JLOGIN'); ?>
					</button>
				</div>

				<?php $return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem'))); ?>
				<input type="hidden" name="return" value="<?php echo base64_encode($return); ?>">
				<?php echo JHtml::_('form.token'); ?>
			</form>

			<div>
				<div class="list-group">
					<a class="list-group-item" href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
						<?php echo JText::_('COM_USERS_LOGIN_RESET'); ?>
					</a>
					<a class="list-group-item" href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
						<?php echo JText::_('COM_USERS_LOGIN_REMIND'); ?>
					</a>
					<?php if ($usersConfig->get('allowUserRegistration')) : ?>
						<a class="list-group-item" href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
							<?php echo JText::_('COM_USERS_LOGIN_REGISTER'); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>
