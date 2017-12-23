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
use Joomla\Component\Workflow\Administrator\Helper\WorkflowHelper;
use Joomla\String\StringHelper;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  __DEPLOY_VERSION__
 */
class StateModel extends AdminModel
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
	 * Method to change the title
	 *
	 * @param   integer  $category_id  The id of the category.
	 * @param   string   $alias        The alias.
	 * @param   string   $title        The title.
	 *
	 * @return	array  Contains the modified title and alias.
	 *
	 * @since	4.0
	 */
	protected function generateNewTitle($category_id, $alias, $title)
	{
		// Alter the title & alias
		$table = $this->getTable();

		while ($table->load(array('title' => $title)))
		{
			$title = StringHelper::increment($title);
		}

		return array($title, $alias);
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
		$context             = $this->option . '.' . $this->name;
		$app                 = \JFactory::getApplication();
		$input               = $app->input;
		$workflowID          = $app->getUserStateFromRequest($context . '.filter.workflow_id', 'workflow_id', 0, 'int');

		if (empty($data['workflow_id']))
		{
			$data['workflow_id'] = $workflowID;
		}

		if ($input->get('task') == 'save2copy')
		{
			$origTable = clone $this->getTable();

			// Alter the title for save as copy
			if ($origTable->load(['title' => $data['title']]))
			{
				list($title) = $this->generateNewTitle(0, '', $data['title']);
				$data['title'] = $title;
			}

			$data['published'] = 0;
			$data['default']   = 0;
		}

		return parent::save($data);
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
		$extension = $app->getUserStateFromRequest('com_workflow.state.filter.extension', 'extension', 'com_content', 'cmd');

		if (!Factory::getUser()->authorise('core.delete', $extension . '.state.' . (int) $record->id))
		{
			$this->setError(\JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

			return false;
		}

		$isAssigned = WorkflowHelper::callMethodFromHelper($extension, 'canDeleteState', $record->id);

		if ($isAssigned && !$record->default)
		{
			return true;
		}
		elseif ($isAssigned === null && !$record->default)
		{
			return true;
		}
		else
		{
			$this->setError(\JText::_('COM_WORKFLOW_MSG_DELETE_IS_ASSIGNED'));

			return false;
		}
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
		$extension = $app->getUserStateFromRequest('com_workflow.state.filter.extension', 'extension', 'com_content', 'cmd');

		// Check for existing workflow.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', $extension . '.state.' . (int) $record->id);
		}

		// Default to component settings if workflow isn't known.
		return $user->authorise('core.edit.state', $extension);
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
			'com_workflow.state',
			'state',
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

		$item = $this->getItem($form->getValue('id'));

		// Deactivate switcher if default
		// Use $item, otherwise we'll be locked when we get the data from the request
		if (!empty($item->default))
		{
			$form->setValue('default', null, 1);
			$form->setFieldAttribute('default', 'readonly', 'true');
		}

		// Modify the form based on access controls.
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
		$data = \JFactory::getApplication()->getUserState(
			'com_workflow.edit.state.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to change the home state of one or more items.
	 *
	 * @param   array    $pk     A list of the primary keys to change.
	 * @param   integer  $value  The value of the home state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function setHome($pk, $value = 1)
	{
		$table = $this->getTable();

		if ($table->load(array('id' => $pk)))
		{
			if ($table->published !== 1)
			{
				$this->setError(\JText::_("COM_WORKFLOW_ITEM_MUST_PUBLISHED"));

				return false;
			}
		}

		if ($value)
		{
			// Verify that the home page for this language is unique per client id
			if ($table->load(array('default' => '1', 'workflow_id' => $table->workflow_id)))
			{
				$table->default = 0;
				$table->store();
			}
		}

		if ($table->load(array('id' => $pk)))
		{
			$table->default = $value;
			$table->store();
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function publish(&$pks, $value = 1)
	{
		$table = $this->getTable();
		$pks   = (array) $pks;
		$app = Factory::getApplication();
		$extension = $app->getUserStateFromRequest('com_workflow.state.filter.extension', 'extension', 'com_content', 'cmd');

		// Default item existence checks.
		if ($value != 1)
		{
			foreach ($pks as $i => $pk)
			{
				if ($table->load(array('id' => $pk)) && $table->default)
				{
					// Prune items that you can't change.
					$app->enqueueMessage(\JText::_('COM_WORKFLOW_ITEM_MUST_PUBLISHED'), 'error');
					unset($pks[$i]);
				}

				if (!WorkflowHelper::callMethodFromHelper($extension, 'canDeleteState', $pks[$i]))
				{
					$app->enqueueMessage(\JText::_('COM_WORKFLOW_MSG_DELETE_IS_ASSIGNED'), 'error');
					unset($pks[$i]);
				}
			}
		}

		return parent::publish($pks, $value);
	}
}
