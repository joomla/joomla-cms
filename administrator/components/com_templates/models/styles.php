<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of template style records.
 *
 * @since  1.6
 */
class TemplatesModelStyles extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'client_id', 'a.client_id',
				'template', 'a.template',
				'home', 'a.home',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$template = $this->getUserStateFromRequest($this->context . '.filter.template', 'filter_template');
		$this->setState('filter.template', $template);

		$clientId = $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null);
		$this->setState('filter.client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_templates');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.template', 'asc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.template');
		$id .= ':' . $this->getState('filter.client_id');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.template, a.title, a.home, a.client_id, l.title AS language_title, l.image as image'
			)
		);
		$query->from($db->quoteName('#__template_styles') . ' AS a');

		// Join on menus.
		$query->select('COUNT(m.template_style_id) AS assigned')
			->join('LEFT', '#__menu AS m ON m.template_style_id = a.id')
			->group('a.id, a.template, a.title, a.home, a.client_id, l.title, l.image, e.extension_id');

		// Join over the language
		$query->join('LEFT', '#__languages AS l ON l.lang_code = a.home');

		// Filter by extension enabled
		$query->select('extension_id AS e_id')
			->join('LEFT', '#__extensions AS e ON e.element = a.template')
			->where('e.enabled = 1')
			->where('e.type=' . $db->quote('template'));

		// Filter by template.
		if ($template = $this->getState('filter.template'))
		{
			$query->where('a.template = ' . $db->quote($template));
		}

		// Filter by client.
		$clientId = $this->getState('filter.client_id');

		if (is_numeric($clientId))
		{
			$query->where('a.client_id = ' . (int) $clientId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$escapedSearchString = $this->refineSearchStringToRegex($search, '/');
				$search = $db->quote($escapedSearchString);
				$query->where('(' . 'a.template REGEXP ' . $search . ' OR a.title REGEXP ' . $search . ')');
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.title')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}
}
