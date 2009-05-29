<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

jimport('joomla.application.component.modellist');
jimport('joomla.database.query');

/**
 * Links model for Redirect.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @version		1.6
 */
class RedirectModelLinks extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var	string
	 */
	 protected $_context = 'redirect.links';

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

		// Select all fields from the table.
		$query->select($this->getState('list.select', '*'));
		$query->from('`#__redirect_links`');

		// Filter the items over the search string if set.
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			$query->where(
				'(`old_url` LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%') .
				' OR `new_url` LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%') .
				' OR `comment` LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%') .
				' OR `referer` LIKE '.$this->_db->Quote('%'.$this->_db->getEscaped($search, true).'%').')'
			);
		}

		// Filter the items over the published state if set.
		if ($this->getState('check.state')) {
			$state_id = $this->getState('filter.state');
			if ($state_id !== '*' and $state_id !== null) {
				$query->where('`published` = '.(int)$state_id);
			}
		}

		// Add the list ordering clause.
		$query->order($this->_db->getEscaped($this->getState('list.ordering', 'a.name')).' '.$this->_db->getEscaped($this->getState('list.direction', 'ASC')));

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
	 * @param	string	A prefix for the store id.
	 * @return	string	A store id.
	 * @since	1.0
	 */
	protected function _getStoreId($id = '')
	{
		// Compile the store id.
		$id	.= ':'.$this->getState('list.select');
		$id	.= ':'.$this->getState('list.start');
		$id	.= ':'.$this->getState('list.limit');
		$id	.= ':'.$this->getState('list.ordering');
		$id	.= ':'.$this->getState('list.direction');
		$id	.= ':'.$this->getState('filter.search');
		$id	.= ':'.$this->getState('filter.state');

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
	 * @since	1.0
	 */
	protected function _populateState()
	{
		$app		= & JFactory::getApplication('administrator');
		$user		= & JFactory::getUser();
		$params		= JComponentHelper::getParams('com_redirect');
		$context	= 'com_redirect.links.';

		// Load the filter state.
		$this->setState('filter.search', $app->getUserStateFromRequest($context.'filter.search', 'filter_search', ''));
		$this->setState('filter.state', $app->getUserStateFromRequest($context.'filter.state', 'filter_state', '0', 'string'));


		// Load the list state.
		$this->setState('list.start', $app->getUserStateFromRequest($context.'list.start', 'limitstart', 0, 'int'));
		$this->setState('list.limit', $app->getUserStateFromRequest($context.'list.limit', 'limit', $app->getCfg('list_limit', 25), 'int'));
		$this->setState('list.ordering', $app->getUserStateFromRequest($context.'list.ordering', 'filter_order', 'updated_date', 'cmd'));
		$this->setState('list.direction', $app->getUserStateFromRequest($context.'list.direction', 'filter_order_Dir', 'DESC', 'word'));

		// Load the user parameters.
		$this->setState('user',	$user);
		$this->setState('user.id', (int)$user->id);

		// Load the check parameters.
		if ($this->_state->get('filter.state') === '*') {
			$this->setState('check.state', false);
		} else {
			$this->setState('check.state', true);
		}

		// Load the parameters.
		$this->setState('params', $params);
	}
}