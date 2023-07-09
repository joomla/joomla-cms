<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Workflow;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Category;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Workflow Class.
 *
 * @since  4.0.0
 */
class Workflow
{
    /**
     * The booted component
     *
     * @var ComponentInterface
     */
    protected $component = null;

    /**
     * Name of the extension the workflow belong to
     *
     * @var    string
     * @since  4.0.0
     */
    protected $extension = null;

    /**
     * Application Object
     *
     * @var    CMSApplication
     * @since  4.0.0
     */
    protected $app;

    /**
     * Database Driver
     *
     * @var    DatabaseDriver
     * @since  4.0.0
     */
    protected $db;

    /**
     * Condition to names mapping
     *
     * @since  4.0.0
     */
    public const CONDITION_NAMES = [
        self::CONDITION_PUBLISHED   => 'JPUBLISHED',
        self::CONDITION_UNPUBLISHED => 'JUNPUBLISHED',
        self::CONDITION_TRASHED     => 'JTRASHED',
        self::CONDITION_ARCHIVED    => 'JARCHIVED',
    ];

    /**
     * Every item with a state which has the condition PUBLISHED is visible/active on the page
     */
    public const CONDITION_PUBLISHED = 1;

    /**
     * Every item with a state which has the condition UNPUBLISHED is not visible/inactive on the page
     */
    public const CONDITION_UNPUBLISHED = 0;

    /**
     * Every item with a state which has the condition TRASHED is trashed
     */
    public const CONDITION_TRASHED = -2;

    /**
     * Every item with a state which has the condition ARCHIVED is archived
     */
    public const CONDITION_ARCHIVED = 2;

    /**
     * Class constructor
     *
     * @param   string           $extension  The extension name
     * @param   ?CMSApplication  $app        Application Object
     * @param   ?DatabaseDriver  $db         Database Driver Object
     *
     * @since   4.0.0
     */
    public function __construct(string $extension, ?CMSApplication $app = null, ?DatabaseDriver $db = null)
    {
        $this->extension = $extension;

        // Initialise default objects if none have been provided
        if ($app === null) {
            @trigger_error('In 6.0 is the app dependency mandatory.', E_USER_DEPRECATED);
            $app = Factory::getApplication();
        }

        $this->app = $app;

        if ($db === null) {
            @trigger_error('In 6.0 is the database dependency mandatory.', E_USER_DEPRECATED);
            $db = Factory::getContainer()->get(DatabaseDriver::class);
        }

        $this->db = $db;
    }

    /**
     * Returns the translated condition name, based on the given number
     *
     * @param   integer  $value  The condition ID
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public function getConditionName(int $value): string
    {
        $component = $this->getComponent();

        if ($component instanceof WorkflowServiceInterface) {
            $conditions = $component->getConditions($this->extension);
        } else {
            $conditions = self::CONDITION_NAMES;
        }

        return ArrayHelper::getValue($conditions, $value, '', 'string');
    }

    /**
     * Returns the booted component
     *
     * @return ComponentInterface
     *
     * @since   4.0.0
     */
    protected function getComponent()
    {
        if (\is_null($this->component)) {
            $parts = explode('.', $this->extension);

            $this->component = $this->app->bootComponent($parts[0]);
        }

        return $this->component;
    }

    /**
     * Try to load a workflow default stage by category ID.
     *
     * @param   integer   $catId  The category ID.
     *
     * @return  boolean|integer  An integer, holding the stage ID or false
     * @since   4.0.0
     */
    public function getDefaultStageByCategory($catId = 0)
    {
        // Let's check if a workflow ID is assigned to a category
        $category = new Category($this->db);

        $categories = array_reverse($category->getPath($catId));

        $workflow_id = 0;

        foreach ($categories as $cat) {
            $cat->params = new Registry($cat->params);

            $workflow_id = $cat->params->get('workflow_id');

            if ($workflow_id == 'inherit') {
                $workflow_id = 0;
            } elseif ($workflow_id == 'use_default') {
                $workflow_id = 0;

                break;
            } elseif ($workflow_id > 0) {
                break;
            }
        }

        // Check if the workflow exists
        if ($workflow_id = (int) $workflow_id) {
            $query = $this->db->getQuery(true);

            $query->select(
                [
                    $this->db->quoteName('ws.id'),
                ]
            )
                ->from(
                    [
                        $this->db->quoteName('#__workflow_stages', 'ws'),
                        $this->db->quoteName('#__workflows', 'w'),
                    ]
                )
                ->where(
                    [
                        $this->db->quoteName('ws.workflow_id') . ' = ' . $this->db->quoteName('w.id'),
                        $this->db->quoteName('ws.default') . ' = 1',
                        $this->db->quoteName('w.published') . ' = 1',
                        $this->db->quoteName('ws.published') . ' = 1',
                        $this->db->quoteName('w.id') . ' = :workflowId',
                        $this->db->quoteName('w.extension') . ' = :extension',
                    ]
                )
                ->bind(':workflowId', $workflow_id, ParameterType::INTEGER)
                ->bind(':extension', $this->extension);

            $stage_id = (int) $this->db->setQuery($query)->loadResult();

            if (!empty($stage_id)) {
                return $stage_id;
            }
        }

        // Use default workflow
        $query  = $this->db->getQuery(true);

        $query->select(
            [
                $this->db->quoteName('ws.id'),
            ]
        )
            ->from(
                [
                    $this->db->quoteName('#__workflow_stages', 'ws'),
                    $this->db->quoteName('#__workflows', 'w'),
                ]
            )
            ->where(
                [
                    $this->db->quoteName('ws.default') . ' = 1',
                    $this->db->quoteName('ws.workflow_id') . ' = ' . $this->db->quoteName('w.id'),
                    $this->db->quoteName('w.published') . ' = 1',
                    $this->db->quoteName('ws.published') . ' = 1',
                    $this->db->quoteName('w.default') . ' = 1',
                    $this->db->quoteName('w.extension') . ' = :extension',
                ]
            )
            ->bind(':extension', $this->extension);

        $stage_id = (int) $this->db->setQuery($query)->loadResult();

        // Last check if we have a workflow ID
        if (!empty($stage_id)) {
            return $stage_id;
        }

        return false;
    }

    /**
     * Check if a transition can be executed
     *
     * @param   integer[]  $pks           The item IDs, which should use the transition
     * @param   integer    $transitionId  The transition which should be executed
     *
     * @return  object | null
     */
    public function getValidTransition(array $pks, int $transitionId)
    {
        $pks = ArrayHelper::toInteger($pks);
        $pks = array_filter($pks);

        if (!\count($pks)) {
            return null;
        }

        $query = $this->db->getQuery(true);

        $user = $this->app->getIdentity();

        $query->select(
            [
                $this->db->quoteName('t.id'),
                $this->db->quoteName('t.to_stage_id'),
                $this->db->quoteName('t.from_stage_id'),
                $this->db->quoteName('t.options'),
                $this->db->quoteName('t.workflow_id'),
            ]
        )
            ->from(
                [
                    $this->db->quoteName('#__workflow_transitions', 't'),
                ]
            )
            ->join('INNER', $this->db->quoteName('#__workflows', 'w'))
            ->join(
                'LEFT',
                $this->db->quoteName('#__workflow_stages', 's'),
                $this->db->quoteName('s.id') . ' = ' . $this->db->quoteName('t.to_stage_id')
            )
            ->where(
                [
                    $this->db->quoteName('t.id') . ' = :id',
                    $this->db->quoteName('t.workflow_id') . ' = ' . $this->db->quoteName('w.id'),
                    $this->db->quoteName('t.published') . ' = 1',
                    $this->db->quoteName('w.extension') . ' = :extension',
                ]
            )
            ->bind(':id', $transitionId, ParameterType::INTEGER)
            ->bind(':extension', $this->extension);

        $transition = $this->db->setQuery($query)->loadObject();

        $parts  = explode('.', $this->extension);
        $option = reset($parts);

        if (!empty($transition->id) && $user->authorise('core.execute.transition', $option . '.transition.' . (int) $transition->id)) {
            return $transition;
        }

        return null;
    }

    /**
     * Executes a transition to change the current state in the association table
     *
     * @param   integer[]  $pks           The item IDs, which should use the transition
     * @param   integer    $transitionId  The transition which should be executed
     *
     * @return  boolean
     */
    public function executeTransition(array $pks, int $transitionId): bool
    {
        $pks = ArrayHelper::toInteger($pks);
        $pks = array_filter($pks);

        if (!\count($pks)) {
            return true;
        }

        $transition = $this->getValidTransition($pks, $transitionId);

        if (is_null($transition)) {
            return false;
        }

        $transition->options = new Registry($transition->options);

        // Check if the items can execute this transition
        foreach ($pks as $pk) {
            $assoc = $this->getAssociation($pk);

            // The transition has to be in the same workflow
            if (
                !\in_array($transition->from_stage_id, [
                    $assoc->stage_id,
                    -1,
                ]) || $transition->workflow_id !== $assoc->workflow_id
            ) {
                return false;
            }
        }

        PluginHelper::importPlugin('workflow');

        $eventResult = $this->app->getDispatcher()->dispatch(
            'onWorkflowBeforeTransition',
            AbstractEvent::create(
                'onWorkflowBeforeTransition',
                [
                    'eventClass'     => 'Joomla\CMS\Event\Workflow\WorkflowTransitionEvent',
                    'subject'        => $this,
                    'extension'      => $this->extension,
                    'pks'            => $pks,
                    'transition'     => $transition,
                    'stopTransition' => false,
                ]
            )
        );

        if ($eventResult->getArgument('stopTransition')) {
            return false;
        }

        $success = $this->updateAssociations($pks, (int) $transition->to_stage_id);

        if ($success) {
            $this->app->getDispatcher()->dispatch(
                'onWorkflowAfterTransition',
                AbstractEvent::create(
                    'onWorkflowAfterTransition',
                    [
                        'eventClass' => 'Joomla\CMS\Event\Workflow\WorkflowTransitionEvent',
                        'subject'    => $this,
                        'extension'  => $this->extension,
                        'pks'        => $pks,
                        'transition' => $transition,
                    ]
                )
            );
        }

        return $success;
    }

    /**
     * Creates an association for the workflow_associations table
     *
     * @param   integer  $pk     ID of the item
     * @param   integer  $state  ID of state
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    public function createAssociation(int $pk, int $state): bool
    {
        try {
            $query = $this->db->getQuery(true);

            $query->insert($this->db->quoteName('#__workflow_associations'))
                ->columns(
                    [
                        $this->db->quoteName('item_id'),
                        $this->db->quoteName('stage_id'),
                        $this->db->quoteName('extension'),
                    ]
                )
                ->values(':pk, :state, :extension')
                ->bind(':pk', $pk, ParameterType::INTEGER)
                ->bind(':state', $state, ParameterType::INTEGER)
                ->bind(':extension', $this->extension);

            $this->db->setQuery($query)->execute();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Update an existing association with a new state
     *
     * @param   array    $pks    An Array of item IDs which should be changed
     * @param   integer  $state  The new state ID
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    public function updateAssociations(array $pks, int $state): bool
    {
        $pks = ArrayHelper::toInteger($pks);

        try {
            $query = $this->db->getQuery(true);

            $query->update($this->db->quoteName('#__workflow_associations'))
                ->set($this->db->quoteName('stage_id') . ' = :state')
                ->whereIn($this->db->quoteName('item_id'), $pks)
                ->where($this->db->quoteName('extension') . ' = :extension')
                ->bind(':state', $state, ParameterType::INTEGER)
                ->bind(':extension', $this->extension);

            $this->db->setQuery($query)->execute();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Removes associations from the workflow_associations table
     *
     * @param   integer[]  $pks  ID of content
     *
     * @return  boolean
     *
     * @since  4.0.0
     */
    public function deleteAssociation(array $pks): bool
    {
        $pks = ArrayHelper::toInteger($pks);

        try {
            $query = $this->db->getQuery(true);

            $query
                ->delete($this->db->quoteName('#__workflow_associations'))
                ->whereIn($this->db->quoteName('item_id'), $pks)
                ->where($this->db->quoteName('extension') . ' = :extension')
                ->bind(':extension', $this->extension);

            $this->db->setQuery($query)->execute();
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Loads an existing association item with state and item ID
     *
     * @param   integer  $itemId  The item ID to load
     *
     * @return  \stdClass|null
     *
     * @since  4.0.0
     */
    public function getAssociation(int $itemId): ?\stdClass
    {
        $query = $this->db->getQuery(true);

        $query->select(
            [
                $this->db->quoteName('a.item_id'),
                $this->db->quoteName('a.stage_id'),
                $this->db->quoteName('s.workflow_id'),
            ]
        )
            ->from($this->db->quoteName('#__workflow_associations', 'a'))
            ->innerJoin(
                $this->db->quoteName('#__workflow_stages', 's'),
                $this->db->quoteName('a.stage_id') . ' = ' . $this->db->quoteName('s.id')
            )
            ->where(
                [
                    $this->db->quoteName('item_id') . ' = :id',
                    $this->db->quoteName('extension') . ' = :extension',
                ]
            )
            ->bind(':id', $itemId, ParameterType::INTEGER)
            ->bind(':extension', $this->extension);

        return $this->db->setQuery($query)->loadObject();
    }
}
