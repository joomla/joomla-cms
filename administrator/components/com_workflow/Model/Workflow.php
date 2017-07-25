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

use JError;
use Joomla\CMS\Factory;
use Joomla\CMS\Model\Admin;

/**
 * The first example class, this is in the same
 * package as declared at the start of file but
 * this example has a defined subpackage
 *
 * @since  4.0
 */
class Workflow extends Admin
{

	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean True on success.
	 *
	 * @since 4.0
	 */
	public function save($data)
	{
		$user                = \JFactory::getUser();
		$app                 = \JFactory::getApplication();
		$extension           = $app->getUserStateFromRequest($this->context . '.filter.extension', 'extension', 'com_content', 'int');
		$data['extension']   = $extension;
		$data['asset_id']    = 0;
		$data['modified_by'] = $user->get('id');

		if (!empty($data['id']))
		{
			$data['modified'] = date("Y-m-d H:i:s");
		}
		else
		{
			$data['created_by'] = $user->get('id');
		}

		if ($data['default'] == '1')
		{
			if ($data['published'] !== '1')
			{
				$this->setError(\JText::_("COM_WORKFLOW_ITEM_MUST_PUBLISHED"));

				return false;
			}

			$table = $this->getTable();

			if ($table->load(array('default' => '1')))
			{
				$table->default = 0;
				$table->store();
			}
		}
		else
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true);

			$query->select("id")
				->from("#__workflows");
			$db->setQuery($query);
			$workflows = $db->loadObjectList();

			if (empty($workflows))
			{
				$data['default'] = '1';
			}
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
	 * @since 4.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			'com_workflow.workflow',
			'workflow',
			array(
				'control'   => 'jform',
				'load_data' => $loadData
			)
		);

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
			'com_workflow.edit.workflow.data',
			array()
		);

		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}


	/**
	 * Method to change the home state of one item.
	 *
	 * @param   array    $pk     A list of the primary keys to change.
	 * @param   integer  $value  The value of the home state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   4.0
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
			// Unset other default item
			if ($table->load(array('default' => '1')))
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
		// @TODO check here if the record can be deleted (no item is assigned to a status etc...)
		return parent::canDelete($record);
	}

	/**
	 * Method to change the published state of one or more records.
	 *
	 * @param   array    &$pks   A list of the primary keys to change.
	 * @param   integer  $value  The value of the published state.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function publish(&$pks, $value = 1)
	{
		$table = $this->getTable();
		$pks   = (array) $pks;

		// Default menu item existence checks.
		if ($value != 1)
		{
			foreach ($pks as $i => $pk)
			{
				if ($table->load($pk) && $table->default)
				{
					// Prune items that you can't change.
					$this->setError(\JText::_('COM_WORKFLOW_ITEM_MUST_PUBLISHED'));
					unset($pks[$i]);
					break;
				}
			}
		}

		return parent::publish($pks, $value);
	}
}
