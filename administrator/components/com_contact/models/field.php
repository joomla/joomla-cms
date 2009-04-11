<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class ContactModelField extends JModel{

	var $_id = null;
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	protected function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if ($edit)
			$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the field identifier
	 *
	 * @access	public
	 * @param	int Field identifier
	 */

	public function setId($id)
	{
		// Set field id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a field
	 *
	 * @since 1.5
	 */

	public function &getData()
	{
		// Load the field data
		$result = $this->_loadData();
		if (!$result) $this->_initData();

		return $this->_data;
	}

	/**
	 * Method to load the field data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT * FROM #__contact_fields WHERE id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the field data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function _initData()
	{
		// Lets load the field data if it doesn't already exist
		if (empty($this->_data))
		{
			$field = new stdClass();
			$field->id = null;
			$field->title = '';
			$field->description	 = null;
			$field->type = 'text';
			$field->published = 0;
			$field->checked_out = 0;
			$field->checked_out_time = 0;
			$field->ordering	 = 0;
			$field->pos = 'main';
			$field->access = 0;
			$field->params = null;
			$this->_data	= $field;
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Tests if field is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.5
	 */
	public function isCheckedOut($uid=0)
	{
		if ($this->_loadData())
		{
			if ($uid) {
				return ($this->_data->checked_out && $this->_data->checked_out != $uid);
			} else {
				return $this->_data->checked_out;
			}
		}
	}

	/**
	 * Method to checkin/unlock the field
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function checkin()
	{
		if ($this->_id)
		{
			$field = & $this->getTable();
			if (! $field->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

	/**
	 * Method to checkout/lock the field
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the article out
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$field = & $this->getTable();
			if (!$field->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * Method to store the field
	 *
	 * @access	public
	 * @return	the id on success else false
	 * @since	1.5
	 */
	public function store($data)
	{
		$row =& $this->getTable();

		// Bind the form fields to the field table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Create the timestamp for the date
		$row->checked_out_time = gmdate('Y-m-d H:i:s');

		// if new item, order last in appropriate group
		if (!$row->id) {
			$where = 'pos = ' . (int) $row->pos ;
			$row->ordering = $row->getNextOrder($where);
		}

		// Make sure the fields table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the fields table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return $row->id;
	}

	/**
	 * Method to remove a field
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function delete($cid = array())
	{
		$row =& $this->getTable();

		// delete field from contact_field table
		for($i=0; $i < count($cid); $i++)
		{
			if (!$row->load((int) $cid[$i])) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			if ($row->id == 1){
				$this->setError(JText::_('You are not authorized to delete the email field'));
				return false;
			}

			$query = 'DELETE FROM #__contact_fields'
					. ' WHERE id = '.$row->id;
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			$query = 'DELETE FROM #__contact_details WHERE field_id = '.$row->id;
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to (un)publish a field
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);

			$query = 'UPDATE #__contact_fields'
				. ' SET published = '.(int) $publish
				. ' WHERE id IN ('.$cids.')'
				. ' AND (checked_out = 0 OR (checked_out = '.(int) $user->get('id').'))';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return true;
	}

	/**
	 * Method to move a field
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function move($direction)
	{
		$row =& $this->getTable();
		if (!$row->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move($direction, "pos = '$row->pos' AND published != 0")) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}
		return true;
	}

	/**
	 * Method to move a field
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.5
	 */
	public function saveorder($cid, $order)
	{
		$row =& $this->getTable();

		// update ordering values
		for($i=0; $i < count($cid); $i++)
		{
			$row->load((int) $cid[$i]);

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}
		return true;
	}

	/**
	* Set the access of selected menu items
	*/
	public function setAccess($cid = array(), $access = 0)
	{
		$row =& $this->getTable();
		foreach ($cid as $id)
		{
			$row->load($id);
			$row->access = $access;

			if (!$row->check()) {
				$this->setError($row->getError());
				return false;
			}
			if (!$row->store()) {
				$this->setError($row->getError());
				return false;
			}
		}
		return true;
	}
}

?> 
