<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for categories
 *
 * @since  1.5
 */
abstract class Category
{
    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  1.5
     */
    protected static $items = [];

    /**
     * Returns an array of categories for the given extension.
     *
     * @param   string  $extension  The extension option e.g. com_something.
     * @param   array   $config     An array of configuration options. By default, only
     *                              published and unpublished categories are returned.
     *
     * @return  array
     *
     * @since   1.5
     */
    public static function options($extension, $config = ['filter.published' => [0, 1]])
    {
        $hash = md5($extension . '.' . serialize($config));

        if (!isset(static::$items[$hash])) {
            $config = (array) $config;
            $db     = Factory::getDbo();
            $user   = Factory::getUser();

            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('a.id'),
                        $db->quoteName('a.title'),
                        $db->quoteName('a.level'),
                        $db->quoteName('a.language'),
                    ]
                )
                ->from($db->quoteName('#__categories', 'a'))
                ->where($db->quoteName('a.parent_id') . ' > 0');

            // Filter on extension.
            $query->where($db->quoteName('a.extension') . ' = :extension')
                ->bind(':extension', $extension);

            // Filter on user access level
            if (!$user->authorise('core.admin')) {
                $query->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels());
            }

            // Filter on the published state
            if (isset($config['filter.published'])) {
                if (is_numeric($config['filter.published'])) {
                    $query->where($db->quoteName('a.published') . ' = :published')
                        ->bind(':published', $config['filter.published'], ParameterType::INTEGER);
                } elseif (is_array($config['filter.published'])) {
                    $config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
                    $query->whereIn($db->quoteName('a.published'), $config['filter.published']);
                }
            }

            // Filter on the language
            if (isset($config['filter.language'])) {
                if (is_string($config['filter.language'])) {
                    $query->where($db->quoteName('a.language') . ' = :language')
                        ->bind(':language', $config['filter.language']);
                } elseif (is_array($config['filter.language'])) {
                    $query->whereIn($db->quoteName('a.language'), $config['filter.language'], ParameterType::STRING);
                }
            }

            // Filter on the access
            if (isset($config['filter.access'])) {
                if (is_numeric($config['filter.access'])) {
                    $query->where($db->quoteName('a.access') . ' = :access')
                        ->bind(':access', $config['filter_access'], ParameterType::INTEGER);
                } elseif (is_array($config['filter.access'])) {
                    $config['filter.access'] = ArrayHelper::toInteger($config['filter.access']);
                    $query->whereIn($db->quoteName('a.access'), $config['filter.access']);
                }
            }

            $query->order($db->quoteName('a.lft'));

            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Assemble the list options.
            static::$items[$hash] = [];

            foreach ($items as &$item) {
                $repeat      = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
                $item->title = str_repeat('- ', $repeat) . $item->title;

                if ($item->language !== '*') {
                    $item->title .= ' (' . $item->language . ')';
                }

                static::$items[$hash][] = HTMLHelper::_('select.option', $item->id, $item->title);
            }
        }

        return static::$items[$hash];
    }

    /**
     * Returns an array of categories for the given extension.
     *
     * @param   string  $extension  The extension option.
     * @param   array   $config     An array of configuration options. By default, only published and unpublished categories are returned.
     *
     * @return  array   Categories for the extension
     *
     * @since   1.6
     */
    public static function categories($extension, $config = ['filter.published' => [0, 1]])
    {
        $hash = md5($extension . '.' . serialize($config));

        if (!isset(static::$items[$hash])) {
            $config = (array) $config;
            $user   = Factory::getUser();
            $db     = Factory::getDbo();
            $query  = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('a.id'),
                        $db->quoteName('a.title'),
                        $db->quoteName('a.level'),
                        $db->quoteName('a.parent_id'),
                        $db->quoteName('a.language'),
                    ]
                )
                ->from($db->quoteName('#__categories', 'a'))
                ->where($db->quoteName('a.parent_id') . ' > 0');

            // Filter on extension.
            $query->where($db->quoteName('extension') . ' = :extension')
                ->bind(':extension', $extension);

            // Filter on user level.
            if (!$user->authorise('core.admin')) {
                $query->whereIn($db->quoteName('a.access'), $user->getAuthorisedViewLevels());
            }

            // Filter on the published state
            if (isset($config['filter.published'])) {
                if (is_numeric($config['filter.published'])) {
                    $query->where($db->quoteName('a.published') . ' = :published')
                        ->bind(':published', $config['filter.published'], ParameterType::INTEGER);
                } elseif (is_array($config['filter.published'])) {
                    $config['filter.published'] = ArrayHelper::toInteger($config['filter.published']);
                    $query->whereIn($db->quoteName('a.published'), $config['filter.published']);
                }
            }

            $query->order($db->quoteName('a.lft'));

            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Assemble the list options.
            static::$items[$hash] = [];

            foreach ($items as &$item) {
                $repeat      = ($item->level - 1 >= 0) ? $item->level - 1 : 0;
                $item->title = str_repeat('- ', $repeat) . $item->title;

                if ($item->language !== '*') {
                    $item->title .= ' (' . $item->language . ')';
                }

                static::$items[$hash][] = HTMLHelper::_('select.option', $item->id, $item->title);
            }

            // Special "Add to root" option:
            static::$items[$hash][] = HTMLHelper::_('select.option', '1', Text::_('JLIB_HTML_ADD_TO_ROOT'));
        }

        return static::$items[$hash];
    }
}
