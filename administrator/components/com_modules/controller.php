<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.controller' );

$client	= JRequest::getVar('client', 0, '', 'int');
if ($client == 1) {
	JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_modules&client_id=0');
	JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_modules&client=1', true );
} else {
	JSubMenuHelper::addEntry(JText::_('Site'), 'index.php?option=com_modules&client_id=0', true );
	JSubMenuHelper::addEntry(JText::_('Administrator'), 'index.php?option=com_modules&client=1');
}

class ModulesController extends JController
{
	/**
	 * Constructor
	 */
	function __construct( $config = array() )
	{
		parent::__construct( $config );

		// Register Extra tasks
		$this->registerTask( 'apply', 			'save' );
		$this->registerTask( 'unpublish', 		'publish' );
		$this->registerTask( 'orderup', 		'reorder' );
		$this->registerTask( 'orderdown', 		'reorder' );
		$this->registerTask( 'accesspublic', 	'access' );
		$this->registerTask( 'accessregistered','access' );
		$this->registerTask( 'accessspecial',	'access' );
	}

	/**
	 * Compiles a list of installed or defined modules
	 */
	function view()
	{
		global $mainframe;

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$option	= 'com_modules';

		$filter_order		= $mainframe->getUserStateFromRequest( $option.'filter_order',		'filter_order',		'm.position',	'cmd' );
		$filter_order_Dir	= $mainframe->getUserStateFromRequest( $option.'filter_order_Dir',	'filter_order_Dir',	'',				'word' );
		$filter_state		= $mainframe->getUserStateFromRequest( $option.'filter_state',		'filter_state',		'',				'word' );
		$filter_position	= $mainframe->getUserStateFromRequest( $option.'filter_position',	'filter_position',	'',				'cmd' );
		$filter_type		= $mainframe->getUserStateFromRequest( $option.'filter_type',		'filter_type',		'',				'cmd' );
		$filter_assigned	= $mainframe->getUserStateFromRequest( $option.'filter_assigned',	'filter_assigned',	'',				'cmd' );
		$search				= $mainframe->getUserStateFromRequest( $option.'search',			'search',			'',				'string' );
		$search				= JString::strtolower( $search );

		$limit		= $mainframe->getUserStateFromRequest( 'global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int' );
		$limitstart	= $mainframe->getUserStateFromRequest( $option.'limitstart', 'limitstart', 0, 'int' );

		$where[] = 'm.client_id = '.(int) $client->id;

		$joins[] = 'LEFT JOIN #__users AS u ON u.id = m.checked_out';
		$joins[] = 'LEFT JOIN #__groups AS g ON g.id = m.access';
		$joins[] = 'LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id';

		// used by filter
		if ( $filter_assigned ) {
			$joins[] = 'LEFT JOIN #__templates_menu AS t ON t.menuid = mm.menuid';
			$where[] = 't.template = '.$db->Quote($filter_assigned);
		}
		if ( $filter_position ) {
			$where[] = 'm.position = '.$db->Quote($filter_position);
		}
		if ( $filter_type ) {
			$where[] = 'm.module = '.$db->Quote($filter_type);
		}
		if ( $search ) {
			$where[] = 'LOWER( m.title ) LIKE '.$db->Quote('%'.$search.'%');
		}
		if ( $filter_state ) {
			if ( $filter_state == 'P' ) {
				$where[] = 'm.published = 1';
			} else if ($filter_state == 'U' ) {
				$where[] = 'm.published = 0';
			}
		}

		$where 		= ' WHERE ' . implode( ' AND ', $where );
		$join 		= ' ' . implode( ' ', $joins );
		$orderby 	= ' ORDER BY '. $filter_order .' '. $filter_order_Dir .', m.ordering ASC';

		// get the total number of records
		$query = 'SELECT COUNT(DISTINCT m.id)'
		. ' FROM #__modules AS m'
		. $join
		. $where
		;
		$db->setQuery( $query );
		$total = $db->loadResult();

		jimport('joomla.html.pagination');
		$pageNav = new JPagination( $total, $limitstart, $limit );

		$query = 'SELECT m.*, u.name AS editor, g.name AS groupname, MIN(mm.menuid) AS pages'
		. ' FROM #__modules AS m'
		. $join
		. $where
		. ' GROUP BY m.id'
		. $orderby
		;
		$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
		$rows = $db->loadObjectList();
		if ($db->getErrorNum()) {
			echo $db->stderr();
			return false;
		}

		// get list of Positions for dropdown filter
		$query = 'SELECT m.position AS value, m.position AS text'
		. ' FROM #__modules as m'
		. ' WHERE m.client_id = '.(int) $client->id
		. ' GROUP BY m.position'
		. ' ORDER BY m.position'
		;
		$positions[] = JHTML::_('select.option',  '0', '- '. JText::_( 'Select Position' ) .' -' );
		$db->setQuery( $query );
		$positions = array_merge( $positions, $db->loadObjectList() );
		$lists['position']	= JHTML::_('select.genericlist',   $positions, 'filter_position', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_position" );

		// get list of Positions for dropdown filter
		$query = 'SELECT module AS value, module AS text'
		. ' FROM #__modules'
		. ' WHERE client_id = '.(int) $client->id
		. ' GROUP BY module'
		. ' ORDER BY module'
		;
		$db->setQuery( $query );
		$types[] 		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select Type' ) .' -' );
		$types 			= array_merge( $types, $db->loadObjectList() );
		$lists['type']	= JHTML::_('select.genericlist',   $types, 'filter_type', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_type" );

		// state filter
		$lists['state']	= JHTML::_('grid.state',  $filter_state );

		// template assignment filter
		$query = 'SELECT DISTINCT(template) AS text, template AS value'.
				' FROM #__templates_menu' .
				' WHERE client_id = '.(int) $client->id;
		$db->setQuery( $query );
		$assigned[]		= JHTML::_('select.option',  '0', '- '. JText::_( 'Select Template' ) .' -' );
		$assigned 		= array_merge( $assigned, $db->loadObjectList() );
		$lists['assigned']	= JHTML::_('select.genericlist',   $assigned, 'filter_assigned', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_assigned" );

		// table ordering
		$lists['order_Dir']	= $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$lists['search']= $search;

		require_once( JApplicationHelper::getPath( 'admin_html' ) );
		HTML_modules::view( $rows, $client, $pageNav, $lists );
	}

	/**
	* Compiles information to add or edit a module
	* @param string The current GET/POST option
	* @param integer The unique id of the record to edit
	*/
	function copy()
	{
		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$n		= count( $cid );

		if ($n == 0) {
			return JError::raiseWarning( 500, JText::_( 'No items selected' ) );
		}

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

			$row->reorder( 'position='.$db->Quote( $row->position ).' AND client_id='.(int) $client->id );

			$query = 'SELECT menuid'
			. ' FROM #__modules_menu'
			. ' WHERE moduleid = '.(int) $cid[0]
			;
			$db->setQuery( $query );
			$rows = $db->loadResultArray();

			foreach ($rows as $menuid) {
				$tuples[] = '('.(int) $row->id.','.(int) $menuid.')';
			}
		}

		if (!empty( $tuples ))
		{
			// Module-Menu Mapping: Do it in one query
			$query = 'INSERT INTO #__modules_menu (moduleid,menuid) VALUES '.implode( ',', $tuples );
			$db->setQuery( $query );
			if (!$db->query()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
		}

		$msg = JText::sprintf( 'Items Copied', $n );
		$this->setRedirect( 'index.php?option=com_modules&client='. $client->id, $msg );
	}

	/**
	 * Saves the module after an edit form submit
	 */
	function save()
	{
		global $mainframe;

		$cache = & JFactory::getCache();
		$cache->clean( 'com_content' );

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$post	= JRequest::get( 'post' );
		// fix up special html fields
		$post['content']   = JRequest::getVar( 'content', '', 'post', 'string', JREQUEST_ALLOWRAW );
		$post['client_id'] = $client->id;

		$row =& JTable::getInstance('module');

		if (!$row->bind( $post, 'selections' )) {
			return JError::raiseWarning( 500, $row->getError() );
		}

		if (!$row->check()) {
			return JError::raiseWarning( 500, $row->getError() );
		}

		// if new item, order last in appropriate group
		if (!$row->id) {
			$where = 'position='.$db->Quote( $row->position ).' AND client_id='.(int) $client->id ;
			$row->ordering = $row->getNextOrder( $where );
		}

		if (!$row->store()) {
			return JError::raiseWarning( 500, $row->getError() );
		}
		$row->checkin();

		$menus = JRequest::getVar( 'menus', '', 'post', 'word' );
		$selections = JRequest::getVar( 'selections', array(), 'post', 'array' );
		JArrayHelper::toInteger($selections);

		// delete old module to menu item associations
		$query = 'DELETE FROM #__modules_menu'
		. ' WHERE moduleid = '.(int) $row->id
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $row->getError() );
		}

		// check needed to stop a module being assigned to `All`
		// and other menu items resulting in a module being displayed twice
		if ( $menus == 'all' ) {
			// assign new module to `all` menu item associations
			$query = 'INSERT INTO #__modules_menu'
			. ' SET moduleid = '.(int) $row->id.' , menuid = 0'
			;
			$db->setQuery( $query );
			if (!$db->query()) {
				return JError::raiseWarning( 500, $row->getError() );
			}
		}
		else
		{
			foreach ($selections as $menuid)
			{
				// this check for the blank spaces in the select box that have been added for cosmetic reasons
				if ( (int) $menuid >= 0 ) {
					// assign new module to menu item associations
					$query = 'INSERT INTO #__modules_menu'
					. ' SET moduleid = '.(int) $row->id .', menuid = '.(int) $menuid
					;
					$db->setQuery( $query );
					if (!$db->query()) {
						return JError::raiseWarning( 500, $row->getError() );
					}
				}
			}
		}

		$this->setMessage( JText::_( 'Item saved' ) );
		switch ($this->getTask())
		{
			case 'apply':
				$this->setRedirect( 'index.php?option=com_modules&client='. $client->id .'&task=edit&id='. $row->id );
				break;
		}
	}

	/**
	* Compiles information to add or edit a module
	* @param string The current GET/POST option
	* @param integer The unique id of the record to edit
	*/
	function edit( )
	{
		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();

		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$module = JRequest::getVar( 'module', '', '', 'cmd' );
		$id 	= JRequest::getVar( 'id', 0, 'method', 'int' );
		$cid 	= JRequest::getVar( 'cid', array( $id ), 'method', 'array' );
		JArrayHelper::toInteger($cid, array(0));

		$model	= &$this->getModel('module');
		$model->setState( 'id',			$cid[0] );
		$model->setState( 'clientId',	$client->id );

		$lists 	= array();
		$row 	=& JTable::getInstance('module');
		// load the row from the db table
		$row->load( (int) $cid[0] );
		// fail if checked out not by 'me'
		if ($row->isCheckedOut( $user->get('id') )) {
			$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );
			return JError::raiseWarning( 500, JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'The module' ), $row->title ) );
		}

		$row->content = htmlspecialchars( str_replace( '&amp;', '&', $row->content ) );

		if ( $cid[0] ) {
			$row->checkout( $user->get('id') );
		}
		// if a new record we must still prime the JTableModel object with a default
		// position and the order; also add an extra item to the order list to
		// place the 'new' record in last position if desired
		if ($cid[0] == 0) {
			$row->position 	= 'left';
			$row->showtitle = true;
			$row->published = 1;
			//$row->ordering = $l;

			$row->module 	= $module;
		}

		if ($client->id == 1)
		{
			$where 				= 'client_id = 1';
			$lists['client_id'] = 1;
			$path				= 'mod1_xml';
		}
		else
		{
			$where 				= 'client_id = 0';
			$lists['client_id'] = 0;
			$path				= 'mod0_xml';
		}

		$query = 'SELECT position, ordering, showtitle, title'
		. ' FROM #__modules'
		. ' WHERE '. $where
		. ' ORDER BY ordering'
		;
		$db->setQuery( $query );
		if ( !($orders = $db->loadObjectList()) ) {
			echo $db->stderr();
			return false;
		}

		$orders2 	= array();

		$l = 0;
		$r = 0;
		for ($i=0, $n=count( $orders ); $i < $n; $i++) {
			$ord = 0;
			if (array_key_exists( $orders[$i]->position, $orders2 )) {
				$ord =count( array_keys( $orders2[$orders[$i]->position] ) ) + 1;
			}

			$orders2[$orders[$i]->position][] = JHTML::_('select.option',  $ord, $ord.'::'.addslashes( $orders[$i]->title ) );
		}

		// get selected pages for $lists['selections']
		if ( $cid[0] ) {
			$query = 'SELECT menuid AS value'
			. ' FROM #__modules_menu'
			. ' WHERE moduleid = '.(int) $row->id
			;
			$db->setQuery( $query );
			$lookup = $db->loadObjectList();
			if (empty( $lookup )) {
				$lookup = array( JHTML::_('select.option',  '-1' ) );
				$row->pages = 'none';
			} elseif (count($lookup) == 1 && $lookup[0]->value == 0) {
				$row->pages = 'all';
			} else {
				$row->pages = null;
			}
		} else {
			$lookup = array( JHTML::_('select.option',  0, JText::_( 'All' ) ) );
			$row->pages = 'all';
		}

		if ( $row->access == 99 || $row->client_id == 1 || $lists['client_id'] ) {
			$lists['access'] 			= 'Administrator';
			$lists['showtitle'] 		= 'N/A <input type="hidden" name="showtitle" value="1" />';
			$lists['selections'] 		= 'N/A';
		} else {
			if ( $client->id == '1' ) {
				$lists['access'] 		= 'N/A';
				$lists['selections'] 	= 'N/A';
			} else {
				$lists['access'] 		= JHTML::_('list.accesslevel',  $row );

				$selections				= JHTML::_('menu.linkoptions');
				$lists['selections']	= JHTML::_('select.genericlist',   $selections, 'selections[]', 'class="inputbox" size="15" multiple="multiple"', 'value', 'text', $lookup, 'selections' );
			}
			$lists['showtitle'] = JHTML::_('select.booleanlist',  'showtitle', 'class="inputbox"', $row->showtitle );
		}

		// build the html select list for published
		$lists['published'] = JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $row->published );

		$row->description = '';

		$lang =& JFactory::getLanguage();
		if ( $client->id != '1' ) {
			$lang->load( trim($row->module), JPATH_SITE );
		} else {
			$lang->load( trim($row->module) );
		}

		// xml file for module
		if ($row->module == 'custom') {
			$xmlfile = JApplicationHelper::getPath( $path, 'mod_custom' );
		} else {
			$xmlfile = JApplicationHelper::getPath( $path, $row->module );
		}

		$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
		if ($data)
		{
			foreach($data as $key => $value) {
				$row->$key = $value;
			}
		}

		// get params definitions
		$params = new JParameter( $row->params, $xmlfile, 'module' );

		require_once( JApplicationHelper::getPath( 'admin_html' ) );
		HTML_modules::edit( $model, $row, $orders2, $lists, $params, $client );
	}

	/**
	* Displays a list to select the creation of a new module
	*/
	function add()
	{
		global $mainframe;

		// Initialize some variables
		$modules	= array();
		$client		=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

		// path to search for modules
		if ($client->id == '1') {
			$path		= JPATH_ADMINISTRATOR.DS.'modules';
			$langbase	= JPATH_ADMINISTRATOR;
		} else {
			$path		= JPATH_ROOT.DS.'modules';
			$langbase	= JPATH_ROOT;
		}

		jimport('joomla.filesystem.folder');
		$dirs = JFolder::folders( $path );
		$lang =& JFactory::getLanguage();

		foreach ($dirs as $dir)
		{
			if (substr( $dir, 0, 4 ) == 'mod_')
			{
				$files 				= JFolder::files( $path.DS.$dir, '^([_A-Za-z0-9]*)\.xml$' );
				$module				= new stdClass;
				$module->file 		= $files[0];
				$module->module 	= str_replace( '.xml', '', $files[0] );
				$module->path 		= $path.DS.$dir;
				$modules[]			= $module;

				$lang->load( $module->module, $langbase );
			}
		}

		require_once( JPATH_COMPONENT.DS.'helpers'.DS.'xml.php' );
		ModulesHelperXML::parseXMLModuleFile( $modules, $client );

		// sort array of objects alphabetically by name
		JArrayHelper::sortObjects( $modules, 'name' );

		require_once( JApplicationHelper::getPath( 'admin_html' ) );
		HTML_modules::add( $modules, $client );
	}

	/**
	* Deletes one or more modules
	*
	* Also deletes associated entries in the #__module_menu table.
	* @param array An array of unique category id numbers
	*/
	function remove()
	{
		global $mainframe;

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger( $cid );

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$cids = implode( ',', $cid );

		$query = 'SELECT id, module, title, iscore, params'
		. ' FROM #__modules WHERE id IN ('.$cids.')'
		;
		$db->setQuery( $query );
		if (!($rows = $db->loadObjectList())) {
			return JError::raiseError( 500, $db->getErrorMsg() );
		}

		// remove mappings first (lest we leave orphans)
		$query = 'DELETE FROM #__modules_menu'
			. ' WHERE moduleid IN ( '.$cids.' )'
			;
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseError( 500, $db->getErrorMsg() );
		}
		// remove module
		$query = 'DELETE FROM #__modules'
			. ' WHERE id IN ('.$cids.')'
			;
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseError( 500, $db->getErrorMsg() );
		}

		$this->setMessage( JText::sprintf( 'Items removed', count( $cid ) ) );
	}

	/**
	* Publishes or Unpublishes one or more modules
	*/
	function publish()
	{
		global $mainframe;

		// Initialize some variables
		$db 	=& JFactory::getDBO();
		$user 	=& JFactory::getUser();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$cache = & JFactory::getCache();
		$cache->clean( 'com_content' );

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$task	= $this->getTask();
		$publish	= ($task == 'publish');

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$cids = implode( ',', $cid );

		$query = 'UPDATE #__modules'
		. ' SET published = ' . intval( $publish )
		. ' WHERE id IN ( '.$cids.' )'
		. ' AND ( checked_out = 0 OR ( checked_out = '.(int) $user->get('id').' ) )'
		;
		$db->setQuery( $query );
		if (!$db->query()) {
			return JError::raiseWarning( 500, $db->getErrorMsg() );
		}

		if (count( $cid ) == 1) {
			$row =& JTable::getInstance('module');
			$row->checkin( $cid[0] );
		}
	}

	/**
	 * Cancels an edit operation
	 */
	function cancel()
	{
		global $mainframe;

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$row =& JTable::getInstance('module');
		// ignore array elements
		$row->bind(JRequest::get('post'), 'selections params' );
		$row->checkin();
	}

	/**
	 * Moves the order of a record
	 */
	function reorder()
	{
		global $mainframe;

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$task	= $this->getTask();
		$inc	= ($task == 'orderup' ? -1 : 1);

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$row =& JTable::getInstance('module');
		$row->load( (int) $cid[0] );

		$row->move( $inc, 'position = '.$db->Quote( $row->position ).' AND client_id='.(int) $client->id  );
	}

	/**
	 * Changes the access level of a record
	 */
	function access()
	{
		global $mainframe;

		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$task	= JRequest::getCmd( 'task' );

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		switch ( $task )
		{
			case 'accesspublic':
				$access = 0;
				break;

			case 'accessregistered':
				$access = 1;
				break;

			case 'accessspecial':
				$access = 2;
				break;
		}

		$row =& JTable::getInstance('module');
		$row->load( (int) $cid[0] );
		$row->access = $access;

		if ( !$row->check() ) {
			JError::raiseWarning( 500, $row->getError() );
		}
		if ( !$row->store() ) {
			JError::raiseWarning( 500, $row->getError() );
		}
	}

	/**
	 * Saves the orders of the supplied list
	 */
	function saveOrder()
	{
		// Initialize some variables
		$db		=& JFactory::getDBO();
		$client	=& JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));
		$this->setRedirect( 'index.php?option=com_modules&client='.$client->id );

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (empty( $cid )) {
			return JError::raiseWarning( 500, 'No items selected' );
		}

		$total		= count( $cid );
		$row 		=& JTable::getInstance('module');
		$groupings = array();

		$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
		JArrayHelper::toInteger($order);

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
					return JError::raiseWarning( 500, $db->getErrorMsg() );
				}
			}
		}

		// execute updateOrder for each parent group
		$groupings = array_unique( $groupings );
		foreach ($groupings as $group){
			$row->reorder('position = '.$db->Quote($group).' AND client_id = '.(int) $client->id);
		}

		$this->setMessage (JText::_( 'New ordering saved' ));
	}

	function preview()
	{
		$document =& JFactory::getDocument();
		$document->setTitle(JText::_('Module Preview'));

		require_once( JApplicationHelper::getPath( 'admin_html' ) );
		HTML_modules::preview( );
	}
}
