<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Comments model for the Comments package.
 *
 * @package		JXtended.Comments
 * @subpackage	com_comments
 * @since		1.6
 */
class CommentsModelThreads extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @access	protected
	 */
	function _populateState()
	{
		$app		= &JFactory::getApplication('administrator');
		$context	= 'com_comments.threads';

		// Load the filter state.
		$search = $app->getUserStateFromRequest($context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$context = $app->getUserStateFromRequest($context.'.filter.context', 'filter_context', '', 'word');
		$this->setState('filter.context', $context);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_comments');
		$this->setState('params', $params);

		// List state information.
		parent::_populateState('a.id', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @access	protected
	 * @param	string		$context	A prefix for the store id.
	 * @return	string		A store id.
	 */
	function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.context');
		$id	.= ':'.$this->getState('filter.thread_id');

		return parent::_getStoreId($id);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @access	protected
	 * @return	string		An SQL query
	 * @since	1.0
	 */
	function _getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__social_threads` AS a');

		// Join on the comments table.
		$query->select('COUNT(c.id) AS comment_count');
		$query->leftJoin('#__social_comments AS c ON c.thread_id = a.id');

		// Join on the ratings table.
		$query->select('pscore_count');
		$query->leftJoin('#__social_ratings AS r ON r.thread_id = a.id');

		$query->group('a.id');

		// Filter the items over the context if set.
		if ($context = $this->getState('filter.context')) {
			$query->where('a.context = '.$db->Quote($context));
		}

		// Filter by search string.
		if ($search = $this->getState('filter.search'))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('a.page_title LIKE '.$search);
			}
		}

		// Add the list ordering clause.
		$query->order($db->getEscaped($this->getState('list.ordering', 'a.id').' '.$this->getState('list.direction', 'asc')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}
