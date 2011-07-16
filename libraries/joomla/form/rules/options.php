<?php
/**
 * @package		Joomla.Platform
 * @subpackage	Form
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.form.formrule');
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
	public function test(& $element, $value, $group = null, & $input = null, & $form = null)
	{
		// Check each value and return true if we get a match
		foreach ($element->option as $option) {
			if ($value == $option->getAttribute('value')) {
				return true;
			}
		}
		return false;
	}
}
