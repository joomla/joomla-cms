<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Reference global application object
$app = JFactory::getApplication();

// JInput object
$input = $app->input;

// Initialize default response
$results = '';

// Requested format passed via URL
$format = strtolower($input->getWord('format'));

/*
 * Module support.
 *
 * modFooHelper::getAjax() is called where 'foo' is the value
 * of the 'module' variable passed via the URL
 * (i.e. index.php?option=com_ajax&module=foo).
 *
 */
if ($input->get('module'))
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

		$class  = 'mod' . ucfirst($module) . 'Helper';
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
				$results = new LogicExeption(JText::sprintf('COM_AJAX_METHOD_DOES_NOT_EXIST', $method . 'Ajax'), 500);
			}
		}
		// The helper file does not exist
		else
		{
			$results = new RuntimeException(JText::sprintf('COM_AJAX_HELPER_DOES_NOT_EXIST', 'mod_' . $module . '/helper.php'), 500);
		}
	}
	// Module is not published, you do not have access to it, or it is not assigned to the current menu item
	else
	{
		$results = new LogicException(JText::sprintf('COM_AJAX_MODULE_NOT_PUBLISHED', 'mod_' . $module), 500);
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
if ($input->get('plugin'))
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
		header('Content-Type: application/json');
		echo new JResponseJson($results, null, false, $input->get('ignoreMessages', false, 'bool'));
		break;

	// Human-readable format
	case 'debug':
		echo '<pre>' . print_r($results, true) . '</pre>';
		break;

	// Expect raw
	default:
		// Echo exception messsage
		if ($results instanceof Exception)
		{
			echo $results->getMessage();
		}
		// Echo as a string
		elseif (is_scalar($results))
		{
			echo (string) $results;
		}
		else
		{
			echo implode((array) $results);
		}

		break;
}

// Close the application by default
if ($input->get('close', 1))
{
	$app->close();
}

