<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Content Component Category Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentModelCategory extends JModel
{
	/**
	 * Category id
	 *
	 * @var int
	 */
	protected $_id = null;

	/**
	 * Category items data
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Category number items
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Category data
	 *
	 * @var object
	 */
	protected $_category = null;

	/**
	 * Category data
	 *
	 * @var array
	 */
	protected $_siblings = null;

	protected $_content = null;

	protected $_category_tree = array();

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();
		$app = JFactory::getApplication();

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->setId((int)$id);

		// here we initialize defaults for category model
		$params = &$app->getParams();
		$params->def('filter',					1);
		$params->def('filter_type',				'title');
	}

	/**
	 * Method to set the category id
	 *
	 * @access	public
	 * @param	int	Category ID number
	 */
	public function setId($id)
	{
		// Set category ID and wipe data
		$this->_id			= $id;
		$this->_category	= null;
		$this->_siblings	= null;
		$this->_data		= array();
		$this->_total		= null;
	}

	/**
	 * Method to get content item data for the current category
	 *
	 * @param	int	$state	The content state to pull from for the current
	 * category
	 * @since 1.5
	 */
	public function getData($state = 1)
	{
		if (empty($this->_category))
		{
			jimport('joomla.application.categorytree');
			$categoryTree = JCategories::getInstance('com_content');
			$this->_category = &$categoryTree->get($this->_id);
		}
		// Load the Category data
		if ($this->_loadData($state))
		{
			// Initialize some variables
			$user	= &JFactory::getUser();

			// Make sure the category is published
			if (!$this->_category->published)
			{
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}

			// check whether category access level allows access
			if (!in_array($this->_category->access, $user->authorisedLevels()))
			{
				JError::raiseError(403, JText::_("ALERTNOTAUTH"));
				return false;
			}
		}
		return $this->_data[$state];
	}

	/**
	 * Method to get the total number of content items for the frontpage
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal($state = 1, $recursive = false)
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			if (empty($this->_category))
			{
				jimport('joomla.application.categorytree');
				$categoryTree = JCategories::getInstance('com_content');
				$this->_category = &$categoryTree->get($this->_id);
			}
			$query = $this->_buildQuery($state, true);
			$this->_db->setQuery($query);
			$this->_total[$state] = $this->_db->loadResult();
		}

		return $this->_total[$state];
	}

	/**
	 * Method to get category data for the current category
	 *
	 * @since 1.5
	 */
	public function getCategory()
	{
		// Load the Category data
		if (empty($this->_category))
		{
			jimport('joomla.application.categorytree');
			$categoryTree = JCategories::getInstance('com_content');
			$this->_category = &$categoryTree->get($this->_id);
		}

		if (empty($this->_category))
		{
			JError::raiseError(404, JText::_("Resource Not Found"));
			return false;
		}
		// Initialize some variables
		$user = &JFactory::getUser();

		// Make sure the category is published
		if (!$this->_category->published) {
			JError::raiseError(404, JText::_("Resource Not Found"));
			return false;
		}
		// check whether category access level allows access
		if (!in_array($this->_category->access, $user->authorisedLevels())) {
			JError::raiseError(403, JText::_("ALERTNOTAUTH"));
			return false;
		}
		return $this->_category;
	}

	/**
	 * Method to get sibling category data for the current category
	 *
	 * @since 1.5
	 */
	public function getSiblings()
	{
		// Initialize some variables
		$user	= &JFactory::getUser();

		// Load the Category data
		if ($this->_loadCategory() && $this->_loadSiblings())
		{
			// Make sure the category is published
			if (!$this->_category->published)
			{
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}

			// check whether category access level allows access
			if ($this->_category->access > $user->get('aid', 0))
			{
				JError::raiseError(403, JText::_("ALERTNOTAUTH"));
				return false;
			}
		}
		return $this->_siblings;
	}

	/**
	 * Method to get archived article data for the current category
	 *
	 * @param	int	$state	The content state to pull from for the current section
	 * @since 1.5
	 */
	public function getArchives($state = -1)
	{
		return $this->getContent(-1);
	}

	public function getChildren($recursive = 0)
	{
		return $this->_category->getChildren();
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
		if (empty($this->_category)) {
			return false; // TODO: set error -- can't get siblings when we don't know the category
		}

		// Lets load the siblings if they don't already exist
		if (empty($this->_content[$state]))
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

		if (!$countOnly) {
			$query = 'SELECT cc.title AS category, a.id, a.title, a.title_alias, a.introtext, a.fulltext, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,' .
				' a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.hits, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access,' .
				' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,';
			if ($params->get('show_subcategory_content', 0))
			{
				$query .= ' cc.catslug as catslug, ';
			} else {
				$query .= ' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug,';
			}
			$query .= ' CHAR_LENGTH(a.`fulltext`) AS readmore, u.name AS author, u.usertype'.$voting['select'];
		} else {
			$query = 'SELECT count(*) ';
		}
		if ($params->get('show_subcategory_content', 0))
		{
			$subquery = ' RIGHT JOIN (SELECT c.id as id, c.title as title, c.alias as alias,'.
			' CASE WHEN CHAR_LENGTH(c.alias) THEN CONCAT_WS(":", c.id, c.alias) ELSE c.id END as catslug'.
			' FROM #__categories AS c'.
			' JOIN #__categories AS cp ON cp.lft <= c.lft AND c.rgt <= cp.rgt'.
			' WHERE c.extension = \'com_content\''.
			' AND c.access IN ('.implode(',', JFactory::getUser()->authorisedLevels('com_content.category.view')).')'.
			' AND cp.id = '.$this->_id.') AS cc ON a.catid = cc.id';
		} else {
			$subquery = ' LEFT JOIN #__categories AS cc ON a.catid = cc.id';
		}
		$query .=
			' FROM #__content AS a' .
			 $subquery.
			' LEFT JOIN #__users AS u ON u.id = a.created_by' .
			$voting['join'].
			$where.
			$orderby;

		return $query;
	}

	protected function _buildContentOrderBy($state = 1)
	{
		$app = JFactory::getApplication();
		// Get the page/component configuration
		$params = &$app->getParams();

		$filter_order		= JRequest::getCmd('filter_order');
		$filter_order_Dir	= JRequest::getWord('filter_order_Dir');

		$orderby = ' ORDER BY ';
		if ($filter_order && $filter_order_Dir)
		{
			$orderby .= $filter_order .' '. $filter_order_Dir.', ';
		}

		if ($filter_order == 'author')
		{
			$orderby .= 'created_by_alias '. $filter_order_Dir.', ';
		}
		switch ($state)
		{
			case -1:
				// Special ordering for archive articles
				$orderby_sec	= $params->def('orderby', 'rdate');
				$secondary		= ContentHelperQuery::orderbySecondary($orderby_sec).', ';
				$primary		= '';
				break;

			case 1:
			default:
				$orderby_sec	= $params->def('orderby_sec', 'rdate');
				$orderby_sec	= ($orderby_sec == 'front') ? '' : $orderby_sec;
				$orderby_pri	= $params->def('orderby_pri', '');
				$secondary		= ContentHelperQuery::orderbySecondary($orderby_sec).', ';
				$primary		= ContentHelperQuery::orderbyPrimary($orderby_pri);
				break;
		}
		$orderby .= $primary .' '. $secondary .' a.created DESC';

		return $orderby;
	}

	protected function _buildContentWhere($state = 1)
	{
		$app = JFactory::getApplication();
		// Get the page/component configuration
		$params = &$app->getParams();

		$user		= &JFactory::getUser();
		$gid		= $user->get('aid', 0);

		$jnow		= &JFactory::getDate();
		$now		= $jnow->toMySQL();

		// Get the page/component configuration
		$noauth		= !$params->get('show_noauth');
		$nullDate	= $this->_db->getNullDate();
		if ($params->get('show_subcategory_content', 0))
		{
			$where = ' WHERE 1 ';
		} else {
			$where = ' WHERE a.catid = '.$this->_id;
		}
		// Does the user have access to view the items?
		if ($noauth) {
			$where .= ' AND a.access IN ('.implode(',', $user->authorisedLevels('com_content.article.view')).')';
		}

		// Regular Published Content
		switch ($state)
		{
			case 1:
				if ($user->authorize('com_content', 'edit', 'content', 'all'))
				{
					$where .= ' AND a.state >= 0';
				}
				else
				{
					$where .= ' AND a.state = 1' .
							' AND (publish_up = '.$this->_db->Quote($nullDate).' OR publish_up <= '.$this->_db->Quote($now).')' .
							' AND (publish_down = '.$this->_db->Quote($nullDate).' OR publish_down >= '.$this->_db->Quote($now).')';
				}
				break;

			// Archive Content
			case -1:
				// Get some request vars specific to this state
				$year	= JRequest::getInt('year', date('Y'));
				$month	= JRequest::getInt('month', date('m'));

				$where .= ' AND a.state = -1';
				$where .= ' AND YEAR(a.created) = '.(int) $year;
				$where .= ' AND MONTH(a.created) = '.(int) $month;
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
			if ($filter)
			{
				// clean filter variable
				$filter = JString::strtolower($filter);
				$filter	= $this->_db->Quote('%'.$this->_db->getEscaped($filter, true).'%', false);

				switch ($params->get('filter_type'))
				{
					case 'title' :
						$where .= ' AND LOWER(a.title) LIKE '.$filter;
						break;

					case 'author' :
						$where .= ' AND ((LOWER(u.name) LIKE '.$filter.') OR (LOWER(a.created_by_alias) LIKE '.$filter.'))';
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
