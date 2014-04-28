<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblinks model.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       1.5
 */
class WeblinksModelWeblink extends JModelAdministrator
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{	
			$config['filter_fields'][] = array('name' => 'id', 'dataKeyName' => 'a.id', 'sortable' => true, 'searchable' => true);
			$config['filter_fields'][] = array('name' => 'title', 'dataKeyName' => 'a.title', 'sortable' => true, 'searchable' => true);
			$config['filter_fields'][] = array('name' => 'alias', 'dataKeyName' => 'a.alias', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'checked_out', 'dataKeyName' => 'a.checked_out', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'checked_out_time', 'dataKeyName' => 'a.checked_out_time', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'category_id', 'dataKeyName' => 'a.catid', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'category_title', 'dataKeyName' => 'c.title', 'sortable' => false, 'searchable' => true);
			$config['filter_fields'][] = array('name' => 'state', 'dataKeyName' => 'a.state', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'access', 'dataKeyName' => 'a.access', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'access_level', 'dataKeyName' => 'ag.title', 'sortable' => true, 'searchable' => true);
			$config['filter_fields'][] = array('name' => 'created', 'dataKeyName' => 'a.created', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'created_by', 'dataKeyName' => 'a.created_by', 'sortable' => true, 'searchable' => true);
			$config['filter_fields'][] = array('name' => 'ordering', 'dataKeyName' => 'a.ordering', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'featured', 'dataKeyName' => 'a.featured', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'language', 'dataKeyName' => 'a.language', 'sortable' => true, 'searchable' => true);
			$config['filter_fields'][] = array('name' => 'hits', 'dataKeyName' => 'a.hits', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'publish_up', 'dataKeyName' => 'a.publish_up', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'publish_down', 'dataKeyName' => 'a.publish_down', 'sortable' => true, 'searchable' => false);
			$config['filter_fields'][] = array('name' => 'url', 'dataKeyName' => 'a.url', 'sortable' => true, 'searchable' => false);
		}
	
		parent::__construct($config);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.6
	 */
	protected function getListQuery(JDatabaseQuery $query = null)
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();
	
		// Select the required fields from the table.
		$query->select(
				$this->getState(
						'list.select',
						'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.catid,' .
						'a.hits,' .
						'a.state, a.access, a.ordering,' .
						'a.language, a.publish_up, a.publish_down'
				)
		);
		$query->from($db->quoteName('#__weblinks') . ' AS a');
	
		// Join over the language
		$query->select('l.title AS language_title')
		->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');
	
		// Join over the users for the checked out user.
		$query->select('uc.name AS editor')
		->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');
	
		// Join over the asset groups.
		$query->select('ag.title AS access_level')
		->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
	
		// Join over the categories.
		$query->select('c.title AS category_title')
		->join('LEFT', '#__categories AS c ON c.id = a.catid');
	
		
		// Implement View Level Access
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}
	
	
		$tagId = $this->getState('filter.tag');
		// Filter by a single tag.
		if (is_numeric($tagId))
		{
			$query->where($db->quoteName('tagmap.tag_id') . ' = ' . (int) $tagId)
			->join(
					'LEFT', $db->quoteName('#__contentitem_tag_map', 'tagmap')
					. ' ON ' . $db->quoteName('tagmap.content_item_id') . ' = ' . $db->quoteName('a.id')
					. ' AND ' . $db->quoteName('tagmap.type_alias') . ' = ' . $db->quote('com_weblinks.weblink')
			);
		}
	
		// add the default filters
		$query = parent::getListQuery($query);
		
		return $query;
	}
	
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->getContext() . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);
	
		$accessId = $this->getUserStateFromRequest($this->getContext() . '.filter.access', 'filter_access', null, 'string');
		$this->setState('filter.access', $accessId);
	
		$published = $this->getUserStateFromRequest($this->getContext() . '.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $published);
	
		$categoryId = $this->getUserStateFromRequest($this->getContext() . '.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);
	
		$language = $this->getUserStateFromRequest($this->getContext() . '.filter.language', 'filter_language', '');
		$this->setState('filter.language', $language);
	
		$tag = $this->getUserStateFromRequest($this->getContext() . '.filter.tag', 'filter_tag', '');
		$this->setState('filter.tag', $tag);
	
		// Load the parameters.
		$params = JComponentHelper::getParams('com_weblinks');
		$this->setState('params', $params);
	
		// List state information.
		parent::populateState('a.title', 'asc');
	}

	
	
//---------------------------- OLD FUNCTIONS -------------------------------//
	/**
	 * The type alias for this content type.
	 *
	 * @var      string
	 * @since    3.2
	 */
	public $typeAlias = 'com_weblinks.weblink';

	/**
	 * The prefix to use with controller messages.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_WEBLINKS';

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id))
		{
			if ($record->state != -2)
			{
				return;
			}

			if ($record->catid)
			{
				return $this->allowAction('core.delete', 'com_weblinks.category.'.(int) $record->catid);
			}
			else
			{
				return $this->allowAction('core.delete');
			}
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 * NOTE: Not used in single task controller models.
	 * Kept here to prevent unintended side effects with the categories implementation.
	 * 
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		if (!empty($record->catid))
		{
			return $this->allowAction('core.edit.state', 'com_weblinks.category.'.(int) $record->catid);
		}
		else
		{
			return $this->allowAction('core.edit.state');
		}
	}

	// removed getTable form as its implemented in the single task MVC model

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = parent::getForm($data, $loadData);
		
		if (empty($form))
		{
			return false;
		}

		// Determine correct permissions to check.
		if ($this->getState('weblink.id'))
		{
			// Existing record. Can only edit in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.edit');
		}
		else
		{
			// New record. Can only create in selected categories.
			$form->setFieldAttribute('catid', 'action', 'core.create');
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');
			$form->setFieldAttribute('publish_up', 'disabled', 'true');
			$form->setFieldAttribute('publish_down', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
			$form->setFieldAttribute('publish_up', 'filter', 'unset');
			$form->setFieldAttribute('publish_down', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 *
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = parent::loadFormData();
		
		if (empty($data))
		{
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('weblink.id') == 0)
			{
				$app = JFactory::getApplication();
				$data->set('catid', $app->input->get('catid', $app->getUserState('com_weblinks.weblinks.filter.category_id'), 'int'));
			}
		}

		$this->preprocessData('com_weblinks.weblink', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed  Object on success, false on failure.
	 *
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Convert the metadata field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();

			// Convert the images field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->images);
			$item->images = $registry->toArray();

			if (!empty($item->id))
			{
				$item->tags = new JHelperTags;
				$item->tags->getTagIds($item->id, 'com_weblinks.weblink');
				$item->metadata['tags'] = $item->tags;
			}
		}

		return $item;
	}

	/**
	 * Prepare and sanitise the table data prior to saving.
	 *
	 * @param   JTable  $table  A reference to a JTable object.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function prepareTable($table)
	{
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->title = htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias = JApplication::stringURLSafe($table->alias);

		if (empty($table->alias))
		{
			$table->alias = JApplication::stringURLSafe($table->title);
		}

		if (empty($table->id))
		{
			// Set the values

			// Set ordering to the last item if not set
			if (empty($table->ordering))
			{
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__weblinks');
				$max = $db->loadResult();

				$table->ordering = $max + 1;
			}
			else
			{
				// Set the values
				$table->modified    = $date->toSql();
				$table->modified_by = $user->get('id');
			}
		}

		// Increment the weblink version number.
		$table->version++;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   1.6
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = ' . (int) $table->catid;

		return $condition;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since	3.1
	 */
	public function save($data)
	{
		$app = JFactory::getApplication();

		// Alter the title for save as copy
		if ($app->input->get('task') == 'save2copy')
		{
			list($name, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
			$data['title']	= $name;
			$data['alias']	= $alias;
			$data['state']	= 0;
		}

		return parent::save($data);
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the parent.
	 * @param   string   $alias        The alias.
	 * @param   string   $name         The title.
	 *
	 * @return  array  Contains the modified title and alias.
	 *
	 * @since   3.1
	 */
	protected function generateNewTitle($category_id, $alias, $name)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias, 'catid' => $category_id)))
		{
			if ($name == $table->title)
			{
				$name = JString::increment($name);
			}

			$alias = JString::increment($alias, 'dash');
		}

		return array($name, $alias);
	}
}
