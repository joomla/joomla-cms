<?php
/**
 * @version $Id: section.php 4754 2006-08-25 11:53:46Z Jinx $
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport('joomla.application.component.model');

/**
 * Content Component Archive Model
 *
 * @author		Louis Landry <louis.landry@joomla.org>
 * @package 	Joomla
 * @subpackage	Content
 * @since		1.5
 */
class ContentModelArchive extends JModel
{
	/**
	 * Article list array
	 *
	 * @var array
	 */
	var $_data = array();

	/**
	 * Article total
	 *
	 * @var integer
	 */
	var $_total = array();

	/**
	 * Method to get the archived article list
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		global $Itemid;

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			// Get the menu object of the active menu item
			$menus	 =& JMenu::getInstance();
			$params  =& JSiteHelper::getMenuParams();

			// Get the pagination request variables
			$limit		= JRequest::getVar('limit', $params->get('display_num', 20), '', 'int');
			$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');

			$query = $this->_buildQuery();

			$this->_data = $this->_getList($query, $limitstart, $limit);
		}

		return $this->_data;
	}

	/**
	 * Method to get the total number of content items for the frontpage
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

	function _buildQuery()
	{
		// If voting is turned on, get voting data as well for the content items
		$voting	= JContentHelper::buildVotingQuery();

		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		$query = "SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by," .
			"\n a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.attribs, a.hits, a.images, a.urls, a.ordering, a.metakey, a.metadesc, a.access," .
			"\n CASE WHEN CHAR_LENGTH(a.title_alias) THEN CONCAT_WS(':', a.id, a.title_alias) ELSE a.id END as slug,".
			"\n CHAR_LENGTH( a.`fulltext` ) AS readmore, u.name AS author, u.usertype, cc.name AS category, g.name AS groups".$voting['select'] .
			"\n FROM #__content AS a" .
			"\n INNER JOIN #__categories AS cc ON cc.id = a.catid" .
			"\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" .
			"\n LEFT JOIN #__users AS u ON u.id = a.created_by" .
			"\n LEFT JOIN #__groups AS g ON a.access = g.id".
			$voting['join'].
			$where.
			$orderby;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$filter_order		= JRequest::getVar('filter_order');
		$filter_order_Dir	= JRequest::getVar('filter_order_Dir');
		$Itemid    			= JRequest::getVar('Itemid');

		$orderby = "\n ORDER BY ";
		if ($filter_order && $filter_order_Dir) {
			$orderby .= "$filter_order $filter_order_Dir, ";
		}

		// Get the paramaters of the active menu item
		$params =& JSiteHelper::getMenuParams();

		// Special ordering for archive articles
		$orderby_sec	= $params->def('orderby', 'rdate');
		$primary  = JContentHelper::orderbySecondary($orderby_sec);
		$orderby .= $primary;

		return $orderby;
	}

	function _buildContentWhere()
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$user	=& JFactory::getUser();
		$gid	= $user->get('gid');

		// First thing we need to do is build the access section of the clause
		$where = "\n WHERE a.access <= $gid";
		$where .= "\n AND s.access <= $gid";
		$where .= "\n AND cc.access <= $gid";
		$where .= "\n AND s.published = 1";
		$where .= "\n AND cc.published = 1";

		$where .= "\n AND a.state = '-1'";
		$year	= JRequest::getVar( 'year' );
		if ($year) {
			$where .= "\n AND YEAR( a.created ) = '$year'";
		}
		$month	= JRequest::getVar( 'month' );
		if ($month) {
			$where .= "\n AND MONTH( a.created ) = '$month'";
		}

		/*
		 * If we have a filter... lets tack the AND clause
		 * for the filter onto the WHERE clause of the archive query.
		 */
		$filter = JRequest::getVar('filter', '', 'post');
		if ($filter) {
			// clean filter variable
			$filter = JString::strtolower($filter);

			// Get the paramaters of the active menu item
			$params =& JSiteHelper::getMenuParams();
			switch ($params->get('filter_type', 'title'))
			{
				case 'title' :
					$where .= "\n AND LOWER( a.title ) LIKE '%$filter%'";
					break;

				case 'author' :
					$where .= "\n AND ( ( LOWER( u.name ) LIKE '%$filter%' ) OR ( LOWER( a.created_by_alias ) LIKE '%$filter%' ) )";
					break;

				case 'hits' :
					$where .= "\n AND a.hits LIKE '%$filter%'";
					break;
			}
		}
		return $where;
	}
}
?>