<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * @package		Joomla
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
	function _getContactQuery( &$options )
	{
		// TODO: Cache on the fingerprint of the arguments
		$db			=& JFactory::getDBO();
		$aid		= @$options['aid'];
		$id			= @$options['id'];
		$groupBy	= @$options['group by'];
		$orderBy	= @$options['order by'];

		$select = 'a.*, cc.title as category_name';
		$from	= '#__contact_details AS a';

		$joins[] = 'INNER JOIN #__categories AS cc on cc.id = a.catid';

		$wheres[] = 'a.id = ' . (int) $id;
		$wheres[] = 'a.published = 1';
		$wheres[] = 'cc.published = 1';

		if ($aid !== null)
		{
			$wheres[] = 'a.access <= ' . (int) $aid;
			$wheres[] = 'cc.access <= ' . (int) $aid;
		}

		/*
		 * Query to retrieve all categories that belong under the contacts
		 * section and that are published.
		 */
		$query = 'SELECT ' . $select .
				' FROM ' . $from .
				' '. implode ( ' ', $joins ) .
				' WHERE ' . implode( ' AND ', $wheres );

		return $query;
	}

	/**
	 * Gets a list of categories
	 * @param array
	 * @return mixed Object or null
	 */
	function getContact( $options=array() )
	{
		$query	= $this->_getContactQuery( $options );
		$result = $this->_getList( $query );
		return @$result[0];
	}
}
?>
