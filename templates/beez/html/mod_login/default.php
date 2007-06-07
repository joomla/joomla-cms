<?php
defined('_JEXEC') or die('Restricted access');

if ($type == 'logout') {
	echo '<form action="index.php" method="post" name="login" class="log">';
	if ($params->get('greeting')) {
		echo '<p>' . sprintf(JText :: _('HINAME'), $user->get('name')) . '</p>';
	}
	echo '<p><input type="submit" name="Submit" class="button" value="' . JText :: _('BUTTON_LOGOUT') . '" /></p>';
	echo '<input type="hidden" name="option" value="com_user" />';
	echo '<input type="hidden" name="task" value="logout" />';
	echo '<input type="hidden" name="return" value="' .$return. '" />';
	echo '</form>';
} else {
	echo '<form action="index.php" method="post" name="login" class="login" >';
	echo '<p>' . $params->get('pretext') . '</p>';
	echo '<fieldset>';
	echo '<label for="mod_login_username">' . JText :: _('Username') . '</label>';
	echo '<input name="username" id="mod_login_username" type="text" class="inputbox" alt="' . JText :: _('Username') . '"  />';
	echo '<label for="mod_login_password">';
	echo '' . JText :: _('Password') . '</label>';
	echo '<input type="password" id="mod_login_password" name="passwd" class="inputbox"  alt="' . JText :: _('Password') . '" />';
	echo ' </fieldset>';
	echo '<label for="mod_login_remember" class="remember">' . JText :: _('Remember me') . '</label>';
	echo '<input type="checkbox" name="remember" id="mod_login_remember" class="checkbox" value="yes" alt="' . JText :: _('Remember me') . '" />';
	echo '<input type="submit" name="Submit" class="button" value="' . JText :: _('BUTTON_LOGIN') . '" />';
	echo '<p><a href="' . JRoute :: _('index.php?option=com_user&amp;task=lostPassword') . '">' . JText :: _('Lost Password?') . '</a></p>';
	$usersConfig = & JComponentHelper :: getParams('com_users');
	if ($usersConfig->get('allowUserRegistration')) {
		echo '<p>' . JText :: _('No account yet?') . ' <a href="' . JRoute :: _('index.php?option=com_user&amp;task=register') . '" >';
		echo JText :: _('Register') . '</a></p>';
	}
	echo $params->get('posttext');
	echo '<input type="hidden" name="option" value="com_user" />';
	echo '<input type="hidden" name="task" value="login" />';
	echo '<input type="hidden" name="return" value="'.$return.'" />';
	echo '<input type="hidden" name="' . JUtility :: getToken() . '" value="1" />';
	echo '</form>';
}
?>