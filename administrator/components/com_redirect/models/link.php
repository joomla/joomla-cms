<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Redirect link model.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_redirect
 * @since		1.6
 */
class RedirectModelLink extends JModelForm
{
	/**
	 * Method to auto-populate the model state.
	 */
	protected function _populateState()
	{
		// Get the application object.
		$app = & JFactory::getApplication();

		// Load the User state.
		if (!$pk = (int) $app->getUserState('com_redirect.edit.link.id')) {
			$pk = (int) JRequest::getInt('id');
		}
		$this->setState('link.id', $pk);

		// Load the parameters.
		$params	= JComponentHelper::getParams('com_redirect');
		$this->setState('params', $params);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param	type 	$type 	 The table type to instantiate
	 * @param	string 	$prefix	 A prefix for the table class name. Optional.
	 * @param	array	$options Configuration array for model. Optional.
	 * @return	JTable	A database object
	*/
	public function getTable($type = 'Link', $prefix = 'RedirectTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to override check-out a row for editing.
	 *
	 * @param	int		The ID of the primary key.
	 * @return	boolean
	 */
	public function checkout($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('link.id');

		return parent::checkout($pk);
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	The ID of the primary key.
	 *
	 * @return	boolean
	 */
	public function checkin($pk = null)
	{
		// Initialise variables.
		$pk	= (!empty($pk)) ? $pk : (int) $this->getState('link.id');

		return parent::checkin($pk);
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function &getItem($pk = null)
	{
		// Initialise variables.
		$pk = (!empty($pk)) ? $pk : (int) $this->getState('link.id');
		$false	= false;

		// Get a row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($pk);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Prime required properties.
		if (empty($table->id))
		{
			// Prepare data for a new record.
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');

		return $value;
	}

	/**
	 * Method to get the record form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 */
	public function getForm()
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('link', 'com_redirect.link', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_redirect.edit.link.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to save the form data.
	 *
	 * @param	array	The form data.
	 * @return	boolean	True on success.
	 */
	public function save($data)
	{
		// Initialise variables.
		$table		= $this->getTable();
		$pk			= (!empty($data['id'])) ? $data['id'] : (int) $this->getState('link.id');
		$isNew		= true;

		// Load the row if saving an existing record.
		if ($pk > 0) {
			$table->load($pk);
			$isNew = false;
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Prepare the row for saving
		$this->_prepareTable($table);

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		$this->setState('link.id', $table->id);

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 */
	protected function _prepareTable(&$table)
	{
	}

	/**
	 * Method to delete links.
	 *
	 * @param	array	An array of link ids.
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function delete(&$pks)
	{
		// Typecast variable.
		$pks = (array) $pks;

		// Get a row instance.
		$table = &$this->getTable();

		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				// Access checks.
				$allow = $user->authorise('core.edit.state', 'com_redirect');

				if ($allow)
				{
					if (!$table->delete($pk))
					{
						$this->setError($table->getError());
						return false;
					}
				}
				else
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JError_Core_Edit_State_not_permitted'));
				}
			}
			else
			{
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to publish links.
	 *
	 * @param	array	The ids of the items to publish.
	 * @param	int		The value of the published state
	 *
	 * @return	boolean	True on success.
	 */
	function publish(&$pks, $value = 1)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$table	= $this->getTable();
		$pks	= (array) $pks;

		// Access checks.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk))
			{
				$allow = $user->authorise('core.edit.state', 'com_redirect');

				if (!$allow)
				{
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JError_Core_Edit_State_not_permitted'));
				}
			}
		}

		// Attempt to change the state of the records.
		if (!$table->publish($pks, $value, $user->get('id'))) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to activate links.
	 *
	 * @param	array	An array of link ids.
	 * @param	string	The new URL to set for the redirect.
	 * @param	string	A comment for the redirect links.
	 * @return	boolean	Returns true on success, false on failure.
	 */
	public function activate(&$pks, $url, $comment = null)
	{
		// Initialise variables.
		$user	= JFactory::getUser();
		$db		= $this->getDbo();

		// Sanitize the ids.
		$pks = (array) $pks;
		JArrayHelper::toInteger($pks);

		// Populate default comment if necessary.
		$comment = (!empty($comment)) ? $comment : JText::sprintf('Redir_Redirected_On', JHtml::date(time()));

		// Access checks.
		if (!$user->authorise('core.edit', 'com_redirect'))
		{
			$pks = array();
			$this->setError(JText::_('JError_Core_Edit_not_permitted'));
			return false;
		}

		if (!empty($pks))
		{
			// Update the link rows.
			$db->setQuery(
				'UPDATE `#__redirect_links`' .
				' SET `new_url` = '.$db->Quote($url).', `published` = 1, `comment` = '.$db->Quote($comment) .
				' WHERE `id` IN ('.implode(',', $pks).')'
			);
			$db->query();

			// Check for a database error.
			if ($error = $this->_db->getErrorMsg())
			{
				$this->setError($error);
				return false;
			}
		}
		return true;
	}
}
