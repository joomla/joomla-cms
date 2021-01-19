<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyExportDomain', __DIR__ . '/export/domain.php');
JLoader::register('PrivacyExportField', __DIR__ . '/export/field.php');
JLoader::register('PrivacyExportItem', __DIR__ . '/export/item.php');
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

/**
 * Base class for privacy plugins
 *
 * @since  3.9.0
 */
abstract class PrivacyPlugin extends JPlugin
{
	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  3.9.0
	 */
	protected $db;

	/**
	 * Affects constructor behaviour. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  3.9.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Create a new domain object
	 *
	 * @param   string  $name         The domain's name
	 * @param   string  $description  The domain's description
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	protected function createDomain($name, $description = '')
	{
		$domain              = new PrivacyExportDomain;
		$domain->name        = $name;
		$domain->description = $description;

		return $domain;
	}

	/**
	 * Create an item object for an array
	 *
	 * @param   array         $data    The array data to convert
	 * @param   integer|null  $itemId  The ID of this item
	 *
	 * @return  PrivacyExportItem
	 *
	 * @since   3.9.0
	 */
	protected function createItemFromArray(array $data, $itemId = null)
	{
		$item = new PrivacyExportItem;
		$item->id = $itemId;

		foreach ($data as $key => $value)
		{
			if (is_object($value))
			{
				$value = (array) $value;
			}

			if (is_array($value))
			{
				$value = print_r($value, true);
			}

			$field        = new PrivacyExportField;
			$field->name  = $key;
			$field->value = $value;

			$item->addField($field);
		}

		return $item;
	}

	/**
	 * Create an item object for a JTable object
	 *
	 * @param   JTable  $table  The JTable object to convert
	 *
	 * @return  PrivacyExportItem
	 *
	 * @since   3.9.0
	 */
	protected function createItemForTable($table)
	{
		$data = array();

		foreach (array_keys($table->getFields()) as $fieldName)
		{
			$data[$fieldName] = $table->$fieldName;
		}

		return $this->createItemFromArray($data, $table->{$table->getKeyName(false)});
	}

	/**
	 * Helper function to create the domain for the items custom fields.
	 *
	 * @param   string  $context  The context
	 * @param   array   $items    The items
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   3.9.0
	 */
	protected function createCustomFieldsDomain($context, $items = array())
	{
		if (!is_array($items))
		{
			$items = array($items);
		}

		$parts = FieldsHelper::extract($context);

		if (!$parts)
		{
			return array();
		}

		$type = str_replace('com_', '', $parts[0]);

		$domain = $this->createDomain($type . '_' . $parts[1] . '_custom_fields', 'joomla_' . $type . '_' . $parts[1] . '_custom_fields_data');

		foreach ($items as $item)
		{
			// Get item's fields, also preparing their value property for manual display
			$fields = FieldsHelper::getFields($parts[0] . '.' . $parts[1], $item);

			foreach ($fields as $field)
			{
				$fieldValue = is_array($field->value) ? implode(', ', $field->value) : $field->value;

				$data = array(
					$type . '_id' => $item->id,
					'field_name'  => $field->name,
					'field_title' => $field->title,
					'field_value' => $fieldValue,
				);

				$domain->addItem($this->createItemFromArray($data));
			}
		}

		return $domain;
	}
}
