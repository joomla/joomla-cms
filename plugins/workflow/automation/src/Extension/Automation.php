<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Workflow.Automation
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Workflow\Automation\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Workflow\Workflow;
use Joomla\CMS\Workflow\WorkflowServiceInterface;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Keeps #__workflow_item_state in step with what the rest of Joomla does to an item.
 *
 * @since  __DEPLOY_VERSION__
 */
final class Automation extends CMSPlugin implements SubscriberInterface
{
    use DatabaseAwareTrait;

    /**
     * @var boolean
     * @since __DEPLOY_VERSION__
     */
    protected $autoloadLanguage = true;

    /**
     * Returns the events this plugin listens to.
     *
     * @return array
     *
     * @since __DEPLOY_VERSION__
     */
    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            'onWorkflowAfterTransition' => 'logStageEntry',
            'onContentAfterSave'        => 'seedNewItemState',
            'onContentPrepareForm'      => 'injectUpcomingTransitionField',
        ];
    }

    /**
     * Adds the read-only "next automated transition" panel to a workflow-enabled item form.
     *
     * @param PrepareFormEvent $event The form event.
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public function injectUpcomingTransitionField(PrepareFormEvent $event): void
    {
        $form = $event->getForm();

        if (!$this->isWorkflowContentForm((string) $form->getName())) {
            return;
        }

        Form::addFormPath(JPATH_PLUGINS . '/workflow/automation/forms');

        if (!$form->loadFile('upcoming_transition')) {
            Log::add(
                'Workflow automation could not load its upcoming transition field into ' . $form->getName() . '.',
                Log::WARNING,
                'workflow'
            );
        }
    }

    /**
     * Returns the component behind an extension when it offers workflow and has it switched on.
     *
     * Unlike isWorkflowActive(), which always answers no from the CLI or the API, this works in
     * every application.
     *
     * @param string $extension The extension, e.g. com_content.article.
     *
     * @return WorkflowServiceInterface|null The component, or null when workflow does not apply.
     *
     * @since __DEPLOY_VERSION__
     */
    private function workflowComponent(string $extension): ?WorkflowServiceInterface
    {
        $parts = explode('.', $extension);

        if (\count($parts) < 2) {
            return null;
        }

        // bootComponent() throws for a component that is not installed.
        try {
            $component = Factory::getApplication()->bootComponent($parts[0]);
        } catch (\Throwable) {
            return null;
        }

        if (!$component instanceof WorkflowServiceInterface) {
            return null;
        }

        return ComponentHelper::getParams($parts[0])->get('workflow_enabled') ? $component : null;
    }

    /**
     * Whether a form belongs to a content item under an active workflow.
     *
     * @param string $extension The form name, e.g. com_content.article.
     *
     * @return boolean
     *
     * @since __DEPLOY_VERSION__
     */
    private function isWorkflowContentForm(string $extension): bool
    {
        $component = $this->workflowComponent($extension);

        return $component !== null && $component->isWorkflowActive($extension);
    }

    /**
     * Records the stage an item has just moved into.
     *
     * @param WorkflowTransitionEvent $event The transition event.
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public function logStageEntry(WorkflowTransitionEvent $event): void
    {
        $transition = $event->getTransition();

        // A public event, so a badly dispatched one must not cause a fatal error.
        if ($transition === null || !isset($transition->to_stage_id)) {
            return;
        }

        $extension     = $event->getExtension();
        $toStageId     = (int) $transition->to_stage_id;
        $now           = Factory::getDate()->toSql();
        $triggeredBy   = $event->getTriggeredBy();
        $pksInt        = array_map('intval', $event->getPks());

        if ($pksInt === []) {
            return;
        }

        $db = $this->getDatabase();

        $findExistingLogQuery = $db->getQuery(true)
            ->select($db->quoteName(['item_id', 'id']))
            ->from($db->quoteName('#__workflow_item_state'))
            ->whereIn($db->quoteName('item_id'), $pksInt)
            ->where($db->quoteName('extension') . ' = :extension')
            ->bind(':extension', $extension, ParameterType::STRING);

        $existingLogs = $db->setQuery($findExistingLogQuery)->loadAssocList('item_id', 'id') ?: [];

        $toInsert = [];
        $stateIds = [];

        foreach ($pksInt as $pk) {
            if (isset($existingLogs[$pk])) {
                $stateIds[] = (int) $existingLogs[$pk];
            } else {
                $toInsert[] = $pk;
            }
        }

        if ($stateIds !== []) {
            $updateStageLogQuery = $db->getQuery(true)
                ->update($db->quoteName('#__workflow_item_state'))
                ->set($db->quoteName('stage_id') . ' = :stageId')
                ->set($db->quoteName('entered_at') . ' = :enteredAt')
                ->set($db->quoteName('triggered_by') . ' = :triggeredBy')

                // Everything below describes the stage the item has just left. Clearing
                // last_checked_at also puts the item at the front of the scheduler queue.
                ->set($db->quoteName('requires_intervention') . ' = 0')
                ->set($db->quoteName('last_checked_at') . ' = NULL')
                ->set($db->quoteName('last_failure_at') . ' = NULL')
                ->set($db->quoteName('last_failure_reason') . ' = NULL')

                ->whereIn($db->quoteName('id'), $stateIds)
                ->bind(':stageId', $toStageId, ParameterType::INTEGER)
                ->bind(':enteredAt', $now, ParameterType::STRING)
                ->bind(':triggeredBy', $triggeredBy, ParameterType::STRING);

            $this->runBookkeepingQuery(
                $updateStageLogQuery,
                'the new stage for ' . \count($stateIds) . ' existing item(s) in ' . $extension
            );
        }

        if ($toInsert !== []) {
            $insertStageLogQuery = $db->getQuery(true)
                ->insert($db->quoteName('#__workflow_item_state'))
                ->columns($db->quoteName([
                    'item_id',
                    'extension',
                    'stage_id',
                    'entered_at',
                    'triggered_by',
                ]));

            foreach ($toInsert as $pk) {
                $insertStageLogQuery->values(
                    $db->quote($pk) . ', '
                        . $db->quote($extension) . ', '
                        . $db->quote($toStageId) . ', '
                        . $db->quote($now) . ', '
                        . $db->quote($triggeredBy)
                );
            }

            $this->runBookkeepingQuery(
                $insertStageLogQuery,
                'the first stage for ' . \count($toInsert) . ' new item(s) in ' . $extension
            );
        }
    }

    /**
     * Seeds the item state row for a brand new content item.
     *
     * Needed because onWorkflowAfterTransition does not fire when an item is created.
     *
     * @param AfterSaveEvent $event The save event.
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    public function seedNewItemState(AfterSaveEvent $event): void
    {
        if (!$event->getIsNew()) {
            return;
        }

        $extension = $event->getContext();

        // Not isWorkflowContentForm(): items are also created from the CLI and the web services API.
        if ($this->workflowComponent($extension) === null) {
            return;
        }

        $table  = $event->getItem();
        $itemId = (int) $table->id;

        $workflow = new Workflow($extension, $this->getApplication());
        $stageId  = (int) $workflow->getDefaultStageByCategory($table->catid ?? 0);

        if ($stageId <= 0) {
            Log::add(
                'Workflow automation found no default stage for ' . $extension . ' item ' . $itemId
                    . ', so it will not be considered for automated transitions.',
                Log::WARNING,
                'workflow'
            );

            return;
        }

        $db          = $this->getDatabase();
        $now         = Factory::getDate()->toSql();
        $insertQuery = $db->getQuery(true)
            ->insert($db->quoteName('#__workflow_item_state'))
            ->columns($db->quoteName([
                'item_id',
                'extension',
                'stage_id',
                'entered_at',
                'triggered_by',
            ]))
            ->values(
                $db->quote($itemId) . ', '
                    . $db->quote($extension) . ', '
                    . $db->quote($stageId) . ', '
                    . $db->quote($now) . ', '
                    . $db->quote('manual')
            );

        $this->runBookkeepingQuery($insertQuery, 'the starting stage for ' . $extension . ' item ' . $itemId);
    }

    /**
     * Runs a bookkeeping query, turning a failure into a log entry instead of an exception.
     *
     * These run on after-events, when the save or transition has already happened, so an exception
     * could not undo it. A missing row is written again when the item next changes stage.
     *
     * @param QueryInterface $query The bookkeeping query to run.
     * @param string $description What was being recorded, for the log line.
     *
     * @return void
     *
     * @since __DEPLOY_VERSION__
     */
    private function runBookkeepingQuery(QueryInterface $query, string $description): void
    {
        try {
            $this->getDatabase()->setQuery($query)->execute();
        } catch (\Throwable $error) {
            Log::add(
                'Workflow automation could not record ' . $description . ': ' . $error->getMessage(),
                Log::WARNING,
                'workflow'
            );
        }
    }
}
