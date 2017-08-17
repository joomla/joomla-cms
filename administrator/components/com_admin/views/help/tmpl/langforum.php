<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$forumId   = (int) JText::_('COM_ADMIN_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');
$forum_url = 'https://forum.joomla.org/viewforum.php?f=' . $forumId;

JFactory::getApplication()->redirect($forum_url);
