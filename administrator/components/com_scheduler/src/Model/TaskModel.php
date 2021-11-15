<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Model;

// Restrict direct access
\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Component\Scheduler\Administrator\Helper\ExecRuleHelper;
use Joomla\Component\Scheduler\Administrator\Helper\SchedulerHelper;
use Joomla\Database\ParameterType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * MVC Model to interact with the Scheduler DB.
 * Implements methods to add, remove, edit tasks.
 *
 * @since  __DEPLOY_VERSION__
 */
class TaskModel extends AdminModel
{
	/**
	 * Maps logical states to their values in the DB
	 * ? Do we end up using this?
	 *
	 * @var array
	 * @since  __DEPLOY_VERSION__
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
	 * @since __DEPLOY_VERSION__
	 */
	public const TASK_TABLE = '#__scheduler_tasks';

	/**
	 * Prefix used with controller messages
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $text_prefix = 'COM_SCHEDULER';

	/**
	 * Type alias for content type
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_scheduler.task';

	/**
	 * The Application object, for convenience
	 *
	 * @var AdministratorApplication $app
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;


	/**
	 * TaskModel constructor. Needed just to set $app
	 *
	 * @param   array                      $config       An array of configuration options
	 * @param   MVCFactoryInterface|null   $factory      The factory [?]
	 * @param   FormFactoryInterface|null  $formFactory  The form factory [?]
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		$config['events_map'] = $config['events_map'] ?? [];
		$config['events_map'] = array_merge(
			[
				'save'     => 'task',
				'validate' => 'task',
			],
			$config['events_map']
		);

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
	 * @return Form|boolean  A JForm object on success, false on failure.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	public function getForm($data = array(), $loadData = true)
	{
		Form::addFieldPath(JPATH_ADMINISTRATOR . 'components/com_scheduler/src/Field');

		/**
		 *  loadForm() (defined by FormBehaviourTrait) also loads the form data by calling
		 *  loadFormData() : $data [implemented here] and binds it to the form by calling
		 *  $form->bind($data).
		 */
		$form = $this->loadForm('com_scheduler.task', 'task', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		$user = $this->app->getIdentity();

		// If new entry, set task type from state
		if ($this->getState('task.id', 0) === 0 && $this->getState('task.type') !== null)
		{
			$form->setValue('type', null, $this->getState('task.type'));
		}

		// @todo : Check if this is working as expected for new items (id == 0)
		if (!$user->authorise('core.edit.state', 'com_scheduler.task.' . $this->getState('task.id')))
		{
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
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	protected function canDelete($record): bool
	{
		// Record doesn't exist, can't delete
		if (empty($record->id))
		{
			return false;
		}

		return $this->app->getIdentity()->authorise('core.delete', 'com_scheduler.task.' . $record->id);
	}

	/**
	 * Populate the model state, we use these instead of toying with input or the global state
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
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
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	public function getTable($name = 'Task', $prefix = 'Table', $options = array()): Table
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Fetches the data to be injected into the form
	 *
	 * @return object  Associative array of form data.
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	protected function loadFormData()
	{
		$data = $this->app->getUserState('com_scheduler.edit.task.data', array());

		// If the data from UserState is empty, we fetch it with getItem()
		if (empty($data))
		{
			/** @var CMSObject $data */
			$data = $this->getItem();

			// @todo : further data processing goes here

			// For a fresh object, set exec-day and exec-time
			if (!($data->id ?? 0))
			{
				$data->execution_rules['exec-day']  = gmdate('d');
				$data->execution_rules['exec-time'] = gmdate('H:i');
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
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if (!is_object($item))
		{
			return false;
		}

		// Parent call leaves `execution_rules` and `cron_rules` JSON encoded
		$item->set('execution_rules', json_decode($item->get('execution_rules')));
		$item->set('cron_rules', json_decode($item->get('cron_rules')));

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
	 *
	 * @return ?\stdClass  Task entry as in the database.
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws UndefinedOptionsException|InvalidOptionsException
	 * @throws \RuntimeException
	 */
	public function getTask(array $options = []): ?\stdClass
	{
		$resolver = new OptionsResolver;

		try
		{
			$this->configureTaskGetterOptions($resolver);
		}
		catch (\Exception $e)
		{
		}

		try
		{
			$options = $resolver->resolve($options);
		}
		catch (\Exception $e)
		{
			if ($e instanceof UndefinedOptionsException || $e instanceof InvalidOptionsException)
			{
				throw $e;
			}
		}

		$db  = $this->getDbo();
		$now = Factory::getDate()->toSql();

		// Get lock on the table to help with concurrency issues
		$db->lockTable(self::TASK_TABLE);

		// If concurrency is not allowed, we only get a task if another one does not have a "lock"
		if (!$options['allowConcurrent'])
		{
			// Get count of locked (presumed running) tasks
			$lockCountQuery = $db->getQuery(true)
				->from($db->quoteName(self::TASK_TABLE))
				->select('COUNT(id)')
				->where($db->quoteName('locked') . ' IS NOT NULL');

			try
			{
				$runningCount = $db->setQuery($lockCountQuery)->loadResult();
			}
			catch (\RuntimeException $e)
			{
				return null;
			}

			if ($runningCount !== 0)
			{
				return null;
			}
		}

		$lockQuery = $db->getQuery(true);

		$lockQuery->update($db->quoteName(self::TASK_TABLE))
			->set($db->quoteName('locked') . ' = :now1')
			->bind(':now1', $now);

		if (!$options['bypassScheduling'])
		{
			$lockQuery->where($db->quoteName('next_execution') . ' <= :now2')
				->bind(':now2', $now);
		}

		if ($options['allowDisabled'])
		{
			$lockQuery->whereIn($db->quoteName('state'), [0, 1]);
		}
		else
		{
			$lockQuery->where($db->quoteName('state') . ' = 1');
		}

		if ($options['id'] > 0)
		{
			$lockQuery->where($db->quoteName('id') . ' = :taskId')
				->bind(':taskId', $options['id'], ParameterType::INTEGER);
		}
		// Pick from the front of the task queue if no 'id' is specified
		else
		{
			// Get the id of the next task in the task queue
			$idQuery = $db->getQuery(true)
				->from($db->quoteName(self::TASK_TABLE))
				->select($db->quoteName('id'))
				->where($db->quoteName('state') . ' = 1')
				->order($db->quoteName('priority') . ' DESC')
				->order($db->quoteName('next_execution') . ' ASC')
				->setLimit(1);

			try
			{
				$ids = $db->setQuery($idQuery)->loadColumn();
			}
			catch (\RuntimeException $e)
			{
				return null;
			}

			$lockQuery->whereIn($db->quoteName('id'), $ids);
		}

		try
		{
			// @todo Remove -- Dbg
			$queryStr = $lockQuery->__toString();
			$db->setQuery($lockQuery)->execute();
		}
		catch (\RuntimeException $e)
		{
		}
		finally
		{
			$db->unlockTables();
		}

		if ($db->getAffectedRows() != 1)
		{
			/*
			 // @todo
			// ? Fatal failure handling here?
			// ! Question is, how? If we check for tasks running beyond there time here, we have no way of
			//  ! what's already been notified (since we're not auto-unlocking/recovering tasks anymore).
			// The solution __may__ be in a "last_successful_finish" (or something) column.
			*/

			return null;
		}

		$getQuery = $db->getQuery(true);

		$getQuery->select('*')
			->from($db->quoteName(self::TASK_TABLE))
			->where($db->quoteName('locked') . ' = :now')
			->bind(':now', $now);

		$task = $db->setQuery($getQuery)->loadObject();

		$task->execution_rules = json_decode($task->execution_rules);
		$task->cron_rules      = json_decode($task->cron_rules);

		$task->taskOption = SchedulerHelper::getTaskOptions()->findOption($task->type);

		return $task;
	}

	/**
	 * Set up an {@see OptionsResolver} to resolve options compatible with the {@see GetTask()} method.
	 *
	 * @param   OptionsResolver  $resolver  The {@see OptionsResolver} instance to set up.
	 *
	 * @return OptionsResolver
	 *
	 * @since __DEPLOY_VERSION__
	 * @throws AccessException
	 */
	public static function configureTaskGetterOptions(OptionsResolver $resolver): OptionsResolver
	{
		$resolver->setDefaults(
			[
				'id'                => 0,
				'allowDisabled'     => false,
				'bypassScheduling'  => false,
				'allowConcurrent' => false,
			]
		)
			->setAllowedTypes('id', 'int')
			->setAllowedTypes('allowDisabled', 'bool')
			->setAllowedTypes('bypassScheduling', 'bool')
			->setAllowedTypes('allowConcurrent', 'bool');

		return $resolver;
	}

	/**
	 * @param   array  $data  The form data
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	public function save($data): bool
	{
		$id    = (int) ($data['id'] ?? $this->getState('task.id'));
		$isNew = $id === 0;

		// Clean up execution rules
		$data['execution_rules'] = $this->processExecutionRules($data['execution_rules']);

		// If a new entry, we'll have to put in place a pseudo-last_execution
		if ($isNew)
		{
			$basisDayOfMonth = $data['execution_rules']['exec-day'];
			[$basisHour, $basisMinute] = explode(':', $data['execution_rules']['exec-time']);

			$data['last_execution'] = Factory::getDate('now', 'GMT')->format('Y-m')
				. "-$basisDayOfMonth $basisHour:$basisMinute:00";
		}
		else
		{
			// phpcs:ignore -- row from table
			$data['last_execution'] = $this->getItem($id)->last_execution;
		}

		// Build the `cron_rules` column from `execution_rules`
		$data['cron_rules'] = $this->buildExecutionRules($data['execution_rules']);

		// `next_execution` would be null if scheduling is disabled with the "manual" rule!
		$data['next_execution'] = (new ExecRuleHelper($data))->nextExec();

		if ($isNew)
		{
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
	 * @since  __DEPLOY_VERSION__
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
		if ($ruleType === 'cron-expression')
		{
			foreach ($executionRules['cron-expression'] as &$values)
			{
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
	 * @since  __DEPLOY_VERSION__
	 * @throws \Exception
	 */
	private function buildExecutionRules(array $executionRules): array
	{
		// Maps interval strings, use with sprintf($map[intType], $interval)
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

		if ($ruleClass === 'interval')
		{
			// Rule type for intervals interval-<minute/hours/...>
			$intervalType    = explode('-', $ruleType)[1];
			$interval        = $executionRules["interval-$intervalType"];
			$buildExpression = sprintf($intervalStringMap[$intervalType], $interval);
		}

		if ($ruleClass === 'cron')
		{
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
	 * Determine if an array is populated by all its possible values by comparison to a reference array, if found a
	 * match a wildcard '*' is returned.
	 *
	 * @param   array  $target       The target array
	 * @param   array  $reference    The reference array, populated by the complete set of possible values in $target
	 * @param   bool   $targetToInt  If true, converts $target array values to integers before comparing
	 *
	 * @return string  A wildcard string if $target is fully populated, else $target itself.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function wildcardIfMatch(array $target, array $reference, bool $targetToInt = false): string
	{
		if ($targetToInt)
		{
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
	 * @since   __DEPLOY_VERSION__
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
