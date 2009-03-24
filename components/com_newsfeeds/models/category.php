<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

jimport('joomla.application.component.model');

/**
 * Newsfeeds Component Category Model
 *
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @since 1.5
 */
class NewsfeedsModelCategory extends JModel
{
	/**
	 * Category id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Category data array
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
	 * Category data
	 *
	 * @var object
	 */
	var $_category = null;
	
	var $_categories = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		global $mainframe;

		parent::__construct();

		$config = JFactory::getConfig();

		// Get the pagination request variables
		$this->setState('limit', $mainframe->getUserStateFromRequest('com_newsfeeds.limit', 'limit', $config->getValue('config.list_limit'), 'int'));
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		$id = JRequest::getVar('id', 0, '', 'int');
		$this->setId((int)$id);

	}

	/**
	 * Method to set the category id
	 *
	 * @access	public
	 * @param	int	Category ID number
	 */
	function setId($id)
	{
		// Set category ID and wipe data
		$this->_id			= $id;
		$this->_category	= null;
	}

	/**
	 * Method to get newsfeed item data for the category
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

			$total = count($this->_data);
			for($i = 0; $i < $total; $i++)
			{
				$item =& $this->_data[$i];
				$item->slug = $item->id.'-'.$item->alias;
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of newsfeed items for the category
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
	 * Method to get a pagination object of the newsfeeds items for the category
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
	 * Method to get category data for the current category
	 *
	 * @since 1.5
	 */
	function getCategory()
	{
		// Load the Category data
		if ($this->_loadCategories())
		{
			// Initialize some variables
			$user = &JFactory::getUser();

			// Make sure the category is published
			if (!$this->_category->published) {
				JError::raiseError(404, JText::_("Resource Not Found"));
				return false;
			}
			// check whether category access level allows access
			if ($this->_category->access > $user->get('aid', 0)) {
				JError::raiseError(403, JText::_("ALERTNOTAUTH"));
				return false;
			}
		}
		return $this->_category;
	}
	
	function getCategories()
	{
		// Load the Category data
		if (!$this->_loadCategories())
		{
			return false;
		}
		$rgt = 0;
		$return = array();
		foreach($this->_categories as $category)
		{
			if($category->lft > $rgt && $category->id != $this->_id)
			{
				$return[] = $category;
				$rgt = $category->rgt;
			}
		}
		return $return;
	}

	/**
	 * Method to load category data if it doesn't exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadCategories()
	{
		if(empty($this->_categories))
		{
			$query = 'SELECT a.*, count(b.id) AS numlinks,'
				.' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug'
				.' FROM #__categories AS a'
				.' JOIN #__categories AS b ON a.lft >= b.lft AND a.rgt <= b.rgt'
				.' LEFT JOIN #__newsfeeds AS c ON a.id = c.catid'
				.' WHERE b.id = '.JRequest::getInt('id')
				.' AND a.extension = \'com_newsfeeds\''
				.' AND a.published = 1'
				.' AND (c.published = 1 OR c.published IS NULL)'
				.' AND a.access <= 0'
				.' GROUP BY a.id'
				.' ORDER BY a.lft';
			$this->_db->setQuery($query);
			$this->_categories = $this->_db->loadObjectList();
			foreach($this->_categories as $category)
			{
				if($category->id == $this->_id)
				{
					$this->_category = $category;
					break;
				}
			}
			return true;
		}
		return true;
	}

	function _buildQuery()
	{
		// We need to get a list of all weblinks in the given category
		$query = 'SELECT *' .
			' FROM #__newsfeeds' .
			' WHERE catid = '.(int) $this->_id.
			' AND published = 1' .
			' ORDER BY ordering';

		return $query;
	}
}
?>