<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Weblinks Component Weblink Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksModelWeblink extends JModelForm
{
	/**
	 * Override to get the weblink table
	 */
	public function &getTable()
	{
		return JTable::getInstance('Weblink', 'WeblinksTable');
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
		$params		= &JComponentHelper::getParams('com_weblinks');

		// Load the User state.
		if (JRequest::getWord('layout') === 'edit') {
			$weblinkId = (int) $app->getUserState('com_weblinks.edit.weblink.id');
			$this->setState('weblink.id', $weblinkId);
		}
		else {
			$weblinkId = (int) JRequest::getInt('weblink_id');
			$this->setState('weblink.id', $weblinkId);
		}

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Method to checkin a row.
	 *
	 * @param	integer	$id		The numeric id of a row
	 * @return	boolean	True on success/false on failure
	 * @since	1.6
	 */
	public function checkin($weblinkId = null)
	{
		// Initialize variables.
		$user		= &JFactory::getUser();
		$userId		= (int) $user->get('id');
		$weblinkId	= (int) $weblinkId;

		if ($weblinkId === 0) {
			$weblinkId = $this->getState('weblink.id');
		}

		if (empty($weblinkId)) {
			return true;
		}

		// Get a WeblinksTableWeblink instance.
		$table = &$this->getTable();

		// Attempt to check-in the row.
		if (!$table->checkin($weblinkId)) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to check-out a weblink for editing.
	 *
	 * @param	int		$weblinkId	The numeric id of the weblink to check-out.
	 * @return	bool	False on failure or error, success otherwise.
	 * @since	1.6
	 */
	public function checkout($weblinkId)
	{
		// Initialize variables.
		$user		= &JFactory::getUser();
		$userId		= (int) $user->get('id');
		$weblinkId	= (int) $weblinkId;

		// Check for a new weblink id.
		if ($weblinkId === -1) {
			return true;
		}

		$table = &$this->getTable();

		// Attempt to check-out the row.
		$return = $table->checkout($userId, $weblinkId);

		// Check for a database error.
		if ($return === false) {
			$this->setError($table->getError());
			return false;
		}

		// Check if the row is checked-out by someone else.
		if ($return === null) {
			$this->setError(JText::_('JCommon_Item_is_checked_out'));
			return false;
		}

		return true;
	}

	/**
	 * Method to get a member item.
	 *
	 * @access	public
	 * @param	integer	The id of the member to get.
	 * @return	mixed	User data object on success, false on failure.
	 * @since	1.0
	 */
	public function &getItem($weblinkId = null)
	{
		// Initialize variables.
		$weblinkId	= (!empty($weblinkId)) ? $weblinkId : (int) $this->getState('weblink.id');
		$false		= false;

		// Get a member row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($weblinkId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Convert the params field to an array.
		$registry = new JRegistry();
		$registry->loadJSON($table->params);
		$table->params = $registry->toArray();

		$value = JArrayHelper::toObject($table->getProperties(1), 'JObject');
		return $value;
	}

	/**
	 * Method to get the group form.
	 *
	 * @access	public
	 * @return	mixed	JForm object on success, false on failure.
	 * @since	1.0
	 */
	public function &getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.DS.'models'.DS.'forms');
		JForm::addFieldPath(JPATH_COMPONENT.DS.'models'.DS.'fields');
		$form = &JForm::getInstance('jform', 'weblink', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_weblinks.edit.weblink.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	public function save($data)
	{
		$weblinkId	= (int) $this->getState('weblink.id');
		$isNew		= true;

		$dispatcher = &JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');

		// Get a weblink row instance.
		$table = &$this->getTable();

		// Load the row if saving an existing item.
		if ($weblinkId > 0) {
			$table->load($weblinkId);
			$isNew = false;
		}

		// Bind the data
		if (!$table->bind($data)) {
			$this->setError(JText::sprintf('JTable_Error_Bind_failed', $table->getError()));
			return false;
		}

		// Prepare the row for saving
		$this->_prepareTable($table);

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

		$this->setState('weblink.id', $table->id);

		return true;
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = &JFactory::getDate();
		$user = &JFactory::getUser();

		$table->title		= htmlspecialchars_decode($table->title, ENT_QUOTES);
		$table->alias		= JFilterOutput::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JFilterOutput::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toMySQL();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = &JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__weblinks');
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
	 * Tests if weblink is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	public function isCheckedOut($userId = 0)
	{
		if ($userId === 0) {
			$user		= &JFactory::getUser();
			$userId		= (int) $user->get('id');
		}

		$weblinkId = (int) $this->getState('weblink.id');

		if (empty($weblinkId)) {
			return true;
		}

		$table = &$this->getTable();

		$return = $table->load($weblinkId);

		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		return $table->isCheckedOut($userId);
	}

	/**
	 * Method to delete weblinks from the database.
	 *
	 * @param	integer	$cid	An array of	numeric ids of the rows.
	 * @return	boolean	True on success/false on failure.
	 */
	public function delete($cid)
	{
		// Get a weblink row instance
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

	/**
	 * Method to adjust the ordering of a row.
	 *
	 * @param	int		$weblinkId	The numeric id of the weblink to move.
	 * @param	int		$direction	The direction to move the row (-1/1).
	 * @return	bool	True on success/false on failure
	 */
	public function reorder($weblinkId, $direction)
	{
		// Get a WeblinksTableWeblink instance.
		$table = &$this->getTable();

		$weblinkId	= (int) $weblinkId;

		if ($weblinkId === 0) {
			$weblinkId = $this->getState('weblink.id');
		}

		// Attempt to check-out and move the row.
		if (!$this->checkout($weblinkId)) {
			return false;
		}

		// Load the row.
		if (!$table->load($weblinkId)) {
			$this->setError($table->getError());
			return false;
		}

		// Move the row.
		$table->move($direction);

		// Check-in the row.
		if (!$this->checkin($weblinkId)) {
			return false;
		}

		return true;
	}
}