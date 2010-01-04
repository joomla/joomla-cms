<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	mod_online
 */
class modOnlineHelper
{
	/**
	 * Count the number of users online.
	 *
	 * @return	mixed	The number of users online, or false on error.
	 */
	function getOnlineCount()
	{
		jimport('joomla.database.query');
		$db			= JFactory::getDbo();
		$session    = JFactory::getSession();
		$sessionId	= $session->getId();

		$query = new JQuery;
		$query->select('COUNT(a.session_id)');
		$query->from('#__session AS a');
		$query->where('a.session_id <> '.$db->Quote($sessionId));
		$db->setQuery($query);
		$result = (int) $db->loadResult();
		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		return $result;
	}
}
