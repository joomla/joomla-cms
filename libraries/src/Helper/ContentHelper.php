<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

/**
 * Helper for standard content style extensions.
 * This class mainly simplifies static helper methods often repeated in individual components
 *
 * @since  3.1
 */
class ContentHelper
{
	/**
	 * Configure the Linkbar. Must be implemented by each extension.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public static function addSubmenu($vName)
	{
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   string   $component  The component name.
	 * @param   string   $section    The access section name.
	 * @param   integer  $id         The item ID.
	 *
	 * @return  \JObject
	 *
	 * @since   3.2
	 */
	public static function getActions($component = '', $section = '', $id = 0)
	{
		$assetName = $component;

		if ($section && $id)
		{
			$assetName .=  '.' . $section . '.' . (int) $id;
		}

		$result = new \JObject;

		$user = Factory::getUser();

		$actions = Access::getActionsFromFile(
			JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml', '/access/section[@name="component"]/'
		);

		if ($actions === false)
		{
			Log::add(
				\JText::sprintf('JLIB_ERROR_COMPONENTS_ACL_CONFIGURATION_FILE_MISSING_OR_IMPROPERLY_STRUCTURED', $component), Log::ERROR, 'jerror'
			);

			return $result;
		}

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Gets the current language
	 *
	 * @param   boolean  $detectBrowser  Flag indicating whether to use the browser language as a fallback.
	 *
	 * @return  string  The language string
	 *
	 * @since   3.1
	 * @note    CmsHelper::getCurrentLanguage is the preferred method
	 */
	public static function getCurrentLanguage($detectBrowser = true)
	{
		$app = Factory::getApplication();
		$langCode = null;

		// Get the languagefilter parameters
		if (Multilanguage::isEnabled())
		{
			$plugin       = PluginHelper::getPlugin('system', 'languagefilter');
			$pluginParams = new Registry($plugin->params);

			if ((int) $pluginParams->get('lang_cookie', 1) === 1)
			{
				$langCode = $app->input->cookie->getString(ApplicationHelper::getHash('language'));
			}
			else
			{
				$langCode = $app->getSession()->get('plg_system_languagefilter.language');
			}
		}

		// No cookie - let's try to detect browser language or use site default
		if (!$langCode)
		{
			if ($detectBrowser)
			{
				$langCode = LanguageHelper::detectLanguage();
			}
			else
			{
				$langCode = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			}
		}

		return $langCode;
	}

	/**
	 * Gets the associated language ID
	 *
	 * @param   string  $langCode  The language code to look up
	 *
	 * @return  integer  The language ID
	 *
	 * @since   3.1
	 * @note    CmsHelper::getLanguage() is the preferred method.
	 */
	public static function getLanguageId($langCode)
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select('lang_id')
			->from('#__languages')
			->where($db->quoteName('lang_code') . ' = ' . $db->quote($langCode));
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Gets a row of data from a table
	 *
	 * @param   Table  $table  Table instance for a row.
	 *
	 * @return  array  Associative array of all columns and values for a row in a table.
	 *
	 * @since   3.1
	 */
	public function getRowData(Table $table)
	{
		$data = new CMSHelper;

		return $data->getRowData($table);
	}
}
