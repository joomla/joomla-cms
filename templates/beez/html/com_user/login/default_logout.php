<?php
defined('_JEXEC') or die('Restricted access');

// temporary fix
$hlevel = 2;
$ptlevel = 1;

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

echo '<input type="hidden" name="option" value="com_user" />';
echo '<input type="hidden" name="task" value="logout" />';
echo '<input type="hidden" name="return" value="'. $this->return .'" />';
echo '</form>';