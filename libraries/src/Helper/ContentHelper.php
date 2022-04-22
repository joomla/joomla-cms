<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Access\Access;
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
	 * Adds Count relations for Category and Tag Managers
	 *
	 * @param   stdClass[]  &$items  The category or tag objects
	 * @param   stdClass    $config  Configuration object allowing to use a custom relations table
	 *
	 * @return  stdClass[]
	 *
	 * @since   3.9.1
	 */
	public static function countRelations(&$items, $config)
	{
		$db = Factory::getDbo();

		// Allow custom state / condition values and custom column names to support custom components
		$counter_names = isset($config->counter_names) ? $config->counter_names : array(
			'-2' => 'count_trashed',
			'0'  => 'count_unpublished',
			'1'  => 'count_published',
			'2'  => 'count_archived',
		);

		// Index category objects by their ID
		$records = array();

		foreach ($items as $item)
		{
			$records[(int) $item->id] = $item;
		}

		// The relation query does not return a value for cases without relations of a particular state / condition, set zero as default
		foreach ($items as $item)
		{
			foreach ($counter_names as $n)
			{
				$item->{$n} = 0;
			}
		}

		// Table alias for related data table below will be 'c', and state / condition column is inside related data table
		$related_tbl = $db->quoteName('#__' . $config->related_tbl, 'c');
		$state_col   = $db->quoteName('c.' . $config->state_col);

		// Supported cases
		switch ($config->relation_type)
		{
			case 'tag_assigments':
				$recid_col = $db->quoteName('ct.' . $config->group_col);

				$query = $db->getQuery(true)
					->from($db->quoteName('#__contentitem_tag_map', 'ct'))
					->join('INNER', $related_tbl . ' ON ' . $db->quoteName('ct.content_item_id') . ' = ' . $db->quoteName('c.id') . ' AND ' .
						$db->quoteName('ct.type_alias') . ' = ' . $db->quote($config->extension)
					);
				break;

			case 'category_or_group':
				$recid_col = $db->quoteName('c.' . $config->group_col);

				$query = $db->getQuery(true)
					->from($related_tbl);
				break;

			default:
				return $items;
		}

		/**
		 * Get relation counts for all category objects with single query
		 * NOTE: 'state IN', allows counting specific states / conditions only, also prevents warnings with custom states / conditions, do not remove
		 */
		$query
			->select($recid_col . ' AS catid, ' . $state_col . ' AS state, COUNT(*) AS count')
			->where($recid_col . ' IN (' . implode(',', array_keys($records)) . ')')
			->where($state_col . ' IN (' . implode(',', array_keys($counter_names)) . ')')
			->group($recid_col . ', ' . $state_col);

		$relationsAll = $db->setQuery($query)->loadObjectList();

		// Loop through the DB data overwriting the above zeros with the found count
		foreach ($relationsAll as $relation)
		{
			// Sanity check in case someone removes the state IN above ... and some views may start throwing warnings
			if (isset($counter_names[$relation->state]))
			{
				$id = (int) $relation->catid;
				$cn = $counter_names[$relation->state];

				$records[$id]->{$cn} = $relation->count;
			}
		}

		return $items;
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  $categoryId  The category ID.
	 * @param   integer  $id          The item ID.
	 * @param   string   $assetName   The asset name
	 *
	 * @return  \JObject
	 *
	 * @since   3.1
	 * @deprecated  3.2  Use ContentHelper::getActions() instead
	 */
	public static function _getActions($categoryId = 0, $id = 0, $assetName = '')
	{
		// Log usage of deprecated function
		Log::add(__METHOD__ . '() is deprecated, use ContentHelper::getActions() with new arguments order instead.', Log::WARNING, 'deprecated');

		// Reverted a change for version 2.5.6
		$user   = Factory::getUser();
		$result = new \JObject;

		$path = JPATH_ADMINISTRATOR . '/components/' . $assetName . '/access.xml';

		if (empty($id) && empty($categoryId))
		{
			$section = 'component';
		}
		elseif (empty($id))
		{
			$section = 'category';
			$assetName .= '.category.' . (int) $categoryId;
		}
		else
		{
			// Used only in com_content
			$section = 'article';
			$assetName .= '.article.' . (int) $id;
		}

		$actions = Access::getActionsFromFile($path, "/access/section[@name='" . $section . "']/");

		foreach ($actions as $action)
		{
			$result->set($action->name, $user->authorise($action->name, $assetName));
		}

		return $result;
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
		// Check for deprecated arguments order
		if (is_int($component) || $component === null)
		{
			$result = self::_getActions($component, $section, $id);

			return $result;
		}

		$assetName = $component;

		if ($section && $id)
		{
			$assetName .= '.' . $section . '.' . (int) $id;
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
				$langCode = Factory::getSession()->get('plg_system_languagefilter.language');
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
