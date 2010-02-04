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
 * Methods supporting a list of comment records.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @version		1.2
 */
class CommentsModelComments extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var	string
	 */
	protected $_context = 'com_comments.comments';

	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->_context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$state = $app->getUserStateFromRequest($this->_context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$context = $app->getUserStateFromRequest($this->_context.'filter.branch', 'filter_context', '', 'word');
		$this->setState('filter.context', $context);

		$threadId = $app->getUserStateFromRequest($this->_context.'filter.thread_id', 'filter_thread', null, 'int');
		$this->setState('filter.thread_id', $threadId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_comments');
		$this->setState('params', $params);

		// List state information.
		parent::_populateState('a.name', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string	A prefix for the store id.
	 *
	 * @return	string	A store id.
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.context');
		$id	.= ':'.$this->getState('filter.thread_id');

		return parent::_getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JQuery
	 */
	protected function _getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from('`#__social_comments` AS a');

		// Resolve foriegn keys.
		$query->select('b.page_route, b.page_url, b.page_title');
		$query->join('LEFT', '`#__social_threads` AS b ON b.id=a.thread_id');

		// Filter by published state.
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}
		else if ($published === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// Filter the items over the thread id if set.
		$thread_id = $this->getState('filter.thread_id');
		if ($thread_id !== null && $thread_id > 0) {
			$query->where('a.thread_id = '.(int)$thread_id);
		}

		// Filter the items over the context if set.
		$context = $this->getState('filter.context');
		if (!empty($context)) {
			$query->where('b.context = '.$db->Quote($context));
		}

		// Filter over the search string if set.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('a.name LIKE '.$search);
			}
		}

		// Add the list ordering clause.
		$query->order($db->getEscaped($this->getState('list.ordering').' '.$this->getState('list.direction')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}

	/**
	 * @return	mixed	An array if successful, false otherwise and the internal error is set.
	 */
	function getContexts()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		$query->select('DISTINCT(context) AS value');
		$query->from('#__social_threads');
		$query->order('context');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		return $result;
	}
}
