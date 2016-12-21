<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('JFolder', JPATH_LIBRARIES . '/joomla/filesystem/folder.php');

/**
 * Fields component helper.
 *
 * @since  3.7.0
 */
class FieldsHelperInternal
{
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
	public static function addSubmenu ($context, $vName)
	{
		$parts = FieldsHelper::extract($context);

		if (!$parts)
		{
			return;
		}

		$component = $parts[0];

		// Avoid nonsense situation.
		if ($component == 'com_fields')
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

		$cName  = ucfirst($eName) . 'Helper';

		if (class_exists($cName) && is_callable(array($cName, 'addSubmenu')))
		{
			$lang = JFactory::getLanguage();
			$lang->load($component, JPATH_ADMINISTRATOR)
			|| $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);

			$cName::addSubmenu('fields.' . $vName);
		}
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
		$parts = FieldsHelper::extract($field->context);

		return JFactory::getUser()->authorise('core.edit.value', $parts[0] . '.field.' . (int) $field->id);
	}

	/**
	 * Loads the fields plugins and returns an array of field specifications from the plugins.
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
	public static function getFieldsSpecification()
	{
		JPluginHelper::importPlugin('fields');
		$eventData = JEventDispatcher::getInstance()->trigger('onGetCustomFields');

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
}
