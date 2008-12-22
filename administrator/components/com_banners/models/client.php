<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.model');

/**
 * Banners Component Banner Client Model
 *
 * @package		Joomla
 * @subpackage	Banners
 * @since 1.6
 */
class BannerModelClient extends JModel
{
	/**
	 * Banner Client id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Banner Client data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * Constructor
	 *
	 * @since 1.6
	 */
	function __construct()
	{
		parent::__construct();

		$array = JRequest::getVar('cid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if ($edit)
			$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the banner client identifier
	 *
	 * @access	public
	 * @param	int identifier
	 */
	function setId($id)
	{
		// Set banner client id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a banner client
	 *
	 * @since 1.6
	 */
	function &getData()
	{
		// Load the banner client data
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Tests if banner client is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.6
	 */
	function isCheckedOut($uid=0)
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
	 * Method to checkin/unlock the banner client
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$table = JTable::getInstance('Client', 'BannerTable');
			if (!$table->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

	/**
	 * Method to checkout/lock the banner client
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the banner client out
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the banner client with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$table = JTable::getInstance('Client', 'BannerTable');
			if (!$table->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			return true;
		}
		return false;
	}

	/**
	 * Method to store the banner client
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function store($data)
	{
		$table = JTable::getInstance('Client', 'BannerTable');

		// Bind the form fields to the web link table
		if (!$table->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the data is valid
		if (!$table->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the data to the database
		if (!$table->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to remove a banner client
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count($cid))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode(',', $cid);
			$query = 'DELETE FROM #__bannerclient'
				. ' WHERE cid IN ('.$cids.')';
			$this->_db->setQuery($query);
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to load banner client data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function _loadData()
	{
		if (empty($this->_data))
		{
			// Lets load the banner clients
			$query = 'SELECT bc.*'.
					' FROM #__bannerclient AS bc' .
					' WHERE bc.cid = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the banner client data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function _initData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$bannerclient = new stdClass();
			$bannerclient->id					= 0;
			$bannerclient->name					= null;
			$bannerclient->contact				= null;
			$bannerclient->email				= null;
			$bannerclient->extrainfo			= null;
			$bannerclient->checked_out			= 0;
			$bannerclient->checked_out_time		= 0;
			$bannerclient->editor				= null;
			$this->_data					= $bannerclient;
			return (boolean) $this->_data;
		}
		return true;
	}
}