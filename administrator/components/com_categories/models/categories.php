<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Categories
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Categories Component Categories Model
 *
 * @package		Joomla.Administrator
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

	protected $extension;
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
		$filter->extension 	= JRequest::getCmd( 'extension', 'com_content' );
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
			$extension = $this->getExtension();
			$query = $this->_buildQuery();
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			$tempcat = array();
			foreach($this->_data as $category)
			{
				$tempcat[$category->id] = $category;
				$tempcat[$category->id]->depth = 0;
				if($category->parent_id != 0)
				{
					$tempcat[$category->id]->depth = $tempcat[$category->parent_id]->depth + 1;
				}
			}
			foreach($this->_data as &$category)
			{
				$category->depth = $tempcat[$category->id]->depth;
			}
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
	 * Method to get the Categories Section Name
	 *
	 * @access public
	 * @return string
	 */
	function getExtension()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->extension))
		{
			$this->extension = new stdClass();
			$this->extension->option = JRequest::getCmd('extension', 'com_content');
			$db = JFactory::getDBO();
			$db->setQuery('SELECT name FROM #__components WHERE parent = \'0\' AND `option` = '.$db->Quote($this->extension->option));
			$this->extension->name = $db->loadResult();
		}

		return $this->extension;
	}

	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere($this->_filter->extension);
		$orderby	= $this->_buildContentOrderBy($this->_filter->extension);

		$query = 'SELECT  c.*, c.checked_out as checked_out_contact_category, u.name AS editor, COUNT( DISTINCT s2.checked_out ) AS checked_out_count'
		. $this->content_add
		. ' FROM #__categories AS c'
		. ' LEFT JOIN #__users AS u ON u.id = c.checked_out'
		. ' LEFT JOIN #__'.$this->table.' AS s2 ON s2.catid = c.id AND s2.checked_out > 0'
		//. ', #__categories AS cp'
		. $this->content_join
		. $where
		. ' AND c.published != -2'
		. ' GROUP BY c.id'
		. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy($extension)
	{
		return ' ORDER BY c.lft';
	}

	function _buildContentWhere($extension)
	{
		global $mainframe, $option;

		$db					=& JFactory::getDBO();
		$search				= JString::strtolower( $this->_filter->search );
		$filter = '';
		$where = array();
		$parent_category = JRequest::getInt('parent', 0);

		$where 		= ( count( $where ) ? ' WHERE '. implode( ' AND ', $where ) : '' );

		$this->content_add 	= '';
		$this->content_join 	= '';
		$this->table = substr( $extension, 4 );

		//$where 			= ' WHERE c.lft BETWEEN cp.lft AND cp.rgt'
		$where				= ' WHERE '
						.' c.extension = '.$db->Quote($this->extension->option);
						//.' AND cp.extension = '.$db->Quote($this->extension->option);
		
		if ( $parent_category == 0 )
		{
		//	$where .= ' AND cp.lft = 1';
		} else {
		//	$where .= ' AND cp.id = '.$parent_category;
		}
		// allows for viweing of all content categories
		if ( $this->extension->option == 'com_content' ) {
			$this->table 			= 'content';
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
