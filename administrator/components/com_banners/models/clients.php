<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Banners Component Banner Clients Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Banners
 * @since 1.6
 */
class BannerModelClients extends JModel
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
	 * @since 1.6
	 */
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart	= $mainframe->getUserStateFromRequest($option.'.limitstart', 'limitstart', 0, 'int');

		// In case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$context			= 'com_banners.client.list.';
		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest($context.'filter_order',		'filter_order',		'a.name',		'cmd');
		$filter->order_Dir	= $mainframe->getUserStateFromRequest($context.'filter_order_Dir',	'filter_order_Dir',	'',				'word');
		$filter->state		= $mainframe->getUserStateFromRequest($context.'filter_state',		'filter_state',		'',				'word');
		$filter->search		= $mainframe->getUserStateFromRequest($context.'search',			'search',			'',				'string');
		$this->_filter = $filter;
	}

	/**
	 * Method to get weblinks item data
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
	 * Method to get the total number of weblink items
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
	 * Method to get a pagination object for the weblinks
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
	 * Method to get filter object for the weblinks
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
		jimport('joomla.database.query');
		$query = new JQuery;

		// Set up query
		$query->select('a.*, count(b.bid) AS nbanners, u.name AS editor');
		$query->from('#__bannerclient AS a');
		$query->join('LEFT', '#__banner AS b ON a.cid = b.cid');
		$query->join('LEFT', '#__users AS u ON u.id = a.checked_out');

		if ($search = JString::strtolower($this->_filter->search)) {
			$query->where('LOWER(a.name) LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%', false));
		}
		$query->group('a.cid');
		$query->order($this->_filter->order .' '. $this->_filter->order_Dir .', a.cid');

		return $query;
	}
}
