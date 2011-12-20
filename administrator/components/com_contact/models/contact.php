<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Item Model for a Contact.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_contact
 * @since		1.6
 */
class ContactModelContact extends JModelAdmin
{
	/**
	 * Method to test whether a record can be deleted.
	 *
	 * @param	object	$record	A record object.
	 *
	 * @return	boolean	True if allowed to delete the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canDelete($record)
	{
		if (!empty($record->id)) {
			if ($record->published != -2) {
				return ;
			}
			$user = JFactory::getUser();
			return $user->authorise('core.delete', 'com_contact.category.'.(int) $record->catid);
		}
	}

	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param	object	$record	A record object.
	 *
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record)
	{
		$user = JFactory::getUser();

		// Check against the category.
		if (!empty($record->catid)) {
			return $user->authorise('core.edit.state', 'com_contact.category.'.(int) $record->catid);
		}
		// Default to component settings if category not known.
		else {
			return parent::canEditState($record);
		}
	}

	/**
	 * Returns a Table object, always creating it
	 *
	 * @param	type	$type	The table type to instantiate
	 * @param	string	$prefix	A prefix for the table class name. Optional.
	 * @param	array	$config	Configuration array for model. Optional.
	 *
	 * @return	JTable	A database object
	 * @since	1.6
	 */
	public function getTable($type = 'Contact', $prefix = 'ContactTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the row form.
	 *
	 * @param	array	$data		Data for the form.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 *
	 * @return	mixed	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		JForm::addFieldPath('JPATH_ADMINISTRATOR/components/com_users/models/fields');

		// Get the form.
		$form = $this->loadForm('com_contact.contact', 'contact', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}

		// Modify the form based on access controls.
		if (!$this->canEditState((object) $data)) {
			// Disable fields for display.
			$form->setFieldAttribute('featured', 'disabled', 'true');
			$form->setFieldAttribute('ordering', 'disabled', 'true');
			$form->setFieldAttribute('published', 'disabled', 'true');

			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('featured', 'filter', 'unset');
			$form->setFieldAttribute('ordering', 'filter', 'unset');
			$form->setFieldAttribute('published', 'filter', 'unset');
		}

		return $form;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	$pk	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)
	{
		if ($item = parent::getItem($pk)) {
			// Convert the params field to an array.
			$registry = new JRegistry;
			$registry->loadString($item->metadata);
			$item->metadata = $registry->toArray();
		}

		return $item;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_contact.edit.contact.data', array());

		if (empty($data)) {
			$data = $this->getItem();

			// Prime some default values.
			if ($this->getState('contact.id') == 0) {
				$app = JFactory::getApplication();
				$data->set('catid', JRequest::getInt('catid', $app->getUserState('com_contact.contacts.filter.category_id')));
			}
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param	JTable	$table
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = JFactory::getDate();
		$user = JFactory::getUser();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias		= JApplication::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->name);
		}

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toMySQL();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__contact_details');
				$max = $db->loadResult();

				$table->ordering = $max+1;
			}
		}
		else {
			// Set the values
			//$table->modified	= $date->toMySQL();
			//$table->modified_by	= $user->get('id');
		}
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param	JTable	$table	A record object.
	 *
	 * @return	array	An array of conditions to add to add to ordering queries.
	 * @since	1.6
	 */
	protected function getReorderConditions($table)
	{
		$condition = array();
		$condition[] = 'catid = '.(int) $table->catid;

		return $condition;
	}

	/**
	 * Method to toggle the featured setting of contacts.
	 *
	 * @param	array	$pks	The ids of the items to toggle.
	 * @param	int		$value	The value to toggle to.
	 *
	 * @return	boolean	True on success.
	 * @since	1.6
	 */
	public function featured($pks, $value = 0)
	{
		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		if (empty($pks)) {
			$this->setError(JText::_('COM_CONTACT_NO_ITEM_SELECTED'));
			return false;
		}

		$table = $this->getTable();

		try
		{
			$db = $this->getDbo();

			$db->setQuery(
				'UPDATE #__contact_details' .
				' SET featured = '.(int) $value.
				' WHERE id IN ('.implode(',', $pks).')'
			);
			if (!$db->query()) {
				throw new Exception($db->getErrorMsg());
			}

		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());
			return false;
		}

		$table->reorder();

		// Clean component's cache
		$this->cleanCache();

		return true;
	}
}
