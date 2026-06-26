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
}
