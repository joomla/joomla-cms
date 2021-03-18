<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getLanguage()->load('mod_menu', JPATH_ADMINISTRATOR);

$forumId   = (int) Text::_('MOD_MENU_HELP_SUPPORT_OFFICIAL_LANGUAGE_FORUM_VALUE');

if (empty($forumId))
{
	$forumId = 511;
}

$forumUrl = 'https://forum.joomla.org/viewforum.php?f=' . $forumId;

Factory::getApplication()->redirect($forumUrl);
