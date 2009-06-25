<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modelform');

/**
 * Newsfeeds Component Newsfeed Model
 *
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @since		1.5
 */
class NewsfeedsModelNewsfeed extends JModelForm
{
	/**
	 * Override to get the newsfeed table
	 */
	public function &getTable()
	{
		return JTable::getInstance('Newsfeed', 'Table');
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
		$params		= &JComponentHelper::getParams('com_newsfeeds');

		// Load the User state.
		if (JRequest::getWord('layout') === 'edit') {
			$newsfeedId = (int) $app->getUserState('com_newsfeeds.edit.newsfeed.id');
			$this->setState('newsfeed.id', $newsfeedId);
		}
		else {
			$newsfeedId = (int) JRequest::getInt('newsfeed_id');
			$this->setState('newsfeed.id', $newsfeedId);
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
	public function checkin($newsfeedId = null)
	{
		// Initialize variables.
		$user		= &JFactory::getUser();
		$userId		= (int) $user->get('id');
		$newsfeedId	= (int) $newsfeedId;

		if ($newsfeedId === 0) {
			$newsfeedId = $this->getState('newsfeed.id');
		}

		if (empty($newsfeedId)) {
			return true;
		}

		// Get a NewsfeedsTableNewsfeed instance.
		$table = &$this->getTable();

		// Attempt to check-in the row.
		$return = $table->checkin($userId, $newsfeedId);

		// Check for a database error.
		if ($return === false) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to check-out a newsfeed for editing.
	 *
	 * @param	int		$newsfeedId	The numeric id of the newsfeed to check-out.
	 * @return	bool	False on failure or error, success otherwise.
	 * @since	1.6
	 */
	public function checkout($newsfeedId)
	{
		// Initialize variables.
		$user		= &JFactory::getUser();
		$userId		= (int) $user->get('id');
		$newsfeedId	= (int) $newsfeedId;

		// Check for a new newsfeed id.
		if ($newsfeedId === -1) {
			return true;
		}

		$table = &$this->getTable();

		// Attempt to check-out the row.
		$return = $table->checkout($userId, $newsfeedId);

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
	public function &getItem($newsfeedId = null)
	{
		// Initialize variables.
		$newsfeedId	= (!empty($newsfeedId)) ? $newsfeedId : (int) $this->getState('newsfeed.id');
		$false		= false;

		// Get a member row instance.
		$table = &$this->getTable();

		// Attempt to load the row.
		$return = $table->load($newsfeedId);

		// Check for a table object error.
		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return $false;
		}

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return $false;
		}

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
		$form = &JForm::getInstance('jform', 'newsfeed', true, array('array' => true));

		// Check for an error.
		if (JError::isError($form)) {
			$this->setError($form->getMessage());
			return $false;
		}

		// Check the session for previously entered form data.
		$data = $app->getUserState('com_newsfeeds.edit.newsfeed.data', array());

		// Bind the form data if present.
		if (!empty($data)) {
			$form->bind($data);
		}

		return $form;
	}

	public function save($data)
	{
		$newsfeedId	= (int) $this->getState('newsfeed.id');
		$isNew		= true;

		$dispatcher = &JDispatcher::getInstance();
		JPluginHelper::importPlugin('content');

		// Get a newsfeed row instance.
		$table = &$this->getTable();

		// Load the row if saving an existing item.
		if ($newsfeedId > 0) {
			$table->load($newsfeedId);
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

		return $table->id;
	}

	protected function _prepareTable(&$table)
	{
		jimport('joomla.filter.output');
		$date = &JFactory::getDate();
		$user = &JFactory::getUser();

		$table->name		= htmlspecialchars_decode($table->name, ENT_QUOTES);
		$table->alias		= JFilterOutput::stringURLSafe($table->alias);

		if (empty($table->alias)) {
			$table->alias = JFilterOutput::stringURLSafe($table->name);
		}

		if (empty($table->id)) {
			// Set the values
			//$table->created	= $date->toMySQL();

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db = &JFactory::getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__newsfeeds');
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
	 * Tests if newsfeed is checked out
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

		$newsfeedId = (int) $this->getState('newsfeed.id');

		if (empty($newsfeedId)) {
			return true;
		}

		$table = &$this->getTable();

		$return = $table->load($newsfeedId);

		if ($return === false && $table->getError()) {
			$this->setError($table->getError());
			return false;
		}

		return $table->isCheckedOut($userId);
	}

	/**
	 * Method to delete newsfeeds from the database.
	 *
	 * @param	integer	$cid	An array of	numeric ids of the rows.
	 * @return	boolean	True on success/false on failure.
	 */
	public function delete($cid)
	{
		// Get a newsfeed row instance
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
	 * @param	int		$newsfeedId	The numeric id of the newsfeed to move.
	 * @param	int		$direction	The direction to move the row (-1/1).
	 * @return	bool	True on success/false on failure
	 */
	public function reorder($newsfeedId, $direction)
	{
		// Get a NewsfeedsTableNewsfeed instance.
		$table = &$this->getTable();

		$newsfeedId	= (int) $newsfeedId;

		if ($newsfeedId === 0) {
			$newsfeedId = $this->getState('newsfeed.id');
		}

		// Attempt to check-out and move the row.
		if (!$this->checkout($newsfeedId)) {
			return false;
		}

		// Load the row.
		if (!$table->load($newsfeedId)) {
			$this->setError($table->getError());
			return false;
		}

		// Move the row.
		$table->move($direction);

		// Check-in the row.
		if (!$this->checkin($newsfeedId)) {
			return false;
		}

		return true;
	}
}