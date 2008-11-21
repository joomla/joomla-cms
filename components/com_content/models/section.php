<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Content Component Section Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentModelSection extends JModel
{
	/**
	 * Category id
	 *
	 * @var int
	 */
	protected $_id = null;

	/**
	 * Frontpage data array
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Frontpage total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Section data
	 *
	 * @var object
	 */
	protected $_section = null;

	/**
	 * Categories data
	 *
	 * @var array
	 */
	protected $_categories = null;


	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct( )
	{
		parent::__construct();

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->setId((int)$id);
	}

	/**
	 * Method to set the section id
	 *
	 * @access	public
	 * @param	int	Section ID number
	 */
	public function setId($id)
	{
		// Set new ID and wipe data
		$this->_id			= $id;
		$this->_data		= array();
		$this->_total 		= null;
		$this->_section		= null;
		$this->_categories	= null;

	}

	/**
	 * Method to get content item data for the section
	 *
	 * @param	int	$state	The content state to pull from for the current
	 * section
	 * @since 1.5
	 */
	public function getData($state = 1)
	{
		// Load the Category data
		if ($this->_loadSection() && $this->_loadData($state))
		{
			// Initialize some variables
			$user	=& JFactory::getUser();

			// Make sure the category is published
			if (!$this->_section->published) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}

			// check whether category access level allows access
			if ($this->_section->access > $user->get('aid', 0)) {
				JError::raiseError(403, JText::_("ALERTNOTAUTH"));
				return false;
			}
		}
		return $this->_data[$state];
	}

	/**
	 * Method to get the total number of content items for the section
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal($state = 1)
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery($state, true);
			$this->_db->setQuery($query);
			$this->_total[$state] = $this->_db->loadResult();
		}

		return $this->_total[$state];
	}

	/**
	 * Method to get section data for the current section
	 *
	 * @since 1.5
	 */
	public function getSection()
	{
		// Initialize some variables
		$user	=& JFactory::getUser();

		// Load the Category data
		if ($this->_loadSection())
		{
			// Make sure the category is published
			if (!$this->_section->published) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}

			// check whether category access level allows access
			if ($this->_section->access > $user->get('aid', 0)) {
				JError::raiseError(403, JText::_("ALERTNOTAUTH"));
				return false;
			}
		}
		return $this->_section;
	}

	/**
	 * Method to get sibling category data for the current category
	 *
	 * @since 1.5
	 */
	public function getCategories()
	{
		// Initialize some variables
		$user	=& JFactory::getUser();

		// Load the Category data
		if ($this->_loadSection() && $this->_loadCategories())
		{
			// Make sure the category is published
			if (!$this->_section->published) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}

			// check whether category access level allows access
			if ($this->_section->access > $user->get('aid', 0)) {
				JError::raiseError(403, JText::_("ALERTNOTAUTH"));
				return false;
			}
		}
		return $this->_categories;
	}

	/**
	 * Method to get archived article data for the current section
	 *
	 * @param	int	$state	The content state to pull from for the current section
	 * @since 1.5
	 */
	public function getArchives($state = -1)
	{
		return $this->getData(-1);
	}

	/**
	 * Method to get archived article data for the current section
	 *
	 * @param	int	$state	The content state to pull from for the current section
	 * @since 1.5
	 */
	public function getTree()
	{
		return $this->_loadTree();
	}

	/**
	 * Method to load section data if it doesn't exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	protected function _loadSection()
	{
		if (empty($this->_section))
		{
			// Lets get the information for the current section
			if ($this->_id) {
				$where = ' WHERE id = '. (int) $this->_id;
			} else {
				$where = null;
			}

			$query = 'SELECT *' .
					' FROM #__sections' .
					$where;
			$this->_db->setQuery($query, 0, 1);
			$this->_section = $this->_db->loadObject();
		}
		return true;
	}

	/**
	 * Method to load sibling category data if it doesn't exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	protected function _loadCategories()
	{
		// Lets load the siblings if they don't already exist
		if (empty($this->_categories))
		{
			$user	=& JFactory::getUser();

			$app = JFactory::getApplication();
			// Get the page/component configuration
			$params = &$app->getParams();

			$noauth	= !$params->get('show_noauth');
			$gid		= $user->get('aid', 0);
			$now		= $mainframe->get('requestTime');
			$nullDate	= $this->_db->getNullDate();

			// Ordering control
			$orderby = $params->get('orderby', '');
			$orderby = ContentHelperQuery::orderbySecondary($orderby);

			// Handle the access permissions part of the main database query
			if ($user->authorize('com_content', 'edit', 'content', 'all')) {
				$xwhere = '';
				$xwhere2 = ' AND b.state >= 0';
			} else {
				$xwhere = ' AND a.published = 1';
				$xwhere2 = ' AND b.state = 1' .
						' AND ( b.publish_up = '.$this->_db->Quote($nullDate).' OR b.publish_up <= '.$this->_db->Quote($now).' )' .
						' AND ( b.publish_down = '.$this->_db->Quote($nullDate).' OR b.publish_down >= '.$this->_db->Quote($now).' )';
				if ($noauth) {
					$xwhere2 .= ' AND b.access <= '.(int) $gid;
				}
 			}

			// Determine whether to show/hide the empty categories and sections
			$empty = null;
			$empty_sec = null;

			// show/hide empty categories in section
			if (!$params->get('show_empty_categories')) {
				$empty_sec = ' HAVING numitems > 0';
			}

			// Handle the access permissions
			$access_check = null;
			if ($noauth) {
				$access_check = ' AND a.access <= '.(int) $gid;
				//$access_check .= ' AND b.access <= '.(int) $gid;
			}

			// Query of categories within section
			$query = 'SELECT a.*, COUNT( b.id ) AS numitems,' .
					' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'.
					' FROM #__categories AS a' .
					' LEFT JOIN #__content AS b ON b.catid = a.id'.
					$xwhere2 .
					' WHERE a.section = '.(int) $this->_id.
					$xwhere.
					$access_check .
					' GROUP BY a.id'.$empty.$empty_sec .
					' ORDER BY '. $orderby;
			$this->_db->setQuery($query);
			$this->_categories = $this->_db->loadObjectList();
		}
		return true;
	}

	/**
	 * Method to load content item data for items in the category if they don't
	 * exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	protected function _loadData($state = 1)
	{
		if (empty($this->_section)) {
			return false; // TODO: set error -- can't get siblings when we don't know the category
		}

		// Lets load the content if it doesn't already exist
		if (empty($this->_data[$state]))
		{
			// Get the pagination request variables

			$limit		= JRequest::getVar('limit', 0, '', 'int');
			$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

			$query = $this->_buildQuery();
			$Arows = $this->_getList($query, $limitstart, $limit);

			// special handling required as Uncategorized content does not have a section / category id linkage
			$i = $limitstart;
			$rows = array();
			foreach ($Arows as $row)
			{
				// check to determine if section or category has proper access rights
				$rows[$i] = $row;
				$i ++;
			}
			$this->_data[$state] = $rows;
		}
		return true;
	}

	/**
	 * Method to load content item data for items in the category if they don't
	 * exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	protected function _loadTree()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_tree))
		{
			$user		=& JFactory::getUser();
			$aid		= $user->get('aid', 0);
			$nowdate 	= JFactory::getDate();
			$now = $nowdate->toMySQL();
			$nullDate	= $this->_db->getNullDate();

			// Get the information for the current section
			if ($this->_id) {
				$and = ' AND a.section = '.(int) $this->_id;
			} else {
				$and = null;
			}

			// Query of categories within section
			$query = 'SELECT a.name AS catname, a.title AS cattitle, b.* ' .
				' FROM #__categories AS a' .
				' INNER JOIN #__content AS b ON b.catid = a.id' .
				' AND b.state = 1' .
				' AND ( b.publish_up = '.$this->_db->Quote($nullDate).' OR b.publish_up <= '.$this->_db->Quote($now).' )' .
				' AND ( b.publish_down = '.$this->_db->Quote($nullDate).' OR b.publish_down >= '.$this->_db->Quote($now).' )';
				' WHERE a.published = 1' .
				$and .
				' AND a.access <= '.(int) $aid .
				' ORDER BY a.catid, a.ordering, b.ordering';
			$this->_db->setQuery($query);
			$this->_tree = $this->_db->loadObjectList();
		}
		return true;
	}

	protected function _buildQuery($state = 1, $countOnly = false)
	{
		$app = JFactory::getApplication();
		// Get the page/component configuration
		$params = &$app->getParams();

		// If voting is turned on, get voting data as well for the content items
		$voting	= ContentHelperQuery::buildVotingQuery($params);

		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere($state);
		$orderby	= $this->_buildContentOrderBy($state);

		if(!$countOnly) {
			$query = 'SELECT a.id, a.title, a.title_alias, a.introtext, a.fulltext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,' .
					' a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.hits, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,' .
					' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'.
					' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug,'.
					' CHAR_LENGTH( a.`fulltext` ) AS readmore, u.name AS author, u.usertype, cc.title AS category, g.name AS groups'.$voting['select'];
		} else {
			$query = 'SELECT count(*) ';
		} 
		$query .=
				' FROM #__content AS a' .
				' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
				' LEFT JOIN #__sections AS s ON s.id = a.sectionid' .
				' LEFT JOIN #__users AS u ON u.id = a.created_by' .
				' LEFT JOIN #__core_acl_axo_groups AS g ON a.access = g.value'.
				$voting['join'].
				$where.
				$orderby;

		return $query;
	}

	protected function _buildContentOrderBy($state = 1)
	{
		$filter_order		= JRequest::getCmd('filter_order');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir');

		$orderby = ' ORDER BY ';
		if ($filter_order && $filter_order_Dir) {
			$orderby .= $filter_order .' '. $filter_order_Dir.', ';
		}

		// Get the page/component configuration
		$app =& JFactory::getApplication();
		$params =& $app->getParams();

		switch ($state)
		{
			case -1:
				// Special ordering for archive articles
				$orderby_sec	= $params->def('orderby', 'rdate');
				$secondary		= ContentHelperQuery::orderbySecondary($orderby_sec);
				$primary		= '';
				break;

			case 1:
			default:
				$orderby_sec	= $params->def('orderby_sec', 'rdate');
				$orderby_sec	= ($orderby_sec == 'front') ? '' : $orderby_sec;
				$orderby_pri	= $params->def('orderby_pri', '');
				$secondary		= ContentHelperQuery::orderbySecondary($orderby_sec);
				$primary		= ContentHelperQuery::orderbyPrimary($orderby_pri);
				break;
		}
		$orderby .= "$primary $secondary";

		return $orderby;
	}

	protected function _buildContentWhere($state = 1)
	{
		$user		=& JFactory::getUser();
		$aid		= $user->get('aid', 0);

		$jnow		=& JFactory::getDate();
		$now		= $jnow->toMySQL();

		$app = JFactory::getApplication();
		// Get the page/component configuration
		$params = &$app->getParams();

		$noauth		= !$params->get('show_noauth');
		$nullDate	= $this->_db->getNullDate();

		// First thing we need to do is assert that the articles are in the current category
		if ($noauth) {
			$where = ' WHERE a.access <= '.(int) $aid;
		} else {
			$where = ' WHERE 1';
		}
		if ($this->_id) {
			$where .= ' AND s.id = '.(int)$this->_id;
		}

		$where .= ' AND s.access <= '.(int) $aid;
		$where .= ' AND cc.access <= '.(int) $aid;
		$where .= ' AND s.published = 1';
		$where .= ' AND cc.published = 1';

		// Regular Published Content
		switch ($state)
		{
			case 1:
				if ($user->authorize('com_content', 'edit', 'content', 'all')) {
					$where .= ' AND a.state >= 0';
				} else {
					$where .= ' AND a.state = 1' .
							' AND ( publish_up = '.$this->_db->Quote($nullDate).' OR publish_up <= '.$this->_db->Quote($now).' )' .
							' AND ( publish_down = '.$this->_db->Quote($nullDate).' OR publish_down >= '.$this->_db->Quote($now).' )';
				}
				break;

			// Archive Content
			case -1:
				// Get some request vars specific to this state
				$year	= JRequest::getInt( 'year', date('Y') );
				$month	= JRequest::getInt( 'month', date('m') );

				$where .= ' AND a.state = -1';
				$where .= ' AND YEAR( a.created ) = '.(int) $year;
				$where .= ' AND MONTH( a.created ) = '.(int) $month;
				break;

			default:
				$where .= ' AND a.state = '.(int) $state;
				break;
		}

		/*
		 * If we have a filter, and this is enabled... lets tack the AND clause
		 * for the filter onto the WHERE clause of the content item query.
		 */
		if ($params->get('filter'))
		{
			$filter = JRequest::getString('filter', '', 'request');
			if ($filter) {
				// clean filter variable
				$filter = JString::strtolower($filter);
				$filter	= $this->_db->Quote( '%'.$this->_db->getEscaped( $filter, true ).'%', false );

				switch ($params->get('filter_type'))
				{
					case 'title' :
						$where .= ' AND LOWER( a.title ) LIKE '.$filter;
						break;

					case 'author' :
						$where .= ' AND ( ( LOWER( u.name ) LIKE '.$filter.' ) OR ( LOWER( a.created_by_alias ) LIKE '.$filter.' ) )';
						break;

					case 'hits' :
						$where .= ' AND a.hits LIKE '.$filter;
						break;
				}
			}
		}
		return $where;
	}
}

