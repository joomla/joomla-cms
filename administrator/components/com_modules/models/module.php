<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.model' );

/**
 * @package		Joomla
 * @subpackage	Modules
 */
class ModulesModelModule extends JModel
{
	var $_xml;

	/**
	 * module id
	 *
	 * @var int
	 */
	var $_id = null;

	/**
	 * module data
	 *
	 * @var array
	 */
	var $_data = null;

	/**
	 * client object
	 *
	 * @var object
	 */
	var $_client = null;

	/**
	 * Constructor
	 *
	 * @since 1.5
	 */
	function __construct()
	{
		parent::__construct();

		$id	= JRequest::getVar('id',null);
		$array = JRequest::getVar('cid', array($id), '', 'array');
		$edit	= JRequest::getVar('edit',true);
		if($edit)
			$this->setId((int)$array[0]);

		$this->_client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
	}

	/**
	 * Method to set the module identifier
	 *
	 * @access	public
	 * @param	int module identifier
	 */
	function setId($id)
	{
		// Set module id and wipe data
		$this->_id		= $id;
		$this->_data	= null;
	}

	/**
	 * Method to get a module
	 *
	 * @since 1.6
	 */
	function &getData()
	{
		// Load the data
		if (!$this->_loadData())
			$this->_initData();

		return $this->_data;
	}

	/**
	 * Method to get the client object
	 *
	 * @since 1.6
	 */
	function &getClient()
	{
		return $this->_client;
	}

	function &getModule()
	{
		return $this->getData();
	}

	function &_getXML()
	{
		if (!$this->_xml)
		{
			$clientId	= $this->getState( 'clientId', 0 );
			$path		= ($clientId == 1) ? 'mod1_xml' : 'mod0_xml';
			$module		= &$this->getData();

			if ($module->module == 'custom') {
				$xmlpath = JApplicationHelper::getPath( $path, 'mod_custom' );
			} else {
				$xmlpath = JApplicationHelper::getPath( $path, $module->module );
			}

			if (file_exists($xmlpath))
			{
				$xml =& JFactory::getXMLParser('Simple');
				if ($xml->loadFile($xmlpath)) {
					$this->_xml = &$xml;
				}
			}
		}
		return $this->_xml;
	}

	function &getParams()
	{
		// Get the state parameters
		$module	=& $this->getData();
		$params	= new JParameter($module->params);

		if ($xml =& $this->_getXML())
		{
			if ($ps = & $xml->document->params) {
				foreach ($ps as $p)
				{
					$params->setXML( $p );
				}
			}
		}
		return $params;
	}

	function getPositions()
	{
		jimport('joomla.filesystem.folder');

		// template assignment filter
		$query = 'SELECT DISTINCT(template) AS text, template AS value'.
				' FROM #__templates_menu' .
				' WHERE client_id = '.(int) $this->_client->id;
		$this->_db->setQuery( $query );
		$templates = $this->_db->loadObjectList();

		// Get a list of all module positions as set in the database
		$query = 'SELECT DISTINCT(position)'.
				' FROM #__modules' .
				' WHERE client_id = '.(int) $this->_client->id;
		$this->_db->setQuery( $query );
		$positions = $this->_db->loadResultArray();
		$positions = (is_array($positions)) ? $positions : array();

		// Get a list of all template xml files for a given application

		// Get the xml parser first
		for ($i = 0, $n = count($templates); $i < $n; $i++ )
		{
			$path = $client->path.DS.'templates'.DS.$templates[$i]->value;

			$xml =& JFactory::getXMLParser('Simple');
			if ($xml->loadFile($path.DS.'templateDetails.xml'))
			{
				$p =& $xml->document->getElementByPath('positions');
				if ($p INSTANCEOF JSimpleXMLElement && count($p->children()))
				{
					foreach ($p->children() as $child)
					{
						if (!in_array($child->data(), $positions)) {
							$positions[] = $child->data();
						}
					}
				}
			}
		}

		$positions = array_unique($positions);
		sort($positions);

		return $positions;
	}

	/**
	 * Tests if module is checked out
	 *
	 * @access	public
	 * @param	int	A user id
	 * @return	boolean	True if checked out
	 * @since	1.6
	 */
	function isCheckedOut( $uid=0 )
	{
		if ($this->_id)
		{
			$module =& JTable::getInstance('module');
			if (!$module->load($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
			return $module->isCheckedOut($uid);
		}
	}

	/**
	 * Method to checkin/unlock the module
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function checkin()
	{
		if ($this->_id)
		{
			$module =& JTable::getInstance('module');
			if(! $module->checkin($this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}
		return false;
	}

	/**
	 * Method to checkout/lock the module
	 *
	 * @access	public
	 * @param	int	$uid	User ID of the user checking out
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function checkout($uid = null)
	{
		if ($this->_id)
		{
			// Make sure we have a user id to checkout the article with
			if (is_null($uid)) {
				$user	=& JFactory::getUser();
				$uid	= $user->get('id');
			}
			// Lets get to it and checkout the thing...
			$module =& JTable::getInstance('module');
			if(!$module->checkout($uid, $this->_id)) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}

			return true;
		}
		return false;
	}

	/**
	 * Method to store the module
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function store($data)
	{
		$row =& JTable::getInstance('module');

		// Bind the form fields to the web link table
		if (!$row->bind($data)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// Make sure the data table is valid
		if (!$row->check()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// if new item, order last in appropriate group
		if (!$row->id) {
			$where = 'position='.$this->_db->Quote( $row->position ).' AND client_id='.(int) $this->_client->id ;
			$row->ordering = $row->getNextOrder( $where );
		}

		// Store the web link table to the database
		if (!$row->store()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		$menus = JRequest::getVar( 'menus', '', 'post', 'word' );
		$selections = JRequest::getVar( 'selections', array(), 'post', 'array' );
		JArrayHelper::toInteger($selections);

		// delete old module to menu item associations
		$query = 'DELETE FROM #__modules_menu'
		. ' WHERE moduleid = '.(int) $row->id
		;
		$this->_db->setQuery( $query );
		if (!$this->_db->query()) {
			return JError::raiseWarning( 500, $row->getError() );
		}

		// check needed to stop a module being assigned to `All`
		// and other menu items resulting in a module being displayed twice
		if ( $menus == 'all' ) {
			// assign new module to `all` menu item associations
			$query = 'INSERT INTO #__modules_menu'
			. ' SET moduleid = '.(int) $row->id.' , menuid = 0'
			;
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
		}
		else
		{
			$sign = ($menus == 'deselect') ? -1 : 1;
			foreach ($selections as $menuid)
			{
				/*
				 * This checks for the blank spaces in the select box that have
				 * been added for cosmetic reasons.
				 */
				$menuid = (int) $menuid;
				if ($menuid >= 0) {
					// assign new module to menu item associations
					$query = 'INSERT INTO #__modules_menu'
					. ' SET moduleid = ' . (int) $row->id . ', menuid = ' . ($sign * $menuid)
					;
					$this->_db->setQuery($query);
					if (!$this->_db->query()) {
						return JError::raiseWarning(500, $row->getError());
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to remove a module
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
			// remove mappings first (lest we leave orphans)
			$query = 'DELETE FROM #__modules_menu'
				. ' WHERE moduleid IN ( '.$cids.' )'
				;
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
			}
			// remove module
			$query = 'DELETE FROM #__modules'
				. ' WHERE id IN ( '.$cids.' )'
				;
			$this->_db->setQuery( $query );
			if(!$this->_db->query()) {
				$this->setError($this->_db->getErrorMsg());
				return false;
			}
		}

		return true;
	}

	/**
	 * Method to (un)publish a module
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

			$query = 'UPDATE #__modules'
				. ' SET published = '.(int) $publish
				. ' WHERE id IN ( '.$cids.' )'
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
	 * Method to set the access
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function setAccess($cid = array(), $access = 0)
	{
		if (count( $cid ))
		{
			$user 	=& JFactory::getUser();

			JArrayHelper::toInteger($cid);
			$cids = implode( ',', $cid );

			$query = 'UPDATE #__modules'
				. ' SET access = '.(int) $access
				. ' WHERE id IN ( '.$cids.' )'
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
	 * Method to copy modules
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function copy($cid = array())
	{
		$row 	=& JTable::getInstance('module');
		$tuples	= array();

		foreach ($cid as $id)
		{
			// load the row from the db table
			$row->load( (int) $id );
			$row->title 		= JText::sprintf( 'Copy of', $row->title );
			$row->id 			= 0;
			$row->iscore 		= 0;
			$row->published 	= 0;

			if (!$row->check()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
			if (!$row->store()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
			$row->checkin();

			$row->reorder( 'position='.$this->_db->Quote( $row->position ).' AND client_id='.(int) $client->id );

			$query = 'SELECT menuid'
			. ' FROM #__modules_menu'
			. ' WHERE moduleid = '.(int) $cid[0]
			;
			$this->_db->setQuery( $query );
			$rows = $this->_db->loadResultArray();

			foreach ($rows as $menuid) {
				$tuples[] = '('.(int) $row->id.','.(int) $menuid.')';
			}
		}

		if (!empty( $tuples ))
		{
			// Module-Menu Mapping: Do it in one query
			$query = 'INSERT INTO #__modules_menu (moduleid,menuid) VALUES '.implode( ',', $tuples );
			$this->_db->setQuery( $query );
			if (!$this->_db->query()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
		}

		return true;
	}

	/**
	 * Method to move a module
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function move($direction)
	{
		$row =& JTable::getInstance('module');
		if (!$row->load($this->_id)) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		if (!$row->move( $direction, 'position = '.$this->_db->Quote( $row->position ).' AND client_id='.(int) $client->id )) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		return true;
	}

	/**
	 * Method to move a module
	 *
	 * @access	public
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function saveorder($cid = array(), $order)
	{
		$total		= count( $cid );

		$row 		=& JTable::getInstance('module');
		$groupings = array();

		// update ordering values
		for ($i = 0; $i < $total; $i++)
		{
			$row->load( (int) $cid[$i] );
			// track postions
			$groupings[] = $row->position;

			if ($row->ordering != $order[$i])
			{
				$row->ordering = $order[$i];
				if (!$row->store()) {
					return JError::raiseWarning( 500, $this->_db->getErrorMsg() );
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder('position = '.$this->_db->Quote($group).' AND client_id = '.(int) $client->id);
		}

		return true;
	}

	/**
	 * Method to load module data
	 *
	 * @access	private
	 * @return	boolean	True on success
	 * @since	1.6
	 */
	function _loadData()
	{
		// Lets load the content if it doesn't already exist
		if (empty($this->_data))
		{
			$query = 'SELECT m.*'.
					' FROM #__modules AS m' .
					' WHERE m.id = '.(int) $this->_id;
			$this->_db->setQuery($query);
			$this->_data = $this->_db->loadObject();
			return (boolean) $this->_data;
		}
		return true;
	}

	/**
	 * Method to initialise the module data
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
			$module = new stdClass();
			$module->id					= 0;
			$module->title				= null;
			$module->content			= null;
			$module->ordering			= 0;
			$module->position			= null;
			$module->checked_out		= 0;
			$module->checked_out_time	= 0;
			$module->published			= 0;
			$module->module				= null;
			$module->numnews			= 0;
			$module->access				= 0;
			$module->showtitle			= 0;
			$module->params				= null;
			$module->iscore				= 0;
			$module->client_id			= 0;
			$module->control			= null;
			$this->_data					= $module;
			return (boolean) $this->_data;
		}
		return true;
	}
}
