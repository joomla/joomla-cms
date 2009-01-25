<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Members Model for JXtended Members.
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @version		1.5
 */
class WeblinksModelWeblinks extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @access	protected
	 * @var		string
	 */
	 var $_context = 'weblinks.weblinks';

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
		$query->select($this->getState('list.select', 'a.*'));
		$query->select('cc.title AS category');
		$query->select('u.name AS editor');
		$query->from('`#__weblinks` AS a');

		// Join over the categories.
		$query->join('LEFT', '#__categories AS cc ON cc.id = a.catid');

		// Join over the users.
		$query->join('LEFT', '#__users AS u ON u.id = a.checked_out');

		// Filter by category
		$categoryId = $this->getState('filter.category_id');
		if (is_numeric($categoryId)) {
			$query->where('a.catid = '.(int) $categoryId);
		}

		// Filter by state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('a.state = '.(int) $state);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$search = $db->Quote( $db->getEscaped( $search, true ).'%', false );
			$query->where('(a.title LIKE '.$search);
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.username')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query->toString())).'<hr/>';
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
		$user		= &JFactory::getUser();
		$params		= JComponentHelper::getParams('com_members');
		$context	= 'com_members.members.';

		// Load the filter state.
		$this->setState('filter.search', $app->getUserStateFromRequest($context.'filter.search', 'filter_search', ''));
		$this->setState('filter.state', $app->getUserStateFromRequest($context.'filter.state', 'filter_state', '*', 'string'));
		$this->setState('filter.category_id', $app->getUserStateFromRequest($context.'filter.category_id', 'filter_catid', null, 'int'));

		// Load the list state.
		$this->setState('list.start', $app->getUserStateFromRequest($context.'list.start', 'limitstart', 0, 'int'));
		$this->setState('list.limit', $app->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit', 25), 'int'));
		$this->setState('list.ordering', $app->getUserStateFromRequest($context.'list.ordering', 'filter_order', 'a.id', 'cmd'));
		$this->setState('list.direction', $app->getUserStateFromRequest($context.'list.direction', 'filter_order_Dir', 'ASC', 'word'));

		// Load the user parameters.
		$this->setState('user',	$user);
		$this->setState('user.id', (int) $user->id);
		$this->setState('user.aid', (int )$user->get('aid'));

		// Load the check parameters.
		if ($this->_state->get('filter.state') === '*') {
			$this->setState('check.state', false);
		} else {
			$this->setState('check.state', true);
		}

		// Load the parameters.
		$this->setState('params', $params);
	}

	function setStates($cid, $state = 0)
	{
		$user = &JFactory::getUser();

		// Add a table include path.
		JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

		// Get a labels row instance.
		$table = JTable::getInstance('Weblink', 'WeblinksTable');

		// Update the state for each row
		for ($i=0; $i < count($cid); $i++)
		{
			// Load the row.
			$table->load($cid[$i]);

			// Make sure the label isn't checked out by someone else.
			if ($table->checked_out != 0 && $table->checked_out != $user->id)
			{
				$this->setError(JText::sprintf('LABELS_LABEL_CHECKED_OUT', $cid[$i]));
				return false;
			}

			// Check the current ordering.
			if ($table->state != $state)
			{
				// Set the new ordering.
				$table->state = $state;

				// Save the row.
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		return true;
	}

}