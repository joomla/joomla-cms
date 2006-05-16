<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Modules
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_modules', 'manage' )) {
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'admin_html' ) );

$cid 		= JRequest::getVar( 'cid', array(0), 'post', 'array' );
$id 		= JRequest::getVar( 'id', 0, '', 'int' );
$moduleid 	= JRequest::getVar( 'moduleid' );
$module 	= JRequest::getVar( 'module' );

if ($cid[0] == 0 && isset($moduleid) ) {
	$cid[0] = $moduleid;
}

switch ( $task )
{
	case 'copy':
		copyModule( $option, intval( $cid[0] ));
		break;

	case 'new':
		//editModule( $option, 0, $client );
		selectnew();
		break;

	case 'edit':
		if ( $module && $cid[0] == 0 && $id == 0 ) {
			editModule( $option, 0, $module );
		} else {
			editModule( $option, $cid[0]);
		}
		break;

	case 'editA':
		editModule( $option, $id );
		break;

	case 'save':
	case 'apply':
		$cache = & JFactory::getCache();
		$cache->cleanCache( 'com_content' );
		saveModule( $option, $task );
		break;

	case 'remove':
		removeModule( $cid, $option );
		break;

	case 'cancel':
		cancelModule( $option );
		break;

	case 'publish':
	case 'unpublish':
		mosCache::cleanCache( 'com_content' );
		publishModule( $cid, ($task == 'publish'), $option );
		break;

	case 'orderup':
	case 'orderdown':
		orderModule( $cid[0], ($task == 'orderup' ? -1 : 1), $option );
		break;

	case 'accesspublic':
	case 'accessregistered':
	case 'accessspecial':
		accessMenu( $cid[0], $task, $option );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'preview' :
		previewModule($id );
		break;

	default:
		viewModules( $option );
		break;
}

/**
* Compiles a list of installed or defined modules
*/
function viewModules()
{
	global $mainframe;

	/*
	 * Initialize some variables
	 */
	$db		=& $mainframe->getDBO();
	$option = JRequest::getVar('option');
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$filter_order		= $mainframe->getUserStateFromRequest( "$option.filter_order", 		'filter_order', 	'm.position' );
	$filter_order_Dir	= $mainframe->getUserStateFromRequest( "$option.filter_order_Dir",	'filter_order_Dir',	'' );
	$filter_state 		= $mainframe->getUserStateFromRequest( "$option.filter_state", 		'filter_state', 	'' );
	$filter_position 	= $mainframe->getUserStateFromRequest( "$option.filter_position", 	'filter_position', 	0 );
	$filter_type	 	= $mainframe->getUserStateFromRequest( "$option.filter_type", 		'filter_type', 		0 );
	$filter_assigned 	= $mainframe->getUserStateFromRequest( "$option.filter_assigned",	'filter_assigned',	0 );
	$limit 				= $mainframe->getUserStateFromRequest( "limit", 					'limit', 			$mainframe->getCfg('list_limit') );
	$limitstart 		= $mainframe->getUserStateFromRequest( "$option.limitstart", 		'limitstart', 		0 );
	$search 			= $mainframe->getUserStateFromRequest( "$option.search", 			'search', 			'' );
	$search 			= $db->getEscaped( trim( JString::strtolower( $search ) ) );

	$where[] = "m.client_id = ".$client->id;

	$joins[] = 'LEFT JOIN #__users AS u ON u.id = m.checked_out';
	$joins[] = 'LEFT JOIN #__groups AS g ON g.id = m.access';
	$joins[] = 'LEFT JOIN #__modules_menu AS mm ON mm.moduleid = m.id';

	// used by filter
	if ( $filter_assigned ) {
		$joins[] = 'LEFT JOIN #__templates_menu AS t ON t.menuid = m.id';
		$where[] = "t.template = '$filter_assigned'";
	}
	if ( $filter_position ) {
		$where[] = "m.position = '$filter_position'";
	}
	if ( $filter_type ) {
		$where[] = "m.module = '$filter_type'";
	}
	if ( $search ) {
		$where[] = "LOWER( m.title ) LIKE '%$search%'";
	}
	if ( $filter_state ) {
		if ( $filter_state == 'P' ) {
			$where[] = "m.published = 1";
		} else if ($filter_state == 'U' ) {
			$where[] = "m.published = 0";
		}
	}

	$where 		= "\n WHERE " . implode( ' AND ', $where );	$join 		= "\n " . implode( "\n ", $joins );	$orderby 	= "\n ORDER BY $filter_order $filter_order_Dir, m.position ASC";

	// get the total number of records
	$query = "SELECT COUNT(*)"
	. "\n FROM #__modules AS m"
	. $where
	;
	$db->setQuery( $query );
	$total = $db->loadResult();

	jimport('joomla.presentation.pagination');
	$pageNav = new JPagination( $total, $limitstart, $limit );

	$query = "SELECT m.*, u.name AS editor, g.name AS groupname, MIN(mm.menuid) AS pages"
	. "\n FROM #__modules AS m"
	. $join
	. $where
	. "\n GROUP BY m.id"
	. $orderby
	;
	$db->setQuery( $query, $pageNav->limitstart, $pageNav->limit );
	$rows = $db->loadObjectList();
	if ($db->getErrorNum()) {
		echo $db->stderr();
		return false;
	}

	// get list of Positions for dropdown filter
	$query = "SELECT t.position AS value, t.position AS text"
	. "\n FROM #__template_positions as t"
	. "\n LEFT JOIN #__modules AS m ON m.position = t.position"
	. "\n WHERE m.client_id = $client->id"
	. "\n GROUP BY t.position"
	. "\n ORDER BY t.position"
	;
	$positions[] = mosHTML::makeOption( '0', '- '. JText::_( 'Select Position' ) .' -' );
	$db->setQuery( $query );
	$positions = array_merge( $positions, $db->loadObjectList() );
	$lists['position']	= mosHTML::selectList( $positions, 'filter_position', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_position" );

	// get list of Positions for dropdown filter
	$query = "SELECT module AS value, module AS text"
	. "\n FROM #__modules"
	. "\n WHERE client_id = $client->id"
	. "\n GROUP BY module"
	. "\n ORDER BY module"
	;
	$db->setQuery( $query );
	$types[] 		= mosHTML::makeOption( '0', '- '. JText::_( 'Select Type' ) .' -' );
	$types 			= array_merge( $types, $db->loadObjectList() );
	$lists['type']	= mosHTML::selectList( $types, 'filter_type', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_type" );

	// state filter
	$lists['state']	= mosCommonHTML::selectState( $filter_state );

	// template assignment filter
	$query = "SELECT DISTINCT(template) AS text, template AS value" .
			"\nFROM jos_templates_menu" .
			"\nWHERE client_id = " . $client->id;
	$db->setQuery( $query );
	$assigned[]		= mosHTML::makeOption( '0', '- '. JText::_( 'Select Template' ) .' -' );
	$assigned 		= array_merge( $assigned, $db->loadObjectList() );
	$lists['assigned']	= mosHTML::selectList( $assigned, 'filter_assigned', 'class="inputbox" size="1" onchange="this.form.submit()"', 'value', 'text', "$filter_assigned" );

	// table ordering
	if ( $filter_order_Dir == 'DESC' ) {
		$lists['order_Dir'] = 'ASC';
	} else {
		$lists['order_Dir'] = 'DESC';
	}
	$lists['order'] = $filter_order;

	// search filter
	$lists['search']= $search;

	HTML_modules::showModules( $rows, $client, $pageNav, $option, $lists );
}

/**
* Compiles information to add or edit a module
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function copyModule( $option, $uid )
{
	global $database, $my;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$row 	=& JTable::getInstance('module', $database );

	// load the row from the db table
	$row->load( $uid );
	$row->title 		= sprintf( JText::_( 'Copy of' ), $row->title );
	$row->id 			= 0;
	$row->iscore 		= 0;
	$row->published 	= 0;

	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	$row->reorder( "position=".$row->position." AND client_id=".$client->id );

	$query = "SELECT menuid"
	. "\n FROM #__modules_menu"
	. "\n WHERE moduleid = $uid"
	;
	$database->setQuery( $query );
	$rows = $database->loadResultArray();

	foreach($rows as $menuid) {
		$query = "INSERT INTO #__modules_menu"
		. "\n SET moduleid = $row->id, menuid = $menuid"
		;
		$database->setQuery( $query );
		$database->query();
	}

	$msg = sprintf( JText::_( 'Module Copied' ), $row->title );
	josRedirect( 'index2.php?option='. $option .'&client='. $client, $msg );
}

/**
* Saves the module after an edit form submit
*/
function saveModule( $option, $task )
{
	global $database;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$row =& JTable::getInstance('module', $database );

	if (!$row->bind( $_POST, 'selections' )) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->check()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	if (!$row->store()) {
		echo "<script> alert('".$row->getError()."'); window.history.go(-1); </script>\n";
		exit();
	}
	$row->checkin();

	$row->reorder( "position=".$row->position." AND client_id=".$client->id );

	$menus = JRequest::getVar( 'selections', array(), 'post', 'array' );

	// delete old module to menu item associations
	$query = "DELETE FROM #__modules_menu"
	. "\n WHERE moduleid = $row->id"
	;
	$database->setQuery( $query );
	$database->query();

	// check needed to stop a module being assigned to `All`
	// and other menu items resulting in a module being displayed twice
	if ( in_array( '0', $menus ) ) {
		// assign new module to `all` menu item associations
		$query = "INSERT INTO #__modules_menu"
		. "\n SET moduleid = $row->id, menuid = 0"
		;
		$database->setQuery( $query );
		$database->query();
	}
	else
	{
		foreach ($menus as $menuid)
		{
			// this check for the blank spaces in the select box that have been added for cosmetic reasons
			if ( $menuid != "-999" ) {
				// assign new module to menu item associations
				$query = "INSERT INTO #__modules_menu"
				. "\n SET moduleid = $row->id, menuid = $menuid"
				;
				$database->setQuery( $query );
				$database->query();
			}
		}
	}

	switch ( $task ) {
		case 'apply':
        	$msg = sprintf( JText::_( 'Successfully Saved changes to Module' ), $row->title );
			josRedirect( 'index2.php?option='. $option .'&client='. $client .'&task=editA&hidemainmenu=1&id='. $row->id, $msg );
			break;

		case 'save':
		default:
        	$msg = sprintf( JText::_( 'Successfully Saved Module' ), $row->title );
			josRedirect( 'index2.php?option='. $option .'&client='. $client, $msg );
			break;
	}
}

/**
* Compiles information to add or edit a module
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function editModule( $option, $uid, $module=NULL )
{
	global $database, $my, $mainframe;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$lists 	= array();
	$row 	=& JTable::getInstance('module', $database );
	// load the row from the db table
	$row->load( $uid );
	// fail if checked out not by 'me'
	if ($row->isCheckedOut( $my->id )) {
    	$msg = sprintf( JText::_( 'DESCBEINGEDITTED' ), JText::_( 'The module' ), $row->title );
		mosErrorAlert( $msg, "document.location.href='index2.php?option=$option" );
	}

	$row->content = htmlspecialchars( str_replace( '&amp;', '&', $row->content ) );

	if ( $uid ) {
		$row->checkout( $my->id );
	}
	// if a new record we must still prime the JTableModel object with a default
	// position and the order; also add an extra item to the order list to
	// place the 'new' record in last position if desired
	if ($uid == 0) {
		$row->position 	= 'left';
		$row->showtitle = true;
		$row->published = 1;
		//$row->ordering = $l;

		$moduleType 	= JRequest::getVar( 'module' );
		$row->module 	= $moduleType;
	}


	if ( $client->id == '1' ) {
		$where 				= 'client_id = 1';
		$lists['client_id'] = 1;
		$path				= 'mod1_xml';
	} else {
		$where 				= 'client_id = 0';
		$lists['client_id'] = 0;
		$path				= 'mod0_xml';
	}

	$query = "SELECT position, ordering, showtitle, title"
	. "\n FROM #__modules"
	. "\n WHERE $where"
	. "\n ORDER BY ordering"
	;
	$database->setQuery( $query );
	if ( !($orders = $database->loadObjectList()) ) {
		echo $database->stderr();
		return false;
	}

	$query = "SELECT position, description"
	. "\n FROM #__template_positions"
	. "\n WHERE position <> ''"
	. "\n ORDER BY position"
	;
	$database->setQuery( $query );
	// hard code options for now
	$positions = $database->loadObjectList();

	$orders2 	= array();
	$pos 		= array();
	foreach ($positions as $position) {
		$orders2[$position->position] = array();
		$pos[] = mosHTML::makeOption( $position->position, $position->description );
	}

	$l = 0;
	$r = 0;
	for ($i=0, $n=count( $orders ); $i < $n; $i++) {
		$ord = 0;
		if (array_key_exists( $orders[$i]->position, $orders2 )) {
			$ord =count( array_keys( $orders2[$orders[$i]->position] ) ) + 1;
		}

		$orders2[$orders[$i]->position][] = mosHTML::makeOption( $ord, $ord.'::'.addslashes( $orders[$i]->title ) );
	}

	// build the html select list
	$pos_select 		= 'onchange="changeDynaList(\'ordering\',orders,document.adminForm.position.options[document.adminForm.position.selectedIndex].value, originalPos, originalOrder)"';
	$active 			= ( $row->position ? $row->position : 'left' );
	$lists['position'] 	= mosHTML::selectList( $pos, 'position', 'class="inputbox" size="1" '. $pos_select, 'value', 'text', $active );

	// get selected pages for $lists['selections']
	if ( $uid ) {
		$query = "SELECT menuid AS value"
		. "\n FROM #__modules_menu"
		. "\n WHERE moduleid = $row->id"
		;
		$database->setQuery( $query );
		$lookup = $database->loadObjectList();
	} else {
		$lookup = array( mosHTML::makeOption( 0, JText::_( 'All' ) ) );
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
			$lists['access'] 		= mosAdminMenus::Access( $row );
			$lists['selections'] 	= mosAdminMenus::MenuLinks( $lookup, 1, 1 );
		}
		$lists['showtitle'] = mosHTML::yesnoRadioList( 'showtitle', 'class="inputbox"', $row->showtitle );
	}

	// build the html select list for published
	$lists['published'] 			= mosAdminMenus::Published( $row );

	$row->description = '';

    $lang =& $mainframe->getLanguage();
	if ( $client->id != '1' ) {
        $lang->load( trim($row->module), JPATH_SITE );
	} else {
        $lang->load( trim($row->module) );
	}

	// xml file for module
	$xmlfile = JApplicationHelper::getPath( $path, $row->module );

	$data = JApplicationHelper::parseXMLInstallFile($xmlfile);
	foreach($data as $key => $value) {
		$row->$key = $value;
	}

	// get params definitions
	$params = new JParameter( $row->params, $xmlfile, 'module' );

	HTML_modules::editModule( $row, $orders2, $lists, $params, $option, $client );
}

/**
* Displays a list to select the creation of a new module
*/
function selectnew()
{
	global $mainframe;

	// Initialize some variables
	$modules	= array();
	$client		= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	// path to search for modules
	if ($client->id == '1') {
		$path = JPATH_ADMINISTRATOR .'/modules/';
	} else {
		$path = JPATH_ROOT .'/modules/';
	}

	// handling for custom module	$modules[0]->file 		= 'custom.xml';
	$modules[0]->module 	= 'custom';
	$modules[0]->path 		= $path;
	$i = 1;
	jimport('joomla.filesystem.folder');
	$dirs = JFolder::folders( $path );

	foreach ($dirs as $dir)
	{
		$file 			= JFolder::files( $path . $dir, '^([_A-Za-z]*)\.xml$' );
		$files_php[] 	= $file[0];

		$modules[$i]->file 		= $file[0];
		$modules[$i]->module 	= str_replace( '.xml', '', $file[0] );
		$modules[$i]->path 		= $path . $dir;
		$i++;
	}

	ReadModuleXML( $modules, $client );

	// sort array of objects alphabetically by name
	SortArrayObjects( $modules, 'name' );

	HTML_modules::addModule( $modules, $client );
}

/**
* Deletes one or more modules
*
* Also deletes associated entries in the #__module_menu table.
* @param array An array of unique category id numbers
*/
function removeModule( &$cid, $option )
{
	global $database;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	if (count( $cid ) < 1) {
		echo "<script> alert('". JText::_( 'Select a module to delete', true ) ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "SELECT id, module, title, iscore, params"
	. "\n FROM #__modules WHERE id IN ( $cids )"
	;
	$database->setQuery( $query );
	if (!($rows = $database->loadObjectList())) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit;
	}

	$err = array();
	$cid = array();
	foreach ($rows as $row) {
		if ($row->module == '' || $row->iscore == 0) {
			$cid[] = $row->id;
		} else {
			$err[] = $row->title;
		}
		// mod_mainmenu modules only deletable via Menu Manager
		if ( $row->module == 'mod_mainmenu' ) {
			if ( strstr( $row->params, 'mainmenu' ) ) {
				echo "<script> alert('". JText::_( 'WARNMAINMENU', true ) ."'); window.history.go(-1); </script>\n";
				exit;
			}
		}
	}

	if (count( $cid )) {
		$cids = implode( ',', $cid );
		$query = "DELETE FROM #__modules"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
			exit;
		}
		$query = "DELETE FROM #__modules_menu"
		. "\n WHERE moduleid IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!$database->query()) {
			echo "<script> alert('".$database->getErrorMsg()."');</script>\n";
			exit;
		}
		$mod =& JTable::getInstance('module', $database );
		$mod->ordering = 0;
		$mod->reorder( "position='left'" );
		$mod->reorder( "position='right'" );
	}

	if (count( $err )) {
		$cids = addslashes( implode( "', '", $err ) );
		echo "<script>alert('". JText::_( 'Module(s)', true ) .": \'". $cids ."\' ". JText::_( 'WARNMODULES', true ) ."');</script>\n";
	}

	josRedirect( 'index2.php?option='. $option .'&client='. $client->id );
}

/**
* Publishes or Unpublishes one or more modules
* @param array An array of unique record id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function publishModule( $cid=null, $publish=1, $option )
{
	global $database, $my;

	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". JText::_( 'Select a module to', true ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$cids = implode( ',', $cid );

	$query = "UPDATE #__modules"
	. "\n SET published = " . intval( $publish )
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = $my->id ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
		exit();
	}

	if (count( $cid ) == 1) {
		$row =& JTable::getInstance('module', $database );
		$row->checkin( $cid[0] );
	}

	josRedirect( 'index2.php?option='. $option .'&client='. $client->id );
}

/**
* Cancels an edit operation
*/
function cancelModule( $option )
{
	global $database;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$row =& JTable::getInstance('module', $database );
	// ignore array elements
	$row->bind( $_POST, 'selections params' );
	$row->checkin();

	josRedirect( 'index2.php?option='. $option .'&client='. $client->id );
}

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderModule( $uid, $inc, $option )
{
	global $database;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$row =& JTable::getInstance('module', $database );
	$row->load( $uid );

	$row->move( $inc, "position = ".$row->position." AND client_id=".$client->id  );

	josRedirect( 'index2.php?option='. $option .'&client='. $client->id );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $option )
{
	global $database;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	switch ( $access ) {
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

	$row =& JTable::getInstance('module', $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	josRedirect( 'index2.php?option='. $option .'&client='. $client->id );
}

function saveOrder( &$cid )
{
	global $database;

	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	$total		= count( $cid );
	$order 		= JRequest::getVar( 'order', array(0), 'post', 'array' );
	$row 		=& JTable::getInstance('module', $database );
	$conditions = array();

	// update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				echo "<script> alert('".$database->getErrorMsg()."'); window.history.go(-1); </script>\n";
				exit();
			} // if
			// remember to updateOrder this group
			$condition = "position = '$row->position' AND client_id = $row->client_id";
			$found = false;
			foreach ( $conditions as $cond )
				if ($cond[1]==$condition) {
					$found = true;
					break;
				} // if
			if (!$found) $conditions[] = array($row->id, $condition);
		} // if
	} // for

	// execute updateOrder for each group
	foreach ( $conditions as $cond ) {
		$row->load( $cond[0] );
		$row->reorder( $cond[1] );
	} // foreach

	$msg 	= JText::_( 'New ordering saved' );
	josRedirect( 'index2.php?option=com_modules&client='. $client->id, $msg );
} // saveOrder

function previewModule($id )
{
	global $mainframe;
	$mainframe->setPageTitle(JText::_('Module Preview'));

	HTML_modules::previewModule( );
}

function ReadAModuleXML( &$rows )
{
	/*
	 * Initialize some variables
	 */
	$client	= JApplicationHelper::getClientInfo(JRequest::getVar('client', '0', '', 'int'));

	// xml file for module
	$xmlfile = JPATH_ADMINISTRATOR .'/components/com_menus/'. $type .'/'. $type .'.xml';

	$data = JApplicationHelper::parseXMLInstallFile($xmlfile);

	$row[0]	= $data['name'];
	$row[1] = $data['description'];
	$row[2] = $data['group'];

	return $row;
}
function ReadModuleXML( &$rows  )
{
	foreach ($rows as $i => $row)
	{
		if ($row->module == '')
		{
			$rows[$i]->name 	= 'custom';
			$rows[$i]->module 	= 'custom';
			$rows[$i]->descrip 	= 'Custom created module, using Module Manager `New` function';
		}
		else
		{
			$data = JApplicationHelper::parseXMLInstallFile( $row->path.DS.$row->file);

			if ( $data['type'] == 'module' )
			{
				$rows[$i]->name		= $data['name'];
				$rows[$i]->descrip	= $data['description'];
			}
		}	}
}
?>