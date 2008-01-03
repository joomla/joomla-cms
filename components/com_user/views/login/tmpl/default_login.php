<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php if(JPluginHelper::isEnabled('authentication', 'openid')) : ?>
	<?php JHTML::_('script', 'openid.js'); ?>
<?php endif; ?>
<form action="<?php echo JRoute::_( 'index.php', true, $this->params->get('usesecure')); ?>" method="post" name="login" id="form-login">
<table width="100%" border="0" align="center" cellpadding="4" cellspacing="0" class="contentpane<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
<tr>
	<td colspan="2">
		<?php if ( $this->params->get( 'show_login_title' ) ) : ?>
		<div class="componentheading<?php echo $this->params->get( 'pageclass_sfx' ); ?>">
			<?php echo $this->params->get( 'header_login' ); ?>
		</div>
		<?php endif; ?>
		<div>
			<?php echo $this->image; ?>
			<?php if ( $this->params->get( 'description_login' ) ) : ?>
				<?php echo $this->params->get( 'description_login_text' ); ?>
				<br/><br/>
			<?php endif; ?>
		</div>
	</td>
</tr>
<tr>
</table>
<fieldset class="input">
	<p id="form-login-username">
		<label for="username"><?php echo JText::_('Username') ?></label><br />
		<input name="username" id="username" type="text" class="inputbox" alt="username" size="18" />
	</p>
	<p id="form-login-password">
		<label for="passwd"><?php echo JText::_('Password') ?></label><br />
		<input type="password" name="passwd" class="inputbox" size="18" alt="password" />
	</p>
	<?php if(JPluginHelper::isEnabled('system', 'remember')) : ?>
	<p id="form-login-remember">
		<label for="remember"><?php echo JText::_('Remember me') ?></label>
		<input type="checkbox" name="remember" class="inputbox" value="yes" alt="Remember Me" />
	</p>
	<?php endif; ?>
	<input type="submit" name="Submit" class="button" value="<?php echo JText::_('LOGIN') ?>" />
</fieldset>
<ul>
	<li>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=reset' ); ?>">
		<?php echo JText::_('FORGOT_YOUR_PASSWORD'); ?></a>
	</li>
	<li>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&view=remind' ); ?>">
		<?php echo JText::_('FORGOT_YOUR_USERNAME'); ?></a>
	</li>
	<?php
	$usersConfig = &JComponentHelper::getParams( 'com_users' );
	if ($usersConfig->get('allowUserRegistration')) : ?>
	<li>
		<a href="<?php echo JRoute::_( 'index.php?option=com_user&task=register' ); ?>">
			<?php echo JText::_('REGISTER'); ?></a>
	</li>
	<?php endif; ?>
</ul>

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $this->return; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
</form>