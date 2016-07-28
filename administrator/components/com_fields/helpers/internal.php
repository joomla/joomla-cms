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

/**
 * Fields component helper.
 *
 * @since  3.6
 */
class FieldsHelperInternal
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $context  The context of the content passed to the helper
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public static function addSubmenu ($context)
	{
		// Avoid nonsense situation.
		if ($context == 'com_fields')
		{
			return;
		}

		$parts = FieldsHelper::extract($context);

		if (! $parts)
		{
			return;
		}

		$component = $parts[0];
		$section   = $parts[1];

		// Try to find the component helper.
		$eName = str_replace('com_', '', $component);
		$file  = JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component . '/helpers/' . $eName . '.php');

		if (file_exists($file))
		{
			require_once $file;

			$prefix = ucfirst(str_replace('com_', '', $component));
			$cName = $prefix . 'Helper';

			if (class_exists($cName))
			{
				if (is_callable(array($cName, 'addSubmenu')))
				{
					$lang = JFactory::getLanguage();

					/*
					 * Loading language file from the administrator/language
					 * directory then loading language file from the
					 * administrator/components/*context* /
					 * language directory
					 */
					$lang->load($component, JPATH_BASE, null, false, true)
						|| $lang->load($component, JPath::clean(JPATH_ADMINISTRATOR . '/components/' . $component), null, false, true);

					call_user_func(array($cName, 'addSubmenu'), 'fields' . (isset($section) ? '.' . $section : ''));
				}
			}
		}
	}

	/**
	 * Return a boolean if the actual logged in user can edit the given field value.
	 *
	 * @param   stdClass  $field  The field
	 *
	 * @return boolean
	 */
	public static function canEditFieldValue($field)
	{
		$user = JFactory::getUser();

		return $user->authorise('core.edit.value', $field->context . '.field.' . (int) $field->id);
	}

	/**
	 * Loads the fields plugins.
	 *
	 * @return void
	 */
	public static function loadPlugins()
	{
		foreach (JFolder::listFolderTree(JPATH_PLUGINS . '/fields', '.', 1) as $folder)
		{
			JFactory::getLanguage()->load('plg_fields_' . strtolower($folder['name']), JPATH_ADMINISTRATOR);
			JFactory::getLanguage()->load('plg_fields_' . strtolower($folder['name']), $folder['fullname']);
			JFormHelper::addFieldPath($folder[fullname] . '/fields');
		}
	}
}
