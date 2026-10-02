<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Workflow\Administrator\Automation;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Works out, on the fly, each item's next automated transition in a workflow.
 *
 * @since  __DEPLOY_VERSION__
 */
final class UpcomingTransitionsCalculator
{
    /**
     * How many items the panel on the workflow edit screen reports on. That panel sits inside an
     * edit form, where a pagination bar would submit the form and lose unsaved changes, so it
     * shows the head of the list rather than all of it.
     *
     * @var    integer
     * @since  __DEPLOY_VERSION__
     */
    public const MAX_ITEMS_PER_PANEL = 20;

    /**
     * @var    DatabaseInterface
     * @since  __DEPLOY_VERSION__
     */
    private DatabaseInterface $database;

    /**
     * @var    ConditionEvaluator
     * @since  __DEPLOY_VERSION__
     */
    private ConditionEvaluator $conditionEvaluator;

    /**
     * @var    ItemFieldResolver
     * @since  __DEPLOY_VERSION__
     */
    private ItemFieldResolver $itemFieldResolver;

    /**
     * @var    ConditionWindowCalculator
     * @since  __DEPLOY_VERSION__
     */
    private ConditionWindowCalculator $conditionWindowCalculator;

    /**
     * @param   DatabaseInterface  $database  The database driver.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function __construct(DatabaseInterface $database)
    {
        $this->database                  = $database;
        $this->conditionEvaluator        = new ConditionEvaluator();
        $this->itemFieldResolver         = new ItemFieldResolver($database);
        $this->conditionWindowCalculator = new ConditionWindowCalculator($database);
    }

    /**
     * Calculates the next automated transition for one page of a workflow's items.
     *
     * @param   integer  $workflowId  The workflow id.
     * @param   integer  $limit       Page size, or 0 for every item. Defaults to the panel's cap.
     * @param   integer  $start       How many items to skip.
     * @param   integer  $stageId     Only items currently in this stage, or 0 for any stage.
     *
     * @return  UpcomingTransition[]  Items needing attention first, then longest waiting.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function forWorkflow(int $workflowId, int $limit = self::MAX_ITEMS_PER_PANEL, int $start = 0, int $stageId = 0): array
    {
        if ($limit <= 0) {
            return $this->buildFromRows($this->fetchRowsForWorkflow($workflowId, [], $stageId));
        }

        $db    = $this->database;
        $scope = $this->inStage($this->inScopeItemStateQuery(), $stageId)
            ->where($db->quoteName('wt.workflow_id') . ' = :workflowId')
            ->bind(':workflowId', $workflowId, ParameterType::INTEGER);

        $itemStateIds = $this->itemStateIds($scope, $limit, $start);

        if ($itemStateIds === []) {
            return [];
        }

        return $this->buildFromRows($this->fetchRowsForWorkflow($workflowId, $itemStateIds));
    }

    /**
     * Counts the items in one workflow that carry a live automation rule. An upper bound on what
     * gets listed, for the same reason as countForExtension().
     *
     * @param   integer  $workflowId  The workflow id.
     * @param   integer  $stageId     Only items currently in this stage, or 0 for any stage.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    public function countForWorkflow(int $workflowId, int $stageId = 0): int
    {
        $db    = $this->database;
        $query = $this->inStage($this->inScopeItemStateQuery(), $stageId)
            ->select('COUNT(DISTINCT ' . $db->quoteName('wis.id') . ')')
            ->where($db->quoteName('wt.workflow_id') . ' = :workflowId')
            ->bind(':workflowId', $workflowId, ParameterType::INTEGER);

        return (int) $db->setQuery($query)->loadResult();
    }

    /**
     * Calculates the next automated transition for one page of the items under an extension,
     * across all of its workflows.
     *
     * @param   string   $extension  The workflow extension, e.g. com_content.article.
     * @param   integer  $limit      Page size, or 0 for every item, which is what the user asks
     *                               for by choosing All in the limit box.
     * @param   integer  $start      How many items to skip.
     * @param   integer  $stageId    Only items currently in this stage, or 0 for any stage.
     *
     * @return  UpcomingTransition[]  Items needing attention first, then longest waiting.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function forExtension(string $extension, int $limit = 0, int $start = 0, int $stageId = 0): array
    {
        if ($limit <= 0) {
            return $this->buildFromRows($this->fetchRowsForExtension($extension, [], $stageId));
        }

        $db    = $this->database;
        $scope = $this->inStage($this->inScopeItemStateQuery(), $stageId)
            ->where($db->quoteName('wis.extension') . ' = :extension')
            ->bind(':extension', $extension);

        $itemStateIds = $this->itemStateIds($scope, $limit, $start);

        if ($itemStateIds === []) {
            return [];
        }

        return $this->buildFromRows($this->fetchRowsForExtension($extension, $itemStateIds));
    }

    /**
     * Counts the items under an extension that carry a live automation rule.
     *
     * This is an upper bound on what the view lists, not an exact figure: an item whose rule
     * filters it out is counted here and not shown, because only evaluating the filter in PHP
     * can tell them apart, which is the work pagination exists to avoid.
     *
     * @param   string   $extension  The workflow extension.
     * @param   integer  $stageId    Only items currently in this stage, or 0 for any stage.
     *
     * @return  integer
     *
     * @since   __DEPLOY_VERSION__
     */
    public function countForExtension(string $extension, int $stageId = 0): int
    {
        $db    = $this->database;
        $query = $this->inStage($this->inScopeItemStateQuery(), $stageId)
            ->select('COUNT(DISTINCT ' . $db->quoteName('wis.id') . ')')
            ->where($db->quoteName('wis.extension') . ' = :extension')
            ->bind(':extension', $extension);

        return (int) $db->setQuery($query)->loadResult();
    }

    /**
     * Calculates the next automated transition for a single item, or null if it has none.
     *
     * @param   integer  $itemId     The content item id.
     * @param   string   $extension  The workflow extension, e.g. com_content.article.
     *
     * @return  UpcomingTransition|null
     *
     * @since   __DEPLOY_VERSION__
     */
    public function forItem(int $itemId, string $extension): ?UpcomingTransition
    {
        $upcoming = $this->buildFromRows($this->fetchRowsForItem($itemId, $extension));

        return $upcoming[0] ?? null;
    }

    /**
     * Turns candidate rows into upcoming transitions: soonest in-scope rule per item.
     *
     * @param   object[]  $rows  Candidate (item, transition, rule) rows.
     *
     * @return  UpcomingTransition[]  Soonest first; uncomputable times last.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function buildFromRows(array $rows): array
    {
        $bestByItem = [];

        // Items whose filter cannot be read are kept aside rather than dropped, so a broken rule
        // still shows in the view. The first message wins, as in the scheduler.
        $liveFailureByItem = [];
        $fallbackRowByItem = [];

        $itemIdsByExtension = [];
        foreach ($rows as $row) {
            $itemIdsByExtension[$row->extension][] = (int) $row->item_id;
        }

        foreach ($itemIdsByExtension as $extension => $itemIds) {
            $this->itemFieldResolver->preload($itemIds, $extension);
        }

        foreach ($rows as $row) {
            $itemKey      = $row->extension . '.' . $row->item_id;
            $resolveField = $this->itemFieldResolver->forItem((int) $row->item_id, $row->extension);

            try {
                if (!$this->conditionEvaluator->evaluate($row->item_filter, $resolveField)) {
                    continue;
                }
            } catch (ConditionEvaluationException $invalidCondition) {
                $liveFailureByItem[$itemKey] ??= $invalidCondition->getMessage();
                $fallbackRowByItem[$itemKey] ??= $row;

                continue;
            }

            // Rules compete on their deadline, before any fire condition is applied.
            $deadline = DeadlineCalculator::forRule($row->entered_at, $row);

            if (!isset($bestByItem[$itemKey]) || $this->isSooner($deadline, $bestByItem[$itemKey]['deadline'])) {
                $bestByItem[$itemKey] = ['row' => $row, 'deadline' => $deadline];
            }
        }

        // An item whose every rule was unreadable is shown through the first rule that failed.
        foreach ($fallbackRowByItem as $itemKey => $row) {
            $bestByItem[$itemKey] ??= ['row' => $row, 'deadline' => null];
        }

        $now      = new \DateTime('now', new \DateTimeZone('UTC'));
        $upcoming = [];

        foreach ($bestByItem as $itemKey => $winner) {
            $upcoming[] = $this->buildUpcomingTransition(
                $winner['row'],
                $winner['deadline'],
                $now,
                $liveFailureByItem[$itemKey] ?? ''
            );
        }

        return $upcoming;
    }

    /**
     * The item state rows that could produce an upcoming transition, with no scope clause yet.
     *
     * Goes through joinLiveRules() like baseRowsQuery(), so a count taken here and a page of rows
     * taken there cannot disagree about what is live.
     *
     * @return  QueryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function inScopeItemStateQuery(): QueryInterface
    {
        $db = $this->database;

        return $this->joinLiveRules($db->getQuery(true)->from($db->quoteName('#__workflow_item_state', 'wis')));
    }

    /**
     * Joins item state rows to the live rules that could move them, matching the scheduler.
     *
     * A transition from any stage has from_stage_id -1 and applies to every item in its own workflow.
     * A rule that has already run for an item since the item entered its current stage is left out,
     * because the scheduler runs each rule once per stay in a stage.
     *
     * @param   QueryInterface  $query  A query selecting from #__workflow_item_state as wis.
     *
     * @return  QueryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function joinLiveRules(QueryInterface $query): QueryInterface
    {
        $db = $this->database;

        // 0 is a successful run in #__workflow_automation_log.exit_code.
        $alreadyRan = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__workflow_automation_log', 'wal'))
            ->where($db->quoteName('wal.rule_id') . ' = ' . $db->quoteName('war.id'))
            ->where($db->quoteName('wal.item_id') . ' = ' . $db->quoteName('wis.item_id'))
            ->where($db->quoteName('wal.extension') . ' = ' . $db->quoteName('wis.extension'))
            ->where($db->quoteName('wal.exit_code') . ' = 0')
            ->where($db->quoteName('wal.executed_at') . ' >= ' . $db->quoteName('wis.entered_at'));

        return $query
            ->join(
                'INNER',
                $db->quoteName('#__workflow_stages', 'ws'),
                $db->quoteName('ws.id') . ' = ' . $db->quoteName('wis.stage_id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__workflow_transitions', 'wt'),
                $db->quoteName('wt.workflow_id') . ' = ' . $db->quoteName('ws.workflow_id')
                    . ' AND ' . $db->quoteName('wt.from_stage_id') . ' IN (' . $db->quoteName('wis.stage_id') . ', -1)'
            )
            ->join(
                'INNER',
                $db->quoteName('#__workflow_automation_rules', 'war'),
                $db->quoteName('war.transition_id') . ' = ' . $db->quoteName('wt.id')
            )
            ->join(
                'INNER',
                $db->quoteName('#__workflows', 'w'),
                $db->quoteName('w.id') . ' = ' . $db->quoteName('wt.workflow_id')
            )
            ->where($db->quoteName('w.published') . ' = 1')
            ->where($db->quoteName('wt.published') . ' = 1')
            ->where($db->quoteName('war.published') . ' = 1')
            ->where('NOT EXISTS (' . $alreadyRan . ')');
    }

    /**
     * Runs a scoped item state query and returns one page of ids.
     *
     * @param   QueryInterface  $scope  A query from inScopeItemStateQuery(), scoped by the caller.
     * @param   integer         $limit  Page size.
     * @param   integer         $start  How many rows to skip.
     *
     * @return  int[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function itemStateIds(QueryInterface $scope, int $limit, int $start): array
    {
        $db = $this->database;

        // The two sort columns are selected as well as ordered by, which PostgreSQL requires of a
        // DISTINCT query. loadColumn() reads the first column, so it still returns ids.
        $scope->select(
            'DISTINCT ' . implode(
                ', ',
                $db->quoteName(['wis.id', 'wis.requires_intervention', 'wis.entered_at'])
            )
        )
            ->order($db->quoteName('wis.requires_intervention') . ' DESC')
            ->order($db->quoteName('wis.entered_at') . ' ASC')
            // Unique last key: items that entered at the same second would otherwise swap places between
            // pages, repeating one item and skipping another.
            ->order($db->quoteName('wis.id') . ' ASC')
            ->setLimit($limit, $start);

        return array_map('intval', $db->setQuery($scope)->loadColumn());
    }

    /**
     * Narrows a query on #__workflow_item_state as wis to items currently in one stage.
     *
     * @param   QueryInterface  $query    The query to narrow.
     * @param   integer         $stageId  The stage, or 0 to leave the query as it is.
     *
     * @return  QueryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function inStage(QueryInterface $query, int $stageId): QueryInterface
    {
        if ($stageId > 0) {
            $query->where($this->database->quoteName('wis.stage_id') . ' = ' . $stageId);
        }

        return $query;
    }

    /**
     * Applies the display order: items needing attention first, then the longest waiting, and
     * within a single item the transition order, which settles ties the way the scheduler does.
     *
     * @param   QueryInterface  $query  A rows query from baseRowsQuery().
     *
     * @return  QueryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function orderedByWait(QueryInterface $query): QueryInterface
    {
        $db = $this->database;

        return $query->order($db->quoteName('wis.requires_intervention') . ' DESC')
            ->order($db->quoteName('wis.entered_at') . ' ASC')
            ->order($db->quoteName('wis.id') . ' ASC')
            ->order($db->quoteName('wt.ordering') . ' ASC');
    }


    /**
     * Loads the candidate rows for a set of item states in a workflow.
     *
     * @param   integer  $workflowId    The workflow id.
     * @param   int[]    $itemStateIds  The item state rows to report on, or none for all of them.
     * @param   integer  $stageId       Only items currently in this stage, or 0 for any stage.
     *
     * @return  object[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fetchRowsForWorkflow(int $workflowId, array $itemStateIds = [], int $stageId = 0): array
    {
        $db    = $this->database;
        $query = $this->inStage($this->baseRowsQuery(), $stageId)
            ->where($db->quoteName('wt.workflow_id') . ' = :workflowId')
            ->bind(':workflowId', $workflowId, ParameterType::INTEGER);

        if ($itemStateIds !== []) {
            $query->whereIn($db->quoteName('wis.id'), $itemStateIds);
        }

        return $this->fetchRows($this->orderedByWait($query));
    }

    /**
     * Loads the candidate rows for every workflow under an extension.
     *
     * @param   string   $extension     The workflow extension.
     * @param   int[]    $itemStateIds  The item state rows to report on, or none for all of them.
     * @param   integer  $stageId       Only items currently in this stage, or 0 for any stage.
     *
     * @return  object[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fetchRowsForExtension(string $extension, array $itemStateIds = [], int $stageId = 0): array
    {
        $db    = $this->database;
        $query = $this->inStage($this->baseRowsQuery(), $stageId)
            ->where($db->quoteName('wis.extension') . ' = :extension')
            ->bind(':extension', $extension);

        if ($itemStateIds !== []) {
            $query->whereIn($db->quoteName('wis.id'), $itemStateIds);
        }

        return $this->fetchRows($this->orderedByWait($query));
    }

    /**
     * Loads the candidate rows for a single item.
     *
     * @param   integer  $itemId     The content item id.
     * @param   string   $extension  The workflow extension.
     *
     * @return  object[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fetchRowsForItem(int $itemId, string $extension): array
    {
        $db    = $this->database;
        $query = $this->baseRowsQuery()
            ->where($db->quoteName('wis.item_id') . ' = :itemId')
            ->where($db->quoteName('wis.extension') . ' = :extension')
            ->bind(':itemId', $itemId, ParameterType::INTEGER)
            ->bind(':extension', $extension)
            ->order($db->quoteName('wt.ordering') . ' ASC');

        return $this->fetchRows($query);
    }

    /**
     * Builds the shared select and joins, without a scope clause.
     *
     * @return  QueryInterface
     *
     * @since   __DEPLOY_VERSION__
     */
    private function baseRowsQuery(): QueryInterface
    {
        $db    = $this->database;
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('wis.item_id'),
                    $db->quoteName('wis.extension'),
                    $db->quoteName('wis.entered_at'),
                    $db->quoteName('wis.requires_intervention'),
                    $db->quoteName('wis.last_failure_at'),
                    $db->quoteName('wis.last_failure_reason'),
                    $db->quoteName('wt.id', 'transition_id'),
                    $db->quoteName('wt.title', 'transition_title'),
                    // The item's own stage, since a transition from any stage stores -1 as its from stage.
                    $db->quoteName('wis.stage_id', 'from_stage_id'),
                    $db->quoteName('wt.to_stage_id'),
                    $db->quoteName('ws.title', 'from_stage_title'),
                    $db->quoteName('sto.title', 'to_stage_title'),
                    $db->quoteName('war.rule_type'),
                    $db->quoteName('war.delay_value'),
                    $db->quoteName('war.delay_unit'),
                    $db->quoteName('war.cron_expression'),
                    $db->quoteName('war.item_filter'),
                    $db->quoteName('war.fire_condition'),
                    $db->quoteName('wt.ordering'),
                    $db->quoteName('w.id', 'workflow_id'),
                    $db->quoteName('w.title', 'workflow_title'),
                ]
            )
            ->from($db->quoteName('#__workflow_item_state', 'wis'));

        return $this->joinLiveRules($query)
            ->join(
                'LEFT',
                $db->quoteName('#__workflow_stages', 'sto'),
                $db->quoteName('sto.id') . ' = ' . $db->quoteName('wt.to_stage_id')
            );
    }

    /**
     * Builds one upcoming transition from a winning row.
     *
     * @param   object          $row       The chosen (item, transition, rule) row.
     * @param   \DateTime|null  $deadline  The moment the rule becomes due, or null.
     * @param   \DateTime       $now       Current time (UTC).
     *
     * @return  UpcomingTransition
     *
     * @since   __DEPLOY_VERSION__
     */
    private function buildUpcomingTransition(
        object $row,
        ?\DateTime $deadline,
        \DateTime $now,
        string $liveFailureReason
    ): UpcomingTransition {
        $requiresIntervention = (int) $row->requires_intervention === 1;
        $hasCondition         = $row->fire_condition !== null && trim((string) $row->fire_condition) !== '';

        [$firesAt, $status, $discoveredReason] = $this->resolveFireTime(
            $row,
            $deadline,
            $now,
            $hasCondition,
            $requiresIntervention,
            $liveFailureReason
        );

        $storedReason = (string) ($row->last_failure_reason ?? '');
        $storedAt     = $row->last_failure_at !== null
            ? new \DateTime((string) $row->last_failure_at, new \DateTimeZone('UTC'))
            : null;

        // A fault found by this render wins over the one the scheduler stored, which may be out of
        // date. A stored fault alone is shown with its time but does not change the status.
        $failureReason = $discoveredReason !== '' ? $discoveredReason : $storedReason;
        $failedAt      = $discoveredReason !== '' ? null : $storedAt;

        return new UpcomingTransition(
            itemId: (int) $row->item_id,
            extension: (string) $row->extension,
            itemTitle: (string) ($row->item_title ?? ''),
            editUrl: $this->buildEditUrl($row),
            fromStage: (string) ($row->from_stage_title ?? ''),
            toStage: (string) ($row->to_stage_title ?? ''),
            transitionTitle: (string) ($row->transition_title ?? ''),
            firesAt: $firesAt,
            status: $status,
            failureReason: $failureReason,
            failedAt: $failedAt,
            ruleType: (string) $row->rule_type,
            delayValue: $row->delay_value !== null ? (int) $row->delay_value : null,
            delayUnit: $row->delay_unit,
            cronExpression: $row->cron_expression,
            hasCondition: $hasCondition,
            workflowTitle: (string) ($row->workflow_title ?? ''),
            workflowId: (int) ($row->workflow_id ?? 0)
        );
    }

    /**
     * Works out when a rule will really fire, and the status to show for it.
     *
     * A gated rule fires at the first moment its condition holds, searching from the deadline,
     * or from now when the rule is already overdue.
     *
     * @param   object          $row                   The rule row.
     * @param   \DateTime|null  $deadline              The moment the rule becomes due.
     * @param   \DateTime       $now                   Current time (UTC).
     * @param   boolean         $hasCondition          Whether a fire condition exists.
     * @param   boolean         $requiresIntervention  Whether the item is flagged stuck.
     * @param   string          $liveFailureReason     A filter fault this render found, or ''.
     *
     * @return  array{0: \DateTime|null, 1: string, 2: string}  Fire time, status, and any fault found.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function resolveFireTime(
        object $row,
        ?\DateTime $deadline,
        \DateTime $now,
        bool $hasCondition,
        bool $requiresIntervention,
        string $liveFailureReason
    ): array {
        // Blocked outranks everything: the scheduler skips the item until a person clears it.
        if ($requiresIntervention) {
            return [$deadline, 'needs_attention', ''];
        }

        if ($liveFailureReason !== '') {
            return [null, 'rule_error', $liveFailureReason];
        }

        if ($deadline === null) {
            return [null, 'not_scheduled', ''];
        }

        if (!$hasCondition) {
            return [$deadline, 'scheduled', ''];
        }

        try {
            $firesAt = $this->conditionWindowCalculator->firstMatchAtOrAfter(
                $row->fire_condition,
                max($deadline, $now),
                (int) $row->item_id,
                (string) $row->extension
            );
        } catch (ConditionEvaluationException $invalidCondition) {
            // Not needs_attention: the item is not stuck. It is retried every run and recovers once
            // the rule is fixed.
            return [null, 'rule_error', $invalidCondition->getMessage()];
        }

        if ($firesAt === null) {
            return [null, 'not_scheduled', ''];
        }

        return [$firesAt, 'scheduled', ''];
    }

    /**
     * Builds an un-routed admin edit link for the item, or '' when the extension is not linkable.
     *
     * @param   object  $row  The rule row.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function buildEditUrl(object $row): string
    {
        $parts  = explode('.', (string) $row->extension);
        $option = $parts[0] ?? '';
        $type   = $parts[1] ?? '';

        if ($option === '' || $type === '') {
            return '';
        }

        return 'index.php?option=' . $option . '&task=' . $type . '.edit&id=' . (int) $row->item_id;
    }

    /**
     * Whether a candidate fire time is sooner than the current best. Null (uncomputable) is
     * treated as the latest possible, so a computable time always wins.
     *
     * @param   \DateTime|null  $candidate  The candidate time.
     * @param   \DateTime|null  $current    The current best time.
     *
     * @return  boolean
     *
     * @since   __DEPLOY_VERSION__
     */
    private function isSooner(?\DateTime $candidate, ?\DateTime $current): bool
    {
        if ($candidate === null) {
            return false;
        }

        if ($current === null) {
            return true;
        }

        return $candidate < $current;
    }

    /**
     * Calculates the next automated transition for a set of items, keyed by item id.
     *
     * @param int[] $itemIds The content item ids.
     * @param string $extension The workflow extension, e.g. com_content.article.
     *
     * @return array<int, UpcomingTransition>
     *
     * @since __DEPLOY_VERSION__
     */
    public function forItems(array $itemIds, string $extension): array
    {
        $itemIds = array_values(array_unique(array_map('intval', $itemIds)));

        if (empty($itemIds)) {
            return [];
        }

        $byItem = [];

        foreach ($this->buildFromRows($this->fetchRowsForItems($itemIds, $extension)) as $transition) {
            $byItem[$transition->itemId] = $transition;
        }

        return $byItem;
    }

    /**
     * Loads the candidate row for a set of items
     *
     * @param int[] $itemIds The content item ids.
     * @param string $extension The workflow extension.
     *
     * @return object[]
     *
     * @since __DEPLOY_VERSION__
     */
    private function fetchRowsForItems(array $itemIds, string $extension): array
    {
        $db                 = $this->database;
        $scheduledRowsQuery = $this->baseRowsQuery()
            ->whereIn($db->quoteName('wis.item_id'), $itemIds)
            ->where($db->quoteName('wis.extension') . ' = :extension')
            ->bind(':extension', $extension)
            ->order($db->quoteName('wt.ordering') . ' ASC');

        return $this->fetchRows($scheduledRowsQuery);
    }

    /**
     * Runs a rows query, drops trashed and archived items, and fills in item titles.
     *
     * Both need the extension's own item table, which one query cannot join per row.
     *
     * @param QueryInterface $query The prepared rows query.
     *
     * @return object[]
     *
     * @since __DEPLOY_VERSION__
     */
    private function fetchRows(QueryInterface $query): array
    {
        $rows = $this->database->setQuery($query)->loadObjectList() ?: [];

        if ($rows === []) {
            return [];
        }

        $itemIdsByExtension = [];

        foreach ($rows as $row) {
            $itemIdsByExtension[$row->extension][] = (int) $row->item_id;
        }

        $itemStorage = new ItemStorage($this->database);
        $excluded    = [];
        $titles      = [];

        foreach ($itemIdsByExtension as $extension => $itemIds) {
            foreach ($itemStorage->trashedOrArchivedIds($itemIds, $extension) as $itemId) {
                // An item id is only unique within its own extension.
                $excluded[$extension . '.' . $itemId] = true;
            }

            foreach ($itemStorage->titlesFor($itemIds, $extension) as $itemId => $title) {
                $titles[$extension . '.' . $itemId] = $title;
            }
        }

        $kept = [];

        foreach ($rows as $row) {
            $key = $row->extension . '.' . $row->item_id;

            if (isset($excluded[$key])) {
                continue;
            }

            $row->item_title = $titles[$key] ?? null;
            $kept[]          = $row;
        }

        return $kept;
    }
}
