<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_category_tags
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\CategoryTags\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\Database\ParameterType;

/**
 * Helper for mod_category_tags
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class CategoryTagsHelper
{
    /**
     * Get list of popular tags
     *
     * @param   \Joomla\Registry\Registry  &$params  module parameters
     *
     * @return  mixed
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function getList(&$params)
    {
        $user        = Factory::getUser();
        $db          = Factory::getDbo();
        $groups      = $user->getAuthorisedViewLevels();
        $catid       = (array) $params->get('catid', []);
        $timeframe   = $params->get('timeframe', 'alltime');
        $maximum     = (int) $params->get('maximum', 5);
        $order_value = $params->get('order_value', 'title');
        $nowDate     = Factory::getDate()->toSql();
        $nullDate    = $db->getNullDate();
        $image_display = $params->get('image_display', false);

        $query = $db->getQuery(true)
            ->select(
                [
                    'MAX(' . $db->quoteName('tag_id') . ') AS ' . $db->quoteName('tag_id'),
                    'COUNT(*) AS ' . $db->quoteName('count'),
                    'MAX(' . $db->quoteName('t.parent_id') . ') AS ' . $db->quoteName('parent_id'),
                    'MAX(' . $db->quoteName('t.title') . ') AS ' . $db->quoteName('title'),
                    'MAX(' . $db->quoteName('t.alias') . ') AS ' . $db->quoteName('alias'),
                    'MAX(' . $db->quoteName('t.access') . ') AS ' . $db->quoteName('access'),
                    'MAX(' . $db->quoteName('t.params') . ') AS ' . $db->quoteName('params'),
                    "'' AS " . $db->quoteName('parent'),
                ]
            )
            ->group($db->quoteName(['tag_id', 't.title', 't.access', 't.alias']))
            ->from($db->quoteName('#__contentitem_tag_map', 'm'))
            ->whereIn($db->quoteName('t.access'), $groups);

        if ($image_display) {
            $query->select('MAX(' . $db->quoteName('t.images') . ') AS ' . $db->quoteName('images'));
        }

        // Only return published tags
        $query->where($db->quoteName('t.published') . ' = 1 ');

        // Filter by Parent Tag
        $parentTags = $params->get('parentTag', []);

        if ($parentTags) {
            $query->whereIn($db->quoteName('t.parent_id'), $parentTags);
        }

        // Optionally filter on language
        $language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

        if ($language !== 'all') {
            if ($language === 'current_language') {
                $language = ContentHelper::getCurrentLanguage();
            }

            $query->whereIn($db->quoteName('t.language'), [$language, '*'], ParameterType::STRING);
        }

        if ($timeframe !== 'alltime') {
            $query->where($db->quoteName('tag_date') . ' > ' . $query->dateAdd($db->quote($nowDate), '-1', strtoupper($timeframe)));
        }

        $query->join('INNER', $db->quoteName('#__tags', 't'), $db->quoteName('tag_id') . ' = ' . $db->quoteName('t.id'))
            ->join(
                'INNER',
                $db->quoteName('#__ucm_content', 'c'),
                $db->quoteName('m.core_content_id') . ' = ' . $db->quoteName('c.core_content_id')
            );

        if ($catid && $catid != [0]) {
            $query->join('INNER', $db->quoteName('#__categories', 'cat'), $db->quoteName('c.core_catid') . ' = ' . $db->quoteName('cat.id'));
            $query->whereIn($db->quoteName('cat.id'), (array)$catid);

            $query->whereIn($db->quoteName('cat.access'), $groups);

            $query->where($db->quoteName('cat.published') . ' = 1');
            $query->select($db->quoteName('cat.title', 'cat_title'))->select($db->quoteName('cat.id', 'cat_id'));
            $query->group($db->quoteName(['cat.id']));
        } else {
            $query->select("'' AS `cat_title`")->select('0 AS `cat_id`');
        }

        $query->where($db->quoteName('m.type_alias') . ' = ' . $db->quoteName('c.core_type_alias'));

        $groupIn = array_merge([0], $groups, []);

        // Only return tags connected to published and authorised items
        $query->where($db->quoteName('c.core_state') . ' = 1')
            ->whereIn($db->quoteName('c.core_access'), $groupIn);

            $query->where(
                '(' . $db->quoteName('c.core_publish_up') . ' IS NULL'
                . ' OR ' . $db->quoteName('c.core_publish_up') . ' = :nullDate2'
                . ' OR ' . $db->quoteName('c.core_publish_up') . ' <= :nowDate2)'
            )
            ->where(
                '(' . $db->quoteName('c.core_publish_down') . ' IS NULL'
                . ' OR ' . $db->quoteName('c.core_publish_down') . ' = :nullDate3'
                . ' OR ' . $db->quoteName('c.core_publish_down') . ' >= :nowDate3)'
            )
            ->bind([':nullDate2', ':nullDate3'], $nullDate)
            ->bind([':nowDate2', ':nowDate3'], $nowDate);

        // Set query depending on order_value param
        if ($order_value === 'rand()') {
            $query->order($query->rand());
        } else {
            $order_direction = $params->get('order_direction', 1) ? 'DESC' : 'ASC';

            if ($params->get('order_value', 'title') === 'title') {
                // Backup bound parameters array of the original query
                $bounded = $query->getBounded();

                if ($maximum > 0) {
                    $query->setLimit($maximum);
                }

                $query->order($db->quoteName('count') . ' DESC');
                $equery = $db->getQuery(true)
                    ->select(
                        $db->quoteName(
                            [
                                'a.tag_id',
                                'a.parent_id',
                                'a.count',
                                'a.title',
                                'a.access',
                                'a.alias',
                                'a.cat_id',
                                'a.cat_title',
                                'a.params',
                                'a.parent',
                             ]
                        )
                    )
                    ->from('(' . (string) $query . ') AS ' . $db->quoteName('a'))
                    ->order($db->quoteName('a.title') . ' ' . $order_direction);

                if ($image_display) {
                    $equery->select($db->quoteName('a.images'));
                }

                $query = $equery;

                // Rebind parameters
                foreach ($bounded as $key => $obj) {
                      $query->bind($key, $obj->value, $obj->dataType);
                }
            } else {
                $query->order($db->quoteName($order_value) . ' ' . $order_direction);
            }
        }

        if ($maximum > 0) {
             $query->setLimit($maximum);
        }

        $db->setQuery($query);

        try {
             $results = $db->loadObjectList('');
        } catch (\RuntimeException $e) {
            $results = array();
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $results;
    }
}
