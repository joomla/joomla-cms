<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Content\Site\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Component\Content\Site\Helper\QueryHelper;
use Joomla\Component\Content\Site\Model\ArticlesModel;
use Joomla\Utilities\ArrayHelper;

/**
 * Content Component Archive Model
 *
 * @since  1.5
 */
class ArchiveModel extends ArticlesModel
{
	/**
	 * Model context string.
	 *
	 * @var		string
	 */
	public $_context = 'com_content.archive';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   The field to order on.
	 * @param   string  $direction  The direction to order on.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		parent::populateState();

		$app = Factory::getApplication();

		// Add archive properties
		$params = $this->state->get('params');

		// Filter on archived articles
		$this->setState('filter.condition', 1);

		// Filter on month, year
		$this->setState('filter.month', $app->input->getInt('month'));
		$this->setState('filter.year', $app->input->getInt('year'));

		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));

		// Get list limit
		$itemid = $app->input->get('Itemid', 0, 'int');
		$limit = $app->getUserStateFromRequest('com_content.archive.list' . $itemid . '.limit', 'limit', $params->get('display_num', 20), 'uint');
		$this->setState('list.limit', $limit);

		// Set the archive ordering
		$articleOrderby   = $params->get('orderby_sec', 'rdate');
		$articleOrderDate = $params->get('order_date');

		// No category ordering
		$secondary = QueryHelper::orderbySecondary($articleOrderby, $articleOrderDate);

		$this->setState('list.ordering', $secondary . ', a.created DESC');
		$this->setState('list.direction', '');
	}

	/**
	 * Get the master query for retrieving a list of articles subject to the model state.
	 *
	 * @return  \JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$params           = $this->state->params;
		$app              = Factory::getApplication();
		$catids           = $app->input->getVar('catid', array());
		$catids           = array_values(array_diff($catids, array('')));
		$states           = $app->input->getVar('state', array());
		$states           = array_values(array_diff($states, array('')));

		$articleOrderDate = $params->get('order_date');

		$this->setState('filter.condition', false);

		// Create a new query object.
		$db = $this->getDbo();
		$query = parent::getListQuery();

		// Add routing for archive
		$query->select($this->getSlugColumn($query, 'a.id', 'a.alias') . ' AS slug');
		$query->select($this->getSlugColumn($query, 'c.id', 'c.alias') . ' AS catslug');

		// Filter on month, year
		// First, get the date field
		$queryDate = QueryHelper::getQueryDate($articleOrderDate);

		if ($month = $this->getState('filter.month'))
		{
			$query->where($query->month($queryDate) . ' = ' . $month);
		}

		if ($year = $this->getState('filter.year'))
		{
			$query->where($query->year($queryDate) . ' = ' . $year);
		}

		if (count($catids) > 0)
		{
			$query->where('c.id IN (' . implode(', ', $catids) . ')');
		}

		if (count($states) > 0)
		{
			$query->where($db->qn('state_id') . ' IN (' . implode(', ', $states) . ')');
		}

		return $query;
	}

	/**
	 * Method to get the archived article list
	 *
	 * @access public
	 * @return array
	 */
	public function getData()
	{
		$app = Factory::getApplication();

		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			// Get the page/component configuration
			$params = $app->getParams();

			// Get the pagination request variables
			$limit      = $app->input->get('limit', $params->get('display_num', 20), 'uint');
			$limitstart = $app->input->get('limitstart', 0, 'uint');

			$query = $this->_buildQuery();

			$this->_data = $this->_getList($query, $limitstart, $limit);
		}

		return $this->_data;
	}

	/**
	 * Model override to add alternating value for $odd
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @since   12.2
	 * @throws  \RuntimeException
	 */
	protected function _getList($query, $limitstart=0, $limit=0)
	{
		$result = parent::_getList($query, $limitstart, $limit);

		$odd = 1;

		foreach ($result as $k => $row)
		{
			$result[$k]->odd = $odd;
			$odd = 1 - $odd;
		}

		return $result;
	}

	/**
	 * Gets the archived articles years
	 *
	 * @return   array
	 *
	 * @since    3.6.0
	 */
	public function getYears()
	{
		$db = $this->getDbo();
		$nullDate = $db->quote($db->getNullDate());
		$nowDate  = $db->quote(Factory::getDate()->toSql());

		$query = $db->getQuery(true);
		$years = $query->year($db->qn('created'));
		$query->select('DISTINCT (' . $years . ')')
			->from($db->qn('#__content'))
			->where($db->qn('state') . '= 3')
			->where('(publish_up = ' . $nullDate . ' OR publish_up <= ' . $nowDate . ')')
			->where('(publish_down = ' . $nullDate . ' OR publish_down >= ' . $nowDate . ')')
			->order('1 ASC');

		$db->setQuery($query);
		return $db->loadColumn();
	}

	/**
	* Generate column expression for slug or catslug.
	*
	* @param   \JDatabaseQuery  $query  Current query instance.
	* @param   string           $id     Column id name.
	* @param   string           $alias  Column alias name.
	*
	* @return  string
	*
	* @since   4.0.0
	*/
	private function getSlugColumn($query, $id, $alias)
	{
		return 'CASE WHEN '
			. $query->charLength($alias, '!=', '0')
			. ' THEN '
			. $query->concatenate(array($query->castAsChar($id), $alias), ':')
			. ' ELSE '
			. $id . ' END';
	}
}
