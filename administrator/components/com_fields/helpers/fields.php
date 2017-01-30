<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

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

		$component = $parts[0];
		$eName     = str_replace('com_', '', $component);

		$path = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($path))
		{
			$cName = ucfirst($eName) . 'Helper';

			JLoader::register($cName, $path);

			if (class_exists($cName) && is_callable(array($cName, 'validateSection')))
			{
				$section = call_user_func_array(array($cName, 'validateSection'), array($parts[1]));

				if ($section)
				{
					$parts[1] = $section;
				}
			}
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

			self::$fieldsCache = JModelLegacy::getInstance(
				'Fields', 'FieldsModel', array('ignore_request' => true,				        )
			);

			self::$fieldsCache->setState('filter.state', 1);
			self::$fieldsCache->setState('list.limit', 0);
		}

		if (is_array($item))
		{
			$item = (object) $item;
		}
		if (JLanguageMultilang::isEnabled() && isset($item->language) && $item->language !== '*')
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

			if (!is_array($assignedCatIds))
			{
				$assignedCatIds = explode(',', $assignedCatIds);
			}

			// Fields without any category assigned should show as well
			$assignedCatIds[] = 0;

			self::$fieldsCache->setState('filter.assigned_cat_ids', $assignedCatIds);
		}

		$fields = self::$fieldsCache->getItems();

		if ($fields === false)
		{
			return array();
		}

		if ($item && isset($item->id))
		{
			if (self::$fieldCache === null)
			{
				self::$fieldCache = JModelLegacy::getInstance(
					'Field', 'FieldsModel', array(
						       'ignore_request' => true,
					       )
				);
			}

			$new = array();

			$subFormFieldsIds = self::$fieldsCache->get('internal_subform_fields_ids', array());

			foreach ($fields as $key => $original)
			{
				/*
				 * Doing a clone, otherwise fields for different items will
				 * always reference to the same object
				 */
				$field = clone $original;

				if ($valuesToOverride && array_key_exists($field->alias, $valuesToOverride))
				{
					$field->value = $valuesToOverride[$field->alias];
				}
				elseif ($valuesToOverride && array_key_exists($field->id, $valuesToOverride))
				{
					$field->value = $valuesToOverride[$field->id];
				}
				else
				{
					if ($field->form_id === null)
					{
						$field->form_id = $field->form_id_res;
					}
					if ($field->type === 'subform')
					{
						$field->value = self::getSubFormValues($item, $field);
					}
					else
					{
						if (!in_array($field->id, $subFormFieldsIds, false))
						{
							$field->value = self::$fieldCache->getFieldValue($field->id, $field->context, $item->id, $field->form_id);
						}
						else
						{
							continue;
						}
					}
				}

				if ($field->value === '' || $field->value === null)
				{
					$field->value = $field->default_value;
				}

				$field->rawvalue = $field->value;

				if ($prepareValue)
				{
					JPluginHelper::importPlugin('fields');

					$dispatcher = JEventDispatcher::getInstance();

					// Event allow plugins to modify the output of the field before it is prepared
					$dispatcher->trigger('onCustomFieldsBeforePrepareField', array($context, $item, &$field));

					// Gathering the value for the field
					$value = $dispatcher->trigger('onCustomFieldsPrepareField', array($context, $item, &$field));

					if (is_array($value))
					{
						$value = implode($value, ' ');
					}

					// Event allow plugins to modify the output of the prepared field
					$dispatcher->trigger('onCustomFieldsAfterPrepareField', array($context, $item, $field, &$value));

					// Assign the value
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

		if ($value === '')
		{
			// Trying to render the layout on Fields itself
			$value = JLayoutHelper::render($layoutFile, $displayData, null, array('component' => 'com_fields', 'client' => 0));
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

		if (!$parts)
		{
			return true;
		}

		// When no fields available return here
		$fields = self::getFields($parts[0] . '.' . $parts[1], new JObject);

		if (!$fields)
		{
			return true;
		}

		$component = $parts[0];
		$section   = $parts[1];

		$assignedCatIds = isset($data->catid) ? $data->catid : (isset($data->fieldscatid) ? $data->fieldscatid : null);

		if (!$assignedCatIds && $form->getField('catid'))
		{
			// Choose the first category available
			$xml = new DOMDocument;
			$xml->loadHTML($form->getField('catid')->__get('input'));
			$options = $xml->getElementsByTagName('option');

			if ($firstChoice = $options->item(0))
			{
				$assignedCatIds    = $firstChoice->getAttribute('value');
				$data->fieldscatid = $assignedCatIds;
			}
		}

		/*
		 * If there is a catid field we need to reload the page when the catid
		 * is changed
		 */
		if ($parts[0] !== 'com_fields' && $form->getField('catid'))
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

			// Preload spindle-wheel when we need to submit form due to category selector changed
			JFactory::getDocument()->addScriptDeclaration(
				"
			function categoryHasChanged(element) {
				Joomla.loadingLayer('show');
				var cat = jQuery(element);
				if (cat.val() == '" . $assignedCatIds . "')return;
				jQuery('input[name=task]').val('field.storeform');
				element.form.action='" . $uri . "';
				element.form.submit();
			}
			jQuery( document ).ready(function() {
				Joomla.loadingLayer('load');
				var formControl = '#" . $form->getFormControl() . "_catid';
				if (!jQuery(formControl).val() != '" . $assignedCatIds . "'){jQuery(formControl).val('" . $assignedCatIds . "');}
			});"
			);
		}

		// Getting the fields
		$fields = self::getFields($parts[0] . '.' . $parts[1], $data);

		if (!$fields)
		{
			return true;
		}

		$fieldTypes = self::getFieldTypes();

		// Creating the dom
		$xml        = new DOMDocument('1.0', 'UTF-8');
		$fieldsNode = $xml->appendChild(new DOMElement('form'))->appendChild(new DOMElement('fields'));
		$fieldsNode->setAttribute('name', 'params');

		// On the front, sometimes the admin fields path is not included
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_fields/tables');

		$subFormToFormAssociation = array();


		$formHierarchy = self::getFormHierarchy($fields);

		$subFormFieldsIds = self::$fieldsCache->get('internal_subform_fields_ids', array());

		foreach ($formHierarchy['forms'] as $hFormId => $hForm)
		{

			foreach ($hForm['groups'] as $hGroupId => $hGroup)
			{

				if (($isSubForm = $hForm['subForm']) === false)
				{
					$fieldSet            = self::createFieldSet($fieldsNode, $hFormId, $hGroupId, $component);
					$fieldSets[$hFormId] = $fieldSet;
				}

				list($label, $description) = self::getLabelAndDescription($hFormId, $hGroupId, $component, $section, $formHierarchy);

				if (!$isSubForm)
				{
					$fieldSet->setAttribute('label', $label);
					$fieldSet->setAttribute('description', strip_tags($description));
				}


				foreach ($hGroup['fields'] as $hFieldId => $hField)
				{
					try
					{
						if ($hField->type === 'subform')
						{

							$subFormToFormAssociation[(int) $hField->fieldparams->get('subform_id')] = $hFormId;

							$subFormDoc = new DOMDocument;

							$subForm = $subFormDoc->createElement('form');

							foreach ($formHierarchy['forms'][(int) $hField->fieldparams->get('subform_id')]['groups'][0]['fields'] as $index => $subFormField)
							{
								JEventDispatcher::getInstance()->trigger('onCustomFieldsPrepareDom', array($subFormField, $subForm, $form));
							}

							$newSubFormNode                = $fieldsNode->ownerDocument->importNode($subForm, true);
							$newSubFormNode                = $fieldsNode->ownerDocument->saveXML($newSubFormNode);
							$hField->subFormFieldsToAttach = $newSubFormNode;
							JEventDispatcher::getInstance()->trigger('onCustomFieldsPrepareDom', array($hField, $fieldSet, $form));
						}
						elseif (!array_key_exists($hField->form_id, $subFormToFormAssociation))
						{
							JEventDispatcher::getInstance()->trigger('onCustomFieldsPrepareDom', array($hField, $fieldSet, $form));
						}

						/*
						 * If the field belongs to an assigned_cat_id but the assigned_cat_ids in the data
						 * is not known, set the required flag to false on any circumstance.
						 */
						if (!$assignedCatIds && !empty($hField->assigned_cat_ids) && $form->getField($hField->alias))
						{
							$form->setFieldAttribute($hField->alias, 'required', 'false');
						}
					}
					catch (Exception $e)
					{
						JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					}
				}

				if (!$isSubForm)
				{

					// When he field set is empty, then remove it
					if (!$fieldSet->hasChildNodes())
					{
						$fieldsNode->removeChild($fieldSet);
					}
				}
			}
		}

		// Loading the XML fields string into the form
		$form->load($xml->saveXML());

		$model = JModelLegacy::getInstance(
			'Field', 'FieldsModel', array(
				       'ignore_request' => true,
			       )
		);

		if ((!isset($data->id) || !$data->id) && JFactory::getApplication()->input->getCmd('controller') === 'config.display.modules'
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
			if ($field->type === 'subform')
			{
				$value = self::getSubFormValues($data, $field);
			}
			else
			{
				if (!in_array($field->id, $subFormFieldsIds, false))
				{
					$value = $model->getFieldValue($field->id, $field->context, $data->id, $field->form_id, false);
				}
				else
				{
					continue;
				}
			}

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
				$property        = $states[$field->state];
				$item->$property = $field->count;
			}
		}

		return $items;
	}

	/**
	 * Gets assigned categories titles for a field
	 *
	 * @param   stdClass[]  $fieldId  The field ID
	 *
	 * @return  array  Array with the assigned categories
	 *
	 * @since   3.7.0
	 */
	public static function getAssignedCategoriesTitles($fieldId)
	{
		$fieldId = (int) $fieldId;

		if (!$fieldId)
		{
			return array();
		}

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName('c.title'))
			->from($db->quoteName('#__fields_forms_categories', 'a'))
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON a.category_id = c.id')
			->where('form_id = ' . $fieldId);

		$db->setQuery($query);

		return $db->loadColumn();
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

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $context  The context the fields are used for
	 * @param   string  $vName    The view currently active
	 *
	 * @return  void
	 *
	 * @since    3.7.0
	 */
	public static function addSubmenu($context, $vName)
	{
		$parts = self::extract($context);

		if (!$parts)
		{
			return;
		}

		$component = $parts[0];

		// Avoid nonsense situation.
		if ($component === 'com_fields')
		{
			return;
		}

		// Try to find the component helper.
		$eName = str_replace('com_', '', $component);
		$file  = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (!file_exists($file))
		{
			return;
		}

		require_once $file;

		$cName = ucfirst($eName) . 'Helper';

		if (class_exists($cName) && is_callable(array($cName, 'addSubmenu')))
		{
			$lang = JFactory::getLanguage();
			$lang->load($component, JPATH_ADMINISTRATOR)
			|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

			$cName::addSubmenu('fields.' . $vName);
		}
	}

	/**
	 * Loads the fields plugins and returns an array of field types from the plugins.
	 *
	 * The returned array contains arrays with the following keys:
	 * - label: The label of the field
	 * - type:  The type of the field
	 * - path:  The path of the folder where the field can be found
	 *
	 * @return  array
	 *
	 * @since   3.7.0
	 */
	public static function getFieldTypes()
	{
		JPluginHelper::importPlugin('fields');
		$eventData = JEventDispatcher::getInstance()->trigger('onCustomFieldsGetTypes');

		$data = array();

		foreach ($eventData as $fields)
		{
			foreach ($fields as $fieldDescription)
			{
				if (!array_key_exists('path', $fieldDescription))
				{
					$fieldDescription['path'] = null;
				}
				$data[$fieldDescription['type']] = $fieldDescription;
			}
		}

		return $data;
	}

    /**
     * Get a form hierarchy to easier build the forms, sub-forms, groups and fields
     *
     * @param   array  $fields  The array of fields to build the hierarchy
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
	public static function getFormHierarchy($fields)
	{
		$formData = array();

		foreach ($fields as $tempField)
		{
			$formData['forms'][$formId = $tempField->form_id_res]['groups'][$groupId = $tempField->group_id]['fields'][$tempField->id] = $tempField;

			$formData['forms'][$formId]['ordering']                     = $tempField->form_ordering;
			$formData['forms'][$formId]['title']                        = $tempField->form_title;
			$formData['forms'][$formId]['groups'][$groupId]['ordering'] = $tempField->group_ordering;

			if (!array_key_exists('subForm', $formData['forms'][$formId]))
			{
				$formData['forms'][$formId]['subForm'] = false;
			}
			if ($tempField->type === 'subform' && ($subFormId = $tempField->fieldparams->get('subform_id')) > 0)
			{
				$formData['forms'][$subFormId]['subForm'] = true;
			}
		}

		unset($tempField);

		// Order forms by form_ordering, but order subforms to be the last ones, nevertheless.
		if (count($formData['forms']) > 1)
		{
			uasort(
				$formData['forms'], function ($a, $b)
				{
					$aVal = $a['subForm'] === true ? (int) $a['ordering'] + 1000 : (int) $a['ordering'];
					$bVal = $b['subForm'] === true ? (int) $b['ordering'] + 1000 : (int) $b['ordering'];

					if ($aVal === $bVal)
					{
						return 0;
					}

					return ($aVal < $bVal) ? -1 : 1;
				}
			);
		}

		// Order groups
		foreach ($formData['forms'] as $tempKey => &$tempForm)
		{
			if (count($tempForm['groups']) > 1)
			{
				uasort(
					$tempForm['groups'], function ($a, $b)
					{
						$aVal = $a['ordering'];
						$bVal = $b['ordering'];

						if ($aVal === $bVal)
						{
							return 0;
						}

						return ($aVal < $bVal) ? -1 : 1;
					}
				);
			}

			// Order fields
			foreach ($tempForm['groups'] as $tempKey3 => &$tempGroup)
			{
				if (count($tempGroup['fields']) > 1)
				{
					uasort(
						$tempGroup['fields'], function ($a, $b)
						{
							$aVal = $a->ordering;
							$bVal = $b->ordering;

							if ($aVal === $bVal)
							{
								return 0;
							}

							return ($aVal < $bVal) ? -1 : 1;
						}
					);
				}
			}
		}
		unset($tempForm, $tempGroup);

		// Now set some often needed info in the fieldsCache.

		self::$fieldsCache->set('internal_hierarchy', $formData);

		$subFormFields = $subForms = $subFormFieldsIds = array();
		foreach ($formData['forms'] as $key => $form)
		{
			if ($form['subForm'] === true)
			{
				$subForms[$key] = $form;
			}
		}
		unset($key, $form);
		self::$fieldsCache->set('internal_subforms', $subForms);

		foreach ($subForms as $key => $form)
		{
			foreach ($form['groups'][0]['fields'] as $key2 => $field)
			{
				if ($form['subForm'] === true)
				{
					$subFormFields[$key2] = $field;
					$subFormFieldsIds[]   = $key2;
				}
			}
		}
		unset($key, $key2, $form, $field);

		self::$fieldsCache->set('internal_subform_fields', $subFormFields);
		self::$fieldsCache->set('internal_subform_fields_ids', $subFormFieldsIds);

		return $formData;
	}

    /**
     * Create a field-set for a form.
     *
     * @param   DOMElement  $fieldsNode  The node where this field-set is appended to.
     * @param   integer     $hFormId     The form id
     * @param   integer     $hGroupId    The group id
     * @param   string      $component   The component name
     *
     * @return  DOMElement
     *
     * @since  __DEPLOY_VERSION__
     */
	protected static function createFieldSet($fieldsNode, $hFormId, $hGroupId, $component)
	{
		// Defining the field set
		/** @var DOMElement $fieldset */
		$fieldset = $fieldsNode->appendChild(new DOMElement('fieldset'));
		$fieldset->setAttribute('name', 'fields-' . $hFormId . '-' . $hGroupId);
		$fieldset->setAttribute('addfieldpath', '/administrator/components/' . $component . '/models/fields');
		$fieldset->setAttribute('addrulepath', '/administrator/components/' . $component . '/models/rules');

		return $fieldset;
	}

    /**
     * Get label/description for a form- or group-tab
     *
     * @param   integer  $hFormId        The form id
     * @param   integer  $hGroupId       The group id
     * @param   string   $component      The component name
     * @param   string   $section        The section name
     * @param   array    $formHierarchy  The form hierarchy array
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
	protected static function getLabelAndDescription($hFormId, $hGroupId, $component, $section, $formHierarchy)
	{
		$label       = '';
		$description = '';

		if ($hGroupId)
		{
			$groupInstance = JTable::getInstance('Group', 'FieldsTable');
			$groupInstance->load($hGroupId);

			if ($groupInstance->id)
			{
				$label       = $groupInstance->title;
				$description = $groupInstance->description;
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
					$key = $formHierarchy['forms'][$hFormId]['title'];
				}

				$label = JText::_($key);
			}

			if (!$description)
			{
				$key = strtoupper($component . '_FIELDS_' . $section . '_DESC');

				if ($lang->hasKey($key))
				{
					$description = JText::_($key);

					return array($label, $description);
				}

				return array($label, $description);
			}

			return array($label, $description);
		}

		return array($label, $description);
	}

    /**
     * Get subform-values for a sub-form field
     *
     * @param   object  $item   The item object
     * @param   object  $field  The field object
     *
     * @return  array
     *
     * @since  __DEPLOY_VERSION__
     */
	public static function getSubFormValues($item, $field)
	{
		$fieldsValues = $subFormValue = array();

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__fields');
		$query->where($db->quoteName('form_id') . ' = ' . $db->quote($field->fieldparams['subform_id']));
		$db->setQuery($query);
		$allowedFields = $db->loadObjectList();


		foreach ($allowedFields as $allowedField)
		{
			$fieldsValues[$allowedField->alias]
							= self::$fieldCache->getFieldValue($allowedField->id, $allowedField->context, $item->id, $allowedField->form_id, true);
		}
		foreach ($fieldsValues as $fieldName => $indexValueArray)
		{
			if (is_array($indexValueArray) && count($indexValueArray) > 0)
			{
				foreach ($indexValueArray as $indexKey => $indexValue)
				{
					$subFormValue[$indexKey][$fieldName] = $indexValue;
				}
			}
		}

		return $subFormValue;
	}
}
