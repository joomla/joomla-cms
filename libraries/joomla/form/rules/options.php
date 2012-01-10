<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 * Requires the value entered be one of the options in a field of type="list"
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormRuleOptions extends JFormRule
{
	/**
	 * Method to test the value.
	 *
	 * @param   JXMLElement  &$element  The JXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed        $value     The form field value to validate.
	 * @param   string       $group     The field name group control value. This acts as as an array container for the field.
	 *                                  For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                  full field name would end up being "bar[foo]".
	 * @param   JRegistry    &$input    An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param   object       &$form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 * @throws  JException on invalid rule.
	 */
	public function test(&$element, $value, $group = null, &$input = null, &$form = null)
	{
		// Check each value and return true if we get a match
		foreach ($element->option as $option)
		{
			if ($value == (string) $option->attributes()->value)
			{
				return true;
			}
		}
		return false;
	}
}
