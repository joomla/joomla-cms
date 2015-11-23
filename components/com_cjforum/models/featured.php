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

class CjForumModelFeatured extends CjForumModelTopics
{

	public $_context = 'com_cjforum.frontpage';

	protected function populateState ($ordering = null, $direction = null)
	{
		parent::populateState($ordering, $direction);
		
		$input = JFactory::getApplication()->input;
		$user = JFactory::getUser();
		
		// List state information
		$limitstart = $input->getUInt('limitstart', 0);
		$this->setState('list.start', $limitstart);
		
		$params = $this->state->params;
		$limit = $params->get('num_leading_topics') + $params->get('num_intro_topics') + $params->get('num_links');
		$this->setState('list.limit', $limit);
		$this->setState('list.links', $params->get('num_links'));
		
		$this->setState('filter.frontpage', true);
		
		if ((! $user->authorise('core.edit.state', 'com_cjforum')) && (! $user->authorise('core.edit', 'com_cjforum')))
		{
			// filter on published for those who do not have edit or edit.state
			// rights.
			$this->setState('filter.published', 1);
		}
		else
		{
			$this->setState('filter.published', array(
					0,
					1,
					2
			));
		}
		
		// check for category selection
		if ($params->get('featured_categories') && implode(',', $params->get('featured_categories')) == true)
		{
			$featuredCategories = $params->get('featured_categories');
			$this->setState('filter.frontpage.categories', $featuredCategories);
		}
	}

	public function getItems ()
	{
		$params = clone $this->getState('params');
		$limit = $params->get('num_leading_topics') + $params->get('num_intro_topics') + $params->get('num_links');
		if ($limit > 0)
		{
			$this->setState('list.limit', $limit);
			return parent::getItems();
		}
		return array();
	}

	protected function getStoreId ($id = '')
	{
		// Compile the store id.
		$id .= $this->getState('filter.frontpage');
		
		return parent::getStoreId($id);
	}

	protected function getListQuery ()
	{
		// Set the blog ordering
		$params = $this->state->params;
		$topicOrderby = $params->get('orderby_sec', 'rdate');
		$topicOrderDate = $params->get('order_date');
		$categoryOrderby = $params->def('orderby_pri', '');
		$secondary = ContentHelperQuery::orderbySecondary($topicOrderby, $topicOrderDate) . ', ';
		$primary = ContentHelperQuery::orderbyPrimary($categoryOrderby);
		
		$orderby = $primary . ' ' . $secondary . ' a.created DESC ';
		$this->setState('list.ordering', $orderby);
		$this->setState('list.direction', '');
		
		// Create a new query object.
		$query = parent::getListQuery();
		
		// Filter by categories
		$featuredCategories = $this->getState('filter.frontpage.categories');
		
		if (is_array($featuredCategories) && ! in_array('', $featuredCategories))
		{
			$query->where('a.catid IN (' . implode(',', $featuredCategories) . ')');
		}
		
		return $query;
	}
}
