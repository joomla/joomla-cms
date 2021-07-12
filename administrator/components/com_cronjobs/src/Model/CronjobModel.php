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
\defined('_JEXEC') or die;

use Exception;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;

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
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	protected $text_prefix = 'COM_CRONJOBS';

	/**
	 * @var string
	 * @since __DEPLOY_VERSION__
	 */
	public $typeAlias = 'com_cronjobs.cronjob';


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

		$user = Factory::getApplication()->getIdentity();

		/*
		 * TODO : Check if this works as expected
		 * ? : Is this the right way to check permissions? (In particular, the assetName)
		 * ? : Are we guaranteed a $data['id'], does this work OK if it's null?
		 */
		if (!$user->authorise('core.edit.state', 'com_cronjobs.cronjob.' . $data['id']))
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
		return Factory::getApplication()->getIdentity()->authorise('core.delete', 'com.cronjobs.cronjob.' . $record->id);
	}

	/**
	 * Populate the model state based on user input.
	 * ? : Do we need this?
	 *     The parent method already sets the primary key
	 *     for the Table object, which might be all we need here.
	 *
	 * @return  void
	 *
	 * @throws Exception
	 * @since __DEPLOY_VERSION__
	 */
	protected function populateState(): void
	{
		parent::populateState();
	}

	/**
	 * Probably don't _need_ to define this method since the parent getTable()
	 * implicitly deduces $name and $prefix anyways. This does make the object
	 * more transparent though.
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
	protected function loadFormData(): object
	{
		/*
		 * Check session for previously entered form data
		 * ? : How and where does this data get saved?
		 *
		 * ! : getUserState() makes the IDE scream "Potentially Polymorphic Call"
		 *     because ConsoleApplication and StubGenerator don't implement the method.
		 *     While we'll never call loadFormData() from those, it looks ugly as it is.
		 *     Is there any way to get rid of it?
		 */
		$app = Factory::getApplication();
		$data = $app->getUserState('com_cronjobs.edit.cronjob.data', array());

		// If the UserState didn't have form data, we fetch it with getItem()
		if (empty($data))
		{
			$data = $this->getItem();

			// TODO : Do we need any _priming_ on the $data here?
		}

		// ? What would this do here? Is it needed or (just) good practice?
		$this->preprocessData('com_cronjobs.cronjob', $data);

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
	 * @return object|boolean  Object on success, false on failure
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function getItem($pk = null)
	{
		// TODO : Add CronjobModel specific handling or remove ⚠
		return parent::getItem($pk);
	}

	/**
	 * @param   array  $data  The form data
	 *
	 * @return boolean  True on success, false on failure
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function save($data): bool
	{
		/**
		 * @var   object  $field  Holds the record we're saving $data to
		 */
		$field = null;

		if (isset($data['id']))
		{
			$field = $this->getItem($data['id']);
		}

		// TODO : Unset fields based on type an trigger selected

		/*
		 * The parent save() takes care of saving to the main
		 * `#__cronjobs` table
		 */
		if (! parent::save($data))
		{
			return false;
		}

		// TODO: Handle the type-specific tables below! ⚠

		// No failures if we get here
		return true;
	}
}
