<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for associations in multilang
 *
 * @since  3.1
 */
class Associations
{
    /**
     * Get the associations.
     *
     * @param   string   $extension   The name of the component.
     * @param   string   $tablename   The name of the table.
     * @param   string   $context     The context
     * @param   integer  $id          The primary key value.
     * @param   string   $pk          The name of the primary key in the given $table.
     * @param   string   $aliasField  If the table has an alias field set it here. Null to not use it
     * @param   string   $catField    If the table has a catid field set it here. Null to not use it
     * @param   array    $advClause   Additional advanced 'where' clause; use c as parent column key, c2 as associations column key
     *
     * @return  array  The associated items
     *
     * @since   3.1
     *
     * @throws  \Exception
     */
    public static function getAssociations(
        $extension,
        $tablename,
        $context,
        $id,
        $pk = 'id',
        $aliasField = 'alias',
        $catField = 'catid',
        $advClause = []
    ) {
        // To avoid doing duplicate database queries.
        static $multilanguageAssociations = [];

        // Cast before creating cache key.
        $id = (int) $id;

        // Multilanguage association array key. If the key is already in the array we don't need to run the query again, just return it.
        $queryKey = md5(serialize(array_merge([$extension, $tablename, $context, $id], $advClause)));

        if (!isset($multilanguageAssociations[$queryKey])) {
            $multilanguageAssociations[$queryKey] = [];

            $db                 = Factory::getDbo();
            $query              = $db->getQuery(true);
            $categoriesExtraSql = '';

            if ($tablename === '#__categories') {
                $categoriesExtraSql = ' AND c2.extension = :extension1';
                $query->bind(':extension1', $extension);
            }

            $query->select($db->quoteName('c2.language'))
                ->from($db->quoteName($tablename, 'c'))
                ->join(
                    'INNER',
                    $db->quoteName('#__associations', 'a'),
                    $db->quoteName('a.id') . ' = ' . $db->quoteName('c.' . $pk)
                    . ' AND ' . $db->quoteName('a.context') . ' = :context'
                )
                ->bind(':context', $context)
                ->join('INNER', $db->quoteName('#__associations', 'a2'), $db->quoteName('a.key') . ' = ' . $db->quoteName('a2.key'))
                ->join(
                    'INNER',
                    $db->quoteName($tablename, 'c2'),
                    $db->quoteName('a2.id') . ' = ' . $db->quoteName('c2.' . $pk) . $categoriesExtraSql
                );

            // Use alias field ?
            if (!empty($aliasField)) {
                $query->select(
                    $query->concatenate(
                        [
                            $db->quoteName('c2.' . $pk),
                            $db->quoteName('c2.' . $aliasField),
                        ],
                        ':'
                    ) . ' AS ' . $db->quoteName($pk)
                );
            } else {
                $query->select($db->quoteName('c2.' . $pk));
            }

            // Use catid field ?
            if (!empty($catField)) {
                $query->join(
                    'INNER',
                    $db->quoteName('#__categories', 'ca'),
                    $db->quoteName('c2.' . $catField) . ' = ' . $db->quoteName('ca.id') . ' AND ' . $db->quoteName('ca.extension') . ' = :extension2'
                )
                    ->bind(':extension2', $extension)
                    ->select(
                        $query->concatenate(
                            [
                                $db->quoteName('ca.id'),
                                $db->quoteName('ca.alias'),
                            ],
                            ':'
                        ) . ' AS ' . $db->quoteName($catField)
                    );
            }

            $query->where($db->quoteName('c.' . $pk) . ' = :id')
                ->bind(':id', $id, ParameterType::INTEGER);

            if ($tablename === '#__categories') {
                $query->where($db->quoteName('c.extension') . ' = :extension3')
                    ->bind(':extension3', $extension);
            }

            // Advanced where clause
            if (!empty($advClause)) {
                foreach ($advClause as $clause) {
                    $query->where($clause);
                }
            }

            $db->setQuery($query);

            try {
                $items = $db->loadObjectList('language');
            } catch (\RuntimeException $e) {
                throw new \Exception($e->getMessage(), 500, $e);
            }

            if ($items) {
                foreach ($items as $tag => $item) {
                    // Do not return itself as result
                    if ((int) $item->{$pk} !== $id) {
                        $multilanguageAssociations[$queryKey][$tag] = $item;
                    }
                }
            }
        }

        return $multilanguageAssociations[$queryKey];
    }

    /**
     * Method to determine if the language filter Associations parameter is enabled.
     * This works for both site and administrator.
     *
     * @return  boolean  True if the parameter is implemented; false otherwise.
     *
     * @since   3.2
     */
    public static function isEnabled()
    {
        // Flag to avoid doing multiple database queries.
        static $tested = false;

        // Status of language filter parameter.
        static $enabled = false;

        if (Multilanguage::isEnabled()) {
            // If already tested, don't test again.
            if (!$tested) {
                $plugin = PluginHelper::getPlugin('system', 'languagefilter');

                if (!empty($plugin)) {
                    $params = new Registry($plugin->params);
                    $enabled  = (bool) $params->get('item_associations', true);
                }

                $tested = true;
            }
        }

        return $enabled;
    }
}
