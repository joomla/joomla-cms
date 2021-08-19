<?php
/**
 * Declares the CronjobModel MVC Model.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cronjobs\Administrator\Model;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
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
use Joomla\Component\Cronjobs\Administrator\Helper\CronjobsHelper;
use Joomla\Component\Cronjobs\Administrator\Helper\ExecRuleHelper;
use function array_diff;
use function defined;
use function gmdate;
use function is_object;
use function sort;

/**
 * MVC Model to interact with the Cronjobs DB.
 * Implements methods to add, remove, edit cronjobs.
 *
 * @since  __DEPLOY_VERSION__
 */
class CronjobModel extends AdminModel
{
	/**
	 * Maps logical states to their values in the DB
	 * ? Do we end up using this?
	 *
	 * @var array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $STATES = [
		'enabled' => 1,
		'disabled' => 0,
		'trashed' => -2
	];

	/**
	 * Prefix used with controller messages
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $text_prefix = 'COM_CRONJOBS';

	/**
	 * Type alias for content type
	 *
	 * @var string
	 * @since  __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_cronjobs.cronjob';

	/**
	 * The Application object, for convenience
	 *
	 * @var AdministratorApplication $app
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;


	/**
	 * CronjobModel constructor. Needed just to set $app
	 *
	 * @param   array                      $config       An array of configuration options
	 * @param   MVCFactoryInterface|null   $factory      The factory [?]
	 * @param   FormFactoryInterface|null  $formFactory  The form factory [?]
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
		$config['events_map'] = $config['events_map'] ?? [];
		$config['events_map'] = array_merge(
			[
				'save' => 'job',
				'validate' => 'job'
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
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		Form::addFieldPath(JPATH_ADMINISTRATOR . 'components/com_cronjobs/src/Field');

		/*
		 *  loadForm() (defined by FormBehaviourTrait) also loads the form data by calling
		 *  loadFormData() : $data [implemented here] and binds it to the form by calling
		 *  $form->bind($data).
		*/
		$form = $this->loadForm('com_cronjobs.cronjob', 'cronjob', ['control' => 'jform', 'load_data' => $loadData]);

		if (empty($form))
		{
			return false;
		}

		$user = $this->app->getIdentity();

		// If new entry, set job type from state
		if ($this->getState('cronjob.id', 0) === 0 && $this->getState('cronjob.type') !== null)
		{
			$form->setValue('type', null, $this->getState('cronjob.type'));
		}

		// @todo : Check if this is working as expected for new items (id == 0)
		if (!$user->authorise('core.edit.state', 'com_cronjobs.cronjob.' . $this->getState('job.id')))
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
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function canDelete($record): bool
	{
		// Record doesn't exist, can't delete
		if (empty($record->id))
		{
			return false;
		}

		return $this->app->getIdentity()->authorise('core.delete', 'com.cronjobs.cronjob.' . $record->id);
	}

	/**
	 * Populate the model state, we use these instead of toying with input or the global state
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function populateState(): void
	{
		$app = $this->app;

		$jobId = $app->getInput()->getInt('id');
		$jobType = $app->getUserState('com_cronjobs.add.cronjob.cronjob_type');
		$jobOption = $app->getUserState('com_cronjobs.add.cronjob.cronjob_option');

		$this->setState('cronjob.id', $jobId);
		$this->setState('cronjob.type', $jobType);
		$this->setState('cronjob.option', $jobOption);

		// Load component params, though com_cronjobs does not (yet) have any params
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
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function getTable($name = 'Cronjob', $prefix = 'Table', $options = array()): Table
	{
		return parent::getTable($name, $prefix, $options);
	}

	/**
	 * Fetches the data to be injected into the form
	 *
	 * @return object  Associative array of form data.
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		$data = $this->app->getUserState('com_cronjobs.edit.cronjob.data', array());

		// If the data from UserState is empty, we fetch it with getItem()
		if (empty($data))
		{
			/** @var CMSObject $data */
			$data = $this->getItem();

			// @todo : further data processing goes here

			// For a fresh object, set exec-day and exec-time
			if (!$id = $data->id ?? 0)
			{
				$data->execution_rules['exec-day'] = gmdate('d');
				$data->execution_rules['exec-time'] = gmdate('H:i');
			}
		}

		// Let plugins manipulate the data
		$this->preprocessData('com_cronjobs.cronjob', $data, 'job');

		return $data;
	}

	/**
	 * Overloads the parent getItem() method.
	 *
	 * @param   integer  $pk  Primary key
	 *
	 * @return  object|boolean  Object on success, false on failure
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
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

		$cronOption = CronjobsHelper::getCronOptions()->findOption(
			($item->id ?? 0) ? ($item->type ?? 0) : $this->getState('cronjob.type')
		);

		$item->set('cronOption', $cronOption);

		return $item;
	}

	/**
	 * @param   array  $data  The form data
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	public function save($data): bool
	{
		/**
		 * @var  object $field Holds the record we're saving $data to
		 */
		$field = null;

		// ? : Is this the right way? 'id' == 0 with $data for a New item so this wouldn't work.
		if (isset($data['id']))
		{
			// ? : Why aren't we doing anything with the field?
			$field = $this->getItem($data['id']);
		}

		// Clean up execution rules
		$this->processExecutionRules($data);

		// Build the `cron_rules` column from `execution_rules`
		$this->buildExecExpression($data);

		// If no params, we set as empty array.
		// ? Is this the right place to do this
		$data['params'] = $data['params'] ?? [];

		// Parent method takes care of saving to the table
		if (!parent::save($data))
		{
			return false;
		}

		return true;
	}

	/**
	 * Clean up and standardise execution rules
	 *
	 * @param   array  $data  The form data [? can just replace with execution_interval]
	 *
	 * @return void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private function processExecutionRules(array &$data): void
	{
		$executionRules = &$data['execution_rules'];
		$ruleType = $executionRules['rule-type'];
		$retainKeys = ['rule-type', $ruleType, 'exec-day', 'exec-time'];
		$executionRules = array_intersect_key($executionRules, array_flip($retainKeys));

		$executionRules['exec-day'] = $executionRules['exec-day'] ?: (string) gmdate('d');
		$executionRules['exec-time'] = $executionRules['exec-time'] ?: (string) gmdate('H:i');

		// If custom ruleset, sort it
		// ? Is this necessary
		if ($ruleType === 'custom')
		{
			foreach ($executionRules['custom'] as &$values)
			{
				sort($values);
			}
		}
	}

	/**
	 * Private method to build execution expression from input execution rules.
	 * This expression is used internally to determine execution times/conditions.
	 *
	 * ! DRY violations here
	 *
	 * @param   array  $data  The form input data
	 *
	 * @return void
	 *
	 * @throws Exception
	 * @since  __DEPLOY_VERSION__
	 */
	private function buildExecExpression(array &$data): void
	{
		// Maps interval strings, use with sprintf($map[intType], $interval)
		$intervalStringMap = [
			'minutes' => 'PT%dM',
			'hours' => 'PT%dH',
			'days' => 'P%dD',
			'months' => 'P%dM',
			'years' => 'P%dY'
		];

		$executionRules = $data['execution_rules'];
		$ruleType = $executionRules['rule-type'];
		$execExpression['visits'] = null;
		$basisDayOfMonth = $executionRules['exec-day'];
		[$basisHour, $basisMinute] = explode(':', $executionRules['exec-time']);

		$execExpression['type'] = $execType = $ruleType === 'custom' ? 'cron' : 'interval';
		$lastExec = Factory::getDate('now', 'GMT')->format('Y-m')
			. "-$basisDayOfMonth $basisHour:$basisMinute:00";

		if ($execType === 'interval')
		{
			$intervalType = explode('-', $ruleType)[1];
			$interval = $executionRules["interval-$intervalType"];
			$execExpression['exp'] = sprintf($intervalStringMap[$intervalType], $interval);
		}

		if ($execType === 'cron')
		{
			// ! custom matches are disabled in the form
			$customRules = &$executionRules['custom'];
			$execExpression['exp'] .= $this->wildcardIfMatch($customRules['minutes'], range(0, 59), true);
			$execExpression['exp'] .= ' ' . $this->wildcardIfMatch($customRules['hours'], range(0, 23), true);
			$execExpression['exp'] .= ' ' . $this->wildcardIfMatch($customRules['days_month'], range(1, 31), true);
			$execExpression['exp'] .= ' ' . $this->wildcardIfMatch($customRules['months'], range(1, 12), true);
			$execExpression['exp'] .= ' ' . $this->wildcardIfMatch($customRules['days_week'], range(0, 6), true);
		}

		$data['last_execution'] = $lastExec;
		$data['cron_rules'] = $execExpression;
		$data['next_execution'] = (new ExecRuleHelper($data))->nextExec();
	}

	/**
	 * Determine if an array is populated by all its possible values by comparison to a reference array.
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
				function (string $x): int {
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
	 * @throws  Exception if there is an error in the form event.
	 * @since   __DEPLOY_VERSION__
	 */
	protected function preprocessForm(Form $form, $data, $group = 'content'): void
	{
		// Load the 'job' plugin group
		PluginHelper::importPlugin('job');

		// Let the parent method take over
		parent::preprocessForm($form, $data, $group);
	}
}
