<?php

/**
 * @package     Joomla\CMS
 * @subpackage  Helper
 *
 * @copyright   (C) 2026 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Secondary Categories helper class, provides methods to perform tasks relevant
 * to secondary category mapping of content.
 *
 * @since  __DEPLOY_VERSION__
 */
class SecondaryCategoriesHelper extends CMSHelper
{
    /**
     * Alias for querying mapping table (e.g., 'com_content.article').
     *
     * @var    string
     * @since  __DEPLOY_VERSION__
     */
    private string $typeAlias;

    /**
     * Secondary category counter property names indexed by item state.
     *
     * @var    array<int, string>
     * @since  __DEPLOY_VERSION__
     */
    private const COUNTER_NAMES = [
        -2 => 'count_secondary_trashed',
        0  => 'count_secondary_unpublished',
        1  => 'count_secondary_published',
        2  => 'count_secondary_archived',
    ];

    /**
     * SecondaryCategories constructor.
     *
     * @param   string  $typeAlias  The Type Alias
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(string $typeAlias)
    {
        $this->typeAlias = $typeAlias;
    }

    /**
     * Get the database driver.
     *
     * @return  DatabaseInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getDb(): DatabaseInterface
    {
        return Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * Add category rows to mapping table.
     * Includes duplicate prevention to prevent database errors during batch operations.
     *
     * @param   integer  $itemId  The item ID.
     * @param   array    $catIds  Array of category IDs to add.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function addMappings(int $itemId, array $catIds): void
    {
        if (empty($catIds)) {
            return;
        }

        $db = $this->getDb();

        // Prevent duplicate key errors: check what already exists
        $existing = $this->getCurrentSecondaryCategoriesByItem($itemId);
        $catIds   = array_diff($catIds, $existing);

        if (empty($catIds)) {
            return;
        }

        // Get max ordering to append new items at the end
        $maxOrderingQuery = $db->createQuery()
            ->select('MAX(' . $db->quoteName('ordering') . ')')
            ->from($db->quoteName('#__category_item_map'))
            ->where($db->quoteName('context') . ' = :context')
            ->where($db->quoteName('item_id') . ' = :itemId')
            ->bind(':context', $this->typeAlias, ParameterType::STRING)
            ->bind(':itemId', $itemId, ParameterType::INTEGER);

        $maxOrdering = (int) $db->setQuery($maxOrderingQuery)->loadResult();
        $ordering    = $maxOrdering + 1;

        $query = $db->createQuery()
            ->insert($db->quoteName('#__category_item_map'))
            ->columns([
                $db->quoteName('context'),
                $db->quoteName('item_id'),
                $db->quoteName('category_id'),
                $db->quoteName('ordering'),
            ]);

        foreach ($catIds as $catId) {
            $query->values(
                implode(
                    ',',
                    $query->bindArray(
                        [$this->typeAlias, $itemId, (int) $catId, $ordering++],
                        [ParameterType::STRING, ParameterType::INTEGER, ParameterType::INTEGER, ParameterType::INTEGER]
                    )
                )
            );
        }

        $db->setQuery($query)->execute();
    }

    /**
     * Remove category rows from mapping table.
     *
     * @param   integer  $itemId  The item ID.
     * @param   array    $catIds  Array of category IDs to remove.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function removeMappings(int $itemId, array $catIds): void
    {
        if (empty($catIds)) {
            return;
        }

        $db = $this->getDb();

        $query = $db->createQuery()
            ->delete($db->quoteName('#__category_item_map'))
            ->where($db->quoteName('context') . ' = :context')
            ->where($db->quoteName('item_id') . ' = :itemId')
            ->whereIn($db->quoteName('category_id'), $catIds, ParameterType::INTEGER)
            ->bind(':context', $this->typeAlias, ParameterType::STRING)
            ->bind(':itemId', $itemId, ParameterType::INTEGER);

        $db->setQuery($query)->execute();
    }

    /**
     * Get current secondary categories for a single item.
     *
     * @param   integer  $itemId  The item ID.
     *
     * @return  array  An array of category IDs.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getCurrentSecondaryCategoriesByItem(int $itemId): array
    {
        $db = $this->getDb();

        $query = $db->createQuery()
            ->select($db->quoteName('category_id'))
            ->from($db->quoteName('#__category_item_map'))
            ->where($db->quoteName('context') . ' = :context')
            ->where($db->quoteName('item_id') . ' = :itemId')
            ->bind(':context', $this->typeAlias, ParameterType::STRING)
            ->bind(':itemId', $itemId, ParameterType::INTEGER);

        return array_map('intval', $db->setQuery($query)->loadColumn());
    }


    /**
     * Get the number of related items for each secondary category grouped by state.
     *
     * @param   int[]  $categoryIds  The category ids.
     *
     * @return  array<int, array<string, int>>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getCategoryItemCounts(array $categoryIds): array
    {
        if (empty($categoryIds)) {
            return [];
        }

        $db = $this->getDb();

        $counts = [];

        foreach ($categoryIds as $categoryId) {
            foreach (self::COUNTER_NAMES as $counterName) {
                $counts[(int) $categoryId][$counterName] = 0;
            }
        }

        $query = $db->createQuery()
            ->select(
                [
                    $db->quoteName('m.category_id', 'catid'),
                    $db->quoteName('c.state', 'state'),
                    'COUNT(*) AS ' . $db->quoteName('count'),
                ]
            )
            ->from($db->quoteName('#__category_item_map', 'm'))
            ->innerJoin(
                $db->quoteName('#__content', 'c'),
                $db->quoteName('c.id') . ' = ' . $db->quoteName('m.item_id')
            )
            ->where($db->quoteName('m.context') . ' = :context')
            ->whereIn($db->quoteName('m.category_id'), $categoryIds)
            ->whereIn($db->quoteName('c.state'), array_keys(self::COUNTER_NAMES))
            ->group($db->quoteName(['m.category_id', 'c.state']))
            ->bind(':context', $this->typeAlias, ParameterType::STRING);

        $relations = $db->setQuery($query)->loadObjectList();
        foreach ($relations as $relation) {
            $counts[(int) $relation->catid][self::COUNTER_NAMES[(int) $relation->state]] = (int) $relation->count;
        }
        return $counts;
    }

    /**
     * Load secondary categories for the given items.
     *
     * Adds a secondary_categories property to each item containing
     * the mapped secondary category objects.
     *
     * @param   array  $items  The items to populate.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function loadSecondaryCategoriesForItems(array &$items): void
    {
        if (empty($items)) {
            return;
        }

        $itemIds = [];

        foreach ($items as $item) {
            if (!empty($item->id)) {
                $itemIds[]                  = (int) $item->id;
                $item->secondary_categories = [];
            }
        }

        if (empty($itemIds)) {
            return;
        }

        $db    = $this->getDb();
        $user  = Factory::getApplication()->getIdentity();
        $query = $db->createQuery();

        $query
            ->select([
                $db->quoteName('m.item_id'),
                $db->quoteName('m.ordering'),
                $db->quoteName('c.id'),
                $db->quoteName('c.title'),
                $db->quoteName('c.alias'),
                $db->quoteName('c.parent_id'),
                $db->quoteName('c.created_user_id', 'category_uid'),
                $db->quoteName('c.level', 'category_level'),
                $db->quoteName('c.published', 'category_published'),
                $db->quoteName('parent.id', 'parent_category_id'),
                $db->quoteName('parent.title', 'parent_category_title'),
                $db->quoteName('parent.created_user_id', 'parent_category_uid'),
                $db->quoteName('parent.level', 'parent_category_level'),
            ])
            ->from($db->quoteName('#__category_item_map', 'm'))
            ->innerJoin(
                $db->quoteName('#__categories', 'c'),
                $db->quoteName('c.id') . ' = ' . $db->quoteName('m.category_id')
            )
            ->leftJoin(
                $db->quoteName('#__categories', 'parent'),
                $db->quoteName('parent.id') . ' = ' . $db->quoteName('c.parent_id')
            )
            ->where($db->quoteName('m.context') . ' = :context')
            ->whereIn($db->quoteName('m.item_id'), $itemIds, ParameterType::INTEGER)
            ->bind(':context', $this->typeAlias, ParameterType::STRING)
            ->order($db->quoteName('m.item_id'))
            ->order($db->quoteName('m.ordering'));

        if (!$user->authorise('core.admin')) {
            $query->whereIn(
                $db->quoteName('c.access'),
                $user->getAuthorisedViewLevels(),
                ParameterType::INTEGER
            );
        }

        $rows = $db->setQuery($query)->loadObjectList();

        $mapped = [];

        foreach ($rows as $row) {
            $mapped[(int) $row->item_id][] = $row;
        }

        foreach ($items as $item) {
            $item->secondary_categories = $mapped[$item->id] ?? [];
        }
    }
}
