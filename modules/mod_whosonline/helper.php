<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_whosonline
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modWhosonlineHelper
{
	// show online count
	static function getOnlineCount() {
		$db		= JFactory::getDbo();
		$sessions = null;
		// calculate number of guests and users
		$result	= array();
		$user_array  = 0;
		$guest_array = 0;
		$query	= $db->getQuery(true);
		$query->select('guest, usertype, client_id');
		$query->from('#__session');
		$query->where('client_id = 0');
		$db->setQuery($query);
		$sessions = $db->loadObjectList();

		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->stderr());
		}

		if (count($sessions)) {
			foreach ($sessions as $session) {
				// if guest increase guest count by 1
				if ($session->guest == 1 && !$session->usertype) {
					$guest_array ++;
				}
				// if member increase member count by 1
				if ($session->guest == 0) {
					$user_array ++;
				}
			}
		}

		$result['user']  = $user_array;
		$result['guest'] = $guest_array;

		return $result;
	}

	// show online member names
	static function getOnlineUserNames() {
		$db		= JFactory::getDbo();
		$result	= null;
		$query	= $db->getQuery(true);
		$query->select('a.username, a.time, a.userid, a.usertype, a.client_id');
		$query->from('#__session AS a');
		$query->where('a.userid != 0');
		$query->group('a.userid');
		$db->setQuery($query);
		$result = $db->loadObjectList();
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->stderr());
		}

		return $result;
	}
}
