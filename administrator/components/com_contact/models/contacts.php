<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of contact records.
 *
 * @since  1.6
 */
class ContactModelContacts extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'name', 'a.name',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'catid', 'a.catid', 'category_title',
				'user_id', 'a.user_id',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'ordering', 'a.ordering',
				'featured', 'a.featured',
				'language', 'a.language',
				'publish_up', 'a.publish_up',
				'publish_down', 'a.publish_down',
				'ul.name', 'linked_user',
			);

			$assoc = JLanguageAssociations::isEnabled();

			if ($assoc)
			{
				$config['filter_fields'][] = 'association';
			}
		}
		$config['table_name'] = '#__contact_details';

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
		$app = JFactory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest($this->context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id');
		$this->setState('filter.category_id', $categoryId);

		$language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Force a language.
		$forcedLanguage = $app->input->get('forcedLanguage');

		if (!empty($forcedLanguage))
		{
			$this->setState('filter.language', $forcedLanguage);
			$this->setState('filter.forcedLanguage', $forcedLanguage);
		}

		$tag = $this->getUserStateFromRequest($this->context . '.filter.tag', 'filter_tag', '');
		$this->setState('filter.tag', $tag);

		// List state information.
		parent::populateState('a.name', 'asc');
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
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.access');
		$id .= ':' . $this->getState('filter.published');
		$id .= ':' . $this->getState('filter.category_id');
		$id .= ':' . $this->getState('filter.language');

		return parent::getStoreId($id);
	}

	/**
	 * Builds SELECT columns list for the query
	 *
	 * @param   JDatabaseQuery  $query  The query object
	 *
	 * @return $this
	 *
	 * @since 3.4.1
	 */
	protected function _buildQueryColumns(JDatabaseQuery $query)
	{
		$query->select(
			'a.id, a.name, a.alias, a.checked_out, a.checked_out_time, a.catid, a.user_id' .
			', a.published, a.access, a.created, a.created_by, a.ordering, a.featured, a.language' .
			', a.publish_up, a.publish_down'
		);

		return $this;
	}

	/**
	 * Builds JOIN clauses for the query
	 *
	 * @param   JDatabaseQuery  $query  The query object
	 *
	 * @return $this
	 *
	 * @since 3.4.1
	 */
	protected function _buildQueryJoins(JDatabaseQuery $query)
	{
		parent::_buildQueryJoins($query);

		// Join over the users for the linked user.
		$query->select('ul.name AS linked_user')
			->join('LEFT', '#__users AS ul ON ul.id=a.user_id');

		// Join over the associations.
		$assoc = JLanguageAssociations::isEnabled();

		if ($assoc)
		{
			$db = $this->getDbo();
			$query->select('COUNT(asso2.id)>1 as association')
				->join('LEFT', '#__associations AS asso ON asso.id = a.id AND asso.context=' . $db->quote('com_contact.item'))
				->join('LEFT', '#__associations AS asso2 ON asso2.key = asso.key')
				->group('a.id, ul.name, l.title, uc.name, ag.title, c.title');
		}

		return $this;
	}

	/**
	 * Builds a generic ORDER BY clause
	 *
	 * @param   JDatabaseQuery  $query  The query object
	 *
	 * @return $this
	 *
	 * @since 3.4.1
	 */
	protected function _buildQueryOrder(JDatabaseQuery $query)
	{
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.name');
		$orderDirn = $this->state->get('list.direction', 'asc');

		if ($orderCol == 'a.ordering' || $orderCol == 'category_title')
		{
			$orderCol = 'c.title ' . $orderDirn . ', a.ordering';
		}

		$query->order($this->getDbo()->escape($orderCol . ' ' . $orderDirn));
	}
}
