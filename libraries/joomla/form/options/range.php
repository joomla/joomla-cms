<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Range Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionRange
{

	protected $type = 'Range';

	/**
	 * Method to get a list of options.
	 *
	 * @param  SimpleXMLElement  $option     <option/> element
	 * @param  string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		$options = array();

		// Initialize some field attributes.
		$first = (string) $option['first'];
		$last = (string) $option['last'];
		$step = isset($option['step']) ? (int) $option['step'] : 1;

		$reverse = false;

		// Sanity checks.
		if ($step == 0 || strlen($first) == 0 || strlen($last) == 0)
		{
			// Step of 0 is not allowed, first and last are required
			return $options;
		}
		elseif ($step < 0)
		{
			// PHP's range() doesn't accept negative values but we can.
			$step *= -1;
			$reverse = true;
		}

		foreach (range($first, $last, $step) as $i)
		{
			$options[] = JHtml::_('select.option', $i);
		}

		if ($reverse)
		{
			$options = array_reverse($options);
		}

		return $options;
	}

}
