<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

// Detect if we have full UTF-8 and unicode support.
if (!defined('JCOMPAT_UNICODE_PROPERTIES')) {
	define('JCOMPAT_UNICODE_PROPERTIES', (bool)@preg_match('/\pL/u', 'a'));
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
	 * The regular expression.
	 *
	 * @var		string
	 */
	protected $_regex;

	/**
	 * The regular expression modifiers.
	 *
	 * @var		string
	 */
	protected $_modifiers;

	/**
	 * Method to test the value.
	 *
	 * @param	object		$field		A reference to the form field.
	 * @param	mixed		$values		The values to test for validiaty.
	 * @return	boolean		True if the value is valid, false otherwise.
	 * @throws	JException on invalid rule.
	 */
	public function test(&$field, &$values)
	{
		$return = false;
		$name	= (string)$field->attributes()->name;

		// Check for a valid regex.
		if (empty($this->_regex)) {
			throw new JException('Invalid Form Rule :: '.get_class($this));
		}

		// Add unicode property support if available.
		if (JCOMPAT_UNICODE_PROPERTIES) {
			$this->_modifiers = strpos($this->_modifiers, 'u') ? $this->_modifiers : $this->_modifiers.'u';
		}

		// Test the value against the regular expression.
		if (preg_match('#'.$this->_regex.'#'.$this->_modifiers, $values[$name])) {
			$return = true;
		}

		return $return;
	}
}