<?php
defined('_JEXEC') or die('Restricted access');

/*
 *
 * Get the template parameters
 *
 */
$filename = JPATH_ROOT . DS . 'templates' . DS . $mainframe->getTemplate() . DS . 'params.ini';
if ($content = @ file_get_contents($filename)) {
	$templateParams = new JParameter($content);
} else {
	$templateParams = null;
}
/*
 * hope to get a better solution very soon
 */

$hlevel = $templateParams->get('headerLevelComponent', '2');
$ptlevel = $templateParams->get('pageTitleHeaderLevel', '1');

echo '<form action="index.php" method="post" name="login" id="login" class="login_form' . $this->params->get('pageclass_sfx') . '">';
if ($this->params->get('page_title')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->params->get('header_login');
	echo '</h' . $ptlevel . '>';
}

if ($this->params->get('description_login') || isset ($this->image)) {
	$wrap = '';
	echo '<div class="contentdescription' . $this->params->get('pageclass_sfx') . '">';
	if (isset ($this->image)) {
		echo $this->image;
		$wrap = '<div class="wrap_image">&nbsp;</div>';
	}
	if ($this->params->get('description_login')) {
		echo '<p>' . $this->params->get('description_login_text') . '</p>';
	}
	echo $wrap;
	echo '</div>';
}
echo '<fieldset>';
echo '<div class="name"><label for="user" >' . JText :: _('Username') . '</label>';
echo '<input name="username" type="text" class="inputbox" size="20"  id="user"/></div>';
echo '<div class="pass"><label for="pass" >' . JText :: _('Password') . '</label>';
echo '<input name="passwd" type="password" class="inputbox" size="20" id="pass" /></div>';

echo '<div class="remember"><label for="rem">' . JText :: _('Remember me') . '</label>';
echo '<input type="checkbox" name="remember" class="inputbox" value="yes" id="rem"/></div>';
echo '</fieldset>';
echo '<p><a href="' . JRoute :: _('index.php?option=com_user&amp;task=lostPassword') . '">';
echo JText :: _('Lost Password?');
echo '</a>';

if ($this->params->get('registration')) {
	echo JText :: _('No account yet?');
	echo '<a href="' . JRoute :: _('index.php?option=com_user&amp;task=register') . '">' . JText :: _('Register') . '</a>';
}
echo '</p>';

echo '<input type="submit" name="submit" class="button" value="' . JText :: _('Login') . '" />';
echo '<noscript>' . JText :: _('WARNJAVASCRIPT') . '</noscript>';
echo '<input type="hidden" name="option" value="com_user" />';
echo '<input type="hidden" name="task" value="login" />';
echo '<input type="hidden" name="return" value="'. $this->return .'" />';
echo '<input type="hidden" name="token" value="' . JUtility :: getToken() . ' " />';

echo '</form>';