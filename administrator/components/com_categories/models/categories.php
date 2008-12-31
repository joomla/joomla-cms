<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Categories
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
 * Categories Component Categories Model
 *
 * @package		Joomla
 * @subpackage	Categories
 * @since 1.5
 */
class CategoriesModelCategories extends JModel
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

	protected $section_name = null;
	protected $content_add;
	protected $content_join;
	protected $table;
	protected $type;

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
		$filter->order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'c.ordering',	'cmd' );
		$filter->order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$filter->state		= $mainframe->getUserStateFromRequest( $option.'filter_state',		'filter_state',		'',				'word' );
		$filter->search		= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		$filter->section 	= JRequest::getCmd( 'section', 'com_content' );
		$filter->sectionid	= $mainframe->getUserStateFromRequest( $option.'.'.$filter->section.'.sectionid',		'sectionid',		0,				'int' );
		$this->_filter = $filter;
	}

	/**
	 * Method to get Categories item data
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
			$this->getCategoryTotals();
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
	function getCategoryTotals()
	{
		$db =& JFactory::getDBO();

		$count = count( $this->_data );
		// number of Active Items
		for ( $i = 0; $i < $count; $i++ ) {
			$query = 'SELECT COUNT( a.id )'
			. ' FROM #__content AS a'
			. ' WHERE a.catid = '.(int) $this->_data[$i]->id
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
			. ' WHERE a.catid = '.(int) $this->_data[$i]->id
			. ' AND a.state = -2'
			;
			$db->setQuery( $query );
			$trash = $db->loadResult();
			$this->_data[$i]->trash = $trash;
		}
	}

	/**
	 * Method to get a pagination object for the Categories
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
	 * Method to get filter object for the categories
	 *
	 * @access public
	 * @return object
	 */
	function getFilter()
	{
		return $this->_filter;
	}

	/**
	 * Method to get the Categories type
	 *
	 * @access public
	 * @return string
	 */
	function getType()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->type))
		{
			$query = $this->_buildQuery();
		}

		return $this->type;
	}

	/**
	 * Method to get the Categories Section Name
	 *
	 * @access public
	 * @return string
	 */
	function getSectionName()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->section_name))
		{
			$query = $this->_buildQuery();
		}

		return $this->section_name;
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere($this->_filter->section);
		$orderby	= $this->_buildContentOrderBy($this->_filter->section);

		$query = 'SELECT  c.*, c.checked_out as checked_out_contact_category, g.name AS groupname, u.name AS editor, COUNT( DISTINCT s2.checked_out ) AS checked_out_count'
		. $this->content_add
		. ' FROM #__categories AS c'
		. ' LEFT JOIN #__users AS u ON u.id = c.checked_out'
		. ' LEFT JOIN #__core_acl_axo_groups AS g ON g.value = c.access'
		. ' LEFT JOIN #__'.$this->table.' AS s2 ON s2.catid = c.id AND s2.checked_out > 0'
		. $this->content_join
		. $where
		. ' AND c.published != -2'
		. ' GROUP BY c.id'
		. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy($section)
	{
		if ( $section == 'com_content' ) {
			if ($this->_filter->order == 'c.ordering'){
				$orderby 			= ' ORDER BY  z.title, c.ordering';
			} else {
				$orderby 			= ' ORDER BY  '.$this->_filter->order.' '. $this->_filter->order_Dir.', z.title, c.ordering';
			}
		}
		else
		{
			if ($this->_filter->order == 'c.ordering'){
				$orderby 	= ' ORDER BY c.ordering '.$this->_filter->order_Dir;
			} else {
				$orderby 	= ' ORDER BY '.$this->_filter->order.' '.$this->_filter->order_Dir.' , c.ordering ';
			}
		}

		return $orderby;
	}

	function _buildContentWhere($section)
	{
		global $mainframe, $option;

		$db					=& JFactory::getDBO();
		$search				= JString::strtolower( $this->_filter->search );

		$where = array();

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		$this->section_name 	= '';
		$this->content_add 	= '';
		$this->content_join 	= '';
		if (intval( $section ) > 0) {
			$this->table = 'content';

			$query = 'SELECT title'
			. ' FROM #__sections'
			. ' WHERE id = '.(int) $section;
			$db->setQuery( $query );
			$this->section_name = $db->loadResult();
			$this->section_name = JText::sprintf( 'Content:', JText::_( $this->section_name ) );
			$where 	= ' WHERE c.section = '.$db->Quote($section);
			$this->type 	= 'content';
		} else if (strpos( $section, 'com_' ) === 0) {
			$this->table = substr( $section, 4 );

			$query = 'SELECT name'
			. ' FROM #__components'
			. ' WHERE link = '.$db->Quote('option='.$section);
			;
			$db->setQuery( $query );
			$this->section_name = $db->loadResult();

			$where 	= ' WHERE c.section = '.$db->Quote($section);
			$this->type 	= 'other';
			// special handling for contact component
			if ( $section == 'com_contact_details' ) {
				$this->section_name 	= JText::_( 'Contact' );
			}
			$this->section_name = JText::sprintf( 'Component:', $this->section_name );

		} else {
			$this->table 	= $section;
			$where 	= ' WHERE c.section = '.$db->Quote($section);
			$this->type 	= 'other';
		}

		// allows for viweing of all content categories
		if ( $section == 'com_content' ) {
			$this->table 			= 'content';
			$this->content_add 	= ' , z.title AS section_name';
			$this->content_join 	= ' LEFT JOIN #__sections AS z ON z.id = c.section';
			$where 			= ' WHERE c.section NOT LIKE "%com_%"';

			$this->section_name 	= JText::_( 'All Content:' );

			$this->type 			= 'content';
		}

		// used by filter
		if ( $this->_filter->sectionid > 0 ) {
			$filter = ' AND c.section = '.$db->Quote($this->_filter->sectionid);
		} else {
			$filter = '';
		}

		if ( $this->_filter->state ) {
			if ( $this->_filter->state == 'P' ) {
				$filter .= ' AND c.published = 1';
			} else if ($this->_filter->state == 'U' ) {
				$filter .= ' AND c.published = 0';
			}
		}
		if ($search) {
			$filter .= ' AND LOWER(c.title) LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped( $search, true ).'%');
		}

		$tablesAllowed = $db->getTableList();
		if (!in_array($db->getPrefix().$this->table, $tablesAllowed)) {
			$this->table = 'content';
		}

		return $where . $filter;
	}
}
