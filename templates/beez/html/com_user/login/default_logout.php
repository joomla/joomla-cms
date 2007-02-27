<?php
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id$
 * @author Design & Accessible Team ( Angie Radtke / Robert Deutz )
 * @package Joomla
 * @subpackage Accessible-Template-Beez
 * @copyright Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

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