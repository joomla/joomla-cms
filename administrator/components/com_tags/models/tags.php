<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Tags Component Tags Model
 *
 * @since  3.1
 */
class TagsModelTags extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see    JController
	 * @since  3.0.3
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'language', 'a.language',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'created_time', 'a.created_time',
				'created_user_id', 'a.created_user_id',
				'lft', 'a.lft',
				'rgt', 'a.rgt',
				'level', 'a.level',
				'path', 'a.path',
			);
		}
		$config['table_name'] = '#__tags';

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
	 * @return    void
	 *
	 * @since    3.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$context = $this->context;

		$search = $this->getUserStateFromRequest($context . '.search', 'filter_search');
		$this->setState('filter.search', $search);

		$level = $this->getUserStateFromRequest($context . '.filter.level', 'filter_level', 0, 'int');
		$this->setState('filter.level', $level);

		$access = $this->getUserStateFromRequest($context . '.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $access);

		$published = $this->getUserStateFromRequest($context . '.filter.published', 'filter_published', '');
		$this->setState('filter.published', $published);

		$language = $this->getUserStateFromRequest($context . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_tags');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.lft', 'asc');
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
	 * @since   3.1
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');
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
			'a.id, a.title, a.alias, a.note, a.published, a.access' .
			', a.checked_out, a.checked_out_time, a.created_user_id' .
			', a.path, a.parent_id, a.level, a.lft, a.rgt' .
			', a.language'
		);

		return $this;
	}

	/**
	 * Builds WHERE clauses for the query
	 *
	 * @param   JDatabaseQuery  $query  The query object
	 *
	 * @return $this
	 *
	 * @since 3.4.1
	 */
	protected function _buildQueryWhere(JDatabaseQuery $query)
	{
		$query->where('a.alias <> ' . $this->getDbo()->quote('root'));

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where('a.level <= ' . (int) $level);
		}

		return parent::_buildQueryWhere($query);
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
		// Join over the users for the author.
		$query->select('ua.name AS author_name')
			->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');

		return parent::_buildQueryJoins($query);
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
		$db = $this->getDbo();
		$listOrdering = $this->getState('list.ordering', 'a.lft');
		$listDirn = $db->escape($this->getState('list.direction', 'ASC'));

		if ($listOrdering == 'a.access')
		{
			$query->order('a.access ' . $listDirn . ', a.lft ' . $listDirn);
		}
		else
		{
			$query->order($db->escape($listOrdering) . ' ' . $listDirn);
		}

		return $this;
	}

	/**
	 * Method override to check-in a record or an array of record
	 *
	 * @param   mixed  $pks  The ID of the primary key or an array of IDs
	 *
	 * @return  mixed  Boolean false if there is an error, otherwise the count of records checked in.
	 *
	 * @since   12.2
	 */
	public function checkin($pks = array())
	{
		$pks = (array) $pks;
		$table = $this->getTable();
		$count = 0;

		if (empty($pks))
		{
			$pks = array((int) $this->getState($this->getName() . '.id'));
		}

		// Check in all items.
		foreach ($pks as $pk)
		{
			if ($table->load($pk))
			{
				if ($table->checked_out > 0)
				{
					// Only attempt to check the row in if it exists.
					if ($pk)
					{
						$user = JFactory::getUser();

						// Get an instance of the row to checkin.
						$table = $this->getTable();

						if (!$table->load($pk))
						{
							$this->setError($table->getError());

							return false;
						}

						// Check if this is the user having previously checked out the row.
						if ($table->checked_out > 0 && $table->checked_out != $user->get('id') && !$user->authorise('core.admin', 'com_checkin'))
						{
							$this->setError(JText::_('JLIB_APPLICATION_ERROR_CHECKIN_USER_MISMATCH'));

							return false;
						}

						// Attempt to check the row in.
						if (!$table->checkin($pk))
						{
							$this->setError($table->getError());

							return false;
						}
					}

					$count++;
				}
			}
			else
			{
				$this->setError($table->getError());

				return false;
			}
		}

		return $count;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $type    The table name. Optional.
	 * @param   string  $prefix  The class prefix. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   3.1
	 */
	public function getTable($type = 'Tag', $prefix = 'TagsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
}
