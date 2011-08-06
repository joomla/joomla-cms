<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Component helper class
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JComponentHelper
{
	/**
	 * The component list cache
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected static $_components = array();

	/**
	 * Get the component information.
	 *
	 * @param   string   $option  The component option.
	 * @param   boolean  $strict  If set and the component does not exist, the enabled attribue will be set to false.
	 *
	 * @return  object   An object with the information for the component.
	 *
	 * @since   11.1
	 */
	public static function getComponent($option, $strict = false)
	{
		if (!isset(self::$_components[$option]))
		{
			if (self::_load($option))
			{
				$result = self::$_components[$option];
			}
			else
			{
				$result = new stdClass();
				$result->enabled = $strict ? false : true;
				$result->params = new JRegistry();
			}
		}
		else
		{
			$result = self::$_components[$option];
		}

		return $result;
	}

	/**
	 * Checks if the component is enabled
	 *
	 * @param   string   $option  The component option.
	 * @param   boolean  $strict  If set and the component does not exist, false will be returned.
	 *
	 * @return  boolean
	 *
	 * @since   11.1
	 */
	public static function isEnabled($option, $strict = false)
	{
		$result = self::getComponent($option, $strict);

		return ($result->enabled | JFactory::getApplication()->isAdmin());
	}

	/**
	 * Gets the parameter object for the component
	 *
	 * @param   string   $option  The option for the component.
	 * @param   boolean  $strict  If set and the component does not exist, false will be returned
	 *
	 * @return  JRegistry  A JRegistry object.
	 *
	 * @see     JRegistry
	 * @since   11.1
	 */
	public static function getParams($option, $strict = false)
	{
		$component = self::getComponent($option, $strict);

		return $component->params;
	}

	/**
	 * Render the component.
	 *
	 * @param   string  $option  The component option.
	 * @param   array   $params  The component parameters
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public static function renderComponent($option, $params = array())
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		// Load template language files.
		$template = $app->getTemplate(true)->template;
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, false)
			|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, false)
			|| $lang->load('tpl_' . $template, JPATH_BASE, $lang->getDefault(), false, false)
			|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", $lang->getDefault(), false, false);

		if (empty($option))
		{
			// Throw 404 if no component
			JError::raiseError(404, JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'));
			return;
		}

		// Record the scope
		$scope = $app->scope;
		// Set scope to component name
		$app->scope = $option;

		// Build the component path.
		$option = preg_replace('/[^A-Z0-9_\.-]/i', '', $option);
		$file = substr($option, 4);

		// Define component path.
		define('JPATH_COMPONENT', JPATH_BASE . '/components/' . $option);
		define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/' . $option);
		define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);

		// Get component path
		if ($app->isAdmin() && file_exists(JPATH_COMPONENT . '/admin.' . $file . '.php'))
		{
			$path = JPATH_COMPONENT . '/admin.' . $file . '.php';
		}
		else
		{
			$path = JPATH_COMPONENT . '/' . $file . '.php';
		}

		// If component is disabled throw error
		if (!self::isEnabled($option) || !file_exists($path))
		{
			JError::raiseError(404, JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'));
		}

		$task = JRequest::getString('task');

		// Load common and local language files.
		$lang->load($option, JPATH_BASE, null, false, false) || $lang->load($option, JPATH_COMPONENT, null, false, false)
			|| $lang->load($option, JPATH_BASE, $lang->getDefault(), false, false)
			|| $lang->load($option, JPATH_COMPONENT, $lang->getDefault(), false, false);

		// Handle template preview outlining.
		$contents = null;

		// Execute the component.
		ob_start();
		require_once $path;
		$contents = ob_get_contents();
		ob_end_clean();

		// Build the component toolbar
		jimport('joomla.application.helper');

		if (($path = JApplicationHelper::getPath('toolbar')) && $app->isAdmin())
		{
			// Get the task again, in case it has changed
			$task = JRequest::getString('task');

			// Make the toolbar
			include_once $path;
		}

		// Revert the scope
		$app->scope = $scope;

		return $contents;
	}

	/**
	 * Load the installed components into the _components property.
	 *
	 * @param   string  $option  The element value for the extension
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	protected static function _load($option)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('extension_id AS "id", element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where($query->qn('type') . ' = ' . $db->quote('component'));
		$query->where($query->qn('element') . ' = ' . $db->quote($option));
		$db->setQuery($query);

		$cache = JFactory::getCache('_system', 'callback');

		self::$_components[$option] = $cache->get(array($db, 'loadObject'), null, $option, false);

		if ($error = $db->getErrorMsg() || empty(self::$_components[$option]))
		{
			// Fatal error.
			JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_COMPONENT_NOT_LOADING', $option, $error));
			return false;
		}

		// Convert the params to an object.
		if (is_string(self::$_components[$option]->params))
		{
			$temp = new JRegistry();
			$temp->loadString(self::$_components[$option]->params);
			self::$_components[$option]->params = $temp;
		}

		return true;
	}
}
