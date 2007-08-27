<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Content
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
 * Frontpage Component Model
 *
 * @author	Louis Landry <louis.landry@joomla.org>
 * @package		Joomla
 * @subpackage	Content
 * @since 1.5
 */
class ContentModelFrontpage extends JModel
{
	/**
	 * Frontpage data array
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Frontpage total
	 *
	 * @var integer
	 */
	var $_total = null;


	/**
	 * Method to get content item data for the frontpage
	 *
	 * @access public
	 * @return array
	 */
	function getData()
	{
		// Load the Category data
		if ($this->_loadData())
		{
			// Initialize some variables
			$user	=& JFactory::getUser();

			// raise errors
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

	/**
	 * Method to load content item data for items in the frontpage
	 * exist.
	 *
	 * @access	private
	 * @return	boolean	True on success
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
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
			$this->_data = $rows;
		}
		return true;
	}

	function _buildQuery()
	{
		global $mainframe;
		// Get the page/component configuration
		$params = &$mainframe->getPageParameters();

		// Voting is turned on, get voting data as well for the content items
		$voting	= ContentHelperQuery::buildVotingQuery($params);

		// Get the WHERE and ORDER BY clauses for the query
		$where	 = $this->_buildContentWhere();
		$orderby = $this->_buildContentOrderBy();

		$query = 'SELECT a.id, a.title, a.title_alias, a.introtext, a.sectionid, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,' .
			' a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.attribs, a.urls, a.ordering, a.metakey, a.metadesc, a.access,' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug,'.
			' CHAR_LENGTH( a.`fulltext` ) AS readmore,' .
			' u.name AS author, u.usertype, g.name AS groups, cc.title AS category, s.title AS section'.
			$voting['select'] .
			' FROM #__content AS a' .
			' INNER JOIN #__content_frontpage AS f ON f.content_id = a.id' .
			' LEFT JOIN #__categories AS cc ON cc.id = a.catid'.
			' LEFT JOIN #__sections AS s ON s.id = a.sectionid'.
			' LEFT JOIN #__users AS u ON u.id = a.created_by' .
			' LEFT JOIN #__groups AS g ON a.access = g.id'.
			$voting['join'].
			$where.
			$orderby;

		return $query;
	}

	function _buildContentOrderBy()
	{
		global $mainframe;
		// Get the page/component configuration
		$params = &$mainframe->getPageParameters();
		if (!is_object($params)) {
			$params = &JComponentHelper::getParams('com_content');
		}

		$orderby_sec	= $params->def('orderby_sec', '');
		$orderby_pri	= $params->def('orderby_pri', '');
		$secondary		= ContentHelperQuery::orderbySecondary($orderby_sec);
		$primary		= ContentHelperQuery::orderbyPrimary($orderby_pri);

		$orderby = ' ORDER BY '.$primary.' '.$secondary;

		return $orderby;
	}

	function _buildContentWhere()
	{
		global $mainframe;

		$user		=& JFactory::getUser();
		$gid		= $user->get('aid', 0);
		// TODO: Should we be using requestTime here? or is JDate ok?
		// $now		= $mainframe->get('requestTime');

		jimport('joomla.utilities.date');
		$jnow		= new JDate();
		$now		= $jnow->toMySQL();

		// Get the page/component configuration
		$params = &$mainframe->getPageParameters();

		$noauth		= !$params->get('show_noauth');
		$nullDate	= $this->_db->getNullDate();

		//First thing we need to do is assert that the articles are in the current category
		$where = ' WHERE 1';

		// Does the user have access to view the items?
		if ($noauth) {
			$where .= ' AND a.access <= '.(int) $gid;
		}

		if ($user->authorize('com_content', 'edit', 'content', 'all')) {
			$where .= ' AND a.state >= 0';
		} else {
			$where .= ' AND a.state = 1' .
					' AND ( cc.published = 1 )'.
					' AND ( s.published = 1 )'.
					' AND ( publish_up = '.$this->_db->Quote($nullDate).' OR publish_up <= '.$this->_db->Quote($now).' )' .
					' AND ( publish_down = '.$this->_db->Quote($nullDate).' OR publish_down >= '.$this->_db->Quote($now).' )';
		}

		return $where;
	}
}
