<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

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
	protected static $components = array();

	/**
	 * Get the component information.
	 *
	 * @param   string   $option  The component option.
	 * @param   boolean  $strict  If set and the component does not exist, the enabled attribute will be set to false.
	 *
	 * @return  object   An object with the information for the component.
	 *
	 * @since   11.1
	 */
	public static function getComponent($option, $strict = false)
	{
		if (!isset(self::$components[$option]))
		{
			if (self::_load($option))
			{
				$result = self::$components[$option];
			}
			else
			{
				$result = new stdClass;
				$result->enabled = $strict ? false : true;
				$result->params = new JRegistry;
			}
		}
		else
		{
			$result = self::$components[$option];
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
	 * Applies the global text filters to arbitrary text as per settings for current user groups
	 *
	 * @param   string  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @since   11.4
	 */
	public static function filterText($text)
	{
		// Filter settings
		$config		= self::getParams('com_config');
		$user		= JFactory::getUser();
		$userGroups	= JAccess::getGroupsByUser($user->get('id'));

		$filters = $config->get('filters');

		$blackListTags			= array();
		$blackListAttributes	= array();

		$customListTags			= array();
		$customListAttributes	= array();

		$whiteListTags			= array();
		$whiteListAttributes	= array();

		$noHtml		= false;
		$whiteList	= false;
		$blackList	= false;
		$customList	= false;
		$unfiltered	= false;

		// Cycle through each of the user groups the user is in.
		// Remember they are included in the Public group as well.
		foreach ($userGroups as $groupId)
		{
			// May have added a group by not saved the filters.
			if (!isset($filters->$groupId))
			{
				continue;
			}

			// Each group the user is in could have different filtering properties.
			$filterData = $filters->$groupId;
			$filterType	= strtoupper($filterData->filter_type);

			if ($filterType == 'NH')
			{
				// Maximum HTML filtering.
				$noHtml = true;
			}
			elseif ($filterType == 'NONE')
			{
				// No HTML filtering.
				$unfiltered = true;
			}
			else
			{
				// Black or white list.
				// Preprocess the tags and attributes.
				$tags			= explode(',', $filterData->filter_tags);
				$attributes		= explode(',', $filterData->filter_attributes);
				$tempTags		= array();
				$tempAttributes	= array();

				foreach ($tags as $tag)
				{
					$tag = trim($tag);

					if ($tag)
					{
						$tempTags[] = $tag;
					}
				}

				foreach ($attributes as $attribute)
				{
					$attribute = trim($attribute);

					if ($attribute)
					{
						$tempAttributes[] = $attribute;
					}
				}

				// Collect the black or white list tags and attributes.
				// Each list is cummulative.
				if ($filterType == 'BL')
				{
					$blackList				= true;
					$blackListTags			= array_merge($blackListTags, $tempTags);
					$blackListAttributes	= array_merge($blackListAttributes, $tempAttributes);
				}
				elseif ($filterType == 'CBL')
				{
					// Only set to true if Tags or Attributes were added
					if ($tempTags || $tempAttributes)
					{
						$customList				= true;
						$customListTags			= array_merge($customListTags, $tempTags);
						$customListAttributes	= array_merge($customListAttributes, $tempAttributes);
					}
				}
				elseif ($filterType == 'WL')
				{
					$whiteList				= true;
					$whiteListTags			= array_merge($whiteListTags, $tempTags);
					$whiteListAttributes	= array_merge($whiteListAttributes, $tempAttributes);
				}
			}
		}

		// Remove duplicates before processing (because the black list uses both sets of arrays).
		$blackListTags			= array_unique($blackListTags);
		$blackListAttributes	= array_unique($blackListAttributes);
		$customListTags			= array_unique($customListTags);
		$customListAttributes	= array_unique($customListAttributes);
		$whiteListTags			= array_unique($whiteListTags);
		$whiteListAttributes	= array_unique($whiteListAttributes);

		// Unfiltered assumes first priority.
		if ($unfiltered)
		{
			// Dont apply filtering.
		}
		else
		{
			// Custom blacklist precedes Default blacklist
			if ($customList)
			{
				$filter = JFilterInput::getInstance(array(), array(), 1, 1);

				// Override filter's default blacklist tags and attributes
				if ($customListTags)
				{
					$filter->tagBlacklist = $customListTags;
				}
				if ($customListAttributes)
				{
					$filter->attrBlacklist = $customListAttributes;
				}
			}
			// Black lists take second precedence.
			elseif ($blackList)
			{
				// Remove the white-listed tags and attributes from the black-list.
				$blackListTags			= array_diff($blackListTags, $whiteListTags);
				$blackListAttributes	= array_diff($blackListAttributes, $whiteListAttributes);

				$filter = JFilterInput::getInstance($blackListTags, $blackListAttributes, 1, 1);

				// Remove white listed tags from filter's default blacklist
				if ($whiteListTags)
				{
					$filter->tagBlacklist = array_diff($filter->tagBlacklist, $whiteListTags);
				}
				// Remove white listed attributes from filter's default blacklist
				if ($whiteListAttributes)
				{
					$filter->attrBlacklist = array_diff($filter->attrBlacklist);
				}
			}
			// White lists take third precedence.
			elseif ($whiteList)
			{
				// Turn off xss auto clean
				$filter	= JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
			}
			// No HTML takes last place.
			else
			{
				$filter = JFilterInput::getInstance();
			}

			$text = $filter->clean($text, 'html');
		}

		return $text;
	}

	/**
	 * Render the component.
	 *
	 * @param   string  $option  The component option.
	 * @param   array   $params  The component parameters
	 *
	 * @return  object
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
			JLog::add('Files in the format admin.COMPONENTNAME.php are considered deprecated and will not be loaded in Joomla 3.0.', JLog::WARNING, 'deprecated');
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
		$contents = self::executeComponent($path);

		// Build the component toolbar
		$path = JApplicationHelper::getPath('toolbar');
		if ($path && $app->isAdmin())
		{
			JLog::add('Files in the format toolbar.COMPONENTNAME.php are considered deprecated and will not be loaded in Joomla 3.0.', JLog::WARNING, 'deprecated');
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
	 * Execute the component.
	 *
	 * @param   string  $path  The component path.
	 *
	 * @return  string  The component output
	 *
	 * @since   11.3
	 */
	protected static function executeComponent($path)
	{
		ob_start();
		require_once $path;
		$contents = ob_get_contents();
		ob_end_clean();
		return $contents;
	}

	/**
	 * Load the installed components into the components property.
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
		$query->select('extension_id AS id, element AS "option", params, enabled');
		$query->from('#__extensions');
		$query->where($query->qn('type') . ' = ' . $db->quote('component'));
		$query->where($query->qn('element') . ' = ' . $db->quote($option));
		$db->setQuery($query);

		$cache = JFactory::getCache('_system', 'callback');

		self::$components[$option] = $cache->get(array($db, 'loadObject'), null, $option, false);

		if ($error = $db->getErrorMsg() || empty(self::$components[$option]))
		{
			// Fatal error.
			JError::raiseWarning(500, JText::sprintf('JLIB_APPLICATION_ERROR_COMPONENT_NOT_LOADING', $option, $error));
			return false;
		}

		// Convert the params to an object.
		if (is_string(self::$components[$option]->params))
		{
			$temp = new JRegistry;
			$temp->loadString(self::$components[$option]->params);
			self::$components[$option]->params = $temp;
		}

		return true;
	}
}
