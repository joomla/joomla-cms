<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<?php if($type == 'logout') : ?>
<form action="index.php" method="post" name="login">
<?php if ($params->get('greeting')) : ?>
	<div><?php echo JText::sprintf( 'HINAME', $user->get('name') ); ?></div>
<?php endif; ?>
	<div align="center">
		<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'BUTTON_LOGOUT'); ?>" />
	</div>

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="logout" />
	<input type="hidden" name="return" value="<?php echo $return; ?>" />
</form>
<?php else : ?>
<form action="index.php" method="post" name="login" >
	<?php echo $params->get('pretext'); ?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
	<tr>
		<td>
			<label for="mod_login_username"><?php echo JText::_( 'Username' ); ?></label>
			<br />
			<input name="username" id="mod_login_username" type="text" class="inputbox" alt="<?php echo JText::_( 'Username' ); ?>" size="10" />
			<br />
			<label for="mod_login_password"><?php echo JText::_( 'Password' ); ?></label>
			<br />
			<input type="password" id="mod_login_password" name="passwd" class="inputbox" size="10" alt="<?php echo JText::_( 'Password' ); ?>" />
			<br />
			<input type="checkbox" name="remember" id="mod_login_remember" class="inputbox" value="yes" alt="<?php echo JText::_( 'Remember me' ); ?>" />
			<label for="mod_login_remember"><?php echo JText::_( 'Remember me' ); ?></label>
			<br />
			<input type="submit" name="Submit" class="button" value="<?php echo JText::_( 'BUTTON_LOGIN'); ?>" />
		</td>
	</tr>
	<tr>
		<td>
			<a href="<?php echo JRoute::_( 'index.php?option=com_user&task=lostPassword' ); ?>">
				<?php echo JText::_( 'Lost Password?'); ?>
			</a>
		</td>
	</tr>
	<?php
	$usersConfig = &JComponentHelper::getParams( 'com_users' );
	if ($usersConfig->get('allowUserRegistration')) : ?>
	<tr>
		<td>
			<?php echo JText::_( 'No account yet?'); ?>
			<a href="<?php echo JRoute::_( 'index.php?option=com_user&task=register' ); ?>">
				<?php echo JText::_( 'Register'); ?>
			</a>
		</td>
	</tr>
	<?php endif; ?>
	</table>
	<?php echo $params->get('posttext'); ?>

	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="login" />
	<input type="hidden" name="return" value="<?php echo $return ?>" />
	<input type="hidden" name="<?php echo JUtility::getToken(); ?>" value="1" />
</form>
<?php endif; ?>
