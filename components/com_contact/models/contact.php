<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * @package		Joomla.Site
 * @subpackage	Contact
 */
class ContactModelContact extends JModel
{
	/**
	 * Builds the query to select contact items
	 * @param array
	 * @return string
	 * @access protected
	 */
	function _getContactQuery(&$options)
	{
		// TODO: Cache on the fingerprint of the arguments
		$db			= &JFactory::getDbo();
		$user		= &JFactory::getUser();
		$id			= @$options['id'];
		$groups		= implode(',', $user->authorisedLevels());
		$groupBy	= @$options['group by'];
		$orderBy	= @$options['order by'];

		$select = 'a.*, cc.access as category_access, cc.title as category_name, '
		. ' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug, '
		. ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END AS catslug ';
		$from	= '#__contact_details AS a';

		$joins[] = 'INNER JOIN #__categories AS cc on cc.id = a.catid';

		$wheres[] = 'a.id = ' . (int) $id;
		$wheres[] = 'a.published = 1';
		$wheres[] = 'cc.published = 1';
		$wheres[] = 'a.access IN ('.implode(',', $user->authorisedLevels()).')';

		/*
		 * Query to retrieve all categories that belong under the contacts
		 * section and that are published.
		 */
		$query = 'SELECT ' . $select .
				' FROM ' . $from .
				' '. implode (' ', $joins) .
				' WHERE ' . implode(' AND ', $wheres);
		return $query;
	}

	/**
	 * Gets a list of contacts
	 * @param array
	 * @return mixed Object or null
	 */
	function getContact($options=array())
	{
		$query	= $this->_getContactQuery($options);
		$result = $this->_getList($query);
		if ($contact = @$result[0])
		{
			$user		= &JFactory::getUser();
			$groups	= implode(',', $user->authorisedLevels());		
			//get the content by the linked user
			$query = 'SELECT id, title, state, access, created' .
				' FROM #__content' .
				' WHERE created_by = '.(int)$contact->user_id . 
				' AND access IN ('. $groups . ')' .
				' ORDER BY state DESC, created DESC' ;
			$this->_db->setQuery($query, 0, 10);
			$articles = $this->_db->loadObjectList();
			$contact->articles=$articles;
		}
		return $contact;
	}
	
}
