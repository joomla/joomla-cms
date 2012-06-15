<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Suggestions model class for the Finder package.
 *
 * @package     Joomla.Site
 * @subpackage  com_finder
 * @since       2.5
 */
class FinderModelSuggestions extends JModelList
{
	/**
	 * Context string for the model type.
	 *
	 * @var    string
	 * @since  2.5
	 */
	protected $context = 'com_finder.suggestions';

	/**
	 * Method to get an array of data items.
	 *
	 * @return  array  An array of data items.
	 *
	 * @since   2.5
	 */
	public function getItems()
	{
		// Get the items.
		$items = &parent::getItems();

		// Convert them to a simple array.
		foreach ($items as $k => $v)
		{
			$items[$k] = $v->term;
		}

		return $items;
	}

	/**
	 * Method to build a database query to load the list data.
	 *
	 * @return  JDatabaseQuery  A database query
	 *
	 * @since   2.5
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select required fields
		$query->select('t.term');
		$query->from($db->quoteName('#__finder_terms') . ' AS t');
		$query->where('t.term LIKE ' . $db->quote($db->escape($this->getState('input'), true) . '%'));
		$query->where('t.common = 0');
		$query->order('t.links DESC');
		$query->order('t.weight DESC');

		return $query;
	}

	/**
	 * Method to get a store id based on model the configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id. [optional]
	 *
	 * @return  string  A store id.
	 *
	 * @since   2.5
	 */
	protected function getStoreId($id = '')
	{
		// Add the search query state.
		$id .= ':' . $this->getState('input');
		$id .= ':' . $this->getState('language');

		// Add the list state.
		$id .= ':' . $this->getState('list.start');
		$id .= ':' . $this->getState('list.limit');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.  Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   2.5
	 */
	protected function populateState()
	{
		// Get the configuration options.
		$app = JFactory::getApplication();
		$input = $app->input;
		$params = JComponentHelper::getParams('com_finder');
		$user = JFactory::getUser();

		// Get the query input.
		$this->setState('input', $input->request->get('q', '', 'string'));
		$this->setState('language', $input->request->get('l', '', 'string'));

		// Load the list state.
		$this->setState('list.start', 0);
		$this->setState('list.limit', 10);

		// Load the parameters.
		$this->setState('params', $params);

		// Load the user state.
		$this->setState('user.id', (int) $user->get('id'));
	}
}
