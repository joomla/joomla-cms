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

echo '<form action="' . JURI :: resolve('index.php') . '" method="post" name="josForm" class="lost_password" >';
echo '<h' . $hlevel . ' class="componentheading">' . JText :: _('Lost your Password?') . '</h' . $hlevel . '>';

echo '<p>' . JText :: _('NEW_PASS_DESC') . '</p>';
echo '<p>' . '<label for="jusername">' . JText :: _('Username') . '</label>';
echo '<input type="text" id="jusername" name="jusername" class="inputbox"  maxlength="25" />' . '</p>';

echo '<p>' . '<label for="jemail">' . JText :: _('Email Address') . '</label>';
echo '<input type="text" id="jemail" name="jemail" class="inputbox"  />' . '</p>';

echo '<input type="submit" value="' . JText :: _('Send') . '" class="button" />';
echo '<input type="hidden" name="task" value="sendreminder" />';
echo '<input type="hidden" name="option" value="com_registration" />';
echo '<input type="hidden" name="' . JUtility :: getToken() . '" value="1" />';
echo '</form>';
?>