<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('FieldsHelperInternal', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/internal.php');
JLoader::register('JFolder', JPATH_LIBRARIES . '/joomla/filesystem/folder.php');

/**
 * FieldsHelper
 *
 * @since  3.7.0
 */
class FieldsHelper
{
	private static $fieldsCache = null;

	private static $fieldCache = null;

	/**
	 * Extracts the component and section from the context string which has to
	 * be in the format component.context.
	 *
	 * @param   string  $contextString  contextString
	 *
	 * @return  array|null
	 *
	 * @since   3.7.0
	 */
	public static function extract($contextString)
	{
		$parts = explode('.', $contextString, 2);

		if (count($parts) < 2)
		{
			return null;
		}

		return $parts;
	}

	/**
	 * Returns the fields for the given context.
	 * If the item is an object the returned fields do have an additional field
	 * "value" which represents the value for the given item. If the item has an
	 * assigned_cat_ids field, then additionally fields which belong to that
	 * category will be returned.
	 * Should the value being prepared to be shown in an HTML context then
	 * prepareValue must be set to true. No further escaping needs to be done.
	 * The values of the fields can be overridden by an associative array where the keys
	 * can be an id or an alias and it's corresponding value.
	 *
	 * @param   string    $context           The context of the content passed to the helper
	 * @param   stdClass  $item              item
	 * @param   boolean   $prepareValue      prepareValue
	 * @param   array     $valuesToOverride  The values to override
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getFields($context, $item = null, $prepareValue = false, array $valuesToOverride = null)
	{
		if (self::$fieldsCache === null)
		{
			// Load the model
			JLoader::import('joomla.application.component.model');
			JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/models', 'FieldsModel');

			self::$fieldsCache = JModelLegacy::getInstance('Fields', 'FieldsModel', array(
				'ignore_request' => true)
			);

			self::$fieldsCache->setState('filter.state', 1);
			self::$fieldsCache->setState('list.limit', 0);
		}

		if (is_array($item))
		{
			$item = (object) $item;
		}
		if (JLanguageMultilang::isEnabled() && isset($item->language) && $item->language !='*')
		{
			self::$fieldsCache->setState('filter.language', array('*', $item->language));
		}
		self::$fieldsCache->setState('filter.context', $context);

		/*
		 * If item has assigned_cat_ids parameter display only fields which
		 * belong to the category
		 */
		if ($item && (isset($item->catid) || isset($item->fieldscatid)))
		{
			$assignedCatIds = isset($item->catid) ? $item->catid : $item->fieldscatid;
			self::$fieldsCache->setState('filter.assigned_cat_ids', is_array($assignedCatIds) ? $assignedCatIds : explode(',', $assignedCatIds));
		}

		$fields = self::$fieldsCache->getItems();

		if ($item && isset($item->id))
		{
			if (self::$fieldCache === null)
			{
				self::$fieldCache = JModelLegacy::getInstance('Field', 'FieldsModel', array(
					'ignore_request' => true)
				);
			}

			$new = array();

			foreach ($fields as $key => $original)
			{
				/*
				 * Doing a clone, otherwise fields for different items will
				 * always reference to the same object
				 */
				$field = clone $original;

				if ($valuesToOverride && key_exists($field->alias, $valuesToOverride))
				{
					$field->value = $valuesToOverride[$field->alias];
				}
				elseif ($valuesToOverride && key_exists($field->id, $valuesToOverride))
				{
					$field->value = $valuesToOverride[$field->id];
				}
				else
				{
					$field->value = self::$fieldCache->getFieldValue($field->id, $field->context, $item->id);
				}

				if ($field->value === '' || $field->value === null)
				{
					$field->value = $field->default_value;
				}

				$field->rawvalue = $field->value;

				if ($prepareValue)
				{
					$value = null;

					/*
					 * On before field prepare
					 * Event allow plugins to modfify the output of the field before it is prepared
					 */
					$dispatcher = JEventDispatcher::getInstance();
					$dispatcher->trigger('onFieldBeforePrepare', array($context, $item, &$field));

					// Prepare the value from the type layout
					$value = self::render($context, 'field.prepare.' . $field->type, array('field' => $field));

					// If the value is empty, render the base layout
					if (! $value)
					{
						$value = self::render($context, 'field.prepare.base', array('field' => $field));
					}

					/*
					 * On after field render
					 * Event allow plugins to modfify the output of the prepared field
					 */
					$dispatcher->trigger('onFieldAfterPrepare', array($context, $item, $field, &$value));
					$field->value = $value;
				}

				$new[$key] = $field;
			}

			$fields = $new;
		}

		return $fields;
	}

	/**
	 * Renders the layout file and data on the context and does a fall back to
	 * Fields afterwards.
	 *
	 * @param   string  $context      The context of the content passed to the helper
	 * @param   string  $layoutFile   layoutFile
	 * @param   array   $displayData  displayData
	 *
	 * @return  NULL|string
	 *
	 * @since  3.7.0
	 */
	public static function render($context, $layoutFile, $displayData)
	{
		$value = '';

		/*
		 * Because the layout refreshes the paths before the render function is
		 * called, so there is no way to load the layout overrides in the order
		 * template -> context -> fields.
		 * If there is no override in the context then we need to call the
		 * layout from Fields.
		 */
		if ($parts = self::extract($context))
		{
			// Trying to render the layout on the component fom the context
			$value = JLayoutHelper::render($layoutFile, $displayData, null, array('component' => $parts[0], 'client' => 0));
		}

		if ($value == '')
		{
			// Trying to render the layout on Fields itself
			$value = JLayoutHelper::render($layoutFile, $displayData, null, array('component' => 'com_fields','client' => 0));
		}

		if ($value == '')
		{
			// Trying to render the layout of the plugins
			foreach (JFolder::listFolderTree(JPATH_PLUGINS . '/fields', '.', 1) as $folder)
			{
				$value = JLayoutHelper::render($layoutFile, $displayData, $folder['fullname'] . '/layouts');
			}
		}

		return $value;
	}

	/**
	 * PrepareForm
	 *
	 * @param   string  $context  The context of the content passed to the helper
	 * @param   JForm   $form     form
	 * @param   object  $data     data.
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public static function prepareForm($context, JForm $form, $data)
	{
		// Extracting the component and section
		$parts = self::extract($context);

		if (! $parts)
		{
			return true;
		}

		// When no fields available return here
		$fields = self::getFields($parts[0] . '.' . $parts[1], new JObject);

		if (! $fields)
		{
			return true;
		}

		$component = $parts[0];
		$section   = $parts[1];

		$assignedCatids = isset($data->catid) ? $data->catid : (isset($data->fieldscatid) ? $data->fieldscatid : null);

		if (!$assignedCatids && $form->getField('catid'))
		{
			// Choose the first category available
			$xml = new DOMDocument;
			$xml->loadHTML($form->getField('catid')->__get('input'));
			$options = $xml->getElementsByTagName('option');

			if ($firstChoice = $options->item(0))
			{
				$assignedCatids = $firstChoice->getAttribute('value');
				$data->fieldscatid = $assignedCatids;
			}
		}

		/*
		 * If there is a catid field we need to reload the page when the catid
		 * is changed
		 */
		if ($form->getField('catid') && $parts[0] != 'com_fields')
		{
			// The uri to submit to
			$uri = clone JUri::getInstance('index.php');

			/*
			 * Removing the catid parameter from the actual url and set it as
			 * return
			*/
			$returnUri = clone JUri::getInstance();
			$returnUri->setVar('catid', null);
			$uri->setVar('return', base64_encode($returnUri->toString()));

			// Setting the options
			$uri->setVar('option', 'com_fields');
			$uri->setVar('task', 'field.storeform');
			$uri->setVar('context', $parts[0] . '.' . $parts[1]);
			$uri->setVar('formcontrol', $form->getFormControl());
			$uri->setVar('view', null);
			$uri->setVar('layout', null);

			/*
			 * Setting the onchange event to reload the page when the category
			 * has changed
			*/
			$form->setFieldAttribute('catid', 'onchange', 'categoryHasChanged(this);');
			JFactory::getDocument()->addScriptDeclaration(
					"function categoryHasChanged(element){
				var cat = jQuery(element);
				if (cat.val() == '" . $assignedCatids . "')return;
				jQuery('input[name=task]').val('field.storeform');
				element.form.action='" . $uri . "';
				element.form.submit();
			}
			jQuery( document ).ready(function() {
				var formControl = '#" . $form->getFormControl() . "_catid';
				if (!jQuery(formControl).val() != '" . $assignedCatids . "'){jQuery(formControl).val('" . $assignedCatids . "');}
			});");
		}

		// Getting the fields
		$fields = self::getFields($parts[0] . '.' . $parts[1], $data);

		if (!$fields)
		{
			return true;
		}

		$fieldTypes = FieldsHelperInternal::getFieldTypes();

		// Creating the dom
		$xml = new DOMDocument('1.0', 'UTF-8');
		$fieldsNode = $xml->appendChild(new DOMElement('form'))->appendChild(new DOMElement('fields'));
		$fieldsNode->setAttribute('name', 'params');

		// Organizing the fields according to their group
		$fieldsPerGroup = array(
			0 => array()
		);

		foreach ($fields as $field)
		{
			if (!array_key_exists($field->type, $fieldTypes))
			{
				// Field type is not available
				continue;
			}

			if (!array_key_exists($field->group_id, $fieldsPerGroup))
			{
				$fieldsPerGroup[$field->group_id] = array();
			}

			if ($path = $fieldTypes[$field->type]['path'])
			{
				// Add the lookup path for the field
				JFormHelper::addFieldPath($path);
			}

			$fieldsPerGroup[$field->group_id][] = $field;
		}

		// On the front, sometimes the admin fields path is not included
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/tables');

		// Looping trough the groups
		foreach ($fieldsPerGroup as $group_id => $groupFields)
		{
			if (!$groupFields)
			{
				continue;
			}

			// Defining the field set
			/** @var DOMElement $fieldset */
			$fieldset = $fieldsNode->appendChild(new DOMElement('fieldset'));
			$fieldset->setAttribute('name', 'fields-' . $group_id);
			$fieldset->setAttribute('addfieldpath', '/administrator/components/' . $component . '/models/fields');
			$fieldset->setAttribute('addrulepath', '/administrator/components/' . $component . '/models/rules');

			$label       = '';
			$description = '';

			if ($group_id)
			{
				$group = JTable::getInstance('Group', 'FieldsTable');
				$group->load($group_id);

				if ($group->id)
				{
					$label       = $group->title;
					$description = $group->description;
				}
			}

			if (!$label || !$description)
			{
				$lang = JFactory::getLanguage();

				if (!$label)
				{
					$key = strtoupper($component . '_FIELDS_' . $section . '_LABEL');

					if (!$lang->hasKey($key))
					{
						$key = 'JGLOBAL_FIELDS';
					}

					$label = JText::_($key);
				}

				if (!$description)
				{
					$key = strtoupper($component . '_FIELDS_' . $section . '_DESC');

					if ($lang->hasKey($key))
					{
						$description = JText::_($key);
					}
				}
			}

			$fieldset->setAttribute('label', $label);
			$fieldset->setAttribute('description', strip_tags($description));

			// Looping trough the fields for that context
			foreach ($groupFields as $field)
			{
				try
				{
					JEventDispatcher::getInstance()->trigger('onCustomFieldsPrepareDom', array($field, $fieldset, $form));

					/*
					 * If the field belongs to an assigned_cat_id but the assigned_cat_ids in the data
					 * is not known, set the required flag to false on any circumstance.
					 */
					if (! $assignedCatids && $field->assigned_cat_ids && $form->getField($field->alias))
					{
						$form->setFieldAttribute($field->alias, 'required', 'false');
					}
				}
				catch (Exception $e)
				{
					JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
				}
			}
		}

		// Loading the XML fields string into the form
		$form->load($xml->saveXML());

		$model = JModelLegacy::getInstance('Field', 'FieldsModel', array(
				'ignore_request' => true)
		);

		if ((!isset($data->id) || !$data->id) && JFactory::getApplication()->input->getCmd('controller') == 'config.display.modules'
			&& JFactory::getApplication()->isClient('site'))
		{
			// Modules on front end editing don't have data and an id set
			$data->id = JFactory::getApplication()->input->getInt('id');
		}

		// Looping trough the fields again to set the value
		if (!isset($data->id) || !$data->id)
		{
			return true;
		}

		foreach ($fields as $field)
		{
			$value = $model->getFieldValue($field->id, $field->context, $data->id);

			if ($value === null)
			{
				continue;
			}

			if (!is_array($value) && $value !== '')
			{
				// Function getField doesn't cache the fields, so we try to do it only when necessary
				$formField = $form->getField($field->alias, 'params');

				if ($formField && $formField->forceMultiple)
				{
					$value = (array) $value;
				}
			}

			// Setting the value on the field
			$form->setValue($field->alias, 'params', $value);
		}

		return true;
	}

	/**
	 * Return a boolean if the actual logged in user can edit the given field value.
	 *
	 * @param   stdClass  $field  The field
	 *
	 * @return  boolean
	 *
	 * @since   3.7.0
	 */
	public static function canEditFieldValue($field)
	{
		$parts = self::extract($field->context);

		return JFactory::getUser()->authorise('core.edit.value', $parts[0] . '.field.' . (int) $field->id);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   stdClass[]  &$items  The field category objects
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.7.0
	 */
	public static function countItems(&$items)
	{
		$db = JFactory::getDbo();

		foreach ($items as $item)
		{
			$item->count_trashed     = 0;
			$item->count_archived    = 0;
			$item->count_unpublished = 0;
			$item->count_published   = 0;

			$query = $db->getQuery(true);
			$query->select('state, count(1) AS count')
				->from($db->quoteName('#__fields'))
				->where('group_id = ' . (int) $item->id)
				->group('state');
			$db->setQuery($query);

			$fields = $db->loadObjectList();

			$states = array(
				'-2' => 'count_trashed',
				'0'  => 'count_unpublished',
				'1'  => 'count_published',
				'2'  => 'count_archived',
			);

			foreach ($fields as $field)
			{
				$property = $states[$field->state];
				$item->$property = $field->count;
			}
		}

		return $items;
	}

	/**
	 * Gets the fields system plugin extension id.
	 *
	 * @return  int  The fields system plugin extension id.
	 *
	 * @since   3.7.0
	 */
	public static function getFieldsPluginId()
	{
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select($db->quoteName('extension_id'))
		->from($db->quoteName('#__extensions'))
		->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
		->where($db->quoteName('element') . ' = ' . $db->quote('fields'));
		$db->setQuery($query);

		try
		{
			$result = (int) $db->loadResult();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());
			$result = 0;
		}

		return $result;
	}
}
