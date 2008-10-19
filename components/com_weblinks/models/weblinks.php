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
 * Weblinks Component Weblinks Model
 *
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class WeblinksModelWeblinks extends JModel
{
	/**
	 * Weblinks data array
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

		$config = JFactory::getConfig();

		// Get the pagination request variables
		$this->setState('limit', $mainframe->getUserStateFromRequest('com_weblinks.limit', 'limit', $config->getValue('config.list_limit'), 'int'));
		$this->setState('limitstart', JRequest::getVar('limitstart', 0, '', 'int'));

		// In case limit has been changed, adjust limitstart accordingly
		$this->setState('limitstart', ($this->getState('limit') != 0 ? (floor($this->getState('limitstart') / $this->getState('limit')) * $this->getState('limit')) : 0));

		// Get the filter request variables
		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'w.ordering',	'cmd' );
		$filter->order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$this->_filter = $filter;
	}

	/**
	 * Method to get weblink item data for the category
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
				$item->slug = $item->id.':'.$item->alias;
			}
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of weblink items for the category
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
	 * Method to get a pagination object of the weblink items for the category
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

	function _buildQuery()
	{
		$orderby 			= $this->_buildContentOrderBy();

		// We need to get a list of all weblinks
		$query = 'SELECT w.*, cc.title AS category'
			. ' FROM #__weblinks AS w'
			. ' LEFT JOIN #__categories AS cc ON cc.id = w.catid'
			. ' WHERE w.state = 1'
			. ' AND cc.published = 1'
			. $orderby;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $mainframe;

		if ($this->_filter->order != ''){
			$orderby = ' ORDER BY '. $this->_filter->order .' '. $this->_filter->order_Dir .', w.ordering';
		}
		else {
			// Get the page/component configuration
			$params = &$mainframe->getParams();
			if (!is_object($params)) {
				$params = &JComponentHelper::getParams('com_weblinks');
			}

			$orderby_sec	= $params->def('orderby_sec', '');
			$orderby_pri	= $params->def('orderby_pri', '');
			$secondary		= WeblinksHelperQuery::orderbySecondary($orderby_sec);
			$primary		= WeblinksHelperQuery::orderbyPrimary($orderby_pri);

			$orderby = ' ORDER BY '.$primary.' '.$secondary;
		}

		return $orderby;
	}
}
?>
