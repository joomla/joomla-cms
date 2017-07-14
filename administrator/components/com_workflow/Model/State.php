<?php
/**
 * Item Model for a Prove Component.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_prove
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @since       4.0
 */
namespace Joomla\Component\Workflow\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Model\Admin;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  4.0
 */
class State extends Admin
{

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return   boolean  True on success.
	 *
	 * @since 4.0
	 */
	public function save($data)
	{
		$app = \JFactory::getApplication();
		$workflowID = $app->getUserStateFromRequest($this->context . '.filter.workflow_id', 'workflow_id', 0, 'cmd');
		$data['access'] = 0;
		$data['workflow_id'] = (int) $workflowID;

		if ($data['default'] == '1')
		{
			$table = $this->getTable();

			if ($table->load(array('default' => '1')) && $table->id != $data['id'])
			{
				Factory::getApplication()->enqueueMessage('Default state already is', 'error');

				return false;
			}
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
	 * @since   4.0
	 */
	protected function canDelete($record)
	{
		// @TODO check here if the record can be deleted (no item is assigned etc...)
		return parent::canDelete($record);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return \JForm|boolean  A JForm object on success, false on failure
	 *
	 * @since 4.0
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
			Factory::getApplication()->enqueueMessage('There was a problem with setting form', 'error');

			return false;
		}

		return $form;
	}


	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return mixed  The data for the form.
	 *
	 * @since 4.0
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
	 * @param   array    $pks    A list of the primary keys to change.
	 * @param   integer  $value  The value of the home state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0
	 */
	public function setHome($pks, $value = 1)
	{
		$table = $this->getTable();

		if ($value)
		{
			// Verify that the home page for this language is unique per client id
			if ($table->load(array('default' => '1')))
			{
				$table->default = 0;
				$table->store();
			}
		}

		if ($table->load(array('id' => $pks)))
		{
			$table->default = $value;
			$table->store();
		}

		// Clean the cache
		$this->cleanCache();

		return true;
	}
}
