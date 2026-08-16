<?php

/**
 * Joomla! Content Management System
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
use Joomla\Database\QueryInterface;
use Joomla\Utilities\ArrayHelper;

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
     * Normalize category IDs to a clean integer array.
     *
     * @param   mixed  $categoryIds  Category IDs to normalize (array, int, or mixed).
     *
     * @return  array  Clean array of category IDs, empty if no valid IDs.
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function normalizeCategoryIds(mixed $categoryIds): array
    {
        if (!\is_array($categoryIds)) {
            $categoryIds = (array) $categoryIds;
        }

        return array_values(
            array_unique(
                array_filter(
                    ArrayHelper::toInteger($categoryIds)
                )
            )
        );
    }

    /**
     * Build a complete category membership SQL condition.
     *
     * Generates SQL that matches items belonging to one or more categories,
     * considering both primary category assignment and secondary category mappings.
     *
     * This method centralizes the common pattern:
     *   (alias.catid IN (...) [OR alias.catid IN (descendants)])
     *   OR
     *   alias.id IN (mapped items query)
     *
     * @param   array   $categoryIds           The category IDs to match.
     * @param   bool    $includeSubcategories   Whether to include child categories in the match.
     * @param   int     $levels                 Maximum depth of subcategories to include.
     * @param   string  $tableAlias             Table alias for the content table (default: 'a').
     *
     * @return  string  The complete SQL condition, or '1 = 0' if no valid categories.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function buildCategoryMembershipCondition(array $categoryIds, bool $includeSubcategories = false, int $levels = 1, string $tableAlias = 'a'): string
    {
        $db = $this->getDb();

        $categoryIds = static::normalizeCategoryIds($categoryIds);

        if (empty($categoryIds)) {
            return '1 = 0';
        }

        $boundedIds  = implode(',', $categoryIds);
        $catidColumn = $db->quoteName($tableAlias . '.catid');
        $idColumn    = $db->quoteName($tableAlias . '.id');

        // Build primary category condition (with optional subcategories)
        $primaryCondition = $this->buildPrimaryCategoryCondition($catidColumn, $boundedIds, $categoryIds, $includeSubcategories, $levels);

        // Build secondary category condition (respects subcategories setting)
        $secondaryQuery     = $this->getMappedItemIdsQuery($categoryIds, $includeSubcategories, $levels);
        $secondaryCondition = $idColumn . ' IN (' . $secondaryQuery . ')';

        return '(' . $primaryCondition . ' OR ' . $secondaryCondition . ')';
    }

    /**
     * Build the primary category condition portion.
     *
     * @param   string             $catidColumn          The quoted catid column name.
     * @param   string             $boundedIds           Comma-separated bounded IDs.
     * @param   array              $categoryIds          The category IDs array.
     * @param   bool               $includeSubcategories Whether to include subcategories.
     * @param   int                $levels               Maximum depth.
     *
     * @return  string  The primary category SQL condition.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function buildPrimaryCategoryCondition(string $catidColumn, string $boundedIds, array $categoryIds, bool $includeSubcategories, int $levels): string
    {
        if ($includeSubcategories) {
            $descendantsQuery = $this->getDescendantCategoryIdsQuery($categoryIds, $levels);

            return '(' . $catidColumn . ' IN (' . $boundedIds . ')'
                . ' OR ' . $catidColumn . ' IN (' . $descendantsQuery . '))';
        }

        return $catidColumn . ' IN (' . $boundedIds . ')';
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
     * @param   int[]   $categoryIds      The category ids.
     * @param   string  $itemTable        The database table name for the items (e.g. '#__content').
     * @param   string  $columnStateName  The column name representing the state (defaults to 'state').
     *
     * @return  array<int, array<string, int>>
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getCategoryItemCounts(array $categoryIds, string $itemTable, string $columnStateName = 'state'): array
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
                    $db->quoteName('c.' . $columnStateName, 'state'),
                    'COUNT(*) AS ' . $db->quoteName('count'),
                ]
            )
            ->from($db->quoteName('#__category_item_map', 'm'))
            ->innerJoin(
                $db->quoteName($itemTable, 'c'),
                $db->quoteName('c.id') . ' = ' . $db->quoteName('m.item_id')
            )
            ->where($db->quoteName('m.context') . ' = :countContext')
            ->whereIn($db->quoteName('m.category_id'), $categoryIds, ParameterType::INTEGER)
            ->whereIn($db->quoteName('c.' . $columnStateName), array_keys(self::COUNTER_NAMES), ParameterType::INTEGER)
            ->group($db->quoteName(['m.category_id', 'c.' . $columnStateName]))
            ->bind(':countContext', $this->typeAlias, ParameterType::STRING);

        $relations = $db->setQuery($query)->loadObjectList();
        foreach ($relations as $relation) {
            $counts[(int) $relation->catid][self::COUNTER_NAMES[(int) $relation->state]] = (int) $relation->count;
        }
        return $counts;
    }

    /**
     * Builds a query that returns descendant category ids for one or more categories.
     *
     * @param   integer|array  $categoryIds  Category id(s).
     * @param   integer        $levels       Descendant depth.
     *
     * @return  QueryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getDescendantCategoryIdsQuery(int|array $categoryIds, int $levels = 1): QueryInterface
    {
        $db = $this->getDb();

        $categoryIds = static::normalizeCategoryIds($categoryIds);

        $query = $db->createQuery()
            ->select($db->quoteName('sub.id'))
            ->from($db->quoteName('#__categories', 'sub'))
            ->innerJoin(
                $db->quoteName('#__categories', 'parent'),
                $db->quoteName('sub.lft') . ' > ' . $db->quoteName('parent.lft')
                    . ' AND ' . $db->quoteName('sub.rgt') . ' < ' . $db->quoteName('parent.rgt')
            );

        if (empty($categoryIds)) {
            return $query->where('1 = 0');
        }

        $query->where($db->quoteName('parent.id') . ' IN (' . implode(',', $categoryIds) . ')');

        if ($levels >= 0) {
            $query->where($db->quoteName('sub.level') . ' <= ' . $db->quoteName('parent.level') . ' + ' . (int) $levels);
        }

        return $query;
    }

    /**
     * Builds a query that returns item ids mapped to one or more secondary categories.
     *
     * @param   integer|array  $categoryIds           Category id(s).
     * @param   boolean        $includeSubcategories  Include child categories.
     * @param   integer        $levels                Descendant depth.
     *
     * @return  QueryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getMappedItemIdsQuery(int|array $categoryIds, bool $includeSubcategories = false, int $levels = 1): QueryInterface
    {
        $db = $this->getDb();

        $categoryIds = static::normalizeCategoryIds($categoryIds);

        $query = $db->createQuery()
            ->select('DISTINCT ' . $db->quoteName('m.item_id'))
            ->from($db->quoteName('#__category_item_map', 'm'))
            ->innerJoin(
                $db->quoteName('#__categories', 'mapcat'),
                $db->quoteName('mapcat.id') . ' = ' . $db->quoteName('m.category_id')
            )
            ->where($db->quoteName('m.context') . ' = ' . $db->quote($this->typeAlias));

        if (empty($categoryIds)) {
            return $query->where('1 = 0');
        }

        $categoryIdsSql = implode(',', $categoryIds);

        if ($includeSubcategories) {
            $descendantsQuery = $this->getDescendantCategoryIdsQuery($categoryIds, $levels);

            $query->where(
                '(' . $db->quoteName('m.category_id') . ' IN (' . $categoryIdsSql . ')'
                    . ' OR ' . $db->quoteName('m.category_id') . ' IN (' . $descendantsQuery . ')' . ')'
            );
        } else {
            $query->where($db->quoteName('m.category_id') . ' IN (' . $categoryIdsSql . ')');
        }

        $app = Factory::getApplication();

        if ($app->isClient('site')) {
            $user = $app->getIdentity();

            $query->where($db->quoteName('mapcat.published') . ' = 1');

            if (!$user->authorise('core.admin')) {
                $query->whereIn(
                    $db->quoteName('mapcat.access'),
                    $user->getAuthorisedViewLevels(),
                    ParameterType::INTEGER
                );
            }
        }

        return $query;
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

        if (Factory::getApplication()->isClient('site')) {
            $query->where($db->quoteName('c.published') . ' = 1');
        }

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

    /**
     * Replace all category mappings for an item with a new set.
     * Explicitly preserves the array keys as the ordering value.
     *
     * @param   integer  $itemId  The item ID.
     * @param   array    $catIds  Array of category IDs.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function replaceMappings(int $itemId, array $catIds): void
    {
        $db = $this->getDb();

        // Delete all existing mappings for this item
        $this->removeAllMappings($itemId);
        if (empty($catIds)) {
            return;
        }

        // Insert new mappings, preserving array keys as ordering
        $query = $db->createQuery()
            ->insert($db->quoteName('#__category_item_map'))
            ->columns([
                $db->quoteName('context'),
                $db->quoteName('item_id'),
                $db->quoteName('category_id'),
                $db->quoteName('ordering'),
            ]);

        foreach ($catIds as $ordering => $categoryId) {
            $query->values(implode(',', $query->bindArray([$this->typeAlias, $itemId, (int) $categoryId, (int) $ordering], [ParameterType::STRING, ParameterType::INTEGER, ParameterType::INTEGER, ParameterType::INTEGER])));
        }

        $db->setQuery($query)->execute();
    }

    /**
     * Remove all category mappings for one or more items.
     *
     * @param   integer  ...$itemIds  One or more item IDs.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function removeAllMappings(int ...$itemIds): void
    {
        if (empty($itemIds)) {
            return;
        }

        $db = $this->getDb();

        $query = $db->createQuery()
            ->delete($db->quoteName('#__category_item_map'))
            ->where($db->quoteName('context') . ' = :context')
            ->whereIn($db->quoteName('item_id'), $itemIds, ParameterType::INTEGER)
            ->bind(':context', $this->typeAlias, ParameterType::STRING);

        $db->setQuery($query)->execute();
    }
}
