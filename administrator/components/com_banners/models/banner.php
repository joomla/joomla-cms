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
 * Banners Component Banner Model
 *
 * @package		Joomla
 * @subpackage	Banners
 * @since 1.6
 */
class BannerModelBanner extends JModel
{
	/**
	 * Banner id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * Banner data
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

		$array = JRequest::getVar('bid', array(0), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if($edit)
			$this->setId((int)$array[0]);
	}

	/**
	 * Method to set the banner identifier
	 *
	 * @access	public
	 * @param	int identifier
	 */
	function setId($id)
	{
		// Set banner id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a banner
	 *
	 * @since 1.6
	 */
	function &getData()
	{
		// Load the banner data
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Tests if banner is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.6
	 */
	function isCheckedOut( $uid=0 )
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
	 * Method to checkin/unlock the banner
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$banner = & $this->getTable();
			if(! $banner->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

	/**
	 * Method to checkout/lock the banner
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking the banner out
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the banner with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$banner = & $this->getTable();
			if(!$banner->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			return true;
		}
		return false;
	}

	/**
	 * Method to store the banner
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function store($data)
	{
		$row =& $this->getTable();

		// Bind the form fields to the web link table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the data is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Store the data to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to remove a banner
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function delete($cid = array())
	{
		$result = false;

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );
			$query = 'DELETE FROM #__banner'
				. ' WHERE bid IN ( '.$cids.' )';
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to (un)publish a banner
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function publish($cid = array(), $publish = 1)
	{
		$user 	=& JFactory::getUser();

		if (count( $cid ))
		{
			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__banner'
				. ' SET showBanner = '.(int) $publish
				. ' WHERE bid IN ( '.$cids.' )'
				. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to move a banner
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function move($direction)
	{
		$row =& $this->getTable();
		if (!$row->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move( $direction, ' catid = '.(int) $row->catid.' AND showBanner >= 0 ' )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to move a banner
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function saveorder($cid = array(), $order)
	{
		$row =& $this->getTable();
		$groupings = array();

		// update ordering values
		for( $i=0; $i < count($cid); $i++ )
		{
			$row->load( (int) $cid[$i] );
			// track categories
			$groupings[] = $row->catid;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					$this->setError($this->_db->getErrorMsg());
					return false;
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder('catid = '.$this->_db->Quote($group));
		}

		return true;
	}

	/**
	 * Copies one or more banners
	 */
	function copy($cid = array())
	{
		$row =& $this->getTable();

		$n		= count( $cid );

		// update ordering values
		for( $i=0; $i < $n; $i++ )
		{
			$row->load( (int) $cid[$i] );

			$row->bid				= 0;
			$row->name			= 'Copy of ' . $row->name;
			$row->impmade			= 0;
			$row->clicks			= 0;
			$row->showBanner		= 0;
			$row->date			= $this->_db->getNullDate();

			if (!$row->store()) {
				return JError::raiseWarning( $row->getError() );
			}
		}

		return true;
	}

	/**
	 * Method to load banner data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function _loadData()
	{
		if (empty($this->_data))
		{
			// Lets load the banners
			$query = 'SELECT b.*'.
					' FROM #__banner AS b' .
					' WHERE b.bid = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the banner data
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
			$banner = new stdClass();
			$banner->bid					= 0;
			$banner->cid					= 0;
			$banner->type					= null;
			$banner->name					= null;
			$banner->alias					= null;
			$banner->imptotal				= 0;
			$banner->impmade				= 0;
			$banner->clicks					= 0;
			$banner->imageurl				= null;
			$banner->clickurl				= null;
			$banner->date					= null;
			$banner->showBanner				= null;
			$banner->checked_out			= 0;
			$banner->checked_out_time		= 0;
			$banner->editor					= null;
			$banner->custombannercode		= null;
			$banner->catid					= 0;
			$banner->description			= null;
			$banner->sticky					= 0;
			$banner->ordering				= 0;
			$banner->publish_up				= null;
			$banner->publish_down			= null;
			$banner->tags					= null;
			$banner->params					= null;
			$this->_data					= $banner;
			return (boolean) $this->_data;
		}
		return true;
	}
}