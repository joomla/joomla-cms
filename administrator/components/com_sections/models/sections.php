<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Sections
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
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
 * Sections Component Sections Model
 *
 * @package		Joomla
 * @subpackage	Sections
 * @since 1.5
 */
class SectionsModelSections extends JModel
{
	/**
	 * Category ata array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Category total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * Filter object
	 *
	 * @var object
	 */
	var $_filter = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'.limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		's.ordering',	'cmd' );
		$filter->order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$filter->state		= $mainframe->getUserStateFromRequest( $option.'filter_state',		'filter_state',		'',				'word' );
		$filter->search		= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		$this->_filter = $filter;
	}

	/**
	 * Method to get Sections item data
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			$this->getSectionTotals();
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of section items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get the section totals
	 *
	 * @access public
	 */
	function getSectionTotals()
	{
		$db =& JFactory::getDBO();

		$count = count( $this->_data );
		// number of Active Categories
		for ( $i = 0; $i < $count; $i++ ) {
			$query = 'SELECT COUNT( a.id )'
			. ' FROM #__categories AS a'
			. ' WHERE a.section = '.$db->Quote($this->_data[$i]->id)
			. ' AND a.published <> -2'
			;
			$db->setQuery( $query );
			$active = $db->loadResult();
			$this->_data[$i]->categories = $active;
		}
		// number of Active Items
		for ( $i = 0; $i < $count; $i++ ) {
			$query = 'SELECT COUNT( a.id )'
			. ' FROM #__content AS a'
			. ' WHERE a.sectionid = '.(int) $this->_data[$i]->id
			. ' AND a.state <> -2'
			;
			$db->setQuery( $query );
			$active = $db->loadResult();
			$this->_data[$i]->active = $active;
		}
		// number of Trashed Items
		for ( $i = 0; $i < $count; $i++ ) {
			$query = 'SELECT COUNT( a.id )'
			. ' FROM #__content AS a'
			. ' WHERE a.sectionid = '.(int) $this->_data[$i]->id
			. ' AND a.state = -2'
			;
			$db->setQuery( $query );
			$trash = $db->loadResult();
			$this->_data[$i]->trash = $trash;
		}
	}

	/**
	 * Method to get a pagination object for the Sections
	 *
	 * @access public
	 * @return integer
	 */
	function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');
			$this->_pagination = new JPagination( $this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
		}

		return $this->_pagination;
	}

	/**
	 * Method to get filter object for the sections
	 *
	 * @access public
	 * @return object
	 */
	function getFilter()
	{
		return $this->_filter;
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = ' SELECT s.*, u.name AS editor, g.name AS groupname '
			. ' FROM #__sections AS s '
			. ' LEFT JOIN #__users AS u ON u.id = s.checked_out '
			. ' LEFT JOIN #__core_acl_axo_groups AS g ON g.value = s.access'
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		if ($this->_filter->order == 's.ordering'){
			$orderby 	= ' ORDER BY s.ordering '.$this->_filter->order_Dir;
		} else {
			$orderby 	= ' ORDER BY '.$this->_filter->order.' '.$this->_filter->order_Dir.' , s.ordering ';
		}

		return $orderby;
	}

	function _buildContentWhere()
	{
		$search				= JString::strtolower( $this->_filter->search );

		$where = array();

		if ($search) {
			$where[] = 'LOWER(s.title) LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped( $search, true ).'%');
		}
		if ( $this->_filter->state ) {
			if ( $this->_filter->state == 'P' ) {
				$where[] = 's.published = 1';
			} else if ($this->_filter->state == 'U' ) {
				$where[] = 's.published = 0';
			}
		}

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		return $where;
	}
}