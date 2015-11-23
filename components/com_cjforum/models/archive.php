<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

require_once __DIR__ . '/topics.php';

class CjForumModelArchive extends CjForumModelTopics
{

	public $_context = 'com_cjforum.archive';

	protected function populateState ($ordering = null, $direction = null)
	{
		parent::populateState();
		
		$app = JFactory::getApplication();
		
		// Add archive properties
		$params = $this->state->params;
		
		// Filter on archived topics
		$this->setState('filter.published', 2);
		
		// Filter on month, year
		$this->setState('filter.month', $app->input->getInt('month'));
		$this->setState('filter.year', $app->input->getInt('year'));
		
		// Optional filter text
		$this->setState('list.filter', $app->input->getString('filter-search'));
		
		// Get list limit
		$itemid = $app->input->get('Itemid', 0, 'int');
		$limit = $app->getUserStateFromRequest('com_cjforum.archive.list' . $itemid . '.limit', 'limit', $params->get('display_num'), 'uint');
		$this->setState('list.limit', $limit);
	}

	protected function getListQuery ()
	{
		// Set the archive ordering
		$params = $this->state->params;
		$topicOrderby = $params->get('orderby_sec', 'rdate');
		$topicOrderDate = $params->get('order_date');
		
		// No category ordering
		$categoryOrderby = '';
		$secondary = ContentHelperQuery::orderbySecondary($topicOrderby, $topicOrderDate) . ', ';
		$primary = ContentHelperQuery::orderbyPrimary($categoryOrderby);
		
		$orderby = $primary . ' ' . $secondary . ' a.created DESC ';
		$this->setState('list.ordering', $orderby);
		$this->setState('list.direction', '');
		// Create a new query object.
		$query = parent::getListQuery();
		
		// Add routing for archive
		// sqlsrv changes
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('a.alias', '!=', '0');
		$case_when .= ' THEN ';
		$t_id = $query->castAsChar('a.id');
		$case_when .= $query->concatenate(array(
				$t_id,
				'a.alias'
		), ':');
		$case_when .= ' ELSE ';
		$case_when .= $t_id . ' END as slug';
		
		$query->select($case_when);
		
		$case_when = ' CASE WHEN ';
		$case_when .= $query->charLength('c.alias', '!=', '0');
		$case_when .= ' THEN ';
		$c_id = $query->castAsChar('c.id');
		$case_when .= $query->concatenate(array(
				$c_id,
				'c.alias'
		), ':');
		$case_when .= ' ELSE ';
		$case_when .= $c_id . ' END as catslug';
		$query->select($case_when);
		
		// Filter on month, year
		// First, get the date field
		$queryDate = ContentHelperQuery::getQueryDate($topicOrderDate);
		
		if ($month = $this->getState('filter.month'))
		{
			$query->where('MONTH(' . $queryDate . ') = ' . $month);
		}
		
		if ($year = $this->getState('filter.year'))
		{
			$query->where('YEAR(' . $queryDate . ') = ' . $year);
		}
		
		// echo nl2br(str_replace('#__','jos_',$query));
		
		return $query;
	}

	public function getData ()
	{
		$app = JFactory::getApplication();
		
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			// Get the page/component configuration
			$params = $app->getParams();
			
			// Get the pagination request variables
			$limit = $app->input->get('limit', $params->get('display_num', 20), 'uint');
			$limitstart = $app->input->get('limitstart', 0, 'uint');
			
			$query = $this->_buildQuery();
			
			$this->_data = $this->_getList($query, $limitstart, $limit);
		}
		
		return $this->_data;
	}

	protected function _getList ($query, $limitstart = 0, $limit = 0)
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
}
