<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\TagsSimilar\Site\Helper;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Text;
use Joomla\Component\Tags\Site\Helper\RouteHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_tags_similar
 *
 * @since  3.1
 */
abstract class TagsSimilarHelper
{
    /**
     * Get a list of tags
     *
     * @param   Registry  &$params  Module parameters
     *
     * @return  array
     */
    public static function getList(&$params)
    {
        $app    = Factory::getApplication();
        $option = $app->getInput()->get('option');
        $view   = $app->getInput()->get('view');

        // For now assume com_tags and com_users do not have tags.
        // This module does not apply to list views in general at this point.
        if ($option === 'com_tags' || $view === 'category' || $option === 'com_users') {
            return [];
        }

        $db         = Factory::getDbo();
        $user       = Factory::getUser();
        $groups     = $user->getAuthorisedViewLevels();
        $matchtype  = $params->get('matchtype', 'all');
        $ordering   = $params->get('ordering', 'count');
        $tagsHelper = new TagsHelper();
        $prefix     = $option . '.' . $view;
        $id         = $app->getInput()->getInt('id');
        $now        = Factory::getDate()->toSql();
        $nullDate   = $db->getNullDate();

        // This returns a comma separated string of IDs.
        $tagsToMatch = $tagsHelper->getTagIds($id, $prefix);

        if (!$tagsToMatch) {
            return [];
        }

        $tagsToMatch = explode(',', $tagsToMatch);
        $tagCount    = \count($tagsToMatch);

        $query = $db->getQuery(true);
        $query
            ->select(
                [
                    $db->quoteName('m.core_content_id'),
                    $db->quoteName('m.content_item_id'),
                    $db->quoteName('m.type_alias'),
                    'COUNT( ' . $db->quoteName('tag_id') . ') AS ' . $db->quoteName('count'),
                    $db->quoteName('ct.router'),
                    $db->quoteName('cc.core_title'),
                    $db->quoteName('cc.core_alias'),
                    $db->quoteName('cc.core_catid'),
                    $db->quoteName('cc.core_language'),
                    $db->quoteName('cc.core_params'),
                ]
            )
            ->from($db->quoteName('#__contentitem_tag_map', 'm'))
            ->join(
                'INNER',
                $db->quoteName('#__tags', 't'),
                $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__ucm_content', 'cc'),
                $db->quoteName('m.core_content_id') . ' = ' . $db->quoteName('cc.core_content_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__content_types', 'ct'),
                $db->quoteName('m.type_alias') . ' = ' . $db->quoteName('ct.type_alias')
            )
            ->whereIn($db->quoteName('m.tag_id'), $tagsToMatch)
            ->whereIn($db->quoteName('t.access'), $groups)
            ->where($db->quoteName('cc.core_state') . ' = 1')
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('cc.core_access') . ' IN (' . implode(',', $query->bindArray($groups)) . ')',
                    $db->quoteName('cc.core_access') . ' = 0',
                ],
                'OR'
            )
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('m.content_item_id') . ' <> :currentId',
                    $db->quoteName('m.type_alias') . ' <> :prefix',
                ],
                'OR'
            )
            ->bind(':currentId', $id, ParameterType::INTEGER)
            ->bind(':prefix', $prefix)
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('cc.core_publish_up') . ' IS NULL',
                    $db->quoteName('cc.core_publish_up') . ' = :nullDateUp',
                    $db->quoteName('cc.core_publish_up') . ' <= :nowDateUp',
                ],
                'OR'
            )
            ->bind(':nullDateUp', $nullDate)
            ->bind(':nowDateUp', $now)
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('cc.core_publish_down') . ' IS NULL',
                    $db->quoteName('cc.core_publish_down') . ' = :nullDateDown',
                    $db->quoteName('cc.core_publish_down') . ' >= :nowDateDown',
                ],
                'OR'
            )
            ->bind(':nullDateDown', $nullDate)
            ->bind(':nowDateDown', $now);

        // Optionally filter on language
        $language = ComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

        if ($language !== 'all') {
            if ($language === 'current_language') {
                $language = ContentHelper::getCurrentLanguage();
            }

            $query->whereIn($db->quoteName('cc.core_language'), [$language, '*'], ParameterType::STRING);
        }

        $query->group(
            [
                $db->quoteName('m.core_content_id'),
                $db->quoteName('m.content_item_id'),
                $db->quoteName('m.type_alias'),
                $db->quoteName('ct.router'),
                $db->quoteName('cc.core_title'),
                $db->quoteName('cc.core_alias'),
                $db->quoteName('cc.core_catid'),
                $db->quoteName('cc.core_language'),
                $db->quoteName('cc.core_params'),
            ]
        );

        if ($matchtype === 'all' && $tagCount > 0) {
            $query->having('COUNT( ' . $db->quoteName('tag_id') . ')  = :tagCount')
                ->bind(':tagCount', $tagCount, ParameterType::INTEGER);
        } elseif ($matchtype === 'half' && $tagCount > 0) {
            $tagCountHalf = ceil($tagCount / 2);
            $query->having('COUNT( ' . $db->quoteName('tag_id') . ')  >= :tagCount')
                ->bind(':tagCount', $tagCountHalf, ParameterType::INTEGER);
        }

        if ($ordering === 'count' || $ordering === 'countrandom') {
            $query->order($db->quoteName('count') . ' DESC');
        }

        if ($ordering === 'random' || $ordering === 'countrandom') {
            $query->order($query->rand());
        }

        $maximum = (int) $params->get('maximum', 5);

        if ($maximum > 0) {
            $query->setLimit($maximum);
        }

        $db->setQuery($query);

        try {
            $results = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $results = [];
            $app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }

        foreach ($results as $result) {
            $result->link = RouteHelper::getItemRoute(
                $result->content_item_id,
                $result->core_alias,
                $result->core_catid,
                $result->core_language,
                $result->type_alias,
                $result->router
            );

            $result->core_params = new Registry($result->core_params);
        }

        return $results;
    }
}
