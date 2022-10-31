<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Moduleversion
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Moduleversion;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for plugin moduleversion.
 *
 * @since  1.6
 */
abstract class Helper
{
    /**
     * Datbase helper to get the current module versions.
     *
     * @param   int $moduleId module ID
     * @return array
     */
    public static function getVersions($moduleId): array
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $query->select(
            $db->quoteName(
                array(
                    'id',
                    'current',
                    'mod_id',
                    'title',
                    'note',
                    'content',
                    'ordering',
                    'position',
                    'published',
                    'module',
                    'access',
                    'showtitle',
                    'params',
                    'client_id',
                    'language',
                    'changedate'
                )
            )
        );

        $query
            ->from($db->quoteName('#__modules_versions'))
            ->where($db->quoteName('mod_id') . ' LIKE ' . $db->quote($moduleId))
            ->order($db->quoteName('id') . 'DESC');

        $db->setQuery($query);

        $results = $db->loadObjectList();

        return $results;
    }

    /**
     * Datbase helper to store the current module versions.
     *
     * @param   object $item Current module item
     * @return  void
     */
    public static function storeVersion($item)
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $fields = array_merge(
            self::filterContent((array) $item),
            [
                'mod_id' => $item->id,
                'current' => true,
            ]
        );

        $columns = array_keys($fields);

        $values = array_map(
            function ($value) use ($db) {
                return $db->quote($value);
            },
            $fields
        );

        $query
            ->insert($db->quoteName('#__modules_versions'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Datbase helper to reset the star icon.
     *
     * @param   int $modId  The module ID.
     * @return  void
     */
    public static function resetCurrent(int $modId)
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('current') . ' = ' . 0
        );

        $conditions = [
            $db->quoteName('current') . ' = ' . 1,
            $db->quoteName('mod_id') . ' = ' . $modId,
        ];

        $query
            ->update($db->quoteName('#__modules_versions'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Datbase helper to set the star icon.
     *
     * @param   int $id    Current module item.
     * @param   int $modId The module ID.
     * @return  void
     */
    public static function setCurrent($id, $modId)
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $fields = array(
            $db->quoteName('current') . ' = CASE WHEN ' .
                $db->quoteName('id') . ' = ' . $db->quote($id) . ' THEN ' . 1 . ' ELSE ' . 0 . ' END'
        );

        $conditions = array(
            $db->quoteName('mod_id') . ' = ' . $modId
        );

        $query
            ->update($db->quoteName('#__modules_versions'))
            ->set($fields)
            ->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Database helper to update the module with the selected module versions.
     *
     * @param   \stdClass $item Current module item.
     * @return  void
     */
    public static function updateModuleToVersion(\stdClass $item): void
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $fields = array_map(
            function (string $key, $value) use ($db) {
                return $db->quoteName($key) . ' = ' . $db->quote($value);
            },
            array_keys($temp = self::filterContent((array) $item, ['mod_id'])), array_values($temp)
        );

        $conditions = array(
            $db->quoteName('id') . ' = ' . $item->mod_id //phpcs:ignore
        );

        $query->update($db->quoteName('#__modules'))->set($fields)->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Datbase helper to delete verions of trashed modules.
     *
     * @param   int   $item Current module item
     * @return  void
     */
    public static function deleteVersion($item)
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('mod_id') . ' = ' . $item,
        );

        $query
            ->delete($db->quoteName('#__modules_versions'))
            ->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Datbase helper to delete verions of trashed modules.
     *
     * @param   string   $eid Current extension ID
     * @return  void
     */
    public static function uninstallVersion($eid)
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $query->select($db->quoteName(array('element', 'client_id')));
        $query->from($db->quoteName('#__extensions'));
        $query->where($db->quoteName('extension_id') . " = " . $db->quote($eid));

        $db->setQuery($query);

        $moduleElement = $db->loadObjectList();

        // Remove the module versions of current module
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $conditions = array(
            $db->quoteName('module') . ' = ' . $db->quote($moduleElement[0]->element),
            $db->quoteName('client_id') . ' = ' . $db->quote($moduleElement[0]->client_id),
        );

        $query
            ->delete($db->quoteName('#__modules_versions'))
            ->where($conditions);

        $db->setQuery($query);

        $db->execute();
    }

    /**
     * Datbase helper to compare the current module settings and the latest version in DB
     * @param   \stdClass $moduleSettings The current module item.
     * @param   \stdClass $loadedVersion  The version loaded from the database.
     * @return  boolean
     */
    public static function compareVersion($moduleSettings, $loadedVersion)
    {
        $settingsChanged = false;

        $source = self::filterContent((array) $moduleSettings);
        $target = self::filterContent((array) $loadedVersion);

        return (bool) count(array_diff_assoc($source, $target));
    }

    /**
     * Filters the module content for the values.
     *
     * @param   array $content  The module content.
     * @param   array $excluded The excluded key from the filter.
     * @return  array<string, mixed>
     */
    protected static function filterContent(array $content, array $excluded = []): array
    {
        return array_filter(
            $content,
            function ($key) use ($excluded) {
                return !in_array($key, $excluded, true) && in_array(
                    $key,
                    [
                        'mod_id',
                        'title',
                        'note',
                        'content',
                        'ordering',
                        'position',
                        'published',
                        'module',
                        'access',
                        'showtitle',
                        'params',
                        'client_id',
                        'language',
                    ],
                    true
                );
            },
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Create the html params table from the object
     * @param   object   $values	Object with module parameters
     * @return string
     */
    public static function formatOutput($values): string
    {
        if (is_object($values))
        {
            $values = get_object_vars($values);
        }

        $output = '<dl class="dl-horizontal">';

        foreach ($values as $key => $val)
        {
            if (is_object($val) || is_array($val))
            {
                $val = is_object($val) ? get_object_vars($val) : $val;

                $output .= '<dt>' . $key . '</dt><dd>' . self::formatOutput($val) . '</dd>';
            }
            else
            {
                $val = empty($val) ? '' : $val;

                $val = str_replace('src="images', 'src="' . URI::root(true) . '/images', $val);

                $output .= '<dt class="d-flex justify-content-between"><span>' . $key;
                $output .= '</span><span class="ms-1">:</span></dt><dd>' . $val . '</dd>';
            }
        }

        $output .= '</dl>';

        return($output);
    }

    /**
     * Datbase helper to remove obsolete versions.
     *
     * @param   int $id Current module item
     * @return  void
     */
    public static function removeObsolete($id)
    {
        /**
         * @var \Joomla\Database\DatabaseDriver $db
         */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);

        $query
            ->delete($db->quoteName('#__modules_versions'))
            ->where($db->quoteName('mod_id') . ' LIKE ' . $db->quote($id))
            ->order($db->quoteName('id') . 'DESC')
            ->setLimit(1);

        $db->setQuery($query);

        $db->execute();
    }
}
