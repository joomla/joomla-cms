<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Component;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\Exception\MissingComponentException;
use Joomla\Registry\Registry;

/**
 * Component helper class
 *
 * @since  1.5
 */
class ComponentHelper
{
	/**
	 * The component list cache
	 *
	 * @var    ComponentRecord[]
	 * @since  1.6
	 */
	protected static $components = array();

	/**
	 * Get the component information.
	 *
	 * @param   string   $option  The component option.
	 * @param   boolean  $strict  If set and the component does not exist, the enabled attribute will be set to false.
	 *
	 * @return  ComponentRecord  An object with the information for the component.
	 *
	 * @since   1.5
	 */
	public static function getComponent($option, $strict = false)
	{
		$components = static::getComponents();

		if (isset($components[$option]))
		{
			return $components[$option];
		}

		$result = new ComponentRecord;
		$result->enabled = $strict ? false : true;
		$result->setParams(new Registry);

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
		$components = static::getComponents();

		return isset($components[$option]) && $components[$option]->enabled;
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
		$components = static::getComponents();

		return isset($components[$option]) ? 1 : 0;
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
		return static::getComponent($option, $strict)->getParams();
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
		$text = \JFilterInput::getInstance()->emailToPunycode($text);

		// Filter settings
		$config     = static::getParams('com_config');
		$user       = \JFactory::getUser();
		$userGroups = Access::getGroupsByUser($user->get('id'));

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

			if ($filterType === 'NH')
			{
				// Maximum HTML filtering.
			}
			elseif ($filterType === 'NONE')
			{
				// No HTML filtering.
				$unfiltered = true;
			}
			else
			{
				// Blacklist or whitelist.
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

				// Collect the blacklist or whitelist tags and attributes.
				// Each list is cummulative.
				if ($filterType === 'BL')
				{
					$blackList           = true;
					$blackListTags       = array_merge($blackListTags, $tempTags);
					$blackListAttributes = array_merge($blackListAttributes, $tempAttributes);
				}
				elseif ($filterType === 'CBL')
				{
					// Only set to true if Tags or Attributes were added
					if ($tempTags || $tempAttributes)
					{
						$customList           = true;
						$customListTags       = array_merge($customListTags, $tempTags);
						$customListAttributes = array_merge($customListAttributes, $tempAttributes);
					}
				}
				elseif ($filterType === 'WL')
				{
					$whiteList           = true;
					$whiteListTags       = array_merge($whiteListTags, $tempTags);
					$whiteListAttributes = array_merge($whiteListAttributes, $tempAttributes);
				}
			}
		}

		// Remove duplicates before processing (because the blacklist uses both sets of arrays).
		$blackListTags        = array_unique($blackListTags);
		$blackListAttributes  = array_unique($blackListAttributes);
		$customListTags       = array_unique($customListTags);
		$customListAttributes = array_unique($customListAttributes);
		$whiteListTags        = array_unique($whiteListTags);
		$whiteListAttributes  = array_unique($whiteListAttributes);

		if (!$unfiltered)
		{
			// Custom blacklist precedes Default blacklist
			if ($customList)
			{
				$filter = \JFilterInput::getInstance(array(), array(), 1, 1);

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
			// Blacklists take second precedence.
			elseif ($blackList)
			{
				// Remove the whitelisted tags and attributes from the black-list.
				$blackListTags       = array_diff($blackListTags, $whiteListTags);
				$blackListAttributes = array_diff($blackListAttributes, $whiteListAttributes);

				$filter = \JFilterInput::getInstance($blackListTags, $blackListAttributes, 1, 1);

				// Remove whitelisted tags from filter's default blacklist
				if ($whiteListTags)
				{
					$filter->tagBlacklist = array_diff($filter->tagBlacklist, $whiteListTags);
				}

				// Remove whitelisted attributes from filter's default blacklist
				if ($whiteListAttributes)
				{
					$filter->attrBlacklist = array_diff($filter->attrBlacklist, $whiteListAttributes);
				}
			}
			// Whitelists take third precedence.
			elseif ($whiteList)
			{
				// Turn off XSS auto clean
				$filter = \JFilterInput::getInstance($whiteListTags, $whiteListAttributes, 0, 0, 0);
			}
			// No HTML takes last place.
			else
			{
				$filter = \JFilterInput::getInstance();
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
	 * @throws  MissingComponentException
	 */
	public static function renderComponent($option, $params = array())
	{
		$app = \JFactory::getApplication();

		// Load template language files.
		$template = $app->getTemplate(true)->template;
		$lang = \JFactory::getLanguage();
		$lang->load('tpl_' . $template, JPATH_BASE, null, false, true)
			|| $lang->load('tpl_' . $template, JPATH_THEMES . "/$template", null, false, true);

		if (empty($option))
		{
			throw new MissingComponentException(\JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
		}

		if (JDEBUG)
		{
			\JProfiler::getInstance('Application')->mark('beforeRenderComponent ' . $option);
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
			/**
			 * Defines the path to the active component for the request
			 *
			 * Note this constant is application aware and is different for each application (site/admin).
			 *
			 * @var    string
			 * @since  1.5
			 */
			define('JPATH_COMPONENT', JPATH_BASE . '/components/' . $option);
		}

		if (!defined('JPATH_COMPONENT_SITE'))
		{
			/**
			 * Defines the path to the site element of the active component for the request
			 *
			 * @var    string
			 * @since  1.5
			 */
			define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/' . $option);
		}

		if (!defined('JPATH_COMPONENT_ADMINISTRATOR'))
		{
			/**
			 * Defines the path to the admin element of the active component for the request
			 *
			 * @var    string
			 * @since  1.5
			 */
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);
		}

		$path = JPATH_COMPONENT . '/' . $file . '.php';

		// If component is disabled throw error
		if (!static::isEnabled($option) || !file_exists($path))
		{
			throw new MissingComponentException(\JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'), 404);
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
			\JProfiler::getInstance('Application')->mark('afterRenderComponent ' . $option);
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

		return ob_get_clean();
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
	 * @note    As of 4.0 this method will be restructured to only load the data into memory
	 */
	protected static function load($option)
	{
		$loader = function ()
		{
			$db = \JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName(array('extension_id', 'element', 'params', 'enabled'), array('id', 'option', null, null)))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('type') . ' = ' . $db->quote('component'))
				->where($db->quoteName('state') . ' = 0')
				->where($db->quoteName('enabled') . ' = 1');
			$db->setQuery($query);

			return $db->loadObjectList('option', '\JComponentRecord');
		};

		/** @var \JCacheControllerCallback $cache */
		$cache = \JFactory::getCache('_system', 'callback');

		try
		{
			static::$components = $cache->get($loader, array(), __METHOD__);
		}
		catch (\JCacheException $e)
		{
			static::$components = $loader();
		}

		// Core CMS will use '*' as a placeholder for required parameter in this method. In 4.0 this will not be passed at all.
		if (isset($option) && $option != '*')
		{
			// Log deprecated warning and display missing component warning only if using deprecated format.
			try
			{
				\JLog::add(
					sprintf(
						'Passing a parameter into %s() is deprecated and will be removed in 4.0. Read %s::$components directly after loading the data.',
						__METHOD__,
						__CLASS__
					),
					\JLog::WARNING,
					'deprecated'
				);
			}
			catch (\RuntimeException $e)
			{
				// Informational log only
			}

			if (empty(static::$components[$option]))
			{
				/*
				 * Fatal error
				 *
				 * It is possible for this error to be reached before the global \JLanguage instance has been loaded so we check for its presence
				 * before logging the error to ensure a human friendly message is always given
				 */

				if (\JFactory::$language)
				{
					$msg = \JText::sprintf('JLIB_APPLICATION_ERROR_COMPONENT_NOT_LOADING', $option, \JText::_('JLIB_APPLICATION_ERROR_COMPONENT_NOT_FOUND'));
				}
				else
				{
					$msg = sprintf('Error loading component: %1$s, %2$s', $option, 'Component not found.');
				}

				\JLog::add($msg, \JLog::WARNING, 'jerror');

				return false;
			}
		}

		return true;
	}

	/**
	 * Get installed components
	 *
	 * @return  ComponentRecord[]  The components property
	 *
	 * @since   3.6.3
	 */
	public static function getComponents()
	{
		if (empty(static::$components))
		{
			static::load('*');
		}

		return static::$components;
	}
}
