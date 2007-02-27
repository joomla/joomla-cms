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

echo '<form action="index.php" method="post" name="login" id="login" class="logout_form' . $this->params->get('pageclass_sfx') . '">';

if ($this->params->get('page_title')) {
	echo '<h' . $ptlevel . ' class="componentheading' . $this->params->get('pageclass_sfx') . '">';
	echo $this->params->get('header_logout');
	echo '</h' . $ptlevel . '>';
}

if ($this->params->get('description_logout') || isset ($this->image)) {
	$wrap = '';
	echo '<div class="contentdescription' . $this->params->get('pageclass_sfx') . '">';
	if (isset ($this->image)) {
		echo $this->image;
		$wrap = '<div class="wrap_image">&nbsp;</div>';
	}
	if ($this->params->get('description_logout')) {
		echo '<p>' . $this->params->get('description_logout_text') . '</p>';
	}
	echo $wrap;
	echo '</div>';
}

echo '<p><input type="submit" name="Submit" class="button" value="' . JText :: _('Logout') . '" /></p>';

echo '<input type="hidden" name="option" value="com_login" />';
echo '<input type="hidden" name="task" value="logout" />';
echo '<input type="hidden" name="return" value="' . JRoute :: _($this->params->get('logout')) . '" />';
echo '</form>';