<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Automation;

use Joomla\CMS\Event\Workflow\WorkflowConditionFieldsEvent;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The condition checks Joomla ships with.
 *
 * Asked before the workflow plugins, so a plugin cannot redefine a built-in check and change
 * what an already-saved rule means.
 *
 * @since  __DEPLOY_VERSION__
 */
final class BuiltinConditionFields
{
    /**
     * @var    DatabaseInterface
     * @since  __DEPLOY_VERSION__
     */
    private DatabaseInterface $database;

    /**
     * Content type rows already looked up by extension. Null means the extension
     * is not registered as a content type at all
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
     * The built-in checks available for an extension.
     *
     * @param string $extension The workflow extension, e.g com_content.article.
     *
     * @return array<string, array> Check definitions keyed by name.
     *
     * @since __DEPLOY_VERSION__
     */
    public function declarations(string $extension): array
    {
        $fields                = [];
        $fields['day_of_week'] = $this->field(
            'day_of_week',
            Text::_('COM_WORKFLOW_AUTOMATION_FIELD_DAY_OF_WEEK'),
            WorkflowConditionFieldsEvent::SCOPE_MOMENT,
            [
                WorkflowConditionFieldsEvent::OPERATOR_IN,
                WorkflowConditionFieldsEvent::OPERATOR_NOT_IN,
            ],
            WorkflowConditionFieldsEvent::VALUE_MULTISELECT,
            $this->getWeekdayOptions()
        );

        $fields['date'] = $this->field(
            'date',
            Text::_('COM_WORKFLOW_AUTOMATION_FIELD_DATE'),
            WorkflowConditionFieldsEvent::SCOPE_MOMENT,
            [
                WorkflowConditionFieldsEvent::OPERATOR_AFTER,
                WorkflowConditionFieldsEvent::OPERATOR_BEFORE,
                WorkflowConditionFieldsEvent::OPERATOR_ON,
                WorkflowConditionFieldsEvent::OPERATOR_NOT_ON,
            ],
            WorkflowConditionFieldsEvent::VALUE_DATE
        );

        if ($this->typeIdFor($extension) !== null) {
            $fields['tag'] = $this->field(
                'tag',
                Text::_('COM_WORKFLOW_AUTOMATION_FIELD_TAG'),
                WorkflowConditionFieldsEvent::SCOPE_ITEM,
                [
                    WorkflowConditionFieldsEvent::OPERATOR_HAS_ANY,
                    WorkflowConditionFieldsEvent::OPERATOR_HAS_ALL,
                    WorkflowConditionFieldsEvent::OPERATOR_HAS_NONE,
                ],
                WorkflowConditionFieldsEvent::VALUE_MULTISELECT,
                $this->getTagOptions()
            );
        }

        // Each check below is offered only where the extension's own item table has its column.
        if ($this->itemTableHasColumn($extension, 'catid')) {
            $fields['category'] = $this->field(
                'category',
                Text::_('COM_WORKFLOW_AUTOMATION_FIELD_CATEGORY'),
                WorkflowConditionFieldsEvent::SCOPE_ITEM,
                [
                    WorkflowConditionFieldsEvent::OPERATOR_IS,
                    WorkflowConditionFieldsEvent::OPERATOR_IS_NOT,
                ],
                WorkflowConditionFieldsEvent::VALUE_SELECT,
                $this->getCategoryOptions($extension)
            );
        }

        if ($this->itemTableHasColumn($extension, 'created_by')) {
            $fields['author_group'] = $this->field(
                'author_group',
                Text::_('COM_WORKFLOW_AUTOMATION_FIELD_AUTHOR_GROUP'),
                WorkflowConditionFieldsEvent::SCOPE_ITEM,
                [
                    WorkflowConditionFieldsEvent::OPERATOR_HAS_ANY,
                    WorkflowConditionFieldsEvent::OPERATOR_HAS_ALL,
                    WorkflowConditionFieldsEvent::OPERATOR_HAS_NONE,
                ],
                WorkflowConditionFieldsEvent::VALUE_MULTISELECT,
                $this->getUserGroupOptions()
            );
        }

        if ($this->itemTableHasColumn($extension, 'hits')) {
            $fields['hits'] = $this->field(
                'hits',
                Text::_('JGLOBAL_HITS'),
                WorkflowConditionFieldsEvent::SCOPE_ITEM,
                [
                    WorkflowConditionFieldsEvent::OPERATOR_GREATER_THAN,
                    WorkflowConditionFieldsEvent::OPERATOR_LESS_THAN,
                    WorkflowConditionFieldsEvent::OPERATOR_IS,
                    WorkflowConditionFieldsEvent::OPERATOR_IS_NOT,
                ],
                WorkflowConditionFieldsEvent::VALUE_NUMBER
            );
        }

        $fields['days_in_stage'] = $this->field(
            'days_in_stage',
            Text::_('COM_WORKFLOW_AUTOMATION_FIELD_DAYS_IN_STAGE'),
            WorkflowConditionFieldsEvent::SCOPE_ITEM,
            [
                WorkflowConditionFieldsEvent::OPERATOR_GREATER_THAN,
                WorkflowConditionFieldsEvent::OPERATOR_LESS_THAN,
                WorkflowConditionFieldsEvent::OPERATOR_IS,
                WorkflowConditionFieldsEvent::OPERATOR_IS_NOT,
            ],
            WorkflowConditionFieldsEvent::VALUE_NUMBER
        );

        if ($this->itemTableHasColumn($extension, 'modified')) {
            $fields['days_since_modified'] = $this->field(
                'days_since_modified',
                Text::_('COM_WORKFLOW_AUTOMATION_FIELD_DAYS_SINCE_MODIFIED'),
                WorkflowConditionFieldsEvent::SCOPE_ITEM,
                [
                    WorkflowConditionFieldsEvent::OPERATOR_GREATER_THAN,
                    WorkflowConditionFieldsEvent::OPERATOR_LESS_THAN,
                    WorkflowConditionFieldsEvent::OPERATOR_IS,
                    WorkflowConditionFieldsEvent::OPERATOR_IS_NOT,
                ],
                WorkflowConditionFieldsEvent::VALUE_NUMBER
            );
        }

        return $fields;
    }

    /**
     * Resolves one built-in check for a whole batch of items.
     *
     * @param string $fieldName The check being resolved
     * @param int[] $itemIds The items to resolve it for
     * @param string $extension The workflow extension, e.g com_content.article
     * @param \DateTime|null $moment The moment clock-based checks should describe.
     *
     * @return array<int, mixed>|null Values keyed by item id, or null when the name is not a built-in.
     *
     * @since __DEPLOY_VERSION__
     */
    public function resolve(
        string $fieldName,
        array $itemIds,
        string $extension,
        ?\DateTime $moment = null
    ): ?array {
        $itemIds = array_map('intval', $itemIds);
        $when    = $moment ?? new \DateTime('now', new \DateTimeZone('UTC'));

        switch ($fieldName) {
            case 'day_of_week':
                return $this->sameForEveryItem($itemIds, (int) $when->format('w'));

            case 'date':
                return $this->sameForEveryItem($itemIds, $when->format('Y-m-d H:i:s'));

            case 'tag':
                return $this->loadTagIds($itemIds, $extension);

            case 'category':
                return $this->loadColumnPerItem($itemIds, $extension, 'catid', 0);

            case 'hits':
                return $this->loadColumnPerItem($itemIds, $extension, 'hits', 0);

            case 'author_group':
                return $this->loadAuthorGroupIds($itemIds, $extension);

            case 'days_in_stage':
                return $this->loadDaysInStage($itemIds, $extension, $when);

            case 'days_since_modified':
                return $this->loadDaysSinceModified($itemIds, $extension, $when);
        }

        return null;
    }

    /**
     * Gives every item the same value, for checks that describe the clock rather than the item
     *
     * @param int[] $itemIds The items being resolved.
     * @param mixed $value The value they all share.
     *
     * @return array<int, mixed>
     *
     * @since __DEPLOY_VERSION__
     */
    private function sameForEveryItem(array $itemIds, $value): array
    {
        return $itemIds === [] ? [] : array_fill_keys($itemIds, $value);
    }

    /**
     * Tag ids per item, in one query.
     *
     * Filters by type_id through a subquery, because the tag map's primary key starts with
     * type_id and type_alias has no index.
     *
     * @param int[] $itemIds The items.
     * @param string $extension The workflow extension
     *
     * @return array<int, int[]>
     *
     * @since __DEPLOY_VERSION__
     */
    private function loadTagIds(array $itemIds, string $extension): array
    {
        if ($itemIds === [] || $this->typeIdFor($extension) === null) {
            return [];
        }

        $db = $this->database;

        $tagTypeSubquery = '(SELECT ' . $db->quoteName('ct.type_id')
            . ' FROM ' . $db->quoteName('#__content_types', 'ct')
            . ' WHERE ' . $db->quoteName('ct.type_alias') . ' = :extension)';

        $query = $db->getQuery(true)
            ->select($db->quoteName(['content_item_id', 'tag_id']))
            ->from($db->quoteName('#__contentitem_tag_map'))
            ->where($db->quoteName('type_id') . ' = ' . $tagTypeSubquery)
            ->whereIn($db->quoteName('content_item_id'), $itemIds)
            ->bind(':extension', $extension);

        // An item with no tags gets an empty list. A missing answer would be treated as an error.
        $values = array_fill_keys($itemIds, []);

        foreach ($db->setQuery($query)->loadObjectList() ?: [] as $row) {
            $values[(int) $row->content_item_id][] = (int) $row->tag_id;
        }

        return $values;
    }

    /**
     * One column of the extension's own item table, per item, in one query.
     *
     * @param int[] $itemIds The items.
     * @param string $extension The workflow extension.
     * @param string $column The column to read.
     * @param mixed $default The value for an item the query does not return.
     *
     * @return array<int, mixed>
     *
     * @since __DEPLOY_VERSION__
     */
    private function loadColumnPerItem(array $itemIds, string $extension, string $column, $default): array
    {
        $storage = $this->itemStorageFor($extension);

        if ($itemIds === [] || $storage === null || !$this->itemTableHasColumn($extension, $column)) {
            return [];
        }

        $db              = $this->database;
        $itemValuesQuery = $db->getQuery(true)
            ->select($db->quoteName([$storage['key'], $column]))
            ->from($db->quoteName($storage['table']))
            ->whereIn($db->quoteName($storage['key']), $itemIds);

        $values = array_fill_keys($itemIds, $default);

        foreach ($db->setQuery($itemValuesQuery)->loadAssocList() ?: [] as $row) {
            $values[(int) $row[$storage['key']]] = $row[$column];
        }

        return $values;
    }

    /**
     * How many whole days each item has been sitting in its current stage.
     *
     * An item with no state row yet counts as 0 days, so a filter never silently skips it.
     *
     * @param   int[]      $itemIds    The items.
     * @param   string     $extension  The workflow extension.
     * @param   \DateTime  $when       The moment to measure against.
     *
     * @return  array<int, int>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadDaysInStage(array $itemIds, string $extension, \DateTime $when): array
    {
        if ($itemIds === []) {
            return [];
        }

        $db    = $this->database;
        $query = $db->getQuery(true)
            ->select($db->quoteName(['item_id', 'entered_at']))
            ->from($db->quoteName('#__workflow_item_state'))
            ->where($db->quoteName('extension') . ' = :extension')
            ->whereIn($db->quoteName('item_id'), $itemIds)
            ->bind(':extension', $extension);

        $ages = array_fill_keys($itemIds, 0);

        foreach ($db->setQuery($query)->loadAssocList() ?: [] as $row) {
            if ($this->isRealDate($row['entered_at'])) {
                $ages[(int) $row['item_id']] = $this->wholeDaysSince($row['entered_at'], $when);
            }
        }

        return $ages;
    }

    /**
     * How many whole days since each item was last edited.
     *
     * Falls back to the creation date for an item whose modified date is empty.
     *
     * @param   int[]      $itemIds    The items.
     * @param   string     $extension  The workflow extension.
     * @param   \DateTime  $when       The moment to measure against.
     *
     * @return  array<int, int>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function loadDaysSinceModified(array $itemIds, string $extension, \DateTime $when): array
    {
        $modified = $this->loadColumnPerItem($itemIds, $extension, 'modified', null);
        $created  = $this->itemTableHasColumn($extension, 'created')
            ? $this->loadColumnPerItem($itemIds, $extension, 'created', null)
            : [];

        $ages = [];

        foreach ($itemIds as $itemId) {
            $stamp = $modified[$itemId] ?? null;

            if (!$this->isRealDate($stamp)) {
                $stamp = $created[$itemId] ?? null;
            }

            $ages[$itemId] = $this->isRealDate($stamp) ? $this->wholeDaysSince($stamp, $when) : 0;
        }

        return $ages;
    }

    /**
     * Whole days between a stored timestamp and a moment, never negative.
     *
     * Floored, so 7 means from seven days up to just under eight.
     *
     * @param   string     $stamp  A database timestamp, in UTC.
     * @param   \DateTime  $when   The moment to measure against.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    private function wholeDaysSince(string $stamp, \DateTime $when): int
    {
        $then = date_create_immutable($stamp, new \DateTimeZone('UTC'));

        if ($then === false) {
            return 0;
        }

        return (int) max(0, floor(($when->getTimestamp() - $then->getTimestamp()) / 86400));
    }

    /**
     * Whether a stored timestamp is a real date rather than null or a zero date.
     *
     * @param   mixed  $stamp  The stored value.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isRealDate($stamp): bool
    {
        return \is_string($stamp) && $stamp !== '' && strpos($stamp, '0000-00-00') !== 0;
    }

    /**
     * The author's user groups per item, in two queries: the authors, then their groups.
     *
     * @param int[] $itemIds The items.
     * @param string $extension The workflow extension.
     *
     * @return array<int, int[]>
     *
     * @since __DEPLOY_VERSION__
     */
    private function loadAuthorGroupIds(array $itemIds, string $extension): array
    {
        $authorPerItem = $this->loadColumnPerItem($itemIds, $extension, 'created_by', 0);

        if ($authorPerItem === []) {
            return [];
        }

        $values          = array_fill_keys(array_keys($authorPerItem), []);
        $itemIdsByAuthor = [];

        foreach ($authorPerItem as $itemId => $authorId) {
            if ((int) $authorId > 0) {
                $itemIdsByAuthor[(int) $authorId][] = (int) $itemId;
            }
        }

        if ($itemIdsByAuthor === []) {
            return $values;
        }

        $db         = $this->database;
        $groupQuery = $db->getQuery(true)
            ->select($db->quoteName(['user_id', 'group_id']))
            ->from($db->quoteName('#__user_usergroup_map'))
            ->whereIn($db->quoteName('user_id'), array_keys($itemIdsByAuthor));

        $groupsByAuthor = [];

        foreach ($db->setQuery($groupQuery)->loadObjectList() ?: [] as $row) {
            $groupsByAuthor[(int) $row->user_id][] = (int) $row->group_id;
        }

        foreach ($itemIdsByAuthor as $authorId => $authoredItemIds) {
            foreach ($authoredItemIds as $authoredItemId) {
                $values[$authoredItemId] = $groupsByAuthor[$authorId] ?? [];
            }
        }

        return $values;
    }

    /**
     * Weekday options, Sunday (0) through Saturday (6).
     *
     * @return  array<int, array<string, string>>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getWeekdayOptions(): array
    {
        $weekdayKeys = ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
        $options     = [];

        foreach ($weekdayKeys as $dayNumber => $languageKey) {
            $options[] = ['value' => (string) $dayNumber, 'label' => Text::_($languageKey)];
        }

        return $options;
    }

    /**
     * Published content tags.
     *
     * @return  array<int, array<string, string>>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getTagOptions(): array
    {
        $db    = $this->database;
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'title']))
            ->from($db->quoteName('#__tags'))
            ->where($db->quoteName('published') . ' = 1')
            ->where($db->quoteName('id') . ' > 1')
            ->order($db->quoteName('title') . ' ASC');

        $options = [];

        foreach ($db->setQuery($query)->loadObjectList() ?: [] as $tag) {
            $options[] = ['value' => (string) $tag->id, 'label' => $tag->title];
        }

        return $options;
    }

    /**
     * The category options for an extension, e.g. com_contact's categories for a contact
     * workflow rather than always com_content's.
     *
     * @param   string  $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  array<int, array<string, string>>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getCategoryOptions(string $extension): array
    {
        // Categories belong to the component, so the section part of the extension is dropped.
        $component = strtok($extension, '.');
        $options   = [];

        foreach (HTMLHelper::_('category.options', $component) as $category) {
            $options[] = ['value' => (string) $category->value, 'label' => $category->text];
        }

        return $options;
    }

    /**
     * All user groups.
     *
     * @return  array<int, array<string, string>>
     *
     * @since   __DEPLOY_VERSION__
     */
    private function getUserGroupOptions(): array
    {
        $options = [];

        foreach (UserGroupsHelper::getInstance()->getAll() as $group) {
            $options[] = ['value' => (string) $group->id, 'label' => $group->title];
        }

        return $options;
    }

    /**
     * Builds one declaration.
     *
     * @param string $name Stored key for the check.
     * @param string $label Translated label.
     * @param string $scope SCOPE_ITEM or SCOPE_MOMENT.
     * @param string[] $operators Operators the check supports.
     * @param string $valueType How the value is entered.
     * @param array $options Selectable values if any.
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    private function field(
        string $name,
        string $label,
        string $scope,
        array $operators,
        string $valueType = WorkflowConditionFieldsEvent::VALUE_SELECT,
        array $options = []
    ): array {
        return [
            'name'      => $name,
            'label'     => $label,
            'scope'     => $scope,
            'operators' => $operators,
            'valueType' => $valueType,
            'options'   => $options,
        ];
    }

    /**
     * The content type row for an extension, or null when it is not registered as one.
     *
     * @param string $extension The workflow extension, e.g com_content.article.
     *
     * @return object|null
     *
     * @since __DEPLOY_VERSION__
     */
    private function contentTypeFor(string $extension): ?object
    {
        if (!\array_key_exists($extension, $this->contentTypes)) {
            $db               = $this->database;
            $contentTypeQuery = $db->getQuery(true)
                ->select($db->quoteName(['type_id', 'table']))
                ->from($db->quoteName('#__content_types'))
                ->where($db->quoteName('type_alias') . ' = :extension')
                ->bind(':extension', $extension);

            $this->contentTypes[$extension] = $db->setQuery($contentTypeQuery)->loadObject() ?: null;
        }

        return $this->contentTypes[$extension];
    }

    /**
     * The tag map's numeric type id of an extension, or null when it has none.
     *
     * @param string $extension The workflow's extension.
     *
     * @return integer|null
     *
     * @since __DEPLOY_VERSION__
     */
    private function typeIdFor(string $extension): ?int
    {
        $contentType = $this->contentTypeFor($extension);
        return $contentType === null ? null : (int) $contentType->type_id;
    }

    /**
     * Where an extension keeps its own items: the table and its key column.
     *
     * @param   string  $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  array{table: string, key: string}|null
     *
     * @since   __DEPLOY_VERSION__
     */
    private function itemStorageFor(string $extension): ?array
    {
        $contentType = $this->contentTypeFor($extension);

        if ($contentType === null) {
            return null;
        }

        $definition = json_decode((string) $contentType->table, true);

        // Only the special table is the extension's own. The common one is always
        // #__ucm_content, which is a different thing and is not reliably populated.
        $table = $definition['special']['dbtable'] ?? '';
        $key   = $definition['special']['key'] ?? '';

        // Used as identifiers in a query, so validated even though they come from the database.
        if (!\is_string($table) || !preg_match('/^#__[a-zA-Z0-9_]+$/', $table)) {
            return null;
        }

        if (!\is_string($key) || !preg_match('/^[a-zA-Z0-9_]+$/', $key)) {
            return null;
        }

        return ['table' => $table, 'key' => $key];
    }

    /**
     * Whether an extension's own item table carries a column.
     *
     * @param   string  $extension  The workflow extension.
     * @param   string  $column     The column the check needs.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function itemTableHasColumn(string $extension, string $column): bool
    {
        $storage = $this->itemStorageFor($extension);

        if ($storage === null) {
            return false;
        }

        try {
            return isset($this->database->getTableColumns($storage['table'], false)[$column]);
        } catch (\Throwable) {
            // A registration can outlive its table, for instance after a failed uninstall.
            return false;
        }
    }
}
