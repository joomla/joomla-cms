<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Site\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Banners model for the Joomla Banners component.
 *
 * @since  1.6
 */
class BannersModel extends ListModel
{
    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.tag_search');
        $id .= ':' . $this->getState('filter.client_id');
        $id .= ':' . serialize($this->getState('filter.category_id'));
        $id .= ':' . serialize($this->getState('filter.keywords'));

        return parent::getStoreId($id);
    }

    /**
     * Method to get a QueryInterface object for retrieving the data set from a database.
     *
     * @return  QueryInterface   An object implementing QueryInterface to retrieve the data set.
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        $db         = $this->getDatabase();
        $query      = $db->createQuery();
        $ordering   = $this->getState('filter.ordering');
        $tagSearch  = $this->getState('filter.tag_search');
        $cid        = (int) $this->getState('filter.client_id');
        $categoryId = $this->getState('filter.category_id');
        $keywords   = $this->getState('filter.keywords');
        $randomise  = ($ordering === 'random');
        $nowDate    = Factory::getDate()->toSql();

        $query->select(
            [
                $db->quoteName('a.id'),
                $db->quoteName('a.type'),
                $db->quoteName('a.name'),
                $db->quoteName('a.clickurl'),
                $db->quoteName('a.sticky'),
                $db->quoteName('a.cid'),
                $db->quoteName('a.description'),
                $db->quoteName('a.params'),
                $db->quoteName('a.custombannercode'),
                $db->quoteName('a.track_impressions'),
                $db->quoteName('cl.track_impressions', 'client_track_impressions'),
            ]
        )
            ->from($db->quoteName('#__banners', 'a'))
            ->join('LEFT', $db->quoteName('#__banner_clients', 'cl'), $db->quoteName('cl.id') . ' = ' . $db->quoteName('a.cid'))
            ->where($db->quoteName('a.state') . ' = 1')
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('a.publish_up') . ' IS NULL',
                    $db->quoteName('a.publish_up') . ' <= :nowDate1',
                ],
                'OR'
            )
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('a.publish_down') . ' IS NULL',
                    $db->quoteName('a.publish_down') . ' >= :nowDate2',
                ],
                'OR'
            )
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('a.imptotal') . ' = 0',
                    $db->quoteName('a.impmade') . ' < ' . $db->quoteName('a.imptotal'),
                ],
                'OR'
            )
            ->bind([':nowDate1', ':nowDate2'], $nowDate);

        if ($cid) {
            $query->where(
                [
                    $db->quoteName('a.cid') . ' = :clientId',
                    $db->quoteName('cl.state') . ' = 1',
                ]
            )
                ->bind(':clientId', $cid, ParameterType::INTEGER);
        }

        // Filter by a single or group of categories
        if (is_numeric($categoryId)) {
            $categoryId = (int) $categoryId;
            $type       = $this->getState('filter.category_id.include', true) ? ' = ' : ' <> ';

            // Add subcategory check
            if ($this->getState('filter.subcategories', false)) {
                $levels = (int) $this->getState('filter.max_category_levels', '1');

                // Create a subquery for the subcategory list
                $subQuery = $db->createQuery();
                $subQuery->select($db->quoteName('sub.id'))
                    ->from($db->quoteName('#__categories', 'sub'))
                    ->join(
                        'INNER',
                        $db->quoteName('#__categories', 'this'),
                        $db->quoteName('sub.lft') . ' > ' . $db->quoteName('this.lft')
                        . ' AND ' . $db->quoteName('sub.rgt') . ' < ' . $db->quoteName('this.rgt')
                    )
                    ->where(
                        [
                            $db->quoteName('this.id') . ' = :categoryId1',
                            $db->quoteName('sub.level') . ' <= ' . $db->quoteName('this.level') . ' + :levels',
                        ]
                    );

                // Add the subquery to the main query
                $query->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.catid') . $type . ':categoryId2',
                        $db->quoteName('a.catid') . ' IN (' . $subQuery . ')',
                    ],
                    'OR'
                )
                    ->bind([':categoryId1', ':categoryId2'], $categoryId, ParameterType::INTEGER)
                    ->bind(':levels', $levels, ParameterType::INTEGER);
            } else {
                $query->where($db->quoteName('a.catid') . $type . ':categoryId')
                    ->bind(':categoryId', $categoryId, ParameterType::INTEGER);
            }
        } elseif (\is_array($categoryId) && (\count($categoryId) > 0)) {
            $categoryId = ArrayHelper::toInteger($categoryId);

            if ($this->getState('filter.category_id.include', true)) {
                $query->whereIn($db->quoteName('a.catid'), $categoryId);
            } else {
                $query->whereNotIn($db->quoteName('a.catid'), $categoryId);
            }
        }

        if ($tagSearch) {
            if (!$keywords) {
                // No keywords, select nothing.
                $query->where('0 != 0');
            } else {
                $temp   = [];
                $config = ComponentHelper::getParams('com_banners');
                $prefix = $config->get('metakey_prefix');

                if ($categoryId) {
                    $query->join('LEFT', $db->quoteName('#__categories', 'cat'), $db->quoteName('a.catid') . ' = ' . $db->quoteName('cat.id'));
                }

                foreach ($keywords as $key => $keyword) {
                    $regexp       = '[[:<:]]' . $keyword . '[[:>:]]';
                    $valuesToBind = [$keyword, $keyword, $regexp];

                    if ($cid) {
                        $valuesToBind[] = $regexp;
                    }

                    if ($categoryId) {
                        $valuesToBind[] = $regexp;
                    }

                    // Because values to $query->bind() are passed by reference, using $query->bindArray() here instead to prevent overwriting.
                    $bounded = $query->bindArray($valuesToBind, ParameterType::STRING);

                    $condition1 = $db->quoteName('a.own_prefix') . ' = 1'
                        . ' AND ' . $db->quoteName('a.metakey_prefix')
                        . ' = SUBSTRING(' . $bounded[0] . ',1,LENGTH(' . $db->quoteName('a.metakey_prefix') . '))'
                        . ' OR ' . $db->quoteName('a.own_prefix') . ' = 0'
                        . ' AND ' . $db->quoteName('cl.own_prefix') . ' = 1'
                        . ' AND ' . $db->quoteName('cl.metakey_prefix')
                        . ' = SUBSTRING(' . $bounded[1] . ',1,LENGTH(' . $db->quoteName('cl.metakey_prefix') . '))'
                        . ' OR ' . $db->quoteName('a.own_prefix') . ' = 0'
                        . ' AND ' . $db->quoteName('cl.own_prefix') . ' = 0'
                        . ' AND ' . (str_starts_with($keyword, $prefix) ? '0 = 0' : '0 != 0');

                    $condition2 = $db->quoteName('a.metakey') . ' ' . $query->regexp($bounded[2]);

                    if ($cid) {
                        $condition2 .= ' OR ' . $db->quoteName('cl.metakey') . ' ' . $query->regexp($bounded[3]) . ' ';
                    }

                    if ($categoryId) {
                        $condition2 .= ' OR ' . $db->quoteName('cat.metakey') . ' ' . $query->regexp($bounded[4]) . ' ';
                    }

                    $temp[] = "($condition1) AND ($condition2)";
                }

                $query->where('(' . implode(' OR ', $temp) . ')');
            }
        }

        // Filter by language
        if ($this->getState('filter.language')) {
            $query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
        }

        $query->order($db->quoteName('a.sticky') . ' DESC, ' . ($randomise ? $query->rand() : $db->quoteName('a.ordering')));

        return $query;
    }

    /**
     * Get a list of banners.
     *
     * @return  array
     *
     * @since   1.6
     */
    public function getItems()
    {
        if ($this->getState('filter.tag_search')) {
            // Filter out empty keywords.
            $keywords = array_values(array_filter(array_map('trim', $this->getState('filter.keywords')), 'strlen'));

            // Re-set state before running the query.
            $this->setState('filter.keywords', $keywords);

            // If no keywords are provided, avoid running the query.
            if (!$keywords) {
                $this->cache['items'] = [];

                return $this->cache['items'];
            }
        }

        if (!isset($this->cache['items'])) {
            $this->cache['items'] = parent::getItems();

            foreach ($this->cache['items'] as &$item) {
                $item->params = new Registry($item->params);
            }
        }

        return $this->cache['items'];
    }

    /**
     * Makes impressions on a list of banners
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function impress()
    {
        $trackDate = Factory::getDate()->format('Y-m-d H:00:00');
        $trackDate = Factory::getDate($trackDate)->toSql();
        $items     = $this->getItems();
        $db        = $this->getDatabase();
        $bid       = [];

        if (!\count($items)) {
            return;
        }

        foreach ($items as $item) {
            $bid[] = (int) $item->id;
        }

        // Increment impression made
        $query = $db->createQuery();
        $query->update($db->quoteName('#__banners'))
            ->set($db->quoteName('impmade') . ' = ' . $db->quoteName('impmade') . ' + 1')
            ->whereIn($db->quoteName('id'), $bid);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (ExecutionFailureException $e) {
            throw new \Exception($e->getMessage(), 500);
        }

        foreach ($items as $item) {
            // Track impressions
            $trackImpressions = $item->track_impressions;

            if ($trackImpressions < 0 && $item->cid) {
                $trackImpressions = $item->client_track_impressions;
            }

            if ($trackImpressions < 0) {
                $config           = ComponentHelper::getParams('com_banners');
                $trackImpressions = $config->get('track_impressions');
            }

            if ($trackImpressions > 0) {
                // Is track already created?
                // Update count
                $query = $db->createQuery();
                $query->update($db->quoteName('#__banner_tracks'))
                    ->set($db->quoteName('count') . ' = ' . $db->quoteName('count') . ' + 1')
                    ->where(
                        [
                            $db->quoteName('track_type') . ' = 1',
                            $db->quoteName('banner_id') . ' = :id',
                            $db->quoteName('track_date') . ' = :trackDate',
                        ]
                    )
                    ->bind(':id', $item->id, ParameterType::INTEGER)
                    ->bind(':trackDate', $trackDate);

                $db->setQuery($query);

                try {
                    $db->execute();
                } catch (ExecutionFailureException $e) {
                    throw new \Exception($e->getMessage(), 500);
                }

                if ($db->getAffectedRows() === 0) {
                    // Insert new count
                    $query = $db->createQuery();
                    $query->insert($db->quoteName('#__banner_tracks'))
                        ->columns(
                            [
                                $db->quoteName('count'),
                                $db->quoteName('track_type'),
                                $db->quoteName('banner_id'),
                                $db->quoteName('track_date'),
                            ]
                        )
                        ->values('1, 1, :id, :trackDate')
                        ->bind(':id', $item->id, ParameterType::INTEGER)
                        ->bind(':trackDate', $trackDate);

                    $db->setQuery($query);

                    try {
                        $db->execute();
                    } catch (ExecutionFailureException $e) {
                        throw new \Exception($e->getMessage(), 500);
                    }
                }
            }
        }
    }
}
