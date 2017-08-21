<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for JFormHelper
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       3.4
 */
class JFormWrapperHelper
{
	/**
	 * Helper wrapper method for loadFieldType
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return mixed  JFormField object on success, false otherwise.
	 *
	 * @see     JFormHelper::loadFieldType()
	 * @since   3.4
	 */
	public function loadFieldType($type, $new = true)
	{
		return JFormHelper::loadFieldType($type, $new);
	}

	/**
	 * Helper wrapper method for loadRuleType
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return mixed  JFormField object on success, false otherwise.
	 *
	 * @see     JFormHelper::loadRuleType()
	 * @since   3.4
	 */
	public function loadRuleType($type, $new = true)
	{
		return JFormHelper::loadRuleType($type, $new);
	}

	/**
	 * Helper wrapper method for loadFieldClass
	 *
	 * @param   string  $type  Type of a field whose class should be loaded.
	 *
	 * @return mixed  Class name on success or false otherwise.
	 *
	 * @see     JFormHelper::loadFieldClass()
	 * @since   3.4
	 */
	public function loadFieldClass($type)
	{
		return JFormHelper::loadFieldClass($type);
	}

	/**
	 * Helper wrapper method for loadRuleClass
	 *
	 * @param   string  $type  Type of a rule whose class should be loaded.
	 *
	 * @return mixed  Class name on success or false otherwise.
	 *
	 * @see     JFormHelper::loadRuleClass()
	 * @since   3.4
	 */
	public function loadRuleClass($type)
	{
		return JFormHelper::loadRuleClass($type);
	}

	/**
	 * Helper wrapper method for addFieldPath
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return array  The list of paths that have been added.
	 *
	 * @see     JFormHelper::addFieldPath()
	 * @since   3.4
	 */
	public function addFieldPath($new = null)
	{
		return JFormHelper::addFieldPath($new);
	}

	/**
	 * Helper wrapper method for addFormPath
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return array  The list of paths that have been added.
	 *
	 * @see     JFormHelper::addFormPath()
	 * @since   3.4
	 */
	public function addFormPath($new = null)
	{
		return JFormHelper::addFormPath($new);
	}

	/**
	 * Helper wrapper method for addRulePath
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return array  The list of paths that have been added.
	 *
	 * @see     JFormHelper::addRulePath()
	 * @since   3.4
	 */
	public function addRulePath($new = null)
	{
		return JFormHelper::addRulePath($new);
	}
}
