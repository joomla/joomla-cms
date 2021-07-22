<?php
/**
 * Declares the CronjobModel MVC Model.
 *
 * @package       Joomla.Administrator
 * @subpackage    com_cronjobs
 *
 * @copyright (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license       GPL v3
 */

namespace Joomla\Component\Cronjobs\Administrator\Model;

// Restrict direct access
defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\Component\Cronjobs\Administrator\Helper\CronjobsHelper;
use function defined;
use function is_object;

/**
 * MVC Model to interact with the Cronjobs DB.
 * Implements methods to add, remove, edit cronjobs.
 *
 * @since __DEPLOY_VERSION__
 */
class CronjobModel extends AdminModel
{
	/**
	 * Maps logical states to their values in the DB
	 * ? : Do  we end up using this?
	 *
	 * @var array
	 * @since __DEPLOY_VERSION__
	 */
	protected $STATES = array(
		'enabled' => 1,
		'disabled' => 0
	);

	/**
	 * Prefix used with controller messages
	 *
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	protected $text_prefix = 'COM_CRONJOBS';

	/**
	 * Type alias for content type
	 *
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_cronjobs.cronjob';

	/**
	 * The Application object, for convenience
	 *
	 * @var AdministratorApplication $app
	 * @since __DEPLOY_VERSION__
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
	 * @since __DEPLOY_VERSION__
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null, FormFactoryInterface $formFactory = null)
	{
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
	 * @since __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{

		// TODO :  Can we need any custom fields [?]
		Form::addFieldPath(JPATH_ADMINISTRATOR . 'components/com_cronjobs/src/Field');

		/*
		 * * : loadForm() (defined by FormBehaviourTrait) also loads the form data by calling
		 *     loadFormData() : $data [implemented here] and binds it to the form by calling
		 *     $form->bind($data).
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

		/*
		 * TODO : Check if this is working as expected (what about new items, id == 0 ?)
		 */
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
	 * @since __DEPLOY_VERSION__
	 */
	protected function canDelete($record): bool
	{
		// Record doesn't exist, can't delete
		if (empty($record->id))
		{
			return false;
		}

		// TODO : Check if this is the right way to check authority (in particular the assetName)
		return $this->app->getIdentity()->authorise('core.delete', 'com.cronjobs.cronjob.' . $record->id);
	}

	/**
	 * Populate the model state, we use these instead of toying with input or the global state
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
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
	 * Probably don't _need_ to define this method since the parent getTable()
	 * implicitly deduces $name and $prefix anyways. This does make the object
	 * more transparent though.
	 *
	 *
	 * @param   string  $name     Name of the table
	 * @param   string  $prefix   Class prefix
	 * @param   array   $options  Model config array
	 *
	 * @return Table
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
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
	 * @since __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		/*
		 * Check session for previously entered form data
		 * ? : How and where does this data get saved?
		 *
		 */
		$data = $this->app->getUserState('com_cronjobs.edit.cronjob.data', array());

		// If the data from UserState is empty, we fetch it with getItem()
		if (empty($data))
		{
			$data = $this->getItem();

			$time = explode(':', $data->get('execution_interval'));
			$data->set('interval-hours', $time[0] ?? 0);
			$data->set('interval-minutes', $time[1] ?? 0);

			// TODO : Any further data processing goes here
		}

		/*
		 * Let plugins manipulate the form (add fields)
		 * ? Using the 'job' group (as SelectModel), or should we target the 'cronjob' group?
		 */
		$this->preprocessData('com_cronjobs.cronjob', $data, 'job');

		return $data;
	}

	/**
	 * Overloads the parent getItem() method.
	 * ! : Currently does nothing
	 *
	 * ? : Is this needed at all?
	 *     Should be removed if we end up not needing any special handling.
	 *
	 * @param   integer  $pk  Primary key
	 *
	 * @return  object|boolean  Object on success, false on failure
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	public function getItem($pk = null)
	{
		// TODO : Add CronjobModel specific handling or remove ⚠
		$item = parent::getItem($pk);

		if (!is_object($item))
		{
			return false;
		}

		$cronOption = ($item->id ?? 0) ? CronjobsHelper::getCronOptions()->findOption($item->type ?? 0)
			: ($this->getState('cronjob.option'));

		$item->set('cronOption', $cronOption);

		return $item;
	}

	/**
	 * @param   array  $data  The form data
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function save($data): bool
	{
		/**
		 * @var   object $field Holds the record we're saving $data to
		 */
		$field = null;

		// ? : Is this the right way? 'id' == 0 with $data for a New item so this wouldn't work.
		if (isset($data['id']))
		{
			// ? : Why aren't we doing anything with the field?
			$field = $this->getItem($data['id']);
		}

		/*
		 * ! : Due change
		 * TODO : Change execution interval in DB to TIME, change handling below
		 * TODO : Custom fields and we might not need this ugly handling
		 */
		$intervalHours = str_pad($data['interval-hours'] ?? '0', 2, '0', STR_PAD_LEFT);
		$intervalMinutes = str_pad($data['interval-minutes'] ?? '0', 2, '0', STR_PAD_LEFT);
		$data['execution_interval'] = "$intervalHours:$intervalMinutes:00";

		// TODO : Unset fields based on type and trigger selected

		/*
		 * The parent save() takes care of saving to the main
		 * `#__cronjobs` table
		 */
		if (!parent::save($data))
		{
			return false;
		}

		// TODO: Handle the type-specific tables below! ⚠

		// No failures if we get here
		return true;
	}
}
