<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Database Prefix field.
 *
 * @since  1.6
 */
class InstallationFormFieldPrefix extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'Prefix';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string   The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$size      = $this->element['size'] ? abs((int) $this->element['size']) : 5;
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class     = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly  = (string) $this->element['readonly'] === 'true' ? ' readonly="readonly"' : '';
		$disabled  = (string) $this->element['disabled'] === 'true' ? ' disabled="disabled"' : '';

		// Make sure somebody doesn't put in a too large prefix size value.
		if ($size > 10)
		{
			$size = 10;
		}

		// If a prefix is already set, use it instead.
		$session = JFactory::getSession()->get('setup.options', array());

		if (empty($session['db_prefix']))
		{
			// Create the random prefix.
			$prefix  = '';
			$chars   = range('a', 'z');
			$numbers = range(0, 9);

			// We want the fist character to be a random letter.
			shuffle($chars);
			$prefix .= $chars[0];

			// Next we combine the numbers and characters to get the other characters.
			$symbols = array_merge($numbers, $chars);
			shuffle($symbols);

			for ($i = 0, $j = $size - 1; $i < $j; ++$i)
			{
				$prefix .= $symbols[$i];
			}

			// Add in the underscore.
			$prefix .= '_';
		}
		else
		{
			$prefix = $session['db_prefix'];
		}

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' .
				' value="' . htmlspecialchars($prefix, ENT_COMPAT, 'UTF-8') . '"' .
				$class . $disabled . $readonly . $onchange . $maxLength . '/>';
	}
}
