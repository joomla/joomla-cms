<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFactory::getLanguage()->load('mod_menu', JPATH_ADMINISTRATOR, null, false, true);

$forumId   = (int) JText::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');

if (empty($forumId))
{
	$forumId = 511;
}

$forum_url = 'https://forum.joomla.org/viewforum.php?f=' . $forumId;

JFactory::getApplication()->redirect($forum_url);
