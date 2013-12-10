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
 * Class Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionClass
{
	/**
	 * Method to get a list of options.
	 *
	 * @param   SimpleXMLElement  $option     <option/> element
	 * @param   string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		// The name of the class.
		$className = (string) $option['className'];
		// Allow the model to use a method other than the default 'getOptions'
		$methodName = isset($option['classMethod']) ? (string) $option['classMethod'] : 'getOptions';
		// Allow a model path in the JLoader dot separated format.
		$path = (string) $option['classPath'];
		// Allow a base path as either a constant (ex: JPATH_BASE) or string.
		// Because JLoader:import will use 'libraries' by default and this is not usually what we will want.
		$base = (string) $option['classBase'];

		// If we have a path to the class, import it.
		if ($path)
		{
			JLoader::import($path, defined($base) ? (string) constant($base) : $base);
		}

		if (is_callable(array($className, $methodName)))
		{
			$class = new $className;

			return call_user_func(array($class, $methodName), $option, $fieldname);
		}
		else
		{
			return array();
		}
	}
}
