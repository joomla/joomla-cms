<?php // no direct access
defined('_JEXEC') or die; ?>
<?php if ($type == 'logout') : ?>
<form action="index.php" method="post" name="form-login" id="form-login">
<?php if ($params->get('greeting')) : ?>
	<div>
	<?php if ($params->get('name')) : {
		echo JText::sprintf('HINAME', $user->get('name'));
	} else : {
		echo JText::sprintf('HINAME', $user->get('username'));
	} endif; ?>
	</div>
<?php endif; ?>
	<div align="center">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_('BUTTON_LOGOUT'); ?>" />
	</div>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="logout" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
<?php else : ?>
<?php if (JPluginHelper::isEnabled('authentication', 'openid')) : ?>
	<?php JHtml::_('script', 'openid'); ?>
<?php endif; ?>
<form action="index.php" method="post" name="form-login" id="form-login" >
	<?php echo $params->get('pretext'); ?>
	<fieldset class="input">
	<p id="form-login-username">
		<label for="username">
			<?php echo JText::_('Username') ?><br />
			<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
		</label>
	</p>
	<p id="form-login-password">
		<label for="passwd">
			<?php echo JText::_('Password') ?><br />
			<input type="password" name="passwd" id="passwd" class="inputbox" size="18" alt="password" />
		</label>
	</p>
	<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="form-login-remember">
		<label for="remember">
			<?php echo JText::_('Remember me') ?>
			<input type="checkbox" name="remember" id="remember" value="yes" alt="Remember Me" />
		</label>
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
	</fieldset>
	<ul>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>">
			<?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?>
			</a>
		</li>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
			<?php echo JText::_('FORGOT_YOUR_USERNAME'); ?>
			</a>
		</li>
		<?php
		$usersConfig = &JComponentHelper::getParams('com_users');
		if ($usersConfig->get('allowUserRegistration')) : ?>
		<li>
			<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
				<?php echo JText::_('REGISTER'); ?>
			</a>
		</li>
		<?php endif; ?>
	</ul>
	<?php echo $params->get('posttext'); ?>

	<input type="hidden" name="option" value="com_users" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php endif; ?>
