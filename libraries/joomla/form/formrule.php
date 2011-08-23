<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

// Detect if we have full UTF-8 and unicode PCRE support.
if (!defined('JCOMPAT_UNICODE_PROPERTIES'))
{
	define('JCOMPAT_UNICODE_PROPERTIES', (bool) @preg_match('/\pL/u', 'a'));
}

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $regex;

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $modifiers;

	/**
	 * Method to test the value.
	 *
	 * @param   object  &$element  The JXmlElement object representing the <field /> tag for the form field object.
	 * @param   mixed   $value     The form field value to validate.
	 * @param   string  $group     The field name group control value. This acts as as an array container for the field.
	 *                             For example if the field has name="foo" and the group value is set to "bar" then the
	 *                             full field name would end up being "bar[foo]".
	 * @param   object  &$input    An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param   object  &$form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @throws  JException on invalid rule.
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// Initialize variables.
		$name = (string) $element['name'];


		//Is there a misconfiguration? If so, log it and skip these checks.
		if (!empty($element['min']) && !empty($element['max']) && $element['min'] >= $element['max'])
		{
			// Form settings warning.
			JLog::add('Field setting minimum is greater than maximum.', JLog::WARNING, 'Form');

			// Keep 
			continue;
		}

		// If a specific regex is given in the element attributes use that rather than the default regex 
		// for the field.
		if ( $element['regex'] )
		{
			$this->regex = $element['regex'] ;
		}

		// Check for a valid regex.
		if (empty($this->regex))
		{
			throw new JException(JText::sprintf('JLIB_FORM_INVALID_FORM_RULE', get_class($this)));
		}

		// Add unicode property support if available.
		if (JCOMPAT_UNICODE_PROPERTIES)
		{
			$this->modifiers = (strpos($this->modifiers, 'u') !== false) ? $this->modifiers : $this->modifiers . 'u';
		}

		// Test the value against the regular expression.
		if (preg_match(chr(1) . $this->regex . chr(1) . $this->modifiers, $value))
		{
			return true;
		}

		return false;
	}
	public function checkMax (&$element, $value)
	{
		if (empty($element['max']) || $value < (float) $element['max'])
		{
			return true;
		}
		return false;

	}
	public function checkMin (&$element, $value)
	{

		if (empty($element['min']) || $value > (float) $element['min'])
		{
			return true;
		}
		return false;

	}
	public function checkDataType (&$element, $value, $type)
	{
		return;
	}
	public function checkDataValue (&$element, $value, $type)
	{

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');
		if (!$required && strlen($value) == 0)
		{

			return true;
		}

		// Is this a valid value for the data type if specified?
		if (checkDataType (&$element, $value) == false)
		{

			return false;
		}

		// Check against the maximum and minimum values if present.
		if (checkMax (&$element, $value) == false)
		{

			return false;
		}
		if (checkMin (&$element, $value) == false)
		{

			return false;
		}		
		
	}
}
