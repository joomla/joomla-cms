<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Component
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Component helper class
 *
 * @since  1.5
 */
class JComponentHelper
{
	/**
	 * The component list cache
	 *
	 * @var    array
	 * @since  1.6
	 */
	protected static $components = array();

	/**
	 * Get the component information.
	 *
	 * @param   string   $option  The component option.
	 * @param   boolean  $strict  If set and the component does not exist, the enabled attribute will be set to false.
	 *
	 * @return  stdClass   An object with the information for the component.
	 *
	 * @since   1.5
	 */
	public static function getComponent($option, $strict = false)
	{
		if (!isset(static::$components[$option]))
		{
			if (static::load($option))
			{
				$result = static::$components[$option];
			}
			else
			{
				$result = new stdClass;
				$result->enabled = $strict ? false : true;
				$result->params = new Registry;
			}
		}
		else
		{
			$result = static::$components[$option];
		}

		if (is_string($result->params))
		{
			$temp = new Registry;
			$temp->loadString(static::$components[$option]->params);
			static::$components[$option]->params = $temp;
		}

		return $result;
	}

	/**
	 * Checks if the component is enabled
	 *
	 * @param   string  $option  The component option.
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public static function isEnabled($option)
	{
		$result = static::getComponent($option, true);

		return $result->enabled;
	}

	/**
	 * Checks if a component is installed
	 *
	 * @param   string  $option  The component option.
	 *
	 * @return  integer
	 *
	 * @since   3.4
	 */
	public static function isInstalled($option)
	{
		$db = JFactory::getDbo();

		return (int) $db->setQuery(
			$db->getQuery(true)
				->select('COUNT(extension_id)')
				->from('#__extensions')
				->where('element = ' . $db->quote($option))
				->where('type = ' . $db->quote('component'))
		)->loadResult();
	}

	/**
	 * Gets the parameter object for the component
	 *
	 * @param   string   $option  The option for the component.
	 * @param   boolean  $strict  If set and the component does not exist, false will be returned
	 *
	 * @return  Registry  A Registry object.
	 *
	 * @see     Registry
	 * @since   1.5
	 */
	public static function getParams($option, $strict = false)
	{
		$component = static::getComponent($option, $strict);

		return $component->params;
	}

	/**
	 * Applies the global text filters to arbitrary text as per settings for current user groups
	 *
	 * @param   string  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @since   2.5
	 */
	public static function filterText($text)
	{
		// Punyencoding utf8 email addresses
		$text = JFilterInput::getInstance()->emailToPunycode($text);

		// Filter settings
		$config     = static::getParams('com_config');
		$user       = JFactory::getUser();
		$userGroups = JAccess::getGroupsByUser($user->get('id'));

		$filters = $config->get('filters');

		$blackListTags       = array();
		$blackListAttributes = array();

		$customListTags       = array();
		$customListAttributes = array();

		$whiteListTags       = array();
		$whiteListAttributes = array();

		$whiteList  = false;
		$blackList  = false;
		$customList = false;
		$unfiltered = false;

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
			$filterType = strtoupper($filterData->filter_type);

			if ($filterType == 'NH')
			{
				// Maximum HTML filtering.
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
				$tags           = explode(',', $filterData->filter_tags);
				$attributes     = explode(',', $filterData->filter_attributes);
				$tempTags       = array();
				$tempAttributes = array();

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
					$blackList           = true;
					$blackListTags       = array_merge($blackListTags, $tempTags);
					$blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
				}
				elseif ($filterType == 'CBL')
				{
					// Only set to true if Tags or Attributes were added
					if ($tempTags || $tempAttributes)
					{
						$customList           = true;
						$customListTags       = array_merge($customListTags, $tempTags);
						$customListAttributes = array_merge($customListAttributes, $tempAttributes);
					}
				}
				elseif ($filterType == 'WL')
				{
					$whiteList           = true;
					$whiteListTags       = array_merge($whiteListTags, $tempTags);
					$whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
				}
			}
		}

		// Remove duplicates before processing (because the black list uses both sets of arrays).
		$blackListTags        = array_unique($blackListTags);
		$blackListAttributes  = array_unique($blackListAttributes);
		$customListTags       = array_unique($customListTags);
		$customListAttributes = array_unique($customListAttributes);
		$whiteListTags        = array_unique($whiteListTags);
		$whiteListAttributes  = array_unique($whiteListAttributes);

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
				$blackListTags       = array_diff($blackListTags, $whiteListTags);
				$blackListAttributes = array_diff($blackListAttributes, $whiteListAttributes);

				$filter = JFilterInput::getInstance($blackListTags, $blackListAttributes, 1, 1);

				// Remove white listed tags from filter's default blacklist
				if ($whiteListTags)
				{
					$filter->tagBlacklist = array_diff($filter->tagBlacklist, $whiteListTags);
				}
				// Remove white listed attributes from filter's default blacklist
				if ($whiteListAttributes)
				{
					$filter->attrBlacklist = array_diff($filter->attrBlacklist, $whiteListAttributes);
				}
			}
			// White lists take third precedence.
			elseif ($whiteList)
			{
				// Turn off XSS auto clean
				$filter = JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
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
	 * @return  string
	 *
	 * @since   1.5
	 * @throws  Exception
	 */
	public static function renderComponent($option, $params = array())
	{
		$app = JFactory::getApplication();

		// Load template language files.
		$template = $app->getTemplate(true)->template;
		$lang = JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
			|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);

		if (empty($option))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		if (JDEBUG)
		{
			JProfiler::getInstance('Application')->mark('beforeRenderComponent ' . $option);
		}

		// Record the scope
		$scope = $app->scope;

		// Set scope to component name
		$app->scope = $option;

		// Build the component path.
		$option = preg_replace('/[^A-Z0-9_\.-]/i', '', $option);
		$file = substr($option, 4);

		// Define component path.
		if (!defined('JPATH_COMPONENT'))
		{
			define('JPATH_COMPONENT', JPATH_BASE . '/components/' . $option);
		}

		if (!defined('JPATH_COMPONENT_SITE'))
		{
			define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/' . $option);
		}

		if (!defined('JPATH_COMPONENT_ADMINISTRATOR'))
		{
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);
		}

		$path = JPATH_COMPONENT . '/' . $file . '.php';

		// If component is disabled throw error
		if (!static::isEnabled($option) || !file_exists($path))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		// Load common and local language files.
		$lang->load($option, JPATH_BASE, null, false, true) || $lang->load($option, JPATH_COMPONENT, null, false, true);

		// Handle template preview outlining.
		$contents = null;

		// Execute the component.
		$contents = static::executeComponent($path);

		// Revert the scope
		$app->scope = $scope;

		if (JDEBUG)
		{
			JProfiler::getInstance('Application')->mark('afterRenderComponent ' . $option);
		}

		return $contents;
	}

	/**
	 * Execute the component.
	 *
	 * @param   string  $path  The component path.
	 *
	 * @return  string  The component output
	 *
	 * @since   1.7
	 */
	protected static function executeComponent($path)
	{
		ob_start();
		require_once $path;
		$contents = ob_get_clean();

		return $contents;
	}

	/**
	 * Load the installed components into the components property.
	 *
	 * @param   string  $option  The element value for the extension
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use JComponentHelper::load() instead
	 */
	protected static function _load($option)
	{
		return static::load($option);
	}

	/**
	 * Load the installed components into the components property.
	 *
	 * @param   string  $option  The element value for the extension
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.2
	 */
	protected static function load($option)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('extension_id AS id, element AS "option", params, enabled')
			->from('#__extensions')
			->where($db->quoteName('type') . ' = ' . $db->quote('component'));
		$db->setQuery($query);

		$cache = JFactory::getCache('_system', 'callback');

		try
		{
			$components = $cache->get(array($db, 'loadObjectList'), array('option'), $option, false);

			/**
			 * Verify $components is an array, some cache handlers return an object even though
			 * the original was a single object array.
			 */
			if (!is_array($components))
			{
				static::$components[$option] = $components;
			}
			else
			{
				static::$components = $components;
			}
		}
		catch (RuntimeException $e)
		{
			/*
			 * Fatal error
			 *
			 * It is possible for this error to be reached before the global JLanguage instance has been loaded so we check for its presence
			 * before logging the error to ensure a human friendly message is always given
			 */

			if (JFactory::$language)
			{
				$msg = JText::sprintf('JLIB_APPLICATION_ERROR_COMPONENT_NOT_LOADING', $option, $e->getMessage());
			}
			else
			{
				$msg = sprintf('Error loading component: %1$s, %2$s', $option, $e->getMessage());
			}

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		if (empty(static::$components[$option]))
		{
			/*
			 * Fatal error
			 *
			 * It is possible for this error to be reached before the global JLanguage instance has been loaded so we check for its presence
			 * before logging the error to ensure a human friendly message is always given
			 */

			if (JFactory::$language)
			{
				$msg = JText::sprintf('JLIB_APPLICATION_ERROR_COMPONENT_NOT_LOADING', $option, JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'));
			}
			else
			{
				$msg = sprintf('Error loading component: %1$s, %2$s', $option, 'Component not found.');
			}

			JLog::add($msg, JLog::WARNING, 'jerror');

			return false;
		}

		return true;
	}
}
