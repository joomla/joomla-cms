<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Weblinks Model Class
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @version		1.5
 */
class NewsfeedsModelNewsfeeds extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @access	protected
	 * @var		string
	 */
	 protected $_context = 'com_newsfeeds.newsfeeds';

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 * @since	1.6
	 */
	protected function _getListQuery()
	{
		// Create a new query object.
		$query = new JQuery;

		// Select all fields from the users table.
		$query->select($this->getState('list.select', 'a.*, c.title AS category'));
		$query->from('`#__newsfeeds` AS a');

		// Filter by category
		$categoryId = $this->getState('filter.catid');
		if (is_numeric($categoryId)) {
			$query->where('a.catid = '.(int) $categoryId);
		}

		// Filter by state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = '.(int) $published);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%', false);
			$query->where('(a.name LIKE '.$search.')');
		}

		$query->innerJoin( '#__categories AS c ON a.catid = c.id');
		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.ordering')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');
		$id	.= ':'.$this->getState('check.state');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.category_id');

		return md5($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function _populateState()
	{
		// Initialize variables.
		$app		= &JFactory::getApplication('administrator');
		$params		= JComponentHelper::getParams('com_newsfeeds');
		$context	= $this->_context.'.';

		// Load the filter state.
		$this->setState('filter.catid', $app->getUserStateFromRequest($context.'filter.catid', 'catid', null, 'int'));
		$this->setState('filter.published', $app->getUserStateFromRequest($context.'filter.state', 'filter_published', '*', 'string'));
				
		$this->setState('filter.search', $app->getUserStateFromRequest($context.'filter.search', 'filter_search', ''));


		// Load the list state.
		$this->setState('list.start', $app->getUserStateFromRequest($context.'list.start', 'limitstart', 0, 'int'));
		$this->setState('list.limit', $app->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit', 25), 'int'));
		$this->setState('list.ordering', $app->getUserStateFromRequest($context.'list.ordering', 'filter_order', 'a.ordering', 'cmd'));
		$this->setState('list.direction', $app->getUserStateFromRequest($context.'list.direction', 'filter_order_Dir', 'ASC', 'word'));

		// Load the check parameters.
		if ($this->_state->get('filter.state') === '*') {
			$this->setState('check.state', false);
		} else {
			$this->setState('check.state', true);
		}

		// Load the parameters.
		$this->setState('params', $params);
	}

	public function setStates($cid, $state = 0)
	{
		$user = &JFactory::getUser();

		// Get a newsfeeds row instance.
		$table = JTable::getInstance('Newsfeed', 'Table');

		// Update the state for each row
		foreach ($cid as $id) {
			// Load the row.
			$table->load($id);

			// Make sure the newsfeed isn't checked out by someone else.
			if ($table->checked_out != 0 && $table->checked_out != $user->id) {
				$this->setError(JText::sprintf('NEWSFEEDS_NEWSFEED_CHECKED_OUT', $id));
				return false;
			}

			// Check the current ordering.
			if ($table->published != $state) {
				// Set the new ordering.
				$table->published = $state;

				// Save the row.
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}
	function saveorder( $cid, $order)
	{
		// Initialize variables
		$db			= JFactory::getDbo();
		$table = JTable::getInstance('Newsfeed', 'Table');
		$total		= count($cid);
		$conditions	= array();

		if (empty($cid)) {
			return JError::raiseWarning(500, JText::_('No items selected'));
		}

		// update ordering values
		for ($i = 0; $i < $total; $i++)
		{
			$table->load((int) $cid[$i]);
			if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store()) {
					return JError::raiseError(500, $db->getErrorMsg());
				}
				// remember to reorder this category
				$condition = 'catid = '.(int) $table->catid;
				$found = false;
				foreach ($conditions as $cond) {
					if ($cond[1] == $condition)
					{
						$found = true;
						break;
					}
				}
				if (!$found) {
					$conditions[] = array ($table->bid, $condition);
				}
			}
		}

		// execute reorder for each category
		foreach ($conditions as $cond)
		{
			$table->load($cond[0]);
			$table->reorder($cond[1]);
		}

		// Clear the component's cache
		$cache = &JFactory::getCache('com_newsfeeds');
		$cache->clean();
		
	}

}
