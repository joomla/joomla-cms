<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Newsfeeds\Administrator\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Newsfeeds component helper.
 *
 * @since  1.6
 */
class NewsfeedsHelper extends ContentHelper
{
    /**
     * Name of the extension
     *
     * @var    string
     */
    public static $extension = 'com_newsfeeds';

    /**
     * Adds Count Items for Category Manager.
     *
     * @param   \stdClass[]  &$items  The banner category objects
     *
     * @return  \stdClass[]
     *
     * @since   3.5
     */
    public static function countItems(&$items)
    {
        $db    = Factory::getDbo();
        $query = $db->createQuery();
        $query->select(
            [
                $db->quoteName('published', 'state'),
                'COUNT(*) AS ' . $db->quoteName('count'),
            ]
        )
            ->from($db->quoteName('#__newsfeeds'))
            ->where($db->quoteName('catid') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER)
            ->group($db->quoteName('state'));
        $db->setQuery($query);

        foreach ($items as $item) {
            $item->count_trashed     = 0;
            $item->count_archived    = 0;
            $item->count_unpublished = 0;
            $item->count_published   = 0;

            $id       = (int) $item->id;
            $newfeeds = $db->loadObjectList();

            foreach ($newfeeds as $newsfeed) {
                if ($newsfeed->state == 1) {
                    $item->count_published = $newsfeed->count;
                }

                if ($newsfeed->state == 0) {
                    $item->count_unpublished = $newsfeed->count;
                }

                if ($newsfeed->state == 2) {
                    $item->count_archived = $newsfeed->count;
                }

                if ($newsfeed->state == -2) {
                    $item->count_trashed = $newsfeed->count;
                }
            }
        }

        return $items;
    }

    /**
     * Adds Count Items for Tag Manager.
     *
     * @param   \stdClass[]  &$items     The newsfeed tag objects
     * @param   string       $extension  The name of the active view.
     *
     * @return  \stdClass[]
     *
     * @since   3.6
     */
    public static function countTagItems(&$items, $extension)
    {
        $db        = Factory::getDbo();
        $query     = $db->createQuery();
        $parts     = explode('.', $extension);
        $section   = null;

        if (\count($parts) > 1) {
            $section = $parts[1];
        }

        $query->select(
            [
                $db->quoteName('published', 'state'),
                'COUNT(*) AS ' . $db->quoteName('count'),
            ]
        )
            ->from($db->quoteName('#__contentitem_tag_map', 'ct'));

        if ($section === 'category') {
            $query->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('ct.content_item_id') . ' = ' . $db->quoteName('c.id'));
        } else {
            $query->join('LEFT', $db->quoteName('#__newsfeeds', 'c'), $db->quoteName('ct.content_item_id') . ' = ' . $db->quoteName('c.id'));
        }

        $query->where(
            [
                $db->quoteName('ct.tag_id') . ' = :id',
                $db->quoteName('ct.type_alias') . ' = :extension',
            ]
        )
            ->bind(':id', $id, ParameterType::INTEGER)
            ->bind(':extension', $extension)
            ->group($db->quoteName('state'));

        $db->setQuery($query);

        foreach ($items as $item) {
            $item->count_trashed     = 0;
            $item->count_archived    = 0;
            $item->count_unpublished = 0;
            $item->count_published   = 0;

            // Update ID used in database query.
            $id        = (int) $item->id;
            $newsfeeds = $db->loadObjectList();

            foreach ($newsfeeds as $newsfeed) {
                if ($newsfeed->state == 1) {
                    $item->count_published = $newsfeed->count;
                }

                if ($newsfeed->state == 0) {
                    $item->count_unpublished = $newsfeed->count;
                }

                if ($newsfeed->state == 2) {
                    $item->count_archived = $newsfeed->count;
                }

                if ($newsfeed->state == -2) {
                    $item->count_trashed = $newsfeed->count;
                }
            }
        }

        return $items;
    }
}
