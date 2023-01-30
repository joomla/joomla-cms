<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * @param   \stdClass[]  &$items  The category or tag objects
     * @param   \stdClass    $config  Configuration object allowing to use a custom relations table
     *
     * @return  \stdClass[]
     *
     * @since   3.9.1
     */
    public static function countRelations(&$items, $config)
    {
        $db = Factory::getDbo();

        // Allow custom state / condition values and custom column names to support custom components
        $counter_names = isset($config->counter_names) ? $config->counter_names : [
            '-2' => 'count_trashed',
            '0'  => 'count_unpublished',
            '1'  => 'count_published',
            '2'  => 'count_archived',
        ];

        // Index category objects by their ID
        $records = [];

        foreach ($items as $item) {
            $records[(int) $item->id] = $item;
        }

        // The relation query does not return a value for cases without relations of a particular state / condition, set zero as default
        foreach ($items as $item) {
            foreach ($counter_names as $n) {
                $item->{$n} = 0;
            }
        }

        // Table alias for related data table below will be 'c', and state / condition column is inside related data table
        $related_tbl = '#__' . $config->related_tbl;
        $state_col   = 'c.' . $config->state_col;

        // Supported cases
        switch ($config->relation_type) {
            case 'tag_assigments':
                $recid_col = 'ct.' . $config->group_col;

                $query = $db->getQuery(true)
                    ->from($db->quoteName('#__contentitem_tag_map', 'ct'))
                    ->join(
                        'INNER',
                        $db->quoteName($related_tbl, 'c'),
                        $db->quoteName('ct.content_item_id') . ' = ' . $db->quoteName('c.id')
                        . ' AND ' . $db->quoteName('ct.type_alias') . ' = :extension'
                    )
                    ->bind(':extension', $config->extension);
                break;

            case 'category_or_group':
                $recid_col = 'c.' . $config->group_col;

                $query = $db->getQuery(true)
                    ->from($db->quoteName($related_tbl, 'c'));
                break;

            default:
                return $items;
        }

        /**
         * Get relation counts for all category objects with single query
         * NOTE: 'state IN', allows counting specific states / conditions only, also prevents warnings with custom states / conditions, do not remove
         */
        $query
            ->select(
                [
                    $db->quoteName($recid_col, 'catid'),
                    $db->quoteName($state_col, 'state'),
                    'COUNT(*) AS ' . $db->quoteName('count'),
                ]
            )
            ->whereIn($db->quoteName($recid_col), array_keys($records))
            ->whereIn($db->quoteName($state_col), array_keys($counter_names))
            ->group($db->quoteName([$recid_col, $state_col]));

        $relationsAll = $db->setQuery($query)->loadObjectList();

        // Loop through the DB data overwriting the above zeros with the found count
        foreach ($relationsAll as $relation) {
            // Sanity check in case someone removes the state IN above ... and some views may start throwing warnings
            if (isset($counter_names[$relation->state])) {
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
     * @param   string   $component  The component name.
     * @param   string   $section    The access section name.
     * @param   integer  $id         The item ID.
     *
     * @return  CMSObject
     *
     * @since   3.2
     */
    public static function getActions($component = '', $section = '', $id = 0)
    {
        $assetName = $component;

        if ($section && $id) {
            $assetName .= '.' . $section . '.' . (int) $id;
        }

        $result = new CMSObject();

        $user = Factory::getUser();

        $actions = Access::getActionsFromFile(
            JPATH_ADMINISTRATOR . '/components/' . $component . '/access.xml',
            '/access/section[@name="component"]/'
        );

        if ($actions === false) {
            Log::add(
                Text::sprintf('JLIB_ERROR_COMPONENTS_ACL_CONFIGURATION_FILE_MISSING_OR_IMPROPERLY_STRUCTURED', $component),
                Log::ERROR,
                'jerror'
            );

            return $result;
        }

        foreach ($actions as $action) {
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
        if (Multilanguage::isEnabled()) {
            $plugin       = PluginHelper::getPlugin('system', 'languagefilter');
            $pluginParams = new Registry($plugin->params);

            if ((int) $pluginParams->get('lang_cookie', 1) === 1) {
                $langCode = $app->input->cookie->getString(ApplicationHelper::getHash('language'));
            } else {
                $langCode = $app->getSession()->get('plg_system_languagefilter.language');
            }
        }

        // No cookie - let's try to detect browser language or use site default
        if (!$langCode) {
            if ($detectBrowser) {
                $langCode = LanguageHelper::detectLanguage();
            } else {
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
            ->select($db->quoteName('lang_id'))
            ->from($db->quoteName('#__languages'))
            ->where($db->quoteName('lang_code') . ' = :language')
            ->bind(':language', $langCode);
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
        $data = new CMSHelper();

        return $data->getRowData($table);
    }
}
