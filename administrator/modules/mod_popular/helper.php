<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	mod_popular
 */
class modPopularHelper
{
	/**
	 * Get a list of the logged in users
	 *
	 * @param	JObject		The module parameters.
	 *
	 * @return	array
	 */
	function getList($params)
	{
		jimport('joomla.database.query');

		$db		= JFactory::getDbo();
		$limit	= $params->get('limit', 10);
		$result	= null;
		$query	= new JQuery;
		$query->select('a.hits, a.id, a.sectionid, a.title, a.created, u.name');
		$query->from('#__content AS a');
		$query->join('LEFT', '#__users AS u ON u.id=a.created_by');
		$query->where('a.state <> -2');
		$query->order('hits');

		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
			return false;
		}

		return $rows;
	}
}
