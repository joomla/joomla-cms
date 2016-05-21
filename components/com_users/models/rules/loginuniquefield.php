<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * JFormRule for com_users to be sure only one redirect login field has a value
 *
 * @since  3.6
 */
class JFormRuleLoginUniqueField extends JFormRule
{
	/**
	 * Method to test if two fields have a value in order to use only one field.
	 * To use this rule, the form
	 * XML needs a validate attribute of loginuniquefield and a field attribute
	 * that is equal to the field to test against.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   3.6
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		$loginRedirectUrl       = $input['params']->login_redirect_url;
		$loginRedirectMenuitem  = $input['params']->login_redirect_menuitem;

		if (is_null($form))
		{
			throw new InvalidArgumentException(sprintf('The value for $form must not be null in %s', get_class($this)));
		}

		if (is_null($input))
		{
			throw new InvalidArgumentException(sprintf('The value for $input must not be null in %s', get_class($this)));
		}

		// Test the input values for login.
		if ($loginRedirectUrl != '' && $loginRedirectMenuitem != '')
		{
			return false;
		}

		return true;
	}
}
