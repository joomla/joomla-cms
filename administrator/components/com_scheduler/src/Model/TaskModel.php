<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Model;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Scheduler\Administrator\Helper\ExecRuleHelper;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
use Joomla\Component\Scheduler\Administrator\Table\TaskTable;
use Joomla\Component\Scheduler\Administrator\Task\TaskOption;
use Joomla\Database\ParameterType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * MVC Model to interact with the Scheduler DB.
 * Implements methods to add, remove, edit tasks.
 *
 * @since  4.1.0
 */
class TaskModel extends AdminModel
{
    /**
     * Maps logical states to their values in the DB
     * ? Do we end up using this?
     *
     * @var array
     * @since  4.1.0
     */
    protected const TASK_STATES = [
        'enabled'  => 1,
        'disabled' => 0,
        'trashed'  => -2,
    ];

    /**
     * The name of the  database table with task records.
     *
     * @var  string
     * @since 4.1.0
     */
    public const TASK_TABLE = '#__scheduler_tasks';

    /**
     * Prefix used with controller messages
     *
     * @var string
     * @since  4.1.0
     */
    protected $text_prefix = 'COM_SCHEDULER';

    /**
     * Type alias for content type
     *
     * @var string
     * @since  4.1.0
     */
    public $typeAlias = 'com_scheduler.task';

    /**
     * The Application object, for convenience
     *
     * @var AdministratorApplication $app
     * @since  4.1.0
     */
    protected $app;

    /**
     * The event to trigger before unlocking the data.
     *
     * @var    string
     * @since  4.1.0
     */
    protected $event_before_unlock = null;

    /**
     * The event to trigger after unlocking the data.
     *
     * @var    string
     * @since  4.1.0
     */
    protected $event_unlock = null;

    /**
     * TaskModel constructor. Needed just to set $app
     *
     * @param   array                  $config       An array of configuration options
     * @param   ?MVCFactoryInterface   $factory      The factory
     * @param   ?FormFactoryInterface  $formFactory  The form factory
     *
     * @since  4.1.0
     * @throws \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?FormFactoryInterface $formFactory = null)
    {
        $config['events_map'] = $config['events_map'] ?? [];

        $config['events_map'] = array_merge(
            [
                'save'     => 'task',
                'validate' => 'task',
                'unlock'   => 'task',
            ],
            $config['events_map']
        );

        if (isset($config['event_before_unlock'])) {
            $this->event_before_unlock = $config['event_before_unlock'];
        } elseif (empty($this->event_before_unlock)) {
            $this->event_before_unlock = 'onContentBeforeUnlock';
        }

        if (isset($config['event_unlock'])) {
            $this->event_unlock = $config['event_unlock'];
        } elseif (empty($this->event_unlock)) {
            $this->event_unlock = 'onContentUnlock';
        }

        $this->app = Factory::getApplication();

        parent::__construct($config, $factory, $formFactory);
    }

    /**
     * Fetches the form object associated with this model. By default,
     * loads the corresponding data from the DB and binds it with the form.
     *
     * @param   array  $data      Data that needs to go into the form
     * @param   bool   $loadData  Should the form load its data from the DB?
     *
     * @return Form|boolean  A Form object on success, false on failure.
     *
     * @since  4.1.0
     * @throws \Exception
     */
    public function getForm($data = [], $loadData = true)
    {
        Form::addFieldPath(JPATH_ADMINISTRATOR . 'components/com_scheduler/src/Field');

        /**
         *  loadForm() (defined by FormBehaviourTrait) also loads the form data by calling
         *  loadFormData() : $data [implemented here] and binds it to the form by calling
         *  $form->bind($data).
         */
        $form = $this->loadForm('com_scheduler.task', 'task', ['control' => 'jform', 'load_data' => $loadData]);

        if (empty($form)) {
            return false;
        }

        $user = $this->app->getIdentity();

        // If new entry, set task type from state
        if ($this->getState('task.id', 0) === 0 && $this->getState('task.type') !== null) {
            $form->setValue('type', null, $this->getState('task.type'));
        }

        // @todo : Check if this is working as expected for new items (id == 0)
        if (!$user->authorise('core.edit.state', 'com_scheduler.task.' . $this->getState('task.id'))) {
            // Disable fields
            $form->setFieldAttribute('state', 'disabled', 'true');

            // No "hacking" ._.
            $form->setFieldAttribute('state', 'filter', 'unset');
        }

        return $form;
    }

    /**
     * Determine whether a record may be deleted taking into consideration
     * the user's permissions over the record.
     *
     * @param   object  $record  The database row/record in question
     *
     * @return  boolean  True if the record may be deleted
     *
     * @since  4.1.0
     * @throws \Exception
     */
    protected function canDelete($record): bool
    {
        // Record doesn't exist, can't delete
        if (empty($record->id)) {
            return false;
        }

        return $this->app->getIdentity()->authorise('core.delete', 'com_scheduler.task.' . $record->id);
    }

    /**
     * Populate the model state, we use these instead of toying with input or the global state
     *
     * @return  void
     *
     * @since  4.1.0
     * @throws \Exception
     */
    protected function populateState(): void
    {
        $app = $this->app;

        $taskId   = $app->getInput()->getInt('id');
        $taskType = $app->getUserState('com_scheduler.add.task.task_type');

        // @todo: Remove this. Get the option through a helper call.
        $taskOption = $app->getUserState('com_scheduler.add.task.task_option');

        $this->setState('task.id', $taskId);
        $this->setState('task.type', $taskType);
        $this->setState('task.option', $taskOption);

        // Load component params, though com_scheduler does not (yet) have any params
        $cParams = ComponentHelper::getParams($this->option);
        $this->setState('params', $cParams);
    }

    /**
     * Don't need to define this method since the parent getTable()
     * implicitly deduces $name and $prefix anyways. This makes the object
     * more transparent though.
     *
     * @param   string  $name     Name of the table
     * @param   string  $prefix   Class prefix
     * @param   array   $options  Model config array
     *
     * @return Table
     *
     * @since  4.1.0
     * @throws \Exception
     */
    public function getTable($name = 'Task', $prefix = 'Table', $options = []): Table
    {
        return parent::getTable($name, $prefix, $options);
    }

    /**
     * Fetches the data to be injected into the form
     *
     * @return object  Associative array of form data.
     *
     * @since  4.1.0
     * @throws \Exception
     */
    protected function loadFormData()
    {
        $data = $this->app->getUserState('com_scheduler.edit.task.data', []);

        // If the data from UserState is empty, we fetch it with getItem()
        if (empty($data)) {
            /** @var CMSObject $data */
            $data = $this->getItem();

            // @todo : further data processing goes here

            // For a fresh object, set exec-day and exec-time
            if (!($data->id ?? 0)) {
                $data->execution_rules['exec-day']  = gmdate('d');
                $data->execution_rules['exec-time'] = gmdate('H:i');
            }

            if ($data->next_execution) {
                $data->next_execution = Factory::getDate($data->next_execution);
                $data->next_execution->setTimezone(new \DateTimeZone($this->app->get('offset', 'UTC')));
                $data->next_execution = $data->next_execution->toSql(true);
            }

            if ($data->last_execution) {
                $data->last_execution = Factory::getDate($data->last_execution);
                $data->last_execution->setTimezone(new \DateTimeZone($this->app->get('offset', 'UTC')));
                $data->last_execution = $data->last_execution->toSql(true);
            }
        }

        // Let plugins manipulate the data
        $this->preprocessData('com_scheduler.task', $data, 'task');

        return $data;
    }

    /**
     * Overloads the parent getItem() method.
     *
     * @param   integer  $pk  Primary key
     *
     * @return  object|boolean  Object on success, false on failure
     *
     * @since  4.1.0
     * @throws \Exception
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if (!\is_object($item)) {
            return false;
        }

        // Parent call leaves `execution_rules` and `cron_rules` JSON encoded
        $item->set('execution_rules', json_decode($item->get('execution_rules', '')));
        $item->set('cron_rules', json_decode($item->get('cron_rules', '')));

        $taskOption = SchedulerHelper::getTaskOptions()->findOption(
            ($item->id ?? 0) ? ($item->type ?? 0) : $this->getState('task.type')
        );

        $item->set('taskOption', $taskOption);

        return $item;
    }

    /**
     * Get a task from the database, only if an exclusive "lock" on the task can be acquired.
     * The method supports options to customise the limitations on the fetch.
     *
     * @param   array  $options  Array with options to fetch the task:
     *                           1. `id`: Optional id of the task to fetch.
     *                           2. `allowDisabled`: If true, disabled tasks can also be fetched.
     *                           (default: false)
     *                           3. `bypassScheduling`: If true, tasks that are not due can also be
     *                           fetched. Should only be true if an `id` is targeted instead of the
     *                           task queue. (default: false)
     *                           4. `allowConcurrent`: If true, fetches even when another task is
     *                           running ('locked'). (default: false)
     *                           5. `includeCliExclusive`: If true, can also fetch CLI exclusive tasks. (default: true)
     *
     * @return ?\stdClass  Task entry as in the database.
     *
     * @since   4.1.0
     * @throws UndefinedOptionsException|InvalidOptionsException
     * @throws \RuntimeException
     */
    public function getTask(array $options = []): ?\stdClass
    {
        $resolver = new OptionsResolver();

        try {
            static::configureTaskGetterOptions($resolver);
        } catch (\Exception $e) {
        }

        try {
            $options = $resolver->resolve($options);
        } catch (UndefinedOptionsException | InvalidOptionsException $e) {
            throw $e;
        }

        $db           = $this->getDatabase();
        $now          = Factory::getDate()->toSql();
        $affectedRows = 0;

        try {
            $db->lockTable(self::TASK_TABLE);

            if (!$options['allowConcurrent'] && $this->hasRunningTasks($db)) {
                return null;
            }

            $lockQuery = $this->buildLockQuery($db, $now, $options);

            if ($options['id'] > 0) {
                $lockQuery->where($db->quoteName('id') . ' = :taskId')
                    ->bind(':taskId', $options['id'], ParameterType::INTEGER);
            } else {
                $id = $this->getNextTaskId($db, $now, $options);
                if (\count($id) === 0) {
                    return null;
                }
                $lockQuery->where($db->quoteName('id') . ' = :taskId')
                    ->bind(':taskId', $id, ParameterType::INTEGER);
            }

            $db->setQuery($lockQuery)->execute();
            $affectedRows = $db->getAffectedRows();
        } catch (\RuntimeException $e) {
            return null;
        } finally {
            $db->unlockTables();
        }

        if ($affectedRows != 1) {
            return null;
        }

        return $this->fetchTask($db, $now);
    }

    /**
     * Checks if there are any running tasks in the database.
     *
     * @param \JDatabaseDriver $db The database driver to use.
     * @return bool True if there are running tasks, false otherwise.
     * @since 4.4.9
     */
    private function hasRunningTasks($db): bool
    {
        $lockCountQuery = $db->getQuery(true)
            ->select('COUNT(id)')
            ->from($db->quoteName(self::TASK_TABLE))
            ->where($db->quoteName('locked') . ' IS NOT NULL')
            ->where($db->quoteName('state') . ' = 1');

        try {
            $runningCount = $db->setQuery($lockCountQuery)->loadResult();
        } catch (\RuntimeException $e) {
            return false;
        }

        return $runningCount != 0;
    }

    /**
     * Builds a query to lock a task.
     *
     * @param Database $db The database object.
     * @param string $now The current time.
     * @param array $options The options for building the query.
     *                      - includeCliExclusive: Whether to include CLI exclusive tasks.
     *                      - bypassScheduling: Whether to bypass scheduling.
     *                      - allowDisabled: Whether to allow disabled tasks.
     *                      - id: The ID of the task.
     * @return Query The lock query.
     * @since 5.2.0
     */
    private function buildLockQuery($db, $now, $options)
    {
        $lockQuery = $db->getQuery(true)
            ->update($db->quoteName(self::TASK_TABLE))
            ->set($db->quoteName('locked') . ' = :now1')
            ->bind(':now1', $now);

        $activeRoutines = array_map(
            static function (TaskOption $taskOption): string {
                return $taskOption->id;
            },
            SchedulerHelper::getTaskOptions()->options
        );

        $lockQuery->whereIn($db->quoteName('type'), $activeRoutines, ParameterType::STRING);

        if (!$options['includeCliExclusive']) {
            $lockQuery->where($db->quoteName('cli_exclusive') . ' = 0');
        }

        if (!$options['bypassScheduling']) {
            $lockQuery->where($db->quoteName('next_execution') . ' <= :now2')
                ->bind(':now2', $now);
        }

        $stateCondition = $options['allowDisabled'] ? [0, 1] : [1];
        $lockQuery->whereIn($db->quoteName('state'), $stateCondition);

        return $lockQuery;
    }

    /**
     * Retrieves the ID of the next task based on the given criteria.
     *
     * @param \JDatabaseDriver $db The database object.
     * @param string $now The current time.
     * @param array $options The options for retrieving the next task.
     *                       - includeCliExclusive: Whether to include CLI exclusive tasks.
     *                       - bypassScheduling: Whether to bypass scheduling.
     *                       - allowDisabled: Whether to allow disabled tasks.
     * @return array The ID of the next task, or an empty array if no task is found.
     *
     * @since 5.2.0
     * @throws \RuntimeException If there is an error executing the query.
     */
    private function getNextTaskId($db, $now, $options)
    {
        $idQuery = $db->getQuery(true)
            ->from($db->quoteName(self::TASK_TABLE))
            ->select($db->quoteName('id'));

        $activeRoutines = array_map(
            static function (TaskOption $taskOption): string {
                return $taskOption->id;
            },
            SchedulerHelper::getTaskOptions()->options
        );

        $idQuery->whereIn($db->quoteName('type'), $activeRoutines, ParameterType::STRING);

        if (!$options['includeCliExclusive']) {
            $idQuery->where($db->quoteName('cli_exclusive') . ' = 0');
        }

        if (!$options['bypassScheduling']) {
            $idQuery->where($db->quoteName('next_execution') . ' <= :now2')
                ->bind(':now2', $now);
        }

        $stateCondition = $options['allowDisabled'] ? [0, 1] : [1];
        $idQuery->whereIn($db->quoteName('state'), $stateCondition);

        $idQuery->where($db->quoteName('next_execution') . ' IS NOT NULL')
            ->order($db->quoteName('priority') . ' DESC')
            ->order($db->quoteName('next_execution') . ' ASC')
            ->setLimit(1);

        try {
            return $db->setQuery($idQuery)->loadColumn();
        } catch (\RuntimeException $e) {
            return [];
        }
    }

    /**
     * Fetches a task from the database based on the current time.
     *
     * @param \JDatabaseDriver $db The database driver to use.
     * @param string $now The current time in the database's time format.
     * @return \stdClass|null The fetched task object, or null if no task was found.
     * @since 5.2.0
     * @throws \RuntimeException If there was an error executing the query.
     */
    private function fetchTask($db, $now): ?\stdClass
    {
        $getQuery = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName(self::TASK_TABLE))
            ->where($db->quoteName('locked') . ' = :now')
            ->bind(':now', $now);

        try {
            $task = $db->setQuery($getQuery)->loadObject();
        } catch (\RuntimeException $e) {
            return null;
        }

        $task->execution_rules = json_decode($task->execution_rules);
        $task->cron_rules      = json_decode($task->cron_rules);
        $task->taskOption      = SchedulerHelper::getTaskOptions()->findOption($task->type);

        return $task;
    }

    /**
     * Set up an {@see OptionsResolver} to resolve options compatible with the {@see GetTask()} method.
     *
     * @param   OptionsResolver  $resolver  The {@see OptionsResolver} instance to set up.
     *
     * @return OptionsResolver
     *
     * @since 4.1.0
     * @throws AccessException
     */
    public static function configureTaskGetterOptions(OptionsResolver $resolver): OptionsResolver
    {
        $resolver->setDefaults(
            [
                'id'                  => 0,
                'allowDisabled'       => false,
                'bypassScheduling'    => false,
                'allowConcurrent'     => false,
                'includeCliExclusive' => true,
            ]
        )
            ->setAllowedTypes('id', 'numeric')
            ->setAllowedTypes('allowDisabled', 'bool')
            ->setAllowedTypes('bypassScheduling', 'bool')
            ->setAllowedTypes('allowConcurrent', 'bool')
            ->setAllowedTypes('includeCliExclusive', 'bool');

        return $resolver;
    }

    /**
     * @param   array  $data  The form data
     *
     * @return  boolean  True on success, false on failure
     *
     * @since  4.1.0
     * @throws \Exception
     */
    public function save($data): bool
    {
        $id    = (int) ($data['id'] ?? $this->getState('task.id'));
        $isNew = $id === 0;

        // Clean up execution rules
        $data['execution_rules'] = $this->processExecutionRules($data['execution_rules']);

        // If a new entry, we'll have to put in place a pseudo-last_execution
        if ($isNew) {
            $basisDayOfMonth           = $data['execution_rules']['exec-day'];
            [$basisHour, $basisMinute] = explode(':', $data['execution_rules']['exec-time']);

            $data['last_execution'] = Factory::getDate('now', 'GMT')->format('Y-m')
                . "-$basisDayOfMonth $basisHour:$basisMinute:00";
        } else {
            $data['last_execution'] = $this->getItem($id)->last_execution;
        }

        // Build the `cron_rules` column from `execution_rules`
        $data['cron_rules'] = $this->buildExecutionRules($data['execution_rules']);

        // `next_execution` would be null if scheduling is disabled with the "manual" rule!
        $data['next_execution'] = (new ExecRuleHelper($data))->nextExec();

        if ($isNew) {
            $data['last_execution'] = null;
        }

        // If no params, we set as empty array.
        // ? Is this the right place to do this
        $data['params'] = $data['params'] ?? [];

        // Parent method takes care of saving to the table
        return parent::save($data);
    }

    /**
     * Clean up and standardise execution rules
     *
     * @param   array  $unprocessedRules  The form data [? can just replace with execution_interval]
     *
     * @return array  Processed rules
     *
     * @since  4.1.0
     */
    private function processExecutionRules(array $unprocessedRules): array
    {
        $executionRules = $unprocessedRules;

        $ruleType       = $executionRules['rule-type'];
        $retainKeys     = ['rule-type', $ruleType, 'exec-day', 'exec-time'];
        $executionRules = array_intersect_key($executionRules, array_flip($retainKeys));

        // Default to current date-time in UTC/GMT as the basis
        $executionRules['exec-day']  = $executionRules['exec-day'] ?: (string) gmdate('d');
        $executionRules['exec-time'] = $executionRules['exec-time'] ?: (string) gmdate('H:i');

        // If custom ruleset, sort it
        // ? Is this necessary
        if ($ruleType === 'cron-expression') {
            foreach ($executionRules['cron-expression'] as &$values) {
                sort($values);
            }
        }

        return $executionRules;
    }

    /**
     * Private method to build execution expression from input execution rules.
     * This expression is used internally to determine execution times/conditions.
     *
     * @param   array  $executionRules  Execution rules from the Task form, post-processing.
     *
     * @return array
     *
     * @since  4.1.0
     * @throws \Exception
     */
    private function buildExecutionRules(array $executionRules): array
    {
        // Maps interval strings, use with \sprintf($map[intType], $interval)
        $intervalStringMap = [
            'minutes' => 'PT%dM',
            'hours'   => 'PT%dH',
            'days'    => 'P%dD',
            'months'  => 'P%dM',
            'years'   => 'P%dY',
        ];

        $ruleType        = $executionRules['rule-type'];
        $ruleClass       = strpos($ruleType, 'interval') === 0 ? 'interval' : $ruleType;
        $buildExpression = '';

        if ($ruleClass === 'interval') {
            // Rule type for intervals interval-<minute/hours/...>
            $intervalType    = explode('-', $ruleType)[1];
            $interval        = $executionRules["interval-$intervalType"];
            $buildExpression = \sprintf($intervalStringMap[$intervalType], $interval);
        }

        if ($ruleClass === 'cron-expression') {
            // ! custom matches are disabled in the form
            $matches         = $executionRules['cron-expression'];
            $buildExpression .= $this->wildcardIfMatch($matches['minutes'], range(0, 59), true);
            $buildExpression .= ' ' . $this->wildcardIfMatch($matches['hours'], range(0, 23), true);
            $buildExpression .= ' ' . $this->wildcardIfMatch($matches['days_month'], range(1, 31), true);
            $buildExpression .= ' ' . $this->wildcardIfMatch($matches['months'], range(1, 12), true);
            $buildExpression .= ' ' . $this->wildcardIfMatch($matches['days_week'], range(0, 6), true);
        }

        return [
            'type' => $ruleClass,
            'exp'  => $buildExpression,
        ];
    }

    /**
     * This method releases "locks" on a set of tasks from the database.
     * These locks are pseudo-locks that are used to keep a track of running tasks. However, they require require manual
     * intervention to release these locks in cases such as when a task process crashes, leaving the task "locked".
     *
     * @param   array  $pks  A list of the primary keys to unlock.
     *
     * @return  boolean  True on success.
     *
     * @since   4.1.0
     * @throws \RuntimeException|\UnexpectedValueException|\BadMethodCallException
     */
    public function unlock(array &$pks): bool
    {
        /** @var TaskTable $table */
        $table = $this->getTable();

        $user = $this->getCurrentUser();

        $context = $this->option . '.' . $this->name;

        // Include the plugins for the change of state event.
        PluginHelper::importPlugin($this->events_map['unlock']);

        // Access checks.
        foreach ($pks as $i => $pk) {
            $table->reset();

            if ($table->load($pk)) {
                if (!$this->canEditState($table)) {
                    // Prune items that you can't change.
                    unset($pks[$i]);
                    Log::add(Text::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), Log::WARNING, 'jerror');

                    return false;
                }

                // Prune items that are already at the given state.
                $lockedColumnName = $table->getColumnAlias('locked');

                if (property_exists($table, $lockedColumnName) && \is_null($table->$lockedColumnName)) {
                    unset($pks[$i]);
                }
            }
        }

        // Check if there are items to change.
        if (!\count($pks)) {
            return true;
        }

        $event = AbstractEvent::create(
            $this->event_before_unlock,
            [
                'subject' => $this,
                'context' => $context,
                'pks'     => $pks,
            ]
        );

        try {
            Factory::getApplication()->getDispatcher()->dispatch($this->event_before_unlock, $event);
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Attempt to unlock the records.
        if (!$table->unlock($pks, $user->id)) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the after unlock event
        $event = AbstractEvent::create(
            $this->event_unlock,
            [
                'subject' => $this,
                'context' => $context,
                'pks'     => $pks,
            ]
        );

        try {
            Factory::getApplication()->getDispatcher()->dispatch($this->event_unlock, $event);
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Clear the component's cache
        $this->cleanCache();

        return true;
    }

    /**
     * Determine if an array is populated by all its possible values by comparison to a reference array, if found a
     * match a wildcard '*' is returned.
     *
     * @param   array  $target       The target array
     * @param   array  $reference    The reference array, populated by the complete set of possible values in $target
     * @param   bool   $targetToInt  If true, converts $target array values to integers before comparing
     *
     * @return string  A wildcard string if $target is fully populated, else $target itself.
     *
     * @since  4.1.0
     */
    private function wildcardIfMatch(array $target, array $reference, bool $targetToInt = false): string
    {
        if ($targetToInt) {
            $target = array_map(
                static function (string $x): int {
                    return (int) $x;
                },
                $target
            );
        }

        $isMatch = array_diff($reference, $target) === [];

        return $isMatch ? "*" : implode(',', $target);
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   4.1.0
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content'): void
    {
        // Load the 'task' plugin group
        PluginHelper::importPlugin('task');

        // Let the parent method take over
        parent::preprocessForm($form, $data, $group);
    }
}
