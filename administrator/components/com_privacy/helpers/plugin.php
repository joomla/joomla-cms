<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('PrivacyExportDomain', __DIR__ . '/export/domain.php');
JLoader::register('PrivacyExportField', __DIR__ . '/export/field.php');
JLoader::register('PrivacyExportItem', __DIR__ . '/export/item.php');

/**
 * Base class for privacy plugins
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class PrivacyPlugin extends JPlugin
{
	/**
	 * Create a new domain object
	 *
	 * @param   string  $name         The domain's name
	 * @param   string  $description  The domain's description
	 *
	 * @return  PrivacyExportDomain
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
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
}
