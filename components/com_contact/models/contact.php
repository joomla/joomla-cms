<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
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
	 * Gets a list of categories
	 * @param array
	 * @return mixed Object or null
	 */
	function getContact($options=array())
	{
		$query	= $this->_getContactQuery($options);
		$result = $this->_getList($query);
		return @$result[0];
	}
}