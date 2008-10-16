<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Messages
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
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
 * Messages Component Messages Model
 *
 * @package		Joomla
 * @subpackage	Messages
 * @since 1.5
 */
class MessagesModelMessages extends JModel
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
		$context			= $option.'.list';

		// Get the pagination request variables
		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $context.'.limitstart', 'limitstart', 0, 'int' );

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		$filter = new stdClass();
		$filter->order		= $mainframe->getUserStateFromRequest( $context.'.filter_order',		'filter_order',		'a.date_time',	'cmd' );
		$filter->order_Dir	= $mainframe->getUserStateFromRequest( $context.'.filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$filter->state		= $mainframe->getUserStateFromRequest( $context.'.filter_state',		'filter_state',		'',				'word' );
		$filter->search		= $mainframe->getUserStateFromRequest( $context.'.search',			'search',			'',				'string' );
		$this->_filter = $filter;
	}

	/**
	 * Method to get contacts item data
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
	 * Method to get the total number of contact items
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
	 * Method to get a pagination object for the contacts
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
	 * Method to get filter object for the contacts
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

		$query = 'SELECT a.*, u.name AS user_from'
			. ' FROM #__messages AS a'
			. ' INNER JOIN #__users AS u ON u.id = a.user_id_from'
			. $where
			. $orderby
		;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$orderby 	= ' ORDER BY '. $this->_filter->order .' '. $this->_filter->order_Dir .', a.date_time DESC';

		return $orderby;
	}

	function _buildContentWhere()
	{
		$search				= JString::strtolower( $this->_filter->search );
		$user 				=& JFactory::getUser();

		$where = array();
		$where[] = ' a.user_id_to='.(int) $user->get('id');

		if ($search != '') {
			$searchEscaped = $db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
			$where[] = '( u.username LIKE '.$searchEscaped.' OR email LIKE '.$searchEscaped.' OR u.name LIKE '.$searchEscaped.' )';
		}

		if ( $this->_filter->state ) {
			if ( $this->_filter->state == 'P' ) {
				$where[] = 'a.published = 1';
			} else if ($this->_filter->state == 'U' ) {
				$where[] = 'a.published = 0';
			}
		}

		$where 		= ( count( $where ) ? ' WHERE ' . implode( ' AND ', $where ) : '' );

		return $where;
	}
}