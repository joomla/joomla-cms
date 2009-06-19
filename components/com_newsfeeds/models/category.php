<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');

/**
 * Newsfeeds Component Category Model
 *
 * @package		Joomla.Site
 * @subpackage	com_newsfeeds
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
		$mainframe = JFactory::getApplication();

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
			$this->getCategory();
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
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
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
		if (empty($this->_category))
		{
			jimport('joomla.application.categories');
			$categoryTree = JCategories::getInstance('com_newsfeeds');
			$this->_category = $categoryTree->get($this->_id);
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
			$this->_total = $this->_category->numitems;
		}
		return $this->_category;
	}

	function _buildQuery()
	{
		// We need to get a list of all weblinks in the given category
		$query = 'SELECT *,' .
			' CASE WHEN CHAR_LENGTH(alias) THEN CONCAT_WS(":", id, alias) ELSE id END as slug'.
			' FROM #__newsfeeds' .
			' WHERE catid = '.(int) $this->_id.
			' AND published = 1' .
			' ORDER BY ordering';

		return $query;
	}
}