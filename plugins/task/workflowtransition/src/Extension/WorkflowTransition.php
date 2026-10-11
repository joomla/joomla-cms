<?php

/**
 * @package Joomla.Plugin
 * @subpackage Task.WorkflowTransition
 *
 * @copyright (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Task\WorkflowTransition\Extension;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Workflow\TransitionStatus;
use Joomla\CMS\Workflow\Workflow;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status as TaskStatus;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Component\Workflow\Administrator\Automation\ConditionEvaluationException;
use Joomla\Component\Workflow\Administrator\Automation\ConditionEvaluator;
use Joomla\Component\Workflow\Administrator\Automation\DeadlineCalculator;
use Joomla\Component\Workflow\Administrator\Automation\ItemFieldResolver;
use Joomla\Component\Workflow\Administrator\Automation\ItemStorage;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Event\SubscriberInterface;
use Joomla\Plugin\Task\WorkflowTransition\Dto\DueAutomation;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Scheduler task plugin that fires automated workflow transitions.
 *
 * @since __DEPLOY_VERSION__
 */
final class WorkflowTransition extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;
    use TaskPluginTrait;

    /**
     * @var string[]
     * @since __DEPLOY_VERSION__
     */
    protected const TASKS_MAP = [
        'workflow.automation' => [
            'langConstPrefix' => 'PLG_TASK_WORKFLOWTRANSITION_WORKFLOWAUTOMATION',
            'method'          => 'fireOverdueTransitions',
        ],
    ];

    /**
     * How many (item, rule) rows to consider in one run.
     *
     * @var integer
     * @since __DEPLOY_VERSION__
     */
    private const MAX_CANDIDATES_PER_RUN = 500;

    /**
     * How much of a failure reason is kept. Matches the width of
     * #__workflow_item_state.last_failure_reason.
     *
     * Cut when the message is built, not when it is written, so the next run compares against
     * exactly what was stored instead of sending the same email again.
     *
     * @var integer
     * @since __DEPLOY_VERSION__
     */
    private const MAX_FAILURE_REASON_LENGTH = 500;

    /**
     * Outcomes recorded in #__workflow_automation_log.exit_code.
     *
     * @var integer
     * @since __DEPLOY_VERSION__
     */
    private const EXIT_OK        = 0;
    private const EXIT_REFUSED   = 1;
    private const EXIT_EXCEPTION = 3;

    /**
     * The item moved on before the transition could fire.
     *
     * Not a fault, so nobody is notified: someone moved the item by hand while the run was going.
     *
     * @var integer
     * @since __DEPLOY_VERSION__
     */
    private const EXIT_NO_LONGER_APPLICABLE = 4;

    /**
     * Stands in for "never checked" when sorting. The earliest datetime MySQL accepts, so a
     * row that has never been considered always sorts ahead of one that has.
     *
     * @var string
     * @since __DEPLOY_VERSION__
     */
    private const NEVER_CHECKED = '1000-01-01 00:00:00';

    /**
     * @var boolean
     * @since __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Returns the events the plugin listens to.
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * Main task routine - called by the scheduler when the task is due.
     *
     * No locking is done here, because the scheduler never runs the same task twice at once.
     *
     * @param   ExecuteTaskEvent  $event  The scheduler event.
     *
     * @return  integer  A TaskStatus code (OK, or KNOCKOUT when a transition failed).
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function fireOverdueTransitions(ExecuteTaskEvent $event): int
    {
        $now         = Factory::getDate()->toSql();
        $nowDateTime = new \DateTime($now, new \DateTimeZone('UTC'));

        $this->reconcileItemStates($now);

        $candidates = $this->fetchCandidates();

        if (empty($candidates)) {
            return TaskStatus::OK;
        }

        // Stamped before anything is evaluated, not after, so a row whose condition throws
        // an exception still moves to the back of the queue instead of being re-picked every run.
        $this->markCandidatesChecked($candidates, $now);


        // Dropped after the stamp, never before, or a trashed item would hold its place at the front
        // of the least-recently-checked queue forever and crowd out live ones.
        $candidates = $this->withoutTrashedOrArchived($candidates);

        if ($candidates === []) {
            return TaskStatus::OK;
        }

        $candidatesByItem = [];

        foreach ($candidates as $candidate) {
            $candidatesByItem[$candidate->extension . '.' . $candidate->item_id][] = $candidate;
        }

        $conditionEvaluator = new ConditionEvaluator();
        $itemFieldResolver  = new ItemFieldResolver($this->getDatabase());

        $itemIdsByExtension = [];

        foreach ($candidates as $candidate) {
            $itemIdsByExtension[$candidate->extension][] = (int) $candidate->item_id;
        }

        foreach ($itemIdsByExtension as $extension => $itemIds) {
            $itemFieldResolver->preload($itemIds, $extension);
        }

        $app                = Factory::getApplication();
        $failures           = [];

        $failureReasonsByItemState = [];

        $recoveredItemStates = [];

        foreach ($candidatesByItem as $itemCandidates) {
            $evaluationFailure = null;

            // Every candidate for one item shares its item state row, so any of them can speak for it.
            $firstCandidate = $itemCandidates[0];

            $winningRule = $this->selectRuleForItem(
                $itemCandidates,
                $nowDateTime,
                $conditionEvaluator,
                $itemFieldResolver,
                $evaluationFailure
            );

            if ($evaluationFailure !== null) {
                $failureReasonsByItemState[$firstCandidate->item_state_id] = $evaluationFailure;

                // Only a reason not already stored earns an email, or a broken rule would mail the
                // administrator on every run. A fault that returns after a fix still counts as new.
                if ($evaluationFailure !== $firstCandidate->last_failure_reason) {
                    $failures[] = $this->notificationLine(
                        (int) $firstCandidate->item_id,
                        $evaluationFailure,
                        $this->itemEditLink($firstCandidate)
                    );
                }
            } elseif ($firstCandidate->last_failure_reason !== null) {
                $recoveredItemStates[] = $firstCandidate->item_state_id;
            }

            if ($winningRule !== null) {
                $this->fireRule($winningRule, $app, $failures, $failureReasonsByItemState);
            }
        }

        // Clear before recording: an item can read cleanly and still fail to fire, and the other
        // order would wipe the reason that fireRule() has just written.
        $this->clearEvaluationFailures($recoveredItemStates);
        $this->recordEvaluationFailures($failureReasonsByItemState, $now);

        if (!empty($failures)) {
            $summary = Text::plural('PLG_TASK_WORKFLOWTRANSITION_N_TRANSITIONS_FAILED', \count($failures))
                . "\n" . implode("\n", $failures);
            $this->logTask($summary, 'error');
            $this->snapshot['output_body'] = $summary;

            return TaskStatus::KNOCKOUT;
        }

        return TaskStatus::OK;
    }

    /**
     * Brings #__workflow_item_state back in line with core's #__workflow_associations.
     *
     * Core records an item's stage in its association row even while workflows are switched off, but
     * a state row is only written when the automation plugin sees the change happen. Items created
     * while workflows were off, moved by a batch stage change, imported straight into the database or
     * older than this feature would otherwise stay invisible to automation, or be judged by a stage
     * they have left. As in the upgrade backfill, entered_at is the time of the repair: nothing records
     * when the item really reached its stage, and an earlier guess would make it overdue at once.
     *
     * @param   string  $now  The run time, as SQL.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function reconcileItemStates(string $now): void
    {
        $db = $this->getDatabase();

        $untracked = $db->getQuery(true)
            ->select([
                $db->quoteName('wa.item_id'),
                $db->quoteName('wa.extension'),
                $db->quoteName('wa.stage_id'),
                $db->quote($now),
                $db->quote('manual'),
            ])
            ->from($db->quoteName('#__workflow_associations', 'wa'))
            ->join(
                'LEFT',
                $db->quoteName('#__workflow_item_state', 'wis'),
                $db->quoteName('wis.item_id') . ' = ' . $db->quoteName('wa.item_id')
                    . ' AND ' . $db->quoteName('wis.extension') . ' = ' . $db->quoteName('wa.extension')
            )
            ->where($db->quoteName('wis.id') . ' IS NULL');

        $columns = $db->quoteName(['item_id', 'extension', 'stage_id', 'entered_at', 'triggered_by']);

        $db->setQuery(
            'INSERT INTO ' . $db->quoteName('#__workflow_item_state') . ' (' . implode(', ', $columns) . ') ' . $untracked
        )->execute();

        $movedRows = $db->setQuery(
            $db->getQuery(true)
                ->select([$db->quoteName('wis.id'), $db->quoteName('wa.stage_id')])
                ->from($db->quoteName('#__workflow_item_state', 'wis'))
                ->join(
                    'INNER',
                    $db->quoteName('#__workflow_associations', 'wa'),
                    $db->quoteName('wa.item_id') . ' = ' . $db->quoteName('wis.item_id')
                        . ' AND ' . $db->quoteName('wa.extension') . ' = ' . $db->quoteName('wis.extension')
                )
                ->where($db->quoteName('wa.stage_id') . ' <> ' . $db->quoteName('wis.stage_id'))
        )->loadAssocList();

        $itemStateIdsByStage = [];

        foreach ($movedRows as $movedRow) {
            $itemStateIdsByStage[(int) $movedRow['stage_id']][] = (int) $movedRow['id'];
        }

        foreach ($itemStateIdsByStage as $stageId => $itemStateIds) {
            foreach (array_chunk($itemStateIds, self::MAX_CANDIDATES_PER_RUN) as $chunk) {
                $db->setQuery(
                    $db->getQuery(true)
                        ->update($db->quoteName('#__workflow_item_state'))
                        ->set($db->quoteName('stage_id') . ' = ' . $stageId)
                        ->set($db->quoteName('entered_at') . ' = ' . $db->quote($now))
                        ->set($db->quoteName('triggered_by') . ' = ' . $db->quote('manual'))
                        // The rest describes the stage the item has left, so it is cleared as on a transition.
                        ->set($db->quoteName('requires_intervention') . ' = 0')
                        ->set($db->quoteName('last_checked_at') . ' = NULL')
                        ->set($db->quoteName('last_failure_at') . ' = NULL')
                        ->set($db->quoteName('last_failure_reason') . ' = NULL')
                        ->whereIn($db->quoteName('id'), $chunk)
                )->execute();
            }
        }
    }

    /**
     * Fetches every (item, rule) pair this run could act on.
     *
     * Nothing here filters by time. Whether a rule is due is worked out live from entered_at,
     * because a stored deadline would go stale whenever a rule or an item changes.
     *
     * @return  DueAutomation[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fetchCandidates(): array
    {
        $db = $this->getDatabase();

        $alreadyRan = $db->getQuery(true)
            ->select('1')
            ->from($db->quoteName('#__workflow_automation_log', 'wal'))
            ->where($db->quoteName('wal.rule_id') . ' = ' . $db->quoteName('war.id'))
            ->where($db->quoteName('wal.item_id') . ' = ' . $db->quoteName('wis.item_id'))
            ->where($db->quoteName('wal.extension') . ' = ' . $db->quoteName('wis.extension'))
            ->where($db->quoteName('wal.exit_code') . ' = ' . self::EXIT_OK)
            ->where($db->quoteName('wal.executed_at') . ' >= ' . $db->quoteName('wis.entered_at'));

        $overduePairsQuery = $db->getQuery(true)
            ->select([
                $db->quoteName('war.id', 'rule_id'),
                $db->quoteName('war.transition_id'),
                $db->quoteName('wis.stage_id', 'from_stage_id'),
                $db->quoteName('wt.to_stage_id'),
                $db->quoteName('war.delay_value'),
                $db->quoteName('war.delay_unit'),
                $db->quoteName('war.rule_type'),
                $db->quoteName('war.cron_expression'),
                $db->quoteName('war.item_filter'),
                $db->quoteName('war.fire_condition'),
                $db->quoteName('wt.ordering'),
                $db->quoteName('wis.item_id'),
                $db->quoteName('wis.extension'),
                $db->quoteName('wis.id', 'item_state_id'),
                $db->quoteName('wis.entered_at'),
                $db->quoteName('war.run_as_user_id'),
                $db->quoteName('wis.last_failure_reason'),
            ])
            ->from($db->quoteName('#__workflow_item_state', 'wis'))
            ->join(
                'INNER',
                $db->quoteName('#__workflow_stages', 'ws'),
                $db->quoteName('ws.id') . ' = ' . $db->quoteName('wis.stage_id')
            )
            // A transition from any stage has from_stage_id -1 and applies to every item in its own
            // workflow, the same set core offers it to by hand.
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

            ->where($db->quoteName('wis.requires_intervention') . ' = 0')
            ->where($db->quoteName('w.published') . ' = 1')
            ->where($db->quoteName('wt.published') . ' = 1')
            ->where($db->quoteName('war.published') . ' = 1')

            // Each rule runs once per stay in a stage. Without this a transition that leaves the item where it
            // is, as one from any stage does once it has moved the item, would fire again every delay.
            ->where('NOT EXISTS (' . $alreadyRan . ')')

            // Least-recently-checked first, so rows that a filter keeps excluding cannot hold the front
            // of the queue forever. COALESCE because MySQL and PostgreSQL sort nulls differently.
            ->order(
                'COALESCE(' . $db->quoteName('wis.last_checked_at') . ', '
                    . $db->quote(self::NEVER_CHECKED) . ') ASC'
            )
            ->order($db->quoteName('wis.entered_at') . ' ASC')
            ->order($db->quoteName('wt.ordering') . ' ASC')
            ->setLimit(self::MAX_CANDIDATES_PER_RUN);

        $candidates = array_map(
            [DueAutomation::class, 'fromRow'],
            $db->setQuery($overduePairsQuery)->loadObjectList() ?: []
        );

        return $candidates;
    }

    /**
     * Drops candidates whose item an editor has trashed or archived.
     *
     * Done after the query, because each extension keeps its items in its own table.
     *
     * @param   DueAutomation[]  $candidates  Every candidate row the query returned.
     *
     * @return  DueAutomation[]
     *
     * @since   __DEPLOY_VERSION__
     */
    private function withoutTrashedOrArchived(array $candidates): array
    {
        if ($candidates === []) {
            return [];
        }

        $itemIdsByExtension = [];

        foreach ($candidates as $candidate) {
            $itemIdsByExtension[$candidate->extension][] = (int) $candidate->item_id;
        }

        $itemStorage = new ItemStorage($this->getDatabase());
        $excluded    = [];

        foreach ($itemIdsByExtension as $extension => $itemIds) {
            foreach ($itemStorage->trashedOrArchivedIds($itemIds, $extension) as $itemId) {
                // An item id is only unique within its own extension.
                $excluded[$extension . '.' . $itemId] = true;
            }
        }

        if ($excluded === []) {
            return $candidates;
        }

        return array_values(array_filter(
            $candidates,
            static fn (DueAutomation $candidate): bool
            => !isset($excluded[$candidate->extension . '.' . $candidate->item_id])
        ));
    }

    /**
     * Records the outcome of one automated transition attempt.
     *
     * @param   object   $item      The (item, rule) pair being processed.
     * @param   integer  $exitCode  One of the EXIT_* constants.
     * @param   string   $note      Optional reason, truncated to the column length.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function logAutomationRun(object $item, int $exitCode, string $note = ''): void
    {
        $db  = $this->getDatabase();
        $row = (object) [
            'rule_id'        => (int) $item->rule_id,
            'item_id'        => (int) $item->item_id,
            'extension'      => $item->extension,
            'transition_id'  => (int) $item->transition_id,
            'from_stage_id'  => (int) ($item->from_stage_id ?? 0),
            'to_stage_id'    => (int) ($item->to_stage_id ?? 0),
            'run_as_user_id' => (int) ($item->run_as_user_id ?? 0),
            'trigger_type'   => 'rule',
            'exit_code'      => $exitCode,
            'note'           => $note !== '' ? substr($note, 0, 500) : null,
            'executed_at'    => Factory::getDate()->toSql(),
        ];

        // A logging failure must not escape into fireRule()'s catch, which would report a transition
        // that fired perfectly well as failed and block the item from further retries.
        try {
            $db->insertObject('#__workflow_automation_log', $row);
        } catch (\Throwable $error) {
            $this->logTask(
                'Could not write the automation log row for item ' . (int) $item->item_id . ': ' . $error->getMessage(),
                'warning'
            );
        }
    }

    /**
     * Builds an absolute backend edit link for the item, for use in notification.
     *
     * @param object $item The overdue (item, rule) pair.
     *
     * @return string Absolute URL, or empty string if no link can be built.
     *
     * @since __DEPLOY_VERSION__
     */
    private function itemEditLink(object $item): string
    {
        $parts  = explode('.', (string) $item->extension);
        $option = $parts[0] ?? '';
        $type   = $parts[1] ?? '';

        if ($option === '' || $type === '') {
            return '';
        }

        $app  = Factory::getApplication();
        $base = (string) $app->get('live_site');

        // Not called under cron: with no request, Uri::root() emits a warning before it throws, so
        // catching the exception would still fill the log.
        if ($base === '' && !$app->isClient('cli')) {
            $base = Uri::root();
        }

        if ($base === '') {
            // Under cron a link needs $live_site in configuration.php; without it the report has none.
            return '';
        }

        return rtrim($base, '/') . '/administrator/index.php?option=' . $option
            . '&task=' . $type . '.edit&id=' . (int) $item->item_id;
    }

    /**
     * Builds one line of the run's failure report.
     *
     * The reason is not translated: it is stored and compared on the next run, so it must be the
     * same string whatever language the site runs in.
     *
     * @param   integer  $itemId  The content item id.
     * @param   string   $reason  Why the rule could not fire, as stored on the item.
     * @param   string   $link    Absolute edit link, or '' when none could be built.
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    private function notificationLine(int $itemId, string $reason, string $link): string
    {
        if ($link === '') {
            return Text::sprintf('PLG_TASK_WORKFLOWTRANSITION_FAILURE_LINE', $itemId, $reason);
        }

        return Text::sprintf('PLG_TASK_WORKFLOWTRANSITION_FAILURE_LINE_LINKED', $itemId, $reason, $link);
    }

    /**
     * Records that this run considered these item states, in one query.
     *
     * @param   DueAutomation[]  $candidates  Every candidate row this run fetched.
     * @param   string           $now         The run's timestamp, in SQL format.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function markCandidatesChecked(array $candidates, string $now): void
    {
        $itemStateIds = array_values(array_unique(
            array_map(static fn (DueAutomation $candidate): int => $candidate->item_state_id, $candidates)
        ));

        $db          = $this->getDatabase();
        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__workflow_item_state'))
            ->set($db->quoteName('last_checked_at') . ' = :now')
            ->whereIn($db->quoteName('id'), $itemStateIds)
            ->bind(':now', $now, ParameterType::STRING);

        $db->setQuery($updateQuery)->execute();
    }

    /**
     * Flags an item as needing manual intervention after its transition failed.
     *
     * @param integer $itemStateId The #__workflow_item_state row id.
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    private function markRequiresIntervention(int $itemStateId): void
    {
        $db          = $this->getDatabase();
        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__workflow_item_state'))
            ->set($db->quoteName('requires_intervention') . ' = 1')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':id', $itemStateId, ParameterType::INTEGER);

        $db->setQuery($updateQuery)->execute();
    }

    /**
     * Stores why these item states could not be evaluated, so the reason survives the run.
     *
     * Unlike requires_intervention, this does not stop retries, and it clears itself once the
     * rule is fixed. Written with one query per distinct reason rather than one per item.
     *
     * @param string[] $reasonsByItemStateId  Reason text, keyed by #__workflow_item_state id.
     * @param string $now The run's timestamp, in SQL format.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function recordEvaluationFailures(array $reasonsByItemStateId, string $now): void
    {
        if ($reasonsByItemStateId === []) {
            return;
        }

        $itemStateIdsByReason = [];

        foreach ($reasonsByItemStateId as $itemStateId => $reason) {
            $itemStateIdsByReason[$reason][] = (int) $itemStateId;
        }

        $db = $this->getDatabase();

        foreach ($itemStateIdsByReason as $reason => $itemStateIds) {
            // Numeric-looking reasons became integer keys above, and bind() needs a string.
            $reasonText = (string) $reason;

            $updateQuery = $db->getQuery(true)
                ->update($db->quoteName('#__workflow_item_state'))
                ->set($db->quoteName('last_failure_at') . ' = :now')
                ->set($db->quoteName('last_failure_reason') . ' = :reason')
                ->whereIn($db->quoteName('id'), $itemStateIds)
                ->bind(':now', $now)
                ->bind(':reason', $reasonText);

            $db->setQuery($updateQuery)->execute();
        }
    }

    /**
     * Removes the stored failure note from item states that evaluated cleanly this run.
     *
     * @param   integer[]  $itemStateIds  The #__workflow_item_state row ids to clear.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function clearEvaluationFailures(array $itemStateIds): void
    {
        if ($itemStateIds === []) {
            return;
        }

        $db          = $this->getDatabase();
        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__workflow_item_state'))
            ->set($db->quoteName('last_failure_at') . ' = NULL')
            ->set($db->quoteName('last_failure_reason') . ' = NULL')
            ->whereIn($db->quoteName('id'), $itemStateIds);

        $db->setQuery($updateQuery)->execute();
    }

    /**
     * Chooses which single rule, if any, should fire for one item this run.
     *
     * The rule that came due soonest wins, with the Transitions list ordering as the tiebreak.
     *
     * @param   object[]            $itemCandidates      Candidate rows for a single item.
     * @param   \DateTime           $nowDateTime         Current time (UTC).
     * @param   ConditionEvaluator  $conditionEvaluator  The expression evaluator.
     * @param   ItemFieldResolver   $itemFieldResolver   The field resolver factory.
     * @param   string|null         $evaluationFailure   Set to why the item could not be evaluated.
     *
     * @return  object|null  The winning candidate, or null if none should fire.
     *
     * @since   __DEPLOY_VERSION__
     */
    private function selectRuleForItem(
        array $itemCandidates,
        \DateTime $nowDateTime,
        ConditionEvaluator $conditionEvaluator,
        ItemFieldResolver $itemFieldResolver,
        ?string &$evaluationFailure
    ): ?object {
        $firstCandidate = $itemCandidates[0];
        $fieldResolver  = $itemFieldResolver->forItem((int) $firstCandidate->item_id, $firstCandidate->extension);

        // Deadlines are kept so that sorting does not run the cron parser once per comparison.
        $eligibleRules = [];

        foreach ($itemCandidates as $candidate) {
            $deadline = DeadlineCalculator::forRule($candidate->entered_at, $candidate);

            if ($deadline === null) {
                // A live rule with no computable deadline cannot be used: an expression the parser
                // rejects, an empty one, or a delay unit outside the four we store.
                $evaluationFailure ??= mb_substr(
                    'Rule ' . $candidate->rule_id . ' has no usable schedule.',
                    0,
                    self::MAX_FAILURE_REASON_LENGTH
                );

                continue;
            }

            if ($deadline > $nowDateTime) {
                continue;
            }

            try {
                if (!$conditionEvaluator->evaluate($candidate->item_filter, $fieldResolver)) {
                    continue;
                }

                if (!$conditionEvaluator->evaluate($candidate->fire_condition, $fieldResolver)) {
                    continue;
                }
            } catch (ConditionEvaluationException $invalidCondition) {
                $evaluationFailure ??= mb_substr(
                    'Rule ' . $candidate->rule_id . ' could not be evaluated: ' . $invalidCondition->getMessage(),
                    0,
                    self::MAX_FAILURE_REASON_LENGTH
                );

                continue;
            }
            $eligibleRules[] = ['rule' => $candidate, 'deadline' => $deadline];
        }

        if (!empty($eligibleRules)) {
            usort(
                $eligibleRules,
                static fn (array $a, array $b): int => [$a['deadline'], (int) $a['rule']->ordering, (int) $a['rule']->rule_id]
                    <=> [$b['deadline'], (int) $b['rule']->ordering, (int) $b['rule']->rule_id]
            );

            return $eligibleRules[0]['rule'];
        }

        return null;
    }

    /**
     * Records one rule failure in the run report, the automation log and the item's state row.
     *
     * @param   DueAutomation  $rule            The rule that could not fire.
     * @param   string[]       $failures        Collected failure messages (by reference).
     * @param   string[]       $failureReasons  Reasons keyed by item state row id (by reference).
     * @param   string         $note            What went wrong. Not translated, see notificationLine().
     * @param   integer        $exitCode        A #__workflow_automation_log exit code.
     * @param   boolean        $blockRetries    Whether to take the item out of the scheduler.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function recordRuleFailure(
        DueAutomation $rule,
        array &$failures,
        array &$failureReasons,
        string $note,
        int $exitCode = self::EXIT_REFUSED,
        bool $blockRetries = false
    ): void {
        $reason = mb_substr(
            'Rule ' . $rule->rule_id . ': ' . $note,
            0,
            self::MAX_FAILURE_REASON_LENGTH
        );

        $failureReasons[$rule->item_state_id] = $reason;

        if ($reason !== $rule->last_failure_reason) {
            $failures[] = $this->notificationLine((int) $rule->item_id, $reason, $this->itemEditLink($rule));

            $this->logAutomationRun($rule, $exitCode, $note);
        }

        if ($blockRetries) {
            $this->markRequiresIntervention($rule->item_state_id);
        }
    }

    /**
     * Executes the winning transition for an item, as its configured run-as user.
     *
     * @param   DueAutomation            $rule            The winning candidate row.
     * @param   CMSApplicationInterface  $app             The application, for the identity swap.
     * @param   string[]                 $failures        Collected failure messages (by reference).
     * @param   string[]                 $failureReasons  Reasons keyed by item state row id (by reference).
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    private function fireRule(
        DueAutomation $rule,
        CMSApplicationInterface $app,
        array &$failures,
        array &$failureReasons
    ): void {
        $transitionId = (int) $rule->transition_id;
        $runAsUserId  = $rule->run_as_user_id;
        $originalUser = $app->getIdentity();

        if ($runAsUserId <= 0) {
            $this->recordRuleFailure(
                $rule,
                $failures,
                $failureReasons,
                'This rule has no Run As user, so there is no identity to execute it with.'
            );

            return;
        }

        $runAsUser = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($runAsUserId);

        if ((int) $runAsUser->id !== $runAsUserId) {
            $this->recordRuleFailure(
                $rule,
                $failures,
                $failureReasons,
                'The Run As user (id ' . $runAsUserId . ') no longer exists.'
            );

            return;
        }

        try {
            $app->loadIdentity($runAsUser);

            $workflow = new Workflow($rule->extension);
            $outcome  = $workflow->attemptTransition([$rule->item_id], $transitionId, 'automation');

            if ($outcome === TransitionStatus::SUCCESS) {
                $this->logAutomationRun($rule, self::EXIT_OK);
            } elseif ($outcome === TransitionStatus::STAGE_MISMATCH) {
                // Reported by the same read that refused the transition, so an item moved by hand
                // mid-run cannot be mistaken for a fault and blocked from future retries.
                $this->logAutomationRun(
                    $rule,
                    self::EXIT_NO_LONGER_APPLICABLE,
                    'The item left this stage before the transition ran, so it no longer applies.'
                );
            } else {
                $this->recordRuleFailure(
                    $rule,
                    $failures,
                    $failureReasons,
                    match ($outcome) {
                        TransitionStatus::INVALID_TRANSITION => 'The transition no longer exists, or the Run As user may not execute it.',
                        TransitionStatus::STOPPED_BY_PLUGIN  => 'A plugin stopped this transition.',
                        TransitionStatus::UPDATE_FAILED      => 'The stage change could not be saved.',
                        default                              => 'The transition was refused.',
                    },
                    self::EXIT_REFUSED,
                    true
                );
            }
        } catch (\Throwable $error) {
            $this->recordRuleFailure($rule, $failures, $failureReasons, $error->getMessage(), self::EXIT_EXCEPTION, true);
        } finally {
            $app->loadIdentity($originalUser);
        }
    }
}
