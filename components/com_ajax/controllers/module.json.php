<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The Module Controller for JSON format
 *
 * Module support.
 * modFooHelper::getAjax() is called where 'foo' is the value
 * of the 'name' variable passed via the URL
 * (i.e. index.php?option=com_ajax&task=module.call&name=foo&format=json)
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 */
class AjaxControllerModule extends JControllerLegacy
{

	/**
	 * Do job!
	 *
	 */
	public function call()
	{
		$name	= $this->input->get('name');
		$name 	= strstr($name, 'mod_') ? $name : 'mod_' . $name;
		$module	= JModuleHelper::getModule($name, null);

		/*
		 * As JModuleHelper::isEnabled always returns true, we check
		 * for an id other than 0 to see if it is published.
		 */
		if ($module->id)
		{
			$helperFile = JPATH_BASE . '/modules/' . $name . '/helper.php';

			$class  = 'mod' . ucfirst($module->name) . 'Helper';
			$method = $this->input->get('method', 'get');

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
					$results = new RuntimeException(JText::sprintf('COM_AJAX_METHOD_DOES_NOT_EXIST', $method . 'Ajax'), 404);
				}
			}
			// The helper file does not exist
			else
			{
				$results = new RuntimeException(JText::sprintf('COM_AJAX_HELPER_DOES_NOT_EXIST', $name . '/helper.php'), 404);
			}
		}
		// Module is not published, you do not have access to it, or it is not assigned to the current menu item
		else
		{
			$results = new RuntimeException(JText::sprintf('COM_AJAX_MODULE_NOT_PUBLISHED', $name), 404);
		}

		// Output
		echo new JResponseJson($results, null, false, $this->input->get('ignoreMessages', true, 'bool'));

		return true;
	}
}
