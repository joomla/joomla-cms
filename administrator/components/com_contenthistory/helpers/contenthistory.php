<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_contenthistory
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Categories helper.
 *
 * @since  3.2
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

		if ($object === null)
		{
			return $result;
		}

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
		$expandedObjectArray = static::createObjectArray($object);
		static::loadLanguageFiles($typesTable->type_alias);

		if ($formFile = static::getFormFile($typesTable))
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

						$valueText = null;

						if (is_array($optionFieldArray) && count($optionFieldArray))
						{
							$valueText = trim((string) $optionFieldArray[0]);
						}

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
	 * @return  mixed  JModel object if successful, false if no model found.
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

		if (is_object($options) && isset($options->formFile) && JFile::exists(JPATH_ROOT . '/' . $options->formFile))
		{
			$result = JPATH_ROOT . '/' . $options->formFile;
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
	 * @return  mixed  Value from database (for example, name or title) on success, false on failure.
	 *
	 * @since   3.2
	 */
	public static function getLookupValue($lookup, $value)
	{
		$result = false;

		if (isset($lookup->sourceColumn) && isset($lookup->targetTable) && isset($lookup->targetColumn)&& isset($lookup->displayColumn))
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select($db->quoteName($lookup->displayColumn))
				->from($db->quoteName($lookup->targetTable))
				->where($db->quoteName($lookup->targetColumn) . ' = ' . $db->quote($value));
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
			if (isset($options->hideFields) && is_array($options->hideFields))
			{
				foreach ($options->hideFields as $field)
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
	 * @return  void
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
			$lang->load($component, JPATH_ADMINISTRATOR, null, false, true)
			|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, true);

			// Force loading of backend global language file
			$lang->load('joomla', JPath::clean(JPATH_ADMINISTRATOR), null, false, true);
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
	 * @return  stdClass  Object with translated labels where available
	 *
	 * @since   3.2
	 */
	public static function mergeLabels($object, $formValues)
	{
		$result = new stdClass;

		if ($object === null)
		{
			return $result;
		}

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
	 * @return  stdClass  Object ready for the views.
	 *
	 * @since   3.2
	 */
	public static function prepareData(JTableContenthistory $table)
	{
		$object = static::decodeFields($table->version_data);
		$typesTable = JTable::getInstance('Contenttype');
		$typesTable->load(array('type_id' => $table->ucm_type_id));
		$formValues = static::getFormValues($object, $typesTable);
		$object = static::mergeLabels($object, $formValues);
		$object = static::hideFields($object, $typesTable);
		$object = static::processLookupFields($object, $typesTable);

		return $object;
	}

	/**
	 * Method to process any lookup values found in the content_history_options column for this table.
	 * This allows category title and user name to be displayed instead of the id column.
	 *
	 * @param   stdClass           $object      The std object from the JSON string. Can be nested 1 level deep.
	 * @param   JTableContenttype  $typesTable  Table object loaded with data.
	 *
	 * @return  stdClass  Object with lookup values inserted.
	 *
	 * @since   3.2
	 */
	public static function processLookupFields($object, JTableContenttype $typesTable)
	{
		if ($options = json_decode($typesTable->content_history_options))
		{
			if (isset($options->displayLookup) && is_array($options->displayLookup))
			{
				foreach ($options->displayLookup as $lookup)
				{
					$sourceColumn = isset($lookup->sourceColumn) ? $lookup->sourceColumn : false;
					$sourceValue = isset($object->$sourceColumn->value) ? $object->$sourceColumn->value : false;

					if ($sourceColumn && $sourceValue && ($lookupValue = static::getLookupValue($lookup, $sourceValue)))
					{
						$object->$sourceColumn->value = $lookupValue;
					}
				}
			}
		}

		return $object;
	}
}
