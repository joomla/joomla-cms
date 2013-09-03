<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Categories helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 * @since       3.2
 */
class ContenthistoryHelper
{
	/**
	 * Method to put all field names, including nested ones, in a single array for easy lookup.
	 *
	 * @param   stdClass  $object  Standard class object that may contain one level of nested objects.
	 *
	 * @return  array  Associative array of all field names, including ones in a nested object.
	 *
	 * @since   3.2
	 */
	public static function createObjectArray($object)
	{
		$result = array();

		foreach ($object as $name => $value)
		{
			$result[$name] = $value;

			if (is_object($value))
			{
				foreach ($value as $subName => $subValue)
				{
					$result[$subName] = $subValue;
				}
			}
		}

		return $result;
	}

	/**
	 * Method to decode JSON-encoded fields in a standard object. Used to unpack JSON strings in the content history data column.
	 *
	 * @param   stdClass  $jsonString  Standard class object that may contain one or more JSON-encoded fields.
	 *
	 * @return  stdClass  Object with any JSON-encoded fields unpacked.
	 *
	 * @since   3.2
	 */
	public static function decodeFields($jsonString)
	{
		$object = json_decode($jsonString);

		if (is_object($object))
		{
			foreach ($object as $name => $value)
			{
				if ($subObject = json_decode($value))
				{
					$object->$name = $subObject;
				}
			}
		}

		return $object;
	}

	/**
	 * Method to get field labels for the fields in the JSON-encoded object.
	 * First we see if we can find translatable labels for the fields in the object.
	 * We translate any we can find and return an array in the format object->name => label.
	 *
	 * @param   stdClass           $object      Standard class object in the format name->value.
	 * @param   JTableContenttype  $typesTable  Table object with content history options.
	 *
	 * @return  stdClass  Contains two associative arrays.
	 *                    $formValues->labels in the format name => label (for example, 'id' => 'Article ID').
	 *                    $formValues->values in the format name => value (for example, 'state' => 'Published'.
	 *                    This translates the text from the selected option in the form.
	 *
	 * @since   3.2
	 */
	public static function getFormValues($object, JTableContenttype $typesTable)
	{
		$labels = array();
		$values = array();
		$expandedObjectArray = self::createObjectArray($object);
		self::loadLanguageFiles($typesTable->type_alias);

		if ($formFile = self::getFormFile($typesTable))
		{
			if ($xml = simplexml_load_file($formFile))
			{
				// Now we need to get all of the labels from the form
				$fieldArray = $xml->xpath('//field');
				$fieldArray = array_merge($fieldArray, $xml->xpath('//fields'));

				foreach ($fieldArray as $field)
				{
					if ($label = (string) $field->attributes()->label)
					{
						$labels[(string) $field->attributes()->name] = JText::_($label);
					}
				}

				// Get values for any list type fields
				$listFieldArray = $xml->xpath('//field[@type="list" or @type="radio"]');

				foreach ($listFieldArray as $field)
				{
					$name = (string) $field->attributes()->name;

					if (isset($expandedObjectArray[$name]))
					{
						$optionFieldArray = $field->xpath('option[@value="' . $expandedObjectArray[$name] . '"]');
						$valueText = trim((string) $optionFieldArray[0]);
						$values[(string) $field->attributes()->name] = JText::_($valueText);
					}
				}
			}
		}

		$result = new stdClass;
		$result->labels = $labels;
		$result->values = $values;

		return $result;
	}

	/**
	 * Method to get the XML form file for this component. Used to get translated field names for history preview.
	 *
	 * @param   JTableContenttype  $typesTable  Table object with content history options.
	 *
	 * @return  mixed    JModel object if successful, false if no model found.
	 *
	 * @since   3.2
	 */
	public static function getFormFile(JTableContenttype $typesTable)
	{
		$result = false;
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.folder');

		// First, see if we have a file name in the $typesTable
		$options = json_decode($typesTable->content_history_options);

		if (is_object($options) && isset($options->form_file) && JFile::exists(JPATH_ROOT . '/' . $options->form_file))
		{
			$result = JPATH_ROOT . '/' . $options->form_file;
		}
		else
		{
			$aliasArray = explode('.', $typesTable->type_alias);

			if (count($aliasArray) == 2)
			{
				$component = ($aliasArray[1] == 'category') ? 'com_categories' : $aliasArray[0];
				$path  = JFolder::makeSafe(JPATH_ADMINISTRATOR . '/components/' . $component . '/models/forms/');
				$file = JFile::makeSafe($aliasArray[1] . '.xml');
				$result = JFile::exists($path . $file) ? $path . $file : false;
			}
		}

		return $result;
	}

	/**
	 * Method to query the database using values from lookup objects.
	 *
	 * @param   stdClass  $lookup  The std object with the values needed to do the query.
	 * @param   mixed     $value   The value used to find the matching title or name. Typically the id.
	 *
	 * @return mixed      Value from database (for example, name or title) on success, false on failure.
	 *
	 * @since   3.2
	 */
	public static function getLookupValue($lookup, $value)
	{
		$result = false;

		if (isset($lookup->source_column) && isset($lookup->target_table) && isset($lookup->target_column)&& isset($lookup->display_column))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName($lookup->display_column))
				->from($db->quoteName($lookup->target_table))
				->where($db->quoteName($lookup->target_column) . ' = ' . $db->quote($value));
			$db->setQuery($query);

			try
			{
				$result = $db->loadResult();
			}
			catch (Exception $e)
			{
				// Ignore any errors and just return false
				return false;
			}
		}

		return $result;
	}

	/**
	 * Method to remove fields from the object based on values entered in the #__content_types table.
	 *
	 * @param   stdClass           $object     Object to be passed to view layout file.
	 * @param   JTableContenttype  $typeTable  Table object with content history options.
	 *
	 * @return  stdClass  object with hidden fields removed.
	 *
	 * @since   3.2
	 */
	public static function hideFields($object, JTableContenttype $typeTable)
	{
		if ($options = json_decode($typeTable->content_history_options))
		{
			if (isset($options->hide_fields) && is_array($options->hide_fields))
			{
				foreach ($options->hide_fields as $field)
				{
					unset($object->$field);
				}
			}
		}

		return $object;
	}

	/**
	 * Method to load the language files for the component whose history is being viewed.
	 *
	 * @param   string  $typeAlias  The type alias, for example 'com_content.article'.
	 *
	 * @return  null
	 *
	 * @since   3.2
	 */
	public static function loadLanguageFiles($typeAlias)
	{
		$aliasArray = explode('.', $typeAlias);

		if (is_array($aliasArray) && count($aliasArray) == 2)
		{
			$component = ($aliasArray[1] == 'category') ? 'com_categories' : $aliasArray[0];
			$lang = JFactory::getLanguage();
			/**
			 * Loading language file from the administrator/language directory then
			 * loading language file from the administrator/components/extension/language directory
			 */
			$lang->load($component, JPATH_ADMINISTRATOR, null, false, false)
			|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, false)
			|| $lang->load($component, JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
			|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), $lang->getDefault(), false, false);

			// Force loading of back-end global language file
			$lang->load('joomla', JPath::clean(JPATH_ADMINISTRATOR), null, false, false);
		}
	}

	/**
	 * Method to create object to pass to the layout. Format is as follows:
	 * field is std object with name, value.
	 *
	 * Value can be a std object with name, value pairs.
	 *
	 * @param   stdClass  $object      The std object from the JSON string. Can be nested 1 level deep.
	 * @param   stdClass  $formValues  Standard class of label and value in an associative array.
	 *
	 * @return stdClass   Object with translated labels where available
	 *
	 * @since   3.2
	 */
	public static function mergeLabels($object, $formValues)
	{
		$result = new stdClass;

		$labelsArray = $formValues->labels;
		$valuesArray = $formValues->values;

		foreach ($object as $name => $value)
		{
			$result->$name = new stdClass;
			$result->$name->name = $name;
			$result->$name->value = isset($valuesArray[$name]) ? $valuesArray[$name] : $value;
			$result->$name->label = isset($labelsArray[$name]) ? $labelsArray[$name] : $name;

			if (is_object($value))
			{
				$subObject = new stdClass;

				foreach ($value as $subName => $subValue)
				{
					$subObject->$subName = new stdClass;
					$subObject->$subName->name = $subName;
					$subObject->$subName->value = isset($valuesArray[$subName]) ? $valuesArray[$subName] : $subValue;
					$subObject->$subName->label = isset($labelsArray[$subName]) ? $labelsArray[$subName] : $subName;
					$result->$name->value = $subObject;
				}
			}
		}

		return $result;
	}

	/**
	 * Method to prepare the object for the preview and compare views.
	 *
	 * @param   JTableContenthistory  $table  Table object loaded with data.
	 *
	 * @return stdClass   Object ready for the views.
	 *
	 * @since   3.2
	 */
	public static function prepareData(JTableContenthistory $table)
	{
		$object = self::decodeFields($table->version_data);
		$typesTable = JTable::getInstance('Contenttype');
		$typesTable->load(array('type_id' => $table->ucm_type_id));
		$formValues = self::getFormValues($object, $typesTable);
		$object = self::mergeLabels($object, $formValues);
		$object = self::hideFields($object, $typesTable);
		$object = self::processLookupFields($object, $typesTable);

		return $object;
	}

	/**
	 * Method to process any lookup values found in the content_history_options column for this table.
	 * This allows category title and user name to be displayed instead of the id column.
	 *
	 * @param   stdClass           $object      The std object from the JSON string. Can be nested 1 level deep.
	 * @param   JTableContenttype  $typesTable  Table object loaded with data.
	 *
	 * @return stdClass   Object with lookup values inserted.
	 *
	 * @since   3.2
	 */
	public static function processLookupFields($object, JTableContenttype $typesTable)
	{
		if ($options = json_decode($typesTable->content_history_options))
		{
			if (isset($options->display_lookup) && is_array($options->display_lookup))
			{
				foreach ($options->display_lookup as $lookup)
				{
					$sourceColumn = isset($lookup->source_column) ? $lookup->source_column : false;
					$sourceValue = isset($object->$sourceColumn->value) ? $object->$sourceColumn->value : false;

					if ($sourceColumn && $sourceValue && ($lookupValue = self::getLookupValue($lookup, $sourceValue)))
					{
						$object->$sourceColumn->value = $lookupValue;
					}
				}
			}
		}

		return $object;
	}
}
