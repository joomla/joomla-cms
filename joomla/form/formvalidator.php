<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Form Validator class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Forms
 * @version		1.6
 */
class JFormValidator extends JObject
{
	/**
	 * Method to validate a group of fields.
	 *
	 * @access	public
	 * @param	array		$fields		An array of fields to validate.
	 * @param	array		$data		The data to validate.
	 * @return	mixed		Array on success, JException on error.
	 * @since	1.6
	 */
	public function validate(&$fields, &$data)
	{
		$results = array();

		foreach ($fields as $name => $field)
		{
			// Get the data for the field.
			$value = array_key_exists($name, $data) ? $data[$name] : null;

			// Check if the field is required.
			if ($field->attributes('required') == 'true')
			{
				// Check if the field value is empty.
				if ($value === '')
				{
					// The required field is empty!
					if ($message = $field->attributes('message')) {
						$results[] = new JException(JText::_($message), 0, E_WARNING);
					} else {
						$results[] = new JException(JText::sprintf('Libraries_Form_Validator_Field_Required', JText::_($field->attributes('name'))), 0, E_WARNING);
					}

					// We don't want to display more than one message per field so continue to the next one.
					continue;
				}
			}

			// Run the field validator.
			$return = $this->_isValid($field, $data);

			// Check for an error.
			if (JError::isError($return)) {
				return $return;
			}

			// Check if the field is valid.
			if ($return === false)
			{
				// The field failed validation.
				if ($message = $field->attributes('message')) {
					$results[] = new JException(JText::_($message), 0, E_WARNING);
				} else {
					$results[] = new JException(JText::sprintf('Libraries_Form_Validator_Field_Invalid', $field->attributes('name')), 0, E_WARNING);
				}
			}
		}

		return $results;
	}

	/**
	 * Method to test if a value is valid for a field.
	 *
	 * @access	protected
	 * @param	object		$field		The field to validate.
	 * @param	array		$values		The values to validate.
	 * @return	mixed		Boolean on success, JException on error.
	 * @since	1.6
	 */
	protected function _isValid(&$field, $values)
	{
		$result = true;

		// Get the validator type.
		if ($type = $field->attributes('validate'))
		{
			// Get the validator class.
			$class = 'JFormRule'.$type;

			if (!class_exists($class))
			{
				jimport('joomla.filesystem.path');

				// Attempt to load the rule file.
				if ($file = JPath::find(JFormValidator::addRulePath(), $type.'.php')) {
					require_once $file;
				}

				if (!class_exists($class)) {
					return new JException(JText::sprintf('Libraries_Form_Validator_Rule_Not_Found', $type), 0, E_ERROR);
				}
			}

			// Run the validator.
			$rule	= new $class;
			$result	= $rule->test($field, $values);
		}

		return $result;
	}

	/**
	 * Method to add a path to the list of rule include paths.
	 *
	 * @access	public
	 * @param	mixed		$new		A path or array of paths to add.
	 * @return	array		The list of paths that have been added.
	 * @since	1.6
	 * @static
	 */
	public function addRulePath($new = null)
	{
		static $paths;

		if (!isset($paths)) {
			$paths = array(dirname(__FILE__).DS.'rules');
		}

		// Force path to an array.
		settype($new, 'array');

		// Add the new paths to the list if not already there.
		foreach ($new as $path) {
			if (!in_array($path, $paths)) {
				array_unshift($paths, trim($path));
			}
		}

		return $paths;
	}
}