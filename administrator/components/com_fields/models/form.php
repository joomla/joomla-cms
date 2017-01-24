<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Form Model
 *
 * @since  __DEPLOY_VERSION__
 */
class FieldsModelForm extends JModelAdmin
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

		// Save new form as unpublished
		if ($input->get('task') == 'save2copy')
		{
			$data['state'] = 0;
		}

        if (!parent::save($data))
        {
            return false;
        }

        // Save the assigned categories into #__fields_categories
        $db = $this->getDbo();
        $id = (int) $this->getState('form.id');
        $cats = isset($data['assigned_cat_ids']) ? (array) $data['assigned_cat_ids'] : array();
        $cats = ArrayHelper::toInteger($cats);

        $assignedCatIds = array();

        foreach ($cats as $cat)
        {
            if ($cat)
            {
                $assignedCatIds[] = $cat;
            }
        }

        // First delete all assigned categories
        $query = $db->getQuery(true);
        $query->delete('#__fields_forms_categories')
            ->where('form_id = ' . $id);
        $db->setQuery($query);
        $db->execute();

        // Inset new assigned categories
        $tupel = new stdClass;
        $tupel->form_id = $id;

        foreach ($assignedCatIds as $catId)
        {
            $tupel->category_id = $catId;
            $db->insertObject('#__fields_forms_categories', $tupel);
        }

        return true;
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
	public function getTable($name = 'Form', $prefix = 'FieldsTable', $options = array())
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
		$context = $this->getState('filter.context');
		$jinput = JFactory::getApplication()->input;

		if (empty($context) && isset($data['context']))
		{
			$context = $data['context'];
			$this->setState('filter.context', $context);
		}

		// Get the form.
		$form = $this->loadForm(
			'com_fields.form.' . $context, 'form',
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
		if (empty($data['context']))
		{
			$data['context'] = $context;
		}

		if (!JFactory::getUser()->authorise('core.edit.state', $context . '.fieldform.' . $jinput->get('id')))
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

		return JFactory::getUser()->authorise('core.delete', $record->context . '.fieldform.' . (int) $record->id);
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

		// Check for existing fieldform.
		if (!empty($record->id))
		{
			return $user->authorise('core.edit.state', $record->context . '.fieldform.' . (int) $record->id);
		}

		// Default to component settings.
		return $user->authorise('core.edit.state', $record->context);
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

		$context = JFactory::getApplication()->getUserStateFromRequest('com_fields.forms.context', 'context', 'com_fields', 'CMD');
		$this->setState('filter.context', $context);
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
		return 'context = ' . $this->_db->quote($table->context);
	}

	/**
	 * Method to preprocess the form.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin form to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @see     JFormField
	 * @since   __DEPLOY_VERSION__
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'content')
	{
        $parts = FieldsHelper::extract($this->state->get('filter.context'));

        $form->setFieldAttribute('assigned_cat_ids', 'extension', $parts[0]);


        parent::preprocessForm($form, $data, $group);


		if ($parts)
		{
			// Set the access control rules field component value.
            $form->setFieldAttribute('rules', 'component', $parts[0]);
		}
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
		$data = $app->getUserState('com_fields.edit.form.data', array());

		if (empty($data))
		{
			$data = $this->getItem();

			// Pre-select some filters (Status, Language, Access) in edit form if those have been selected in Field Form Manager
			if (!$data->id)
			{
				// Check for which context the Field Form Manager is used and get selected fields
				$context = substr($app->getUserState('com_fields.forms.filter.context'), 4);
				$filters = (array) $app->getUserState('com_fields.forms.' . $context . '.filter');

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

		$this->preprocessData('com_fields.form', $data);

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
				$item->context = $this->getState('filter.context');
			}

            $db = $this->getDbo();
            $query = $db->getQuery(true);
            $query->select('category_id')
                ->from('#__fields_forms_categories')
                ->where('form_id = ' . (int) $item->id);

            $db->setQuery($query);
            $item->assigned_cat_ids = $db->loadColumn() ?: array(0);


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
	 * @param   string   $form      The cache form
	 * @param   integer  $client_id  The ID of the client
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function cleanCache($form = null, $client_id = 0)
	{
		$context = JFactory::getApplication()->input->get('context');

		parent::cleanCache($context);
	}

    /**
     * Method to delete one or more records.
     *
     * @param   array  &$pks  An array of record primary keys.
     *
     * @return  boolean  True if successful, false if an error occurs.
     *
     * @since   __DEPLOY_VERSION__
     */
    public function delete(&$pks)
    {
        $success = parent::delete($pks);

        if ($success)
        {
            $pks = (array) $pks;
            $pks = ArrayHelper::toInteger($pks);
            $pks = array_filter($pks);

            if (!empty($pks))
            {
                // Delete Values
                $query = $this->getDbo()->getQuery(true);

                $query->delete($query->qn('#__fields_values'))
                    ->where($query->qn('form_id') . ' IN(' . implode(',', $pks) . ')');

                $this->getDbo()->setQuery($query)->execute();

                // Delete Assigned Categories
                $query = $this->getDbo()->getQuery(true);

                $query->delete($query->qn('#__fields_forms_categories'))
                    ->where($query->qn('form_id') . ' IN(' . implode(',', $pks) . ')');

                $this->getDbo()->setQuery($query)->execute();

                // Delete Assigned Field Groups
                $query = $this->getDbo()->getQuery(true);

                $query->delete($query->qn('#__fields_groups'))
                    ->where($query->qn('form_id') . ' IN(' . implode(',', $pks) . ')');

                $this->getDbo()->setQuery($query)->execute();

                // Delete Assigned Fields
                $query = $this->getDbo()->getQuery(true);

                $query->delete($query->qn('#__fields'))
                    ->where($query->qn('form_id') . ' IN(' . implode(',', $pks) . ')');

                $this->getDbo()->setQuery($query)->execute();
            }
        }

        return $success;
    }
}
