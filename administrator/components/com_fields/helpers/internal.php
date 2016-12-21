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
 * @since  __DEPLOY_VERSION__
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
	 * @since    __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function canEditFieldValue($field)
	{
		$parts = FieldsHelper::extract($field->context);

		return JFactory::getUser()->authorise('core.edit.value', $parts[0] . '.field.' . (int) $field->id);
	}

	/**
	 * Loads the fields plugins.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function loadPlugins()
	{
		foreach (JFolder::listFolderTree(JPATH_PLUGINS . '/fields', '.', 1) as $folder)
		{
			if (!JPluginHelper::isEnabled('fields', $folder['name']))
			{
				continue;
			}

			JFactory::getLanguage()->load('plg_fields_' . strtolower($folder['name']), JPATH_ADMINISTRATOR);
			JFactory::getLanguage()->load('plg_fields_' . strtolower($folder['name']), $folder['fullname']);
			JFormHelper::addFieldPath($folder['fullname'] . '/fields');
		}
	}
}
