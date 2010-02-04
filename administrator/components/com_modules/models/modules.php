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
 * Modules Component Module Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.5
 */
class ModulesModelModules extends JModelList
{
	/**
	 * Model context string.
	 *
	 * @var	string
	 */
	protected $_context = 'com_modules.modules';

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

		$accessId = $app->getUserStateFromRequest($this->_context.'.filter.access', 'filter_access', null, 'int');
		$this->setState('filter.access', $accessId);

		$state = $app->getUserStateFromRequest($this->_context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		$position = $app->getUserStateFromRequest($this->_context.'.filter.position', 'filter_position', '', 'string');
		$this->setState('filter.position', $position);

		$module = $app->getUserStateFromRequest($this->_context.'.filter.module', 'filter_module', '', 'string');
		$this->setState('filter.module', $module);

		$clientId = $app->getUserStateFromRequest($this->_context.'.filter.client_id', 'filter_client_id', 0, 'int');
		$this->setState('filter.client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_modules');
		$this->setState('params', $params);

		// List state information.
		parent::_populateState('a.position', 'asc');
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
		$id	.= ':'.$this->getState('filter.access');
		$id	.= ':'.$this->getState('filter.state');
		$id	.= ':'.$this->getState('filter.position');
		$id	.= ':'.$this->getState('filter.module');
		$id	.= ':'.$this->getState('filter.client_id');

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
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.position, a.module, ' .
				'a.checked_out, a.checked_out_time, a.published, a.access, a.ordering, a.language'
			)
		);
		$query->from('`#__modules` AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the module menus
		$query->select('MIN(mm.menuid) AS pages');
		$query->join('LEFT', '#__modules_menu AS mm ON mm.moduleid = a.id');
		$query->group('a.id');

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = '.(int) $access);
		}

		// Filter by published state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('a.published = '.(int) $state);
		}
		else if ($state === '') {
			$query->where('(a.published IN (0, 1))');
		}

		// Filter by position
		$position = $this->getState('filter.position');
		if ($position) {
			$query->where('a.position = '.$db->Quote($position));
		}

		// Filter by module
		$module = $this->getState('filter.module');
		if ($module) {
			$query->where('a.module = '.$db->Quote($module));
		}

		// Filter by client.
		$clientId = $this->getState('filter.client_id');
		if (is_numeric($clientId)) {
			$query->where('a.client_id = '.(int) $clientId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('a.title LIKE '.$search);
			}
		}

		// Add the list ordering clause.
		$query->order($db->getEscaped($this->getState('list.ordering', 'a.ordering')).' '.$db->getEscaped($this->getState('list.direction', 'ASC')));

		//echo nl2br(str_replace('#__','jos_',$query));
		return $query;
	}
}
