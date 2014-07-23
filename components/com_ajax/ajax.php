<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/*
 * References
 *  Support plugins in your component
 * - http://docs.joomla.org/Supporting_plugins_in_your_component
 *
 * Best way for JSON output
 * - https://groups.google.com/d/msg/joomla-dev-cms/WsC0nA9Fixo/Ur-gPqpqh-EJ
 */

// Reference global application object
$app = JFactory::getApplication();

// JInput object
$input = $app->input;

// Requested format passed via URL
$format = strtolower($input->getWord('format'));

// Initialize default response and module name
$results = null;
$parts = null;

// Check for valid format
if (!$format)
{
	$results = new InvalidArgumentException('Please specify response format other that HTML (json, raw, etc.)', 404);
}
/*
 * Module support.
 *
 * modFooHelper::getAjax() is called where 'foo' is the value
 * of the 'module' variable passed via the URL
 * (i.e. index.php?option=com_ajax&module=foo).
 *
 */
elseif ($input->get('module'))
{
	$module       = $input->get('module');
	$moduleObject = JModuleHelper::getModule('mod_' . $module, null);

	/*
	 * As JModuleHelper::isEnabled always returns true, we check
	 * for an id other than 0 to see if it is published.
	 */
	if ($moduleObject->id != 0)
	{
		$helperFile = JPATH_BASE . '/modules/mod_' . $module . '/helper.php';

		if (strpos($module, '_'))
		{
			$parts = explode('_', $module);
		}
		elseif (strpos($module, '-'))
		{
			$parts = explode('-', $module);
		}

		if ($parts)
		{
			$class = 'mod';
			foreach ($parts as $part)
			{
				$class .= ucfirst($part);
			}
			$class .= 'Helper';
		}
		else
		{
			$class = 'mod' . ucfirst($module) . 'Helper';
		}

		$method = $input->get('method') ? $input->get('method') : 'get';

		if (is_file($helperFile))
		{
			require_once $helperFile;

			if (method_exists($class, $method . 'Ajax'))
			{
				try
				{
					$results = call_user_func($class . '::' . $method . 'Ajax');
				}
				catch (Exception $e)
				{
					$results = $e;
				}
			}
			// Method does not exist
			else
			{
				$results = new LogicException(sprintf('Method %s does not exist', $method . 'Ajax'), 404);
			}
		}
		// The helper file does not exist
		else
		{
			$results = new RuntimeException(sprintf('The file at %s does not exist', 'mod_' . $module . '/helper.php'), 404);
		}
	}
	// Module is not published, you do not have access to it, or it is not assigned to the current menu item
	else
	{
		$results = new LogicException(sprintf('Module %s is not published, you do not have access to it, or it\'s not assigned to the current menu item', 'mod_' . $module), 404);
	}
}
/*
 * Plugin support is based on the "Ajax" plugin group.
 *
 * The plugin event triggered is onAjaxFoo, where 'foo' is
 * the value of the 'plugin' variable passed via the URL
 * (i.e. index.php?option=com_ajax&plugin=foo)
 *
 */
elseif ($input->get('plugin'))
{
	JPluginHelper::importPlugin('ajax');
	$plugin     = ucfirst($input->get('plugin'));
	$dispatcher = JEventDispatcher::getInstance();

	try
	{
		$results = $dispatcher->trigger('onAjax' . $plugin);
	}
	catch (Exception $e)
	{
		$results = $e;
	}
}

// Return the results in the desired format
switch ($format)
{
	// JSONinzed
	case 'json':
		echo new JResponseJson($results, null, false, $input->get('ignoreMessages', true, 'bool'));
		break;

	// Human-readable format
	case 'debug':
		echo '<pre>' . print_r($results, true) . '</pre>';
		$app->close();
		break;

	// Handle as raw format
	default:
		// Output exception
		if ($results instanceof Exception)
		{
			// Log an error
			JLog::add($results->getMessage(), JLog::ERROR);

			// Set status header code
			$app->setHeader('status', $results->getCode(), true);

			// Echo exception type and message
			$out = get_class($results) . ': ' . $results->getMessage();
		}
		// Output string/ null
		elseif (is_scalar($results))
		{
			$out = (string) $results;
		}
		// Output array/ object
		else
		{
			$out = implode((array) $results);
		}

		echo $out;

		break;
}
