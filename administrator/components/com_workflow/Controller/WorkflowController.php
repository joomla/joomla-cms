<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\CMS\MVC\Controller\FormController;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  __DEPLOY_VERSION__
 */
class WorkflowController extends FormController
{
	/**
	 * The extension for which the categories apply.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MvcFactoryInterface  $factory  The factory.
	 * @param   \CMSApplication      $app      The JApplication for the dispatcher
	 * @param   \JInput              $input    Input
	 *
	 * @since  __DEPLOY_VERSION__
	 * @see    \JControllerLegacy
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null, $app = null, $input = null)
	{
		parent::__construct($config, $factory, $app, $input);

		if (empty($this->extension))
		{
			$this->extension = $this->input->get('extension', 'com_content');
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param   array  $data  An array of input data.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function allowAdd($data = array())
	{
		$user = Factory::getUser();

		return $user->authorise('core.create', $this->extension);
	}

	/**
	 * Method to check if you can edit a record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = isset($data[$key]) ? (int) $data[$key] : 0;
		$user = Factory::getUser();

		// Check "edit" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit', $this->extension . '.workflow.' . $recordId))
		{
			return true;
		}

		// Check "edit own" permission on record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', $this->extension . '.workflow.' . $recordId))
		{
			// Need to do a lookup from the model to get the owner
			$record = $this->getModel()->getItem($recordId);

			return !empty($record) && $record->created_by == $user->id;
		}

		return false;
	}

	/**
	 * Gets the URL arguments to append to an item redirect.
	 *
	 * @param   integer  $recordId  The primary key id for the item.
	 * @param   string   $urlVar    The name of the URL variable for the id.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Gets the URL arguments to append to a list redirect.
	 *
	 * @return  string  The arguments to append to the redirect URL.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function getRedirectToListAppend()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&extension=' . $this->extension;

		return $append;
	}

	/**
	 * Function that allows child controller access to model data
	 * after the data has been saved.
	 *
	 * @param   \JModelLegacy  $model      The data model object.
	 * @param   array          $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function postSaveHook(\JModelLegacy $model, $validData = array())
	{
		$task = $this->getTask();

		// The save2copy task needs to be handled slightly differently.
		if ($task === 'save2copy')
		{
			$table = $model->getTable();

			$key = $table->getKeyName();

			$recordId = $this->input->getInt($key);

			$db = $model->getDbo();
			$query = $db->getQuery(true);

			$query->select('*')
				->from($db->qn('#__workflow_states'))
				->where($db->qn('workflow_id') . ' = ' . (int) $recordId);

			$statuses = $db->setQuery($query)->loadAssocList();

			$smodel = $this->getModel('State');

			$workflowID = (int) $model->getState($model->getName() . '.id');

			$mapping = [];

			foreach ($statuses as $status)
			{
				$table = $smodel->getTable();

				$oldID = $status['id'];

				$status['workflow_id'] = $workflowID;
				$status['id'] = 0;
				unset($status['asset_id']);

				$table->save($status);

				$mapping[$oldID] = (int) $table->id;
			}

			$query->clear();

			$query->select('*')
				->from($db->qn('#__workflow_transitions'))
				->where($db->qn('workflow_id') . ' = ' . (int) $recordId);

			$transitions = $db->setQuery($query)->loadAssocList();

			$tmodel = $this->getModel('Transition');

			foreach ($transitions as $transition)
			{
				$table = $tmodel->getTable();

				$transition['from_state_id'] = $mapping[$transition['from_state_id']];
				$transition['to_state_id'] = $mapping[$transition['to_state_id']];

				$transition['workflow_id'] = $workflowID;
				$transition['id'] = 0;
				unset($transition['asset_id']);

				$table->save($transition);
			}
		}
	}

	/**
	 * Method to save a workflow.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return  boolean  True if successful, false otherwise.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function save($key = null, $urlVar = null)
	{
		$task = $this->getTask();

		// The save2copy task needs to be handled slightly differently.
		if ($task === 'save2copy')
		{
			$data  = $this->input->post->get('jform', array(), 'array');

			// Prevent default
			$data['default'] = 0;

			$this->input->post->set('jform', $data);
		}

		parent::save($key, $urlVar);
	}
}
