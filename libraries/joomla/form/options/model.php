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
 * Model Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionModel
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
		// The name of the model class.
		$className = (string) $option['modelName'];
		// Allow the model to use a method other than the default 'getOptions'
		$methodName = isset($option['modelMethod']) ? (string) $option['modelMethod'] : 'getOptions';
		// Allow a model path in the JLoader dot separated format.
		$path = (string) $option['modelPath'];
		// Allow a base path as either a constant (ex: JPATH_BASE) or string.
		// Because JLoader:import will use 'libraries' by default and this is not usually what we will want.
		$base = (string) $option['modelBase'];

		// If we have a path to the model class, import it.
		if ($path)
		{
			JLoader::import($path, defined($base) ? (string) constant($base) : $base);
		}

		if (!class_exists($className))
		{
			return array();
		}

		if (is_a($className, 'JModelLegacy', true) && is_callable(array($className, $methodName)))
		{
			return self::getOptionsLegacy($className, $methodName, $option, $fieldname);
		}
		elseif (in_array('JModel', class_implements($className, true)) && is_callable(array($className, $methodName)))
		{
			return self::getOptionsModel($className, $methodName, $option, $fieldname);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Get the options from an instance of a child class of JModelLegacy
	 *
	 * @param   string            $className   The name of the model class to use
	 * @param   string            $methodName  The name of the method to call
	 * @param   SimpleXMLElement  $option      <option/> element
	 * @param   string            $fieldname   The name of the field containing this option.
	 *
	 * @return  array                          A list of objects representing HTML option elements
	 */
	protected static function getOptionsLegacy($className, $methodName, SimpleXMLElement $option, $fieldname = '')
	{
		$state = new JObject;

		foreach ($option->attributes() as $name => $value)
		{
			$state->set($name, $value);
		}

		$config = array(
			'ignore_request' => true,
			'state' => $state
		);

		$model = new $className($config);

		return call_user_func(array($model, $methodName), $option, $fieldname);
	}

	/**
	 * Get the options from an instance of a class that implements JModel
	 *
	 * @param   string            $className   The name of the model class to use
	 * @param   string            $methodName  The name of the method to call
	 * @param   SimpleXMLElement  $option      <option/> element
	 * @param   string            $fieldname   The name of the field containing this option.
	 *
	 * @return  array                          A list of objects representing HTML option elements
	 */
	protected static function getOptionsModel($className, $methodName, SimpleXMLElement $option, $fieldname = '')
	{
		$state = new JRegistry;

		foreach ($option->attributes() as $name => $value)
		{
			$state->set($name, $value);
		}

		$model = new $className($state);

		return call_user_func(array($model, $methodName), $option, $fieldname);
	}
}
