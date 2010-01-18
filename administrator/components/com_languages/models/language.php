<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Languages Component Language Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @since		1.5
 */
class LanguagesModelLanguage extends JModelForm
{
	/**
	 * Override to get the table
	 */
	public function getTable()
	{
		return JTable::getInstance('Language');
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function _populateState()
	{
		$app		= &JFactory::getApplication('administrator');
		$params		= &JComponentHelper::getParams('com_languages');

		// Load the User state.
		if (JRequest::getWord('layout') === 'edit')
		{
			$langId = (int) $app->getUserState('com_languages.edit.language.id');
			$this->setState('language.id', $langId);
		}
		else
		{
			$langId = (int) JRequest::getInt('id');
			$this->setState('language.id', $langId);
		}

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to get a member item.
	 *
	 * @param	integer	The id of the member to get.
	 * @return	mixed	User data object on success, false on failure.
	 * @since	1.0
	 */
	public function &getItem($langId = null)
	{
		// Initialise variables.
		$langId	= (!empty($langId)) ? $langId : (int) $this->getState('language.id');
		$false		= false;

		// Get a member row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($langId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		return $value;
	}

	/**
	 * Method to get the group form.
	 *
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.0
	 */
	public function getForm()
	{
		// Initialise variables.
		$app	= &JFactory::getApplication();

		// Get the form.
		$form = parent::getForm('language', 'com_languages.language', array('array' => 'jform', 'event' => 'onPrepareForm'));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_languages.edit.language.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	public function save($data)
	{
		$langId	= (int) $this->getState('language.id');
		$isNew		= true;

		$dispatcher = &JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');

		$table = &$this->getTable();

		// Load the row if saving an existing item.
		if ($langId > 0) {
			$table->load($langId);
			$isNew = false;
		}

		// Bind the data
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Check the data
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Trigger the onBeforeSaveContent event.
		$result = $dispatcher->trigger('onBeforeContentSave', array(&$table, $isNew));

		// Check the event responses.
		if (in_array(false, $result, true)) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Trigger the onAfterContentSave event.
		$dispatcher->trigger('onAfterContentSave', array(&$table, $isNew));

		$this->setState('language.id', $table->lang_id);

		return true;
	}

	/**
	 * Method to delete from the database.
	 *
	 * @param	integer	$cid	An array of	numeric ids of the rows.
	 * @return	boolean	True on success/false on failure.
	 */
	public function delete($cid)
	{
		$table = $this->getTable();

		for ($i = 0, $c = count($cid); $i < $c; $i++) {
			// Load the row.
			$return = $table->load($cid[$i]);

			// Check for an error.
			if ($return === false) {
				$this->setError($table->getError());
				return false;
			}

			// Delete the row.
			$return = $table->delete();

			// Check for an error.
			if ($return === false) {
				$this->setError($table->getError());
				return false;
			}
		}

		return true;
	}
}