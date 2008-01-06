<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Contact
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
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
class ContactModelCategory extends JModel
{
	/**
	 * Builds the query to select contact categories
	 * @param array
	 * @return string
	 * @access protected
	 */
	function _getCatgoriesQuery( &$options )
	{
		// TODO: Cache on the fingerprint of the arguments
		$db		=& JFactory::getDBO();
		$aid	= @$options['aid'];

		$wheres[] = 'a.published = 1';
		$wheres[] = 'cc.section = ' . $db->Quote( 'com_contact_details' );
		$wheres[] = 'cc.published = 1';

		if ($aid !== null)
		{
			$wheres[] = 'a.access <= ' . (int) $aid;
			$wheres[] = 'cc.access <= ' . (int) $aid;
		}

		$groupBy	= 'cc.id';
		$orderBy	= 'cc.ordering' ;

		/*
		 * Query to retrieve all categories that belong under the contacts
		 * section and that are published.
		 */
		$query = 'SELECT cc.*, COUNT( a.id ) AS numlinks, a.id as cid'.
				' FROM #__categories AS cc'.
				' LEFT JOIN #__contact_details AS a ON a.catid = cc.id'.
				' WHERE ' . implode( ' AND ', $wheres ) .
				' GROUP BY ' . $groupBy .
				' ORDER BY ' . $orderBy;

		//echo $query;
		return $query;
	}

	/**
	 * Builds the query to select contact items
	 * @param array
	 * @return string
	 * @access protected
	 */
	function _getContactsQuery( &$options )
	{
		// TODO: Cache on the fingerprint of the arguments
		$db			=& JFactory::getDBO();
		$aid		= @$options['aid'];
		$catId		= @$options['category_id'];
		$groupBy	= @$options['group by'];
		$orderBy	= @$options['order by'];

		$select = 'cd.*, ' .
				'cc.name AS category_name, cc.description AS category_description, cc.image AS category_image,'.
				' CASE WHEN CHAR_LENGTH(cd.alias) THEN CONCAT_WS(\':\', cd.id, cd.alias) ELSE cd.id END as slug, '.
				' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(\':\', cc.id, cc.alias) ELSE cc.id END as catslug ';
		$from	= '#__contact_details AS cd';

		$joins[] = 'INNER JOIN #__categories AS cc on cd.catid = cc.id';

		if ($catId)
		{
			$wheres[] = 'cd.catid = ' . (int) $catId;
		}
		$wheres[] = 'cc.published = 1';
		$wheres[] = 'cd.published = 1';

		if ($aid !== null)
		{
			$wheres[] = 'cc.access <= ' . (int) $aid;
			$wheres[] = 'cd.access <= ' . (int) $aid;
		}

		/*
		 * Query to retrieve all categories that belong under the contacts
		 * section and that are published.
		 */
		$query = 'SELECT ' . $select .
				' FROM ' . $from .
				' ' . implode ( ' ', $joins ) .
				' WHERE ' . implode( ' AND ', $wheres ) .
				($groupBy ? ' GROUP BY ' . $groupBy : '').
				($orderBy ? ' ORDER BY ' . $orderBy : '');

		return $query;
	}

	/**
	 * Gets a list of categories
	 * @param array
	 * @return array
	 */
	function getCategories( $options=array() )
	{
		$query	= $this->_getCatgoriesQuery( $options );
		return $this->_getList( $query, @$options['limitstart'], @$options['limit'] );
	}

	/**
	 * Gets the count of the categories for the given options
	 * @param array
	 * @return int
	 */
	function getCategoryCount( $options=array() )
	{
		$query	= $this->_getCatgoriesQuery( $options );
		return $this->_getListCount( $query );
	}

	/**
	 * Gets a list of categories
	 * @param array
	 * @return array
	 */
	function getContacts( $options=array() )
	{
		$query	= $this->_getContactsQuery( $options );
		return $this->_getList( $query, @$options['limitstart'], @$options['limit'] );
	}

	/**
	 * Gets the count of the categories for the given options
	 * @param array
	 * @return int
	 */
	function getContactCount( $options=array() )
	{
		$query	= $this->_getContactsQuery( $options );
		return $this->_getListCount( $query );
	}
}