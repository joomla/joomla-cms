<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

defined('JPATH_PLATFORM') or die;

/**
 * Wrapper class for FormHelper
 *
 * @since       3.4
 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
 */
class FormWrapper
{
	/**
	 * Helper wrapper method for loadFieldType
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  JFormField object on success, false otherwise.
	 *
	 * @see     FormHelper::loadFieldType()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
	 */
	public function loadFieldType($type, $new = true)
	{
		return FormHelper::loadFieldType($type, $new);
	}

	/**
	 * Helper wrapper method for loadRuleType
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  JFormField object on success, false otherwise.
	 *
	 * @see     FormHelper::loadRuleType()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
	 */
	public function loadRuleType($type, $new = true)
	{
		return FormHelper::loadRuleType($type, $new);
	}

	/**
	 * Helper wrapper method for loadFieldClass
	 *
	 * @param   string  $type  Type of a field whose class should be loaded.
	 *
	 * @return  mixed  Class name on success or false otherwise.
	 *
	 * @see     FormHelper::loadFieldClass()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
	 */
	public function loadFieldClass($type)
	{
		return FormHelper::loadFieldClass($type);
	}

	/**
	 * Helper wrapper method for loadRuleClass
	 *
	 * @param   string  $type  Type of a rule whose class should be loaded.
	 *
	 * @return  mixed  Class name on success or false otherwise.
	 *
	 * @see     FormHelper::loadRuleClass()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
	 */
	public function loadRuleClass($type)
	{
		return FormHelper::loadRuleClass($type);
	}

	/**
	 * Helper wrapper method for addFieldPath
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see     FormHelper::addFieldPath()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
	 */
	public function addFieldPath($new = null)
	{
		return FormHelper::addFieldPath($new);
	}

	/**
	 * Helper wrapper method for addFormPath
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see     FormHelper::addFormPath()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
	 */
	public function addFormPath($new = null)
	{
		return FormHelper::addFormPath($new);
	}

	/**
	 * Helper wrapper method for addRulePath
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see     FormHelper::addRulePath()
	 * @since   3.4
	 * @deprecated  4.0  Use `Joomla\CMS\Form\FormHelper` directly
	 */
	public function addRulePath($new = null)
	{
		return FormHelper::addRulePath($new);
	}
}
