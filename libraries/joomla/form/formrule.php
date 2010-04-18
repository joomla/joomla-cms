<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Detect if we have full UTF-8 and unicode PCRE support.
if (!defined('JCOMPAT_UNICODE_PROPERTIES')) {
	define('JCOMPAT_UNICODE_PROPERTIES', (bool) @preg_match('/\pL/u', 'a'));
}

/**
 * Form Rule class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $regex;

	/**
	 * The regular expression modifiers to use when testing a form field value.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $modifiers;

	/**
	 * Method to test the value.
	 *
	 * @param	object	$element	The JXMLElement object representing the <field /> tag for the
	 * 								form field object.
	 * @param	mixed	$value		The form field value to validate.
	 * @param	string	$group		The field name group control value. This acts as as an array
	 * 								container for the field. For example if the field has name="foo"
	 * 								and the group value is set to "bar" then the full field name
	 * 								would end up being "bar[foo]".
	 * @param	object	$input		An optional JRegistry object with the entire data set to validate
	 * 								against the entire form.
	 * @param	object	$form		The form object for which the field is being tested.
	 *
	 * @return	boolean	True if the value is valid, false otherwise.
	 * @since	1.6
	 * @throws	JException on invalid rule.
	 */
	public function test(& $element, $value, $group = null, & $input = null, & $form = null)
	{
		// Initialize variables.
		$name = (string) $element['name'];

		// Check for a valid regex.
		if (empty($this->regex)) {
			throw new JException(JText::sprintf('JLIB_FORM_INVALID_FORM_RULE', get_class($this)));
		}

		// Add unicode property support if available.
		if (JCOMPAT_UNICODE_PROPERTIES) {
			$this->modifiers = (strpos($this->modifiers, 'u') !== false) ? $this->modifiers : $this->modifiers.'u';
		}

		// Test the value against the regular expression.
		if (preg_match(chr(1).$this->regex.chr(1).$this->modifiers, $value)) {
			return true;
		}

		return false;
	}
}