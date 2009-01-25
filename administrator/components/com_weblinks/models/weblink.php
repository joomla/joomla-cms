<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modelitem');

// Add a table include path.
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

/**
 * Weblinks Component Weblink Model
 *
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class WeblinksModelWeblink extends JModelItem
{
	/**
	 * Override to get the weblink table
	 */
	function &getTable()
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

		// Load the Member state.
		if (JRequest::getWord('layout') === 'edit') {
			$weblinkId = (int) $app->getUserState('com_weblinks.edit.weblink.id');
			$this->setState('weblink.id', $weblinkId);
		}
		else {
			$weblinkId = (int) JRequest::getInt('weblink_id');
			$this->setState('weblink.id', $weblinkId);
		}

		// Add the Member id to the context to preserve sanity.
		$context	= 'com_weblinks.weblink.'.$weblinkId.'.';

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

		// Get a LabelsTableLabels instance.
		$table = &$this->getTable();

		// Attempt to check-in the row.
		$return = $table->checkin($userId, $weblinkId);

		// Check for a database error.
		if ($return === false) {
			$this->setError($table->getError());
			return false;
		}

		return true;
	}

	/**
	 * Method to check-out a label for editing.
	 *
	 * @param	int		$weblinkId	The numeric id of the label to check-out.
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
	 * @return	mixed	Member data object on success, false on failure.
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
	 * @return	mixed	JXForm object on success, false on failure.
	 * @since	1.0
	 */
	function &getForm()
	{
		// Initialize variables.
		$app	= &JFactory::getApplication();
		$false	= false;

		// Get the form.
		jimport('joomla.form.form');
		JForm::addFormPath(JPATH_COMPONENT.'/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT.'/models/fields');
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

	/**
	 * Tests if weblink is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	function isCheckedOut($uid=0)
	{
		if ($this->_id)
		{
			$weblink = & $this->getTable();
			if (!$weblink->load($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return $weblink->isCheckedOut($uid);
		}
	}


	/**
	 * Method to store the weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function store($data)
	{
		$table =& $this->getTable();

		// Bind the form fields to the web link table
		if (!$table->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Create the timestamp for the date
		$table->date = gmdate('Y-m-d H:i:s');

		// if new item, order last in appropriate group
		if (!$table->id) {
			$where = 'catid = ' . (int) $table->catid ;
			$table->ordering = $table->getNextOrder($where);
		}

		// Make sure the web link table is valid
		if (!$table->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the web link table to the database
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to remove a weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			$query = 'DELETE FROM #__weblinks'
				. ' WHERE id IN ('.$cids.')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to (un)publish a weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__weblinks'
				. ' SET state = '.(int) $publish
				. ' WHERE id IN ('.$cids.')'
				. ' AND (checked_out = 0 OR (checked_out = '.(int) $user->get('id').'))'
			;
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to report a weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function report($cid = array(), $report = -1)
	{
		$user 	=& JFactory::getUser();

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__weblinks'
				. ' SET state = '.(int) $report
				. ' WHERE id IN ('.$cids.')'
			;
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to move a weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function move($direction)
	{
		$table =& $this->getTable();
		if (!$table->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$table->move($direction, ' catid = '.(int) $table->catid.' AND published >= 0 ')) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to move a weblink
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	function saveorder($cid, $order)
	{
		$table =& $this->getTable();
		$groupings = array();

		// update ordering values
		for($i=0; $i < count($cid); $i++)
		{
			$table->load((int) $cid[$i]);
			// track categories
			$groupings[] = $table->catid;

			if ($table->ordering != $order[$i])
			{
				$table->ordering = $order[$i];
				if (!$table->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique($groupings);
		foreach ($groupings as $group){
			$table->reorder('catid = '.(int) $group);
		}

		return true;
	}
}