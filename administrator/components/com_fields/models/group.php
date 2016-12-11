<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\String\StringHelper;

/**
 * Group Model
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsModelGroup extends JModelAdmin
{
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function save($data)
	{
		// Alter the title for save as copy
		$input = JFactory::getApplication()->input;

		// Save new group as unpublished
		if ($input->get('task') == 'save2copy')
		{
			$data['state'] = 0;
		}

		return parent::save($data);
	}

	/**
	 * Method to get a table object, load it if necessary.
	 *
	 * @param   string  $name     The table name. Optional.
	 * @param   string  $prefix   The class prefix. Optional.
	 * @param   array   $options  Configuration array for model. Optional.
	 *
	 * @return  JTable  A JTable object
	 *
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception
	 */
	public function getTable($name = 'Group', $prefix = 'FieldsTable', $options = array())
	{
		return JTable::getInstance($name, $prefix, $options);
	}

	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$extension = $this->getState('filter.extension');
		$jinput = JFactory::getApplication()->input;

		if (empty($extension) && isset($data['extension']))
		{
			$extension = $data['extension'];
			$this->setState('filter.extension', $extension);
		}

		// Get the form.
		$form = $this->loadForm(
			'com_fields.group.' . $extension, 'group',
			array(
				'control'   => 'jform',
				'load_data' => $loadData,
			)
		);

		if (empty($form))
		{
			return false;
		}

		// Modify the form based on Edit State access controls.
		if (empty($data['extension']))
		{
			$data['extension'] = $extension;
		}

		if (!JFactory::getUser()->authorise('core.edit.state', $extension . '.fieldgroup.' . $jinput->get('id')))
		{
			// Disable fields for display.
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('state', 'disabled', 'true');

			// Disable fields while saving. The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('state', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to delete the record. Defaults to the permission for the component.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function canDelete($record)
	{
		if (empty($record->id) || $record->state != -2)
		{
			return false;
		}

		return JFactory::getUser()->authorise('core.delete', $record->extension . '.fieldgroup.' . (int) $record->id);
	}

	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param   object  $record  A record object.
	 *
	 * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the
	 *                   component.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check for existing fieldgroup.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', $record->extension . '.fieldgroup.' . (int) $record->id);
		}

		// Default to component settings.
		return $user->authorise('core.edit.state', $record->extension);
	}

	/**
	 * Auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState()
	{
		parent::populateState();

		$extension = JFactory::getApplication()->getUserStateFromRequest('com_fields.groups.extension', 'extension', 'com_fields', 'CMD');
		$this->setState('filter.extension', $extension);
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A JTable object.
	 *
	 * @return  array  An array of conditions to add to ordering queries.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getReorderConditions($table)
	{
		return 'extension = ' . $this->_db->quote($table->extension);
	}

	/**
	 * Method to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
		parent::preprocessForm($form, $data, $group);

		// Set the access control rules field component value.
		$form->setFieldAttribute('rules', 'component', $this->state->get('filter.extension'));
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array    The default data is an empty array.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = JFactory::getApplication();
		$data = $app->getUserState('com_fields.edit.group.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Language, Access) in edit form if those have been selected in Field Group Manager
			if (!$data->id)
			{
				// Check for which extension the Field Group Manager is used and get selected fields
				$extension = substr($app->getUserState('com_fields.groups.filter.extension'), 4);
				$filters = (array) $app->getUserState('com_fields.groups.' . $extension . '.filter');

				$data->set(
					'state',
					$app->input->getInt('state', (!empty($filters['state']) ? $filters['state'] : null))
				);
				$data->set(
					'language',
					$app->input->getString('language', (!empty($filters['language']) ? $filters['language'] : null))
				);
				$data->set(
					'access',
					$app->input->getInt('access', (!empty($filters['access']) ? $filters['access'] : JFactory::getConfig()->get('access')))
				);
			}
		}

		$this->preprocessData('com_fields.group', $data);

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk))
		{
			// Prime required properties.
			if (empty($item->id))
			{
				$item->extension = $this->getState('filter.extension');
			}

			// Convert the created and modified dates to local user time for display in the form.
			$tz = new DateTimeZone(JFactory::getApplication()->get('offset'));

			if ((int) $item->created)
			{
				$date = new JDate($item->created);
				$date->setTimezone($tz);
				$item->created = $date->toSql(true);
			}
			else
			{
				$item->created = null;
			}

			if ((int) $item->modified)
			{
				$date = new JDate($item->modified);
				$date->setTimezone($tz);
				$item->modified = $date->toSql(true);
			}
			else
			{
				$item->modified = null;
			}
		}

		return $item;
	}

	/**
	 * Clean the cache
	 *
	 * @param   string   $group      The cache group
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function cleanCache($group = null, $client_id = 0)
	{
		$extension = JFactory::getApplication()->input->get('extension');

		parent::cleanCache($extension);
	}
}
