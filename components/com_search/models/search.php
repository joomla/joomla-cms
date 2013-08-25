<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Search Component Search Model
 *
 * @package     Joomla.Site
 * @subpackage  com_search
 * @since       1.5
 */
class SearchModelSearch extends JModelLegacy
{
	/**
	 * Search data array
	 *
	 * @var array
	 */
	protected $_data = null;

	/**
	 * Search total
	 *
	 * @var integer
	 */
	protected $_total = null;

	/**
	 * Search areas
	 *
	 * @var integer
	 */
	protected  $_areas = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	protected $_pagination = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	public function __construct()
	{
		parent::__construct();

		//Get configuration
		$app    = JFactory::getApplication();
		$config = JFactory::getConfig();

		// Get the pagination request variables
		$this->setState('limit', $app->getUserStateFromRequest('com_search.limit', 'limit', $config->get('list_limit'), 'uint'));
		$this->setState('limitstart', $app->input->get('limitstart', 0, 'uint'));

		// Set the search parameters
		$keyword  = urldecode($app->input->getString('searchword'));
		$match    = $app->input->get('searchphrase', 'all', 'word');
		$ordering = $app->input->get('ordering', 'newest', 'word');
		$this->setSearch($keyword, $match, $ordering);

		//Set the search areas
		$areas = $app->input->get('areas', null, 'array');
		$this->setAreas($areas);
	}

	/**
	 * Method to set the search parameters
	 *
	 * @access	public
	 * @param string search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 */
	public function setSearch($keyword, $match = 'all', $ordering = 'newest')
	{
		if (isset($keyword))
		{
			$this->setState('origkeyword', $keyword);
			if ($match !== 'exact')
			{
				$keyword = preg_replace('#\xE3\x80\x80#s', ' ', $keyword);
			}
			$this->setState('keyword', $keyword);
		}

		if (isset($match))
		{
			$this->setState('match', $match);
		}

		if (isset($ordering))
		{
			$this->setState('ordering', $ordering);
		}
	}

	/**
	 * Method to set the search areas
	 *
	 * @access	public
	 * @param   array  Active areas
	 * @param   array  Search areas
	 */
	public function setAreas($active = array(), $search = array())
	{
		$this->_areas['active'] = $active;
		$this->_areas['search'] = $search;
	}

	/**
	 * Method to get weblink item data for the category
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$areas = $this->getAreas();

			JPluginHelper::importPlugin('search');
			$dispatcher = JEventDispatcher::getInstance();
			$results = $dispatcher->trigger('onContentSearch', array(
				$this->getState('keyword'),
				$this->getState('match'),
				$this->getState('ordering'),
				$areas['active'])
			);

			$rows = array();
			foreach ($results as $result)
			{
				$rows = array_merge((array) $rows, (array) $result);
			}

			$this->_total	= count($rows);
			if ($this->getState('limit') > 0)
			{
				$this->_data	= array_splice($rows, $this->getState('limitstart'), $this->getState('limit'));
			} else {
				$this->_data = $rows;
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of weblink items for the category
	 *
	 * @access public
	 * @return  integer
	 */
	public function getTotal()
	{
		return $this->_total;
	}

	/**
	 * Method to get a pagination object of the weblink items for the category
	 *
	 * @access public
	 * @return  integer
	 */
	public function getPagination()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_pagination))
		{
			$this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit'));
		}

		return $this->_pagination;
	}

	/**
	 * Method to get the search areas
	 *
	 * @since 1.5
	 */
	public function getAreas()
	{
		// Load the Category data
		if (empty($this->_areas['search']))
		{
			$areas = array();

			JPluginHelper::importPlugin('search');
			$dispatcher = JEventDispatcher::getInstance();
			$searchareas = $dispatcher->trigger('onContentSearchAreas');

			foreach ($searchareas as $area)
			{
				if (is_array($area))
				{
					$areas = array_merge($areas, $area);
				}
			}

			$this->_areas['search'] = $areas;
		}

		return $this->_areas;
	}
}
