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
 * Frontpage Component Model
 *
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
	protected $_data = null;

	/**
	 * Frontpage total
	 *
	 * @var integer
	 */
	protected $_total = null;


	/**
	 * Method to get content item data for the frontpage
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		// Load the Category data
		$this->_loadData();
		return $this->_data;
	}

	/**
	 * Method to get the total number of content items for the frontpage
	 *
	 * @access public
	 * @return integer
	 */
	public function getTotal()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery(true);
			$this->_db->setQuery($query);
			$this->_total = $this->_db->loadResult();
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
	protected function _loadData()
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

	protected function _buildQuery($countOnly = false)
	{
		$app = JFactory::getApplication();
		// Get the page/component configuration
		$params = &$app->getParams();

		// Voting is turned on, get voting data as well for the content items
		$voting	= ContentHelperQuery::buildVotingQuery($params);

		// Get the WHERE and ORDER BY clauses for the query
		$where	= $this->_buildContentWhere();
		$orderby = $this->_buildContentOrderBy();

		if (!$countOnly) {
			$query = ' SELECT a.id, a.title, a.title_alias, a.introtext, a.fulltext, a.state, a.catid, a.created, a.created_by, a.created_by_alias, a.modified, a.modified_by,' .
				' a.checked_out, a.checked_out_time, a.publish_up, a.publish_down, a.images, a.attribs, a.urls, a.metakey, a.metadesc, a.access,' .
				' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(\':\', a.id, a.alias) ELSE a.id END as slug,'.
				' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug,'.
				' CHAR_LENGTH(a.`fulltext`) AS readmore,' .
				' u.name AS author, u.usertype, cc.title AS category, a.ordering AS a_ordering, f.ordering AS f_ordering'.
				$voting['select'];
		} else {
			$query = 'SELECT count(*)';
		}
		$query .=
			' FROM #__content AS a' .
			' INNER JOIN #__content_frontpage AS f ON f.content_id = a.id' .
			' LEFT JOIN #__categories AS cc ON cc.id = a.catid'.
			' LEFT JOIN #__users AS u ON u.id = a.created_by' .
			$voting['join'].
			$where
			.$orderby
			;

		return $query;
	}

	function _buildContentOrderBy()
	{
		$app = JFactory::getApplication();
		// Get the page/component configuration
		$params = &$app->getParams();
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
		$app = JFactory::getApplication();

		$user	= &JFactory::getUser();
		$gid	= $user->get('aid', 0);

		$jnow	= &JFactory::getDate();
		$now	= $jnow->toMySQL();

		// Get the page/component configuration
		$params = $app->getParams();

		$noauth		= !$params->get('show_noauth');
		$nullDate	= $this->_db->getNullDate();

		//First thing we need to do is assert that the articles are in the current category
		$where = ' WHERE 1';

		// Does the user have access to view the items?
		if ($noauth) {
			$where .= ' AND a.access IN ('.implode(',', $user->authorisedLevels('com_content.article.view')).')'
						.' AND cc.access IN ('.implode(',', $user->authorisedLevels('com_content.category.view')).')';
		}

		if ($user->authorize('com_content.article.edit_article')) {
			$where .= ' AND a.state >= 0';
		} else {
			$where .= ' AND a.state = 1'.
					' AND (cc.published = 1 OR a.catid = 0)';

			$where .= ' AND (a.publish_up = '.$this->_db->Quote($nullDate).' OR a.publish_up <= '.$this->_db->Quote($now).')' .
					' AND (a.publish_down = '.$this->_db->Quote($nullDate).' OR a.publish_down >= '.$this->_db->Quote($now).')';
		}

		return $where;
	}
}
