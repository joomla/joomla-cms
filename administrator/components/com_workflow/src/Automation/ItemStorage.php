<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Automation;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Finds where an extension keeps its items, and which of them are trashed or archived.
 *
 * @since __DEPLOY_VERSION__
 */
final class ItemStorage
{
    /**
     * @var DatabaseInterface
     * @since __DEPLOY_VERSION__
     */
    private DatabaseInterface $database;

    /**
     * Content type rows already looked up, keyed by extension. Null means not registered.
     *
     * @var array<string, object|null>
     * @since __DEPLOY_VERSION__
     */
    private array $contentTypes = [];

    /**
     * @param   DatabaseInterface  $database  The database driver.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    /**
     * Which of these items are trashed or archived.
     *
     * An extension this cannot answer for returns nothing, so automation carries on rather than
     * silently stopping for the whole component.
     *
     * @param   int[]   $itemIds    The items being considered.
     * @param   string  $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  int[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function trashedOrArchivedIds(array $itemIds, string $extension): array
    {
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $storage = $this->locate($extension);

        if ($itemIds === [] || $storage === null) {
            return [];
        }

        $stateColumn = $this->columnFor($extension, 'published');

        if ($stateColumn === null) {
            return [];
        }

        $db                     = $this->database;
        $trashedOrArchivedQuery = $db->getQuery(true)
            ->select($db->quoteName($storage['key']))
            ->from($db->quoteName($storage['table']))
            ->whereIn($db->quoteName($storage['key']), $itemIds)
            ->whereIn($db->quoteName($stateColumn), [Workflow::CONDITION_TRASHED, Workflow::CONDITION_ARCHIVED]);

        return array_map('intval', $db->setQuery($trashedOrArchivedQuery)->loadColumn() ?: []);
    }

    /**
     * The display title of each item, keyed by id.
     *
     * @param   int[]   $itemIds    The items to name.
     * @param   string  $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  array<int, string>  Item id to title, omitting any the query did not return.
     *
     * @since   __DEPLOY_VERSION__
     */

    public function titlesFor(array $itemIds, string $extension): array
    {
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $storage = $this->locate($extension);

        if ($itemIds === [] || $storage === null) {
            return [];
        }

        $titleColumn = $this->columnFor($extension, 'title');

        if ($titleColumn === null) {
            return [];
        }

        $db    = $this->database;
        $query = $db->getQuery(true)
            ->select($db->quoteName([$storage['key'], $titleColumn]))
            ->from($db->quoteName($storage['table']))
            ->whereIn($db->quoteName($storage['key']), $itemIds);

        $titles = [];

        foreach ($db->setQuery($query)->loadAssocList() ?: [] as $row) {
            $titles[(int) $row[$storage['key']]] = (string) $row[$titleColumn];
        }

        return $titles;
    }

    /**
     * The category each item is filed in, keyed by item id.
     *
     * @param   int[]   $itemIds    The items to look up.
     * @param   string  $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  array<int, array{id: int, title: string, extension: string}>  Omits items the extension does not file.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function categoriesFor(array $itemIds, string $extension): array
    {
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));
        $storage = $this->locate($extension);

        if ($itemIds === [] || $storage === null) {
            return [];
        }

        $categoryColumn = $this->columnFor($extension, 'catid');

        if ($categoryColumn === null) {
            return [];
        }

        $db    = $this->database;
        $query = $db->getQuery(true)
            ->select([
                $db->quoteName('item.' . $storage['key'], 'item_id'),
                $db->quoteName('category.id', 'category_id'),
                $db->quoteName('category.title', 'category_title'),
                $db->quoteName('category.extension', 'category_extension'),
            ])
            ->from($db->quoteName($storage['table'], 'item'))
            ->join(
                'INNER',
                $db->quoteName('#__categories', 'category'),
                $db->quoteName('category.id') . ' = ' . $db->quoteName('item.' . $categoryColumn)
            )
            ->whereIn($db->quoteName('item.' . $storage['key']), $itemIds);

        $categories = [];

        foreach ($db->setQuery($query)->loadAssocList() ?: [] as $row) {
            $categories[(int) $row['item_id']] = [
                'id'        => (int) $row['category_id'],
                'title'     => (string) $row['category_title'],
                'extension' => (string) $row['category_extension'],
            ];
        }

        return $categories;
    }

    /**
     * Fills in each row's item title and category, keyed by that row's own extension.
     *
     * Titles repeat, so the category is what tells two items of the same name apart.
     *
     * @param   object[]  $rows  Rows carrying item_id and extension.
     *
     * @return  object[]
     *
     * @since   __DEPLOY_VERSION__
     */
    public function annotateTitles(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $itemIdsByExtension = [];

        foreach ($rows as $row) {
            $itemIdsByExtension[$row->extension][] = (int) $row->item_id;
        }

        $titles     = [];
        $categories = [];

        foreach ($itemIdsByExtension as $extension => $itemIds) {
            foreach ($this->titlesFor($itemIds, $extension) as $itemId => $title) {
                // An item id is only unique within its own extension.
                $titles[$extension . '.' . $itemId] = $title;
            }

            foreach ($this->categoriesFor($itemIds, $extension) as $itemId => $category) {
                $categories[$extension . '.' . $itemId] = $category;
            }
        }

        foreach ($rows as $row) {
            $key      = $row->extension . '.' . $row->item_id;
            $category = $categories[$key] ?? null;

            $row->item_title              = $titles[$key] ?? null;
            $row->item_category_id        = $category['id'] ?? null;
            $row->item_category_title     = $category['title'] ?? null;
            $row->item_category_extension = $category['extension'] ?? null;
        }

        return $rows;
    }

    /**
     * Where an extension keeps its own items: the table and its key column.
     *
     * @param string $extension The workflow extension.
     *
     * @return array{table: string, key: string}|null
     *
     * @since   __DEPLOY_VERSION__
     */
    public function locate(string $extension): ?array
    {
        $contentType = $this->contentTypeFor($extension);

        if ($contentType === null) {
            return null;
        }

        $definition = json_decode((string) $contentType->table, true);

        // Only the special table is the extension's own. The common one is always #__ucm_content,
        // which is not reliably populated.
        $table = $definition['special']['dbtable'] ?? '';
        $key   = $definition['special']['key'] ?? '';

        if (!\is_string($table) || !preg_match('/^#__[a-zA-Z0-9_]+$/', $table)) {
            return null;
        }

        if (!\is_string($key) || !preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
            return null;
        }

        return ['table' => $table, 'key' => $key];
    }

    /**
     * Where an extension keeps its items and which column holds their titles.
     *
     * Every value returned has been validated, so it is safe to pass through quoteName().
     *
     * @param   string  $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  array|null  ['table' => ..., 'key' => ..., 'titleColumn' => ...], or null when
     *                      the extension does not describe itself well enough to be searched.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function titleLocation(string $extension): ?array
    {
        $location = $this->locate($extension);

        if ($location === null) {
            return null;
        }

        $titleColumn = $this->columnFor($extension, 'title');

        if ($titleColumn === null) {
            return null;
        }

        return $location + ['titleColumn' => $titleColumn];
    }

    /**
     * Which real column an extension uses for one of Joomla's logical field names.
     *
     * Asks the extension's Table class through getColumnAlias(), because guessing from the columns
     * fails: #__contact_details has a "state" column that holds an address.
     *
     * @param   string  $extension  The workflow extension.
     * @param   string  $logicalName  A Joomla field name, for instance published or title.
     *
     * @return  string|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function columnFor(string $extension, string $logicalName): ?string
    {
        $contentType = $this->contentTypeFor($extension);

        if ($contentType === null) {
            return null;
        }

        $definition = json_decode((string) $contentType->table, true);
        $class      = ($definition['special']['prefix'] ?? '') . ($definition['special']['type'] ?? '');

        // A registration can outlive its extension, for instance after a failed uninstall.
        if (!\is_string($class) || $class === '' || !class_exists($class)) {
            return null;
        }

        try {
            $table = new $class($this->database);
        } catch (\Throwable) {
            // Some Table constructors need more than a database driver.
            return null;
        }

        if (!$table instanceof Table) {
            return null;
        }

        // getColumnAlias() hands back the name unchanged when there is no alias, whether or not the
        // table has such a column, so a table without categories would otherwise reach the query.
        if (!$table->hasField($logicalName)) {
            return null;
        }

        $column = $table->getColumnAlias($logicalName);

        // Validated because it reaches a query as an identifier.
        return preg_match('/^[a-zA-Z0-9_]+$/', (string) $column) ? (string) $column : null;
    }

    /**
     * The content type row for an extension, or null when it is not registered as one.
     *
     * @param string $extension The workflow extension
     *
     * @return object|null
     *
     * @since __DEPLOY_VERSION__
     */
    private function contentTypeFor(string $extension): ?object
    {
        if (!\array_key_exists($extension, $this->contentTypes)) {
            $db    = $this->database;
            $query = $db->getQuery(true)
                ->select($db->quoteName('table'))
                ->from($db->quoteName('#__content_types'))
                ->where($db->quoteName('type_alias') . ' = :extension')
                ->bind(':extension', $extension);

            $this->contentTypes[$extension] = $db->setQuery($query)->loadObject() ?: null;
        }

        return $this->contentTypes[$extension];
    }
}
