<?php
/**
 * Item Model for a Prove Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       __DEPLOY_VERSION__
 */
namespace Joomla\Component\Workflow\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  __DEPLOY_VERSION__
 */
class TransitionModel extends AdminModel
{
	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function populateState()
	{
		parent::populateState();

		$app       = Factory::getApplication();
		$context   = $this->option . '.' . $this->name;
		$extension = $app->getUserStateFromRequest($context . '.filter.extension', 'extension', 'com_content', 'cmd');

		$this->setState('filter.extension', $extension);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function canDelete($record)
	{
		if (empty($record->id) || $record->published != -2)
		{
			return false;
		}

		$app = Factory::getApplication();
		$extension = $app->getUserStateFromRequest('com_workflow.transition.filter.extension', 'extension', 'com_content', 'cmd');

		return Factory::getUser()->authorise('core.delete', $extension . '.transition.' . (int) $record->id);
	}

	/**
	 * Method to test whether a record can have its state changed.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission set in the component.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function canEditState($record)
	{
		$user = Factory::getUser();
		$app = Factory::getApplication();
		$extension = $app->getUserStateFromRequest('com_workflow.transition.filter.extension', 'extension', 'com_content', 'cmd');

		// Check for existing workflow.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', $extension . '.transition.' . (int) $record->id);
		}

		// Default to component settings if workflow isn't known.
		return $user->authorise('core.edit.state', $extension);
	}

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return   boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function save($data)
	{
		$pk         = (!empty($data['id'])) ? $data['id'] : (int) $this->getState($this->getName() . '.id');
		$isNew      = true;
		$context    = $this->option . '.' . $this->name;
		$app		= Factory::getApplication();

		if ($pk > 0)
		{
			$isNew = false;
		}

		if ($data['to_state_id'] == $data['from_state_id'])
		{
			$this->setError(\JText::_('You choose the same state from and to'));

			return false;
		}

		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->qn('id'))
			->from($db->qn('#__workflow_transitions'))
			->where($db->qn('from_state_id') . ' = ' . (int) $data['from_state_id'])
			->where($db->qn('to_state_id') . ' = ' . (int) $data['to_state_id']);

		if (!$isNew)
		{
			$query->where($db->qn('id') . ' <> ' . (int) $data['id']);
		}

		$db->setQuery($query);
		$checkDupliaction = $db->loadResult();

		if (!empty($checkDupliaction))
		{
			$this->setError(\JText::_("COM_WORKFLOW_TRANSITION_DUPLICATE"));

			return false;
		}

		$workflowID = $app->getUserStateFromRequest($context . '.filter.workflow_id', 'workflow_id', 0, 'int');

		if (empty($data['workflow_id']))
		{
			$data['workflow_id'] = $workflowID;
		}

		return parent::save($data);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return \JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_workflow.transition',
			'transition',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
			return false;
		}

		if ($loadData)
		{
			$data = $this->loadFormData();
		}

		if (!$this->canEditState((object) $data))
		{
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed  The data for the form.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState(
			'com_workflow.edit.transition.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
}
