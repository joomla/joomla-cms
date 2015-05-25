<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$config	= JFactory::getConfig();
$user   = JFactory::getUser();
$db     = JFactory::getDbo();
$lang   = JFactory::getLanguage();
$input  = JFactory::getApplication()->input;
// Get the number of back-end logged in users.
$query->clear()
	->select('COUNT(session_id)')
	->from('#__session')
	->where('guest = 0 AND client_id = 1');

$db->setQuery($query);
$count = (int) $db->loadResult();

// Get the number of frontend logged in users.
$query->clear()
	->select('COUNT(session_id)')
	->from('#__session')
	->where('guest = 0 AND client_id = 0');

$db->setQuery($query);
$online_num = (int) $db->loadResult();

require JModuleHelper::getLayoutPath('mod_status', $params->get('layout', 'default'));
