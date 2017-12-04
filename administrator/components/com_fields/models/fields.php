<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Fields Model
 *
 * @since  3.7.0
 */
class FieldsModelFields extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   3.7.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'type', 'a.type',
				'name', 'a.name',
				'state', 'a.state',
				'access', 'a.access',
				'access_level',
				'language', 'a.language',
				'ordering', 'a.ordering',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'created_time', 'a.created_time',
				'created_user_id', 'a.created_user_id',
				'category_title',
				'category_id', 'a.category_id',
				'group_id', 'a.group_id',
				'assigned_cat_ids'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// List state information.
		parent::populateState('a.ordering', 'asc');

		$context = $this->getUserStateFromRequest($this->context . '.context', 'context', 'com_content.article', 'CMD');
		$this->setState('filter.context', $context);

		// Split context into component and optional section
		$parts = FieldsHelper::extract($context);

		if ($parts)
		{
			$this->setState('filter.component', $parts[0]);
			$this->setState('filter.section', $parts[1]);
		}
	}

	/**
	 * Method to get a store id based on the model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  An identifier string to generate the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   3.7.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.context');
		$id .= ':' . serialize($this->getState('filter.assigned_cat_ids'));
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.group_id');
		$id .= ':' . serialize($this->getState('filter.language'));

		return parent::getStoreId($id);
	}

	/**
	 * Method to get a JDatabaseQuery object for retrieving the data set from a database.
	 *
	 * @return  JDatabaseQuery   A JDatabaseQuery object to retrieve the data set.
	 *
	 * @since   3.7.0
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$user  = JFactory::getUser();
		$app   = JFactory::getApplication();

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.name, a.checked_out, a.checked_out_time, a.note' .
				', a.state, a.access, a.created_time, a.created_user_id, a.ordering, a.language' .
				', a.fieldparams, a.params, a.type, a.default_value, a.context, a.group_id' .
				', a.label, a.description, a.required'
			)
		);
		$query->from('#__fields AS a');

		// Join over the language
		$query->select('l.title AS language_title, l.image AS language_image')
			->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level')->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the users for the author.
		$query->select('ua.name AS author_name')->join('LEFT', '#__users AS ua ON ua.id = a.created_user_id');

		// Join over the field groups.
		$query->select('g.title AS group_title, g.access as group_access, g.state AS group_state');
		$query->join('LEFT', '#__fields_groups AS g ON g.id = a.group_id');

		// Filter by context
		if ($context = $this->getState('filter.context'))
		{
			$query->where('a.context = ' . $db->quote($context));
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access'))
		{
			if (is_array($access))
			{
				$access = ArrayHelper::toInteger($access);
				$query->where('a.access in (' . implode(',', $access) . ')');
			}
			else
			{
				$query->where('a.access = ' . (int) $access);
			}
		}

		if (($categories = $this->getState('filter.assigned_cat_ids')) && $context)
		{
			$categories = (array) $categories;
			$categories = ArrayHelper::toInteger($categories);
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				// Get the category
				$cat = JCategories::getInstance(str_replace('com_', '', $parts[0]));

				if ($cat)
				{
					foreach ($categories as $assignedCatIds)
					{
						// Check if we have the actual category
						$parent = $cat->get($assignedCatIds);

						if ($parent)
						{
							$categories[] = (int) $parent->id;

							// Traverse the tree up to get all the fields which are attached to a parent
							while ($parent->getParent() && $parent->getParent()->id != 'root')
							{
								$parent = $parent->getParent();
								$categories[] = (int) $parent->id;
							}
						}
					}
				}
			}

			$categories = array_unique($categories);

			// Join over the assigned categories
			$query->join('LEFT', $db->quoteName('#__fields_categories') . ' AS fc ON fc.field_id = a.id');

			if (in_array('0', $categories))
			{
				$query->where('(fc.category_id IS NULL OR fc.category_id IN (' . implode(',', $categories) . '))');
			}
			else
			{
				$query->where('fc.category_id IN (' . implode(',', $categories) . ')');
			}
		}

		// Implement View Level Access
		if (!$app->isClient('administrator') || !$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ') AND (a.group_id = 0 OR g.access IN (' . $groups . '))');
		}

		// Filter by state
		$state = $this->getState('filter.state');

		// Include group state only when not on on back end list
		$includeGroupState = !$app->isClient('administrator') ||
			$app->input->get('option') != 'com_fields' ||
			$app->input->get('view') != 'fields';
		if (is_numeric($state))
		{
			$query->where('a.state = ' . (int) $state);

			if ($includeGroupState)
			{
				$query->where('(a.group_id = 0 OR g.state = ' . (int) $state . ')');
			}
		}
		elseif (!$state)
		{
			$query->where('a.state IN (0, 1)');

			if ($includeGroupState)
			{
				$query->where('(a.group_id = 0 OR g.state IN (0, 1))');
			}
		}

		$groupId = $this->getState('filter.group_id');

		if (is_numeric($groupId))
		{
			$query->where('a.group_id = ' . (int) $groupId);
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (! empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'author:') === 0)
			{
				$search = $db->quote('%' . $db->escape(substr($search, 7), true) . '%');
				$query->where('(ua.name LIKE ' . $search . ' OR ua.username LIKE ' . $search . ')');
			}
			else
			{
				$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
				$query->where('(a.title LIKE ' . $search . ' OR a.name LIKE ' . $search . ' OR a.note LIKE ' . $search . ')');
			}
		}

		// Filter on the language.
		if ($language = $this->getState('filter.language'))
		{
			$language = (array) $language;

			foreach ($language as $key => $l)
			{
				$language[$key] = $db->quote($l);
			}

			$query->where('a.language in (' . implode(',', $language) . ')');
		}

		// Add the list ordering clause
		$listOrdering  = $this->state->get('list.ordering', 'a.ordering');
		$orderDirn     = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($listOrdering) . ' ' . $db->escape($orderDirn));		

		return $query;
	}

	/**
	 * Gets an array of objects from the results of database query.
	 *
	 * @param   string   $query       The query.
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @return  array  An array of results.
	 *
	 * @since   3.7.0
	 * @throws  RuntimeException
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$result = parent::_getList($query, $limitstart, $limit);

		if (is_array($result))
		{
			foreach ($result as $field)
			{
				$field->fieldparams = new Registry($field->fieldparams);
				$field->params = new Registry($field->params);
			}
		}

		return $result;
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  JForm|false  the JForm object or false
	 *
	 * @since   3.7.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form = parent::getFilterForm($data, $loadData);

		if ($form)
		{
			$form->setValue('context', null, $this->getState('filter.context'));
			$form->setFieldAttribute('group_id', 'context', $this->getState('filter.context'), 'filter');
			$form->setFieldAttribute('assigned_cat_ids', 'extension', $this->state->get('filter.component'), 'filter');
		}

		return $form;
	}

	/**
	 * Get the groups for the batch method
	 *
	 * @return  array  An array of groups
	 *
	 * @since   3.7.0
	 */
	public function getGroups()
	{
		$user       = JFactory::getUser();
		$viewlevels = ArrayHelper::toInteger($user->getAuthorisedViewLevels());

		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select('title AS text, id AS value, state');
		$query->from('#__fields_groups');
		$query->where('state IN (0,1)');
		$query->where('context = ' . $db->quote($this->state->get('filter.context')));
		$query->where('access IN (' . implode(',', $viewlevels) . ')');

		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
