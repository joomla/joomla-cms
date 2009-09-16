<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$db = &JFactory::getDbo();
$user = &JFactory::getUser();

$query = 'SELECT COUNT(*)'
. ' FROM #__messages'
. ' WHERE state = 0'
. ' AND user_id_to = '.(int) $user->get('id')
;
$db->setQuery($query);
$unread = $db->loadResult();

if ($unread) {
	echo "<div id=\"module-unread-new\"><a href=\"index.php?option=com_messages\">$unread <img src=\"images/mail.png\" alt=\"". JText::_('Mail') ."\" /></a></div>";
} else {
	echo "<div id=\"module-unread\"><a href=\"index.php?option=com_messages\">$unread <img src=\"images/nomail.png\" alt=\"". JText::_('Mail') ."\" /></a></div>";
}