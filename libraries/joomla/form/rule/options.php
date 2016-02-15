<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Form Rule class for the Joomla Platform.
 * Requires the value entered be one of the options in a field of type="list"
 *
 * @since  11.1
 */
class JFormRuleOptions extends JFormRule
{
	/**
	 * Method to test the value.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		// Make an array of all available option values.
		$options = array();

		foreach ($element->option as $opt)
		{
			$options[] = $opt->attributes()->value;
		}

		// There may be multiple values in the form of an array (if the element is checkboxes, for example).
		if (is_array($value))
		{
			// If all values are in the $options array, $diff will be empty and the options valid.
			$diff = array_diff($value, $options);

			return empty($diff);
		}
		else
		{
			// In this case value must be a string

			return in_array((string) $value, $options);
		}
	}
}
