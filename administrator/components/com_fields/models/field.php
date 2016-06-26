<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Field Model
 *
 * @since  3.7
 */
class FieldsModelField extends JModelAdmin
{

	protected $text_prefix = 'COM_FIELDS';

	public $typeAlias = null;

	private $valueCache = array();

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JModelLegacy
	 * @since   12.2
	 */
	public function __construct ($config = array())
	{
		parent::__construct($config);
		$context = JFactory::getApplication()->input->getCmd('context', 'com_content.article');
		$this->typeAlias = $context . '.field';
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canDelete ($record)
	{
		if (! empty($record->id))
		{
			if ($record->state != - 2)
			{
				return;
			}

			$user = JFactory::getUser();

			return $user->authorise('core.delete', $record->context . '.field.' . (int) $record->id);
		}
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
	 *
	 * @since   12.2
	 */
	protected function canEditState ($record)
	{
		$user = JFactory::getUser();

		// Check for existing field.
		if (! empty($record->id))
		{
			return $user->authorise('core.edit.state', $record->context . '.field.' . (int) $record->id);
		}
		else
		{
			return $user->authorise('core.edit.state', $record->context);
		}
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   12.2
	 */
	public function save ($data)
	{
		$field = null;
		if (isset($data['id']) && $data['id'])
		{
			$field = $this->getItem($data['id']);
		}

		if (! isset($data['assigned_cat_ids']))
		{
			$data['assigned_cat_ids'] = array();
		}
		else
		{
			$cats = (array) $data['assigned_cat_ids'];
			foreach ($cats as $key => $c)
			{
				if (empty($c))
				{
					unset($cats[$key]);
				}
			}
			$data['assigned_cat_ids'] = $cats;
		}

		if (!isset($data['label']) && isset($data['params']['label']))
		{
			$data['label'] = $data['params']['label'];
			unset($data['params']['label']);
		}

		// Alter the title for save as copy
		$input  = JFactory::getApplication()->input;
		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();
			$origTable->load($input->getInt('id'));

			if ($data['title'] == $origTable->title)
			{
				list($title, $alias) = $this->generateNewTitle($data['catid'], $data['alias'], $data['title']);
				$data['title'] = $title;
				$data['alias'] = $alias;
			}
			else
			{
				if ($data['alias'] == $origTable->alias)
				{
					$data['alias'] = '';
				}
			}

			$data['state'] = 0;
		}

		JLoader::register('CategoriesHelper', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categories.php');

			// Cast catid to integer for comparison
		$catid = (int) $data['catid'];

		// Check if New Category exists
		if ($catid > 0)
		{
			$catid = CategoriesHelper::validateCategoryId($data['catid'], $data['context'] . '.fields');
		}

		// Save New Category
		if ($catid === 0 && is_string($data['catid']) && $data['catid'] != '')
		{
			$table = array();
			$table['title'] = $data['catid'];
			$table['parent_id'] = 1;
			$table['extension'] = $data['context'] . '.fields';
			$table['language'] = $data['language'];
			$table['published'] = 1;

			// Create new category and get catid back
			$data['catid'] = CategoriesHelper::createCategory($table);
		}

		if ($data['catid'] === '')
		{
			$data['catid'] = '0';
		}
		$success = parent::save($data);

		// If the options have changed delete the values
		if ($success && $field && isset($data['fieldparams']['options']) && isset($field->fieldparams['options']))
		{
			$oldParams = json_decode($field->fieldparams['options']);
			$newParams = json_decode($data['fieldparams']['options']);
			if (is_array($oldParams) && is_array($newParams) && count(array_intersect($oldParams->key, $newParams->key)) != count($oldParams->key))
			{
				$this->_db->setQuery(
						'delete from #__fields_values where field_id = ' . (int) $field->id . ' and value not in (\'' .
							implode("','", $newParams->key) . '\')');
				$this->_db->query();
			}
		}

		return $success;
	}

	/**
	 * Method to delete one or more records.
	 *
	 * @param   array  &$pks  An array of record primary keys.
	 *
	 * @return  boolean  True if successful, false if an error occurs.
	 *
	 * @since   12.2
	 */
	public function delete (&$pks)
	{
		$success = parent::delete($pks);
		if ($success)
		{
			$pks = (array) $pks;
			$pks = ArrayHelper::toInteger($pks);
			$this->_db->setQuery('delete from #__fields_values where field_id in (' . implode(',', $pks) . ')');
			$this->_db->query();
		}
		return $success;
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   12.2
	 * @throws  Exception
	 */
	public function getTable ($name = 'Field', $prefix = 'FieldsTable', $options = array())
	{
		if (strpos(JPATH_COMPONENT, 'com_fields') === false)
		{
			$this->addTablePath(JPATH_ADMINISTRATOR . '/components/com_fields/tables');
		}
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Stock method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function populateState ()
	{
		$app = JFactory::getApplication('administrator');

		// Load the User state.
		$pk = $app->input->getInt('id');
		$this->setState($this->getName() . '.id', $pk);

		$context = $app->input->get('context', 'com_content.article');
		$this->setState('field.context', $context);
		$parts = FieldsHelper::extract($context);

		// Extract the component name
		$this->setState('field.component', $parts[0]);

		// Extract the optional section name
		$this->setState('field.section', (count($parts) > 1) ? $parts[1] : null);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_fields');
		$this->setState('params', $params);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   12.2
	 */
	public function getItem ($pk = null)
	{
		if ($result = parent::getItem($pk))
		{
			// Prime required properties.
			if (empty($result->id))
			{
				$result->context = JFactory::getApplication()->input->getCmd('context', $this->getState('field.context'));
			}

			if (property_exists($result, 'fieldparams'))
			{
				$registry = new Registry;
				$registry->loadString($result->fieldparams);
				$result->fieldparams = $registry->toArray();
			}

			if ($result->assigned_cat_ids)
			{
				$result->assigned_cat_ids = explode(',', $result->assigned_cat_ids);
			}

			// Convert the created and modified dates to local user time for
			// display in the form.
			$tz = new DateTimeZone(JFactory::getApplication()->get('offset'));

			if ((int) $result->created_time)
			{
				$date = new JDate($result->created_time);
				$date->setTimezone($tz);
				$result->created_time = $date->toSql(true);
			}
			else
			{
				$result->created_time = null;
			}

			if ((int) $result->modified_time)
			{
				$date = new JDate($result->modified_time);
				$date->setTimezone($tz);
				$result->modified_time = $date->toSql(true);
			}
			else
			{
				$result->modified_time = null;
			}

			if (! empty($result->id))
			{
				$result->tags = new JHelperTags;
				$result->tags->getTagIds($result->id, 'com_fields.field');
			}
		}

		return $result;
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   12.2
	 */
	public function getForm ($data = array(), $loadData = true)
	{
		$context = $this->getState('field.context');
		$jinput = JFactory::getApplication()->input;

		// A workaround to get the context into the model for save requests.
		if (empty($context) && isset($data['context']))
		{
			$context = $data['context'];
			$parts = explode('.', $context);

			$this->setState('field.context', $context);
			$this->setState('field.component', $parts[0]);
			$this->setState('field.section', @$parts[1]);
		}

		// Get the form.
		$form = $this->loadForm(
				'com_fields.field' . $context, 'field',
				array(
					'control' => 'jform',
					'load_data' => $loadData
				)
		);

		if (empty($form))
		{
			return false;
		}

		// Modify the form based on Edit State access controls.
		if (empty($data['context']))
		{
			$data['context'] = $context;
		}

		if (isset($data['type']))
		{
			$parts = explode('.', JFactory::getApplication()->input->getCmd('context', $this->getState('field.context')));
			$component = $parts[0];
			$this->loadTypeForms($form, $data['type'], $component);
		}

		if (! JFactory::getUser()->authorise('core.edit.state', $context . '.field.' . $jinput->get('id')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving. The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   12.2
	 */
	protected function getReorderConditions ($table)
	{
		return 'context = ' . $this->_db->quote($table->context);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   12.2
	 */
	protected function loadFormData ()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_fields.edit.field.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Language, Access) in edit form
			// if those have been selected in Category Manager
			if (! $data->id)
			{
				// Check for which context the Category Manager is used and
				// get selected fields
				$context = substr($app->getUserState('com_fields.fields.filter.context'), 4);
				$component = FieldsHelper::extract($context);
				$component = $component ? $component[0] : null;
				$filters = (array) $app->getUserState('com_fields.fields.' . $component . '.filter');

				$data->set('published', $app->input->getInt('published', (! empty($filters['published']) ? $filters['published'] : null)));
				$data->set('language', $app->input->getString('language', (! empty($filters['language']) ? $filters['language'] : null)));
				$data->set(
						'access',
						$app->input->getInt('access', (! empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
				);

				// Set the type if available from the request
				$data->set('type', $app->input->getWord('type', $data->get('type')));
			}

			if ($data->label && !isset($data->params['label']))
			{
				$data->params['label'] = $data->label;
			}
		}

		$this->preprocessData('com_fields.field', $data);

		return $data;
	}

	/**
	 * Method to allow derived classes to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   12.2
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm (JForm $form, $data, $group = 'content')
	{
		$parts = FieldsHelper::extract(JFactory::getApplication()->input->getCmd('context', $this->getState('field.context')));

		if ($parts)
		{
			$component = $parts[0];

			$dataObject = $data;
			if (is_array($dataObject))
			{
				$dataObject = (object) $dataObject;
			}
			if (isset($dataObject->type))
			{
				$this->loadTypeForms($form, $dataObject->type, $component);

				$form->setFieldAttribute('type', 'component', $component);

				// Not alowed to change the type of an existing record
				if ($dataObject->id)
				{
					$form->setFieldAttribute('type', 'readonly', 'true');
				}
			}

			// Setting the context for the category field
			$cat = JCategories::getInstance(str_replace('com_', '', $component));
			if ($cat && $cat->get('root')->hasChildren())
			{
				$form->setFieldAttribute('assigned_cat_ids', 'extension', $component);
			}
			else
			{
				$form->removeField('assigned_cat_ids');
			}

			$form->setFieldAttribute('type', 'component', $component);
			$form->setFieldAttribute('catid', 'extension', $component . '.' . $parts[1] . '.fields');
		}

		// Trigger the default form events.
		parent::preprocessForm($form, $data, $group);
	}

	/**
	 * Returning the value for the given field id, context and item id.
	 *
	 * @param   string  $fieldId  The field ID.
	 * @param   string  $context  The context.
	 * @param   string  $itemId   The ID of the item.
	 *
	 * @return  NULL|string
	 */
	public function getFieldValue ($fieldId, $context, $itemId)
	{
		$key = md5($fieldId . $context . $itemId);
		if (! key_exists($key, $this->valueCache))
		{
			$this->valueCache[$key] = null;
			$db = $this->_db;

			$query = 'select value from #__fields_values ';
			$query .= 'where field_id = ' . (int) $fieldId . ' and context = ' . $db->q($context) . ' and item_id = ' . $db->q($itemId) . ' ';
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if (count($rows) == 1)
			{
				$this->valueCache[$key] = $rows[0]->value;
			}
			elseif (count($rows) > 1)
			{
				$data = array();
				foreach ($rows as $row)
				{
					$data[] = $row->value;
				}
				$this->valueCache[$key] = $data;
			}
		}
		return $this->valueCache[$key];
	}

	/**
	 * Setting the value for the gven field id, context and item id.
	 *
	 * @param   string  $fieldId  The field ID.
	 * @param   string  $context  The context.
	 * @param   string  $itemId   The ID of the item.
	 * @param   string  $value    The value.
	 *
	 * @return boolean
	 */
	public function setFieldValue ($fieldId, $context, $itemId, $value)
	{
		$db = $this->_db;
		$field = $this->getItem($fieldId);
		$params = $field->params;
		if (is_array($params))
		{
			$params = new Registry($params);
		}

		// Don't save the value when the field is disabled or the user is
		// not authorized to change it
		if (! $field || $params->get('disabled', 0) || ! FieldsHelperInternal::canEditFieldValue($field))
		{
			return false;
		}

		$needsDelete = false;
		$needsInsert = false;
		$needsUpdate = false;
		if ($field->default_value == $value)
		{
			$needsDelete = true;
		}
		else
		{
			$oldValue = $this->getFieldValue($fieldId, $context, $itemId);
			$value = (array) $value;

			if ($oldValue === null)
			{
				// No records available, doing normal insert
				$needsInsert = true;
			}
			elseif (count($value) == 1 && count((array) $oldValue) == 1)
			{
				// Only a single row value update can be done
				$needsUpdate = true;
			}
			else
			{
				// Multiple values, we need to purge the data and do a new
				// insert
				$needsDelete = true;
				$needsInsert = true;
			}
		}

		if ($needsDelete)
		{
			// Deleting the existing record as it is a reset
			$query = 'delete from #__fields_values where ';
			$query .= 'field_id =' . (int) $fieldId . ' and context = ' . $db->q($context) . ' and item_id = ' . $db->q($itemId);

			$db->setQuery($query);
			$db->query();
		}
		if ($needsInsert)
		{
			$query = 'insert into #__fields_values (field_id, context, item_id, value) values ';

			foreach ($value as $v)
			{
				$query .= '(' . (int) $fieldId . ', ' . $db->q($context) . ', ' . $db->q($itemId) . ', ' . $db->q($v) . '),';
			}
			$query = trim($query, ',');

			$db->setQuery($query);
			$db->query();
		}
		if ($needsUpdate)
		{
			$query = 'update #__fields_values set value = ' . $db->q(reset($value)) . ' where ';
			$query .= 'field_id =' . (int) $fieldId . ' and context = ' . $db->q($context) . ' and item_id = ' . $db->q($itemId);

			$db->setQuery($query);
			$db->query();
		}
		$this->valueCache = array();

		return true;
	}

	/**
	 * Cleaning up the values for the given item on the context.
	 *
	 * @param   string  $context  The context.
	 * @param   string  $itemId   The Item ID.
	 *
	 * @return void
	 */
	public function cleanupValues ($context, $itemId)
	{
		$db = $this->_db;
		$db->setQuery('delete from #__fields_values where context = ' . $db->q($context) . ' and item_id = ' . $db->q($itemId));
		$db->query();
	}

	/**
	 * Batch tag a list of item.
	 *
	 * @param   integer  $value     The value of the new tag.
	 * @param   array    $pks       An array of row IDs.
	 * @param   array    $contexts  An array of item contexts.
	 *
	 * @return  void.
	 *
	 * @since   3.1
	 */
	protected function batchTag ($value, $pks, $contexts)
	{
		// Set the variables
		$user = JFactory::getUser();
		$table = $this->getTable();

		foreach ($pks as $pk)
		{
			if ($user->authorise('core.edit', $contexts[$pk]))
			{
				$table->reset();
				$table->load($pk);
				$tags = array(
						$value
				);

				/**
				 *
				 * @var JTableObserverTags $tagsObserver
				 */
				$tagsObserver = $table->getObserverOfClass('JTableObserverTags');
				$result = $tagsObserver->setNewTags($tags, false);

				if (! $result)
				{
					$this->setError($table->getError());

					return false;
				}
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_BATCH_CANNOT_EDIT'));

				return false;
			}
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Clean the cache
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   12.2
	 */
	protected function cleanCache ($group = null, $client_id = 0)
	{
		$context = JFactory::getApplication()->input->get('context');

		switch ($context)
		{
			case 'com_content':
				parent::cleanCache('com_content');
				parent::cleanCache('mod_articles_archive');
				parent::cleanCache('mod_articles_categories');
				parent::cleanCache('mod_articles_category');
				parent::cleanCache('mod_articles_latest');
				parent::cleanCache('mod_articles_news');
				parent::cleanCache('mod_articles_popular');
				break;
			default:
				parent::cleanCache($context);
				break;
		}
	}

	/**
	 * Method to change the title & alias.
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	12.2
	 */
	protected function generateNewTitle ($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('alias' => $alias)))
		{
			$title = StringHelper::increment($title);
			$alias = StringHelper::increment($alias, 'dash');
		}

		return array(
				$title,
				$alias
		);
	}

	/**
	 * Load the form declaration for the type.
	 *
	 * @param   JForm   &$form      The form
	 * @param   string  $type       The type
	 * @param   string  $component  The component
	 *
	 * @return void
	 */
	private function loadTypeForms (JForm &$form, $type, $component)
	{
		$type = JFormHelper::loadFieldType($type);

		// Load all children that's why we need to define the xpath
		if (!($type instanceof JFormDomfieldinterface))
		{
			return;
		}
		$form->load($type->getFormParameters(), true, '/form/*');
	}
}
