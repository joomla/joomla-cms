<?php defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2013 betweenbrain llc. All Rights Reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Reference global application object
$app = JFactory::getApplication();

// JInput object
$input = JFactory::getApplication()->input;

// Requested format passed via URL
$format = strtolower($input->get('format'));

// Initialized to prevent notices
$results = null;
$error   = null;

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

		jimport('joomla.filesystem.file');
		$helperFile = JPATH_ROOT . '/modules/mod_' . $module . '/helper.php';

		$class  = 'mod' . ucfirst($module) . 'Helper';
		$method = $input->get('method') ? $input->get('method') : 'get';

		if (JFile::exists($helperFile))
		{
			require_once($helperFile);

			if (method_exists($class, $method . 'Ajax'))
			{
				$results = call_user_func($class . '::' . $method . 'Ajax');
			}
			else
			{
				$error = JText::sprintf('COM_AJAX_METHOD_DOES_NOT_EXIST', $method . 'Ajax');
			}
		}
		else
		{
			$error = JText::sprintf('COM_AJAX_HELPER_DOES_NOT_EXIST', 'mod_' . $module . '/helper.php');
		}
	}
	else
	{
		$error = JText::_sprintf('COM_AJAX_MODULE_NOT_PUBLISHED', 'mod_' . $module);
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
	$dispatcher = JDispatcher::getInstance();
	$results    = $dispatcher->trigger('onAjax' . $plugin);
}

if (!is_null($error))
{
	echo $error;
	$app->close();
}

// Return the results in the desired format
switch ($format)
{
	case 'json':
		JResponse::setHeader('Content-Type', 'application/json', true);
		echo json_encode($results);
		$app->close();
		break;
	case 'debug':
		echo '<pre>' . print_r($results, true) . '</pre>';
		$app->close();
		break;
	default:
		echo is_array($results) ? implode($results) : $results;
		// Emulates format=raw by closing $app
		$app->close();
		break;
}

/*
 * References
 *  Support plugins in your component
 * - http://docs.joomla.org/Supporting_plugins_in_your_component
 *
 * Best way for JSON output
 * - https://groups.google.com/d/msg/joomla-dev-cms/WsC0nA9Fixo/Ur-gPqpqh-EJ
 *
 */