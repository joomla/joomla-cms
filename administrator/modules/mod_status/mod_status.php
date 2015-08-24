<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_status
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the helper functions only once
require_once __DIR__ . '/helper.php';

$config = JFactory::getConfig();
$user   = JFactory::getUser();
$db     = JFactory::getDbo();
$lang   = JFactory::getLanguage();
$input  = JFactory::getApplication()->input;

// Get the number of unread messages in your inbox.
$query = $db->getQuery(true)
	->select('COUNT(*)')
	->from('#__messages')
	->where('state = 0 AND user_id_to = ' . (int) $user->get('id'));

$db->setQuery($query);
$unread = (int) $db->loadResult();

// Get the number of back-end logged in users.
$count = (int) ModStatusHelper::getOnlineCount(true);

// Set the inbox link.
if ($input->getBool('hidemainmenu'))
{
	$inboxLink = '';
}
else
{
	$inboxLink = JRoute::_('index.php?option=com_messages');
}

// Set the inbox class.
if ($unread)
{
	$inboxClass = 'unread-messages';
}
else
{
	$inboxClass = 'no-unread-messages';
}

// Get the number of frontend logged in users.
$online_num = (int) ModStatusHelper::getOnlineCount(false);

require JModuleHelper::getLayoutPath('mod_status', $params->get('layout', 'default'));
