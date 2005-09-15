<?php
/**
* @version $Id: admin.mambots.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Mambots
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!$acl->acl_check( 'com_mambots', 'manage', 'users', $my->usertype )) {
		mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@class' );

/**
 * @package Mambo
 * @subpackage Mambots
 */
class mambotTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function mambotTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		$this->setAccessControl( 'com_mambots', 'manage' );

		$this->registerTask( 'editA', 			'edit' );
		$this->registerTask( 'new', 			'edit' );
		$this->registerTask( 'apply', 			'save' );
		$this->registerTask( 'refreshFiles', 	'editXML' );
		$this->registerTask( 'installUpload', 	'install' );
		$this->registerTask( 'installFromDir', 	'install' );
	}

	/**
	 * Generic install page
	 */
	function installOptions() {
		mosFS::load( '@admin_html' );
		mambotScreens::installOptions();
	}

	/**
	 * Installs mambot
	 */
	function install() {
		$userfile = mosGetParam( $_FILES, 'userfile', null );

		$installer = mosMambotFactory::createInstaller();
		if ($this->getTask() == 'installUpload') {
			if (!$installer->uploadArchive( $userfile )) {
				$msg = $installer->error();
			}
			if (!$installer->extractArchive()) {
				$msg = $installer->error();
			}
		} else {
			$installer->installDir( $userfile );
		}
		if (!$installer->install()) {
			$installer->cleanupInstall();
			$msg = $installer->error();
		}
		$installer->cleanupInstall();

		mosFS::load( '@admin_html' );

		mambotScreens::installDone( $installer );
	}

	/**
	 * Options for packaging
	 */
	function packageOptions() {
		global $_LANG;

		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );

		$row = new mosMambot( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( 'index2.php?option=com_mambots', $_LANG->_( 'Mambot not found' ) );
			return false;
		}

		mosFS::load( '@admin_html' );

		mambotScreens::packageOptions( $row );
	}

	/**
	 * Build the package
	 */
	function package() {
		global $_LANG;

		$redirect = 'index2.php?option=com_mambots';

		$compress 	= mosGetParam( $_POST, 'compress', 'gz' );
		$element 	= mosGetParam( $_POST, 'element', '' );
		$fileName 	= mosGetParam( $_POST, 'filename', $element );

		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
		$row = new mosMambot( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Mambot not found' ) );
			return false;
		}

		mosFS::load( 'includes/mambo.files.xml.php' );
		$basePath = mosMambot::getBasePath( 0 ) . $element . DIRECTORY_SEPARATOR;

		$xmlFile = $basePath . 'mambotDetails.xml';

		if (!mosXMLFS::read( $xmlFile, 'mambot', $vars )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Failed to open XML file' ) );
			return false;
		}

		$archiveName = mosFS::getNativePath( dirname( __FILE__ ) . '/files/' . $fileName, false );

		$files = mosGetParam( $vars, 'siteFiles', array() );

		foreach ($files as $k => $v) {
			$files[$k] = $basePath . $files[$k]['file'];
		}
		$archive = mosFS::archive( $archiveName, $files, $compress, '', $basePath, true, false );

		$msg = $_LANG->_( 'Package Made' );
		$this->setRedirect( $redirect . '&task=listFiles', $msg );
	}

	/**
	 * List files in /files directory
	 */
	function listFiles() {
		$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$files = mosFS::listFiles( $path, '\.(tar|gz)$' );
		foreach ($files as $i=>$file) {
			$files[$i] = array(
				'file' 	=> $file,
				'fsize' => number_format( filesize( $path . $file ) ),
				'mtime' => date ( 'd-m-Y H:i:s', filemtime( $path . $file ) ),
				'perms' => mosFS::getPermissions( $path . $file )
			);
		}

		mosFS::load( '@admin_html' );

		mambotScreens::listFiles( $files );
	}

	/**
	 * Delete a set of language files
	 */
	function deleteFile() {
		global $_LANG;

		$cid = mosGetParam( $_POST, 'cid', array() );
		$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$redirect = 'index2.php?option=com_mambots&task=listFiles';
		if (count( $cid ) < 1) {
			$msg = $_LANG->_( 'errorNoFile' );
			$this->setRedirect( $redirect, $msg );
			return false;
		}

		foreach ($cid as $file) {
			mosFS::deleteFile( $path . $file );
		}

		$msg = $_LANG->_( 'Deleted' );
		$this->setRedirect( $redirect, $msg );
	}

	/**
	 * Edit XML Setup File
	 */
	function editXML() {
		global $_LANG, $mainframe;

		$mainframe->set('disableMenu', true);

		mosFS::load( '/includes/mambo.files.xml.php' );

		$xmlVars = mosGetParam( $_POST, 'vars', array() );
		varsStripSlashes( $xmlVars );
		$cid = mosGetParam( $_POST, 'cid', array(0) );

		$row = new mosMambot( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( 'index2.php?option=com_mambots', $_LANG->_( 'Mambot not found' ) );
			return false;
		}

		$basePath = mosFS::getNativePath( $mainframe->getCfg( 'absolute_path' ) . '/mambots/' . $row->folder );

		switch ($this->getTask()) {
			case 'refreshFiles':
				// mambot files
				$exclude = mosGetParam( $_POST, 'exclude', '' );
				$element = mosGetParam( $_POST, 'element', array(0) );
				$xmlVars = mosGetParam( $_POST, 'vars', array() );
				varsStripSlashes( $xmlVars );

				$dir = $basePath;
				$files = mosFS::listFiles( $dir, '^' . $row->element, true, true );
				$temp = array();
				foreach ($files as $i => $v) {
					if (empty( $exclude ) || ($exclude && !eregi( $exclude, $v ) )) {
						$file = str_replace( '\\', '/', str_replace( $dir . DIRECTORY_SEPARATOR, '', $v ) );
						$temp[] = array(
							'file' => $file,
							'special' =>  intval( $file == $row->element.'.php' )
						);
					}
				}
				$xmlVars['siteFiles'] = $temp;
				break;

			default:
				$cid = mosGetParam( $_POST, 'cid', array(0) );
				$element = $cid[0];

				$file = mosFS::getNativePath( $basePath . $row->element .'.xml', false );

				if (file_exists( $file )) {
					mosXMLFS::read( $file, 'mambot', $xmlVars );
				} else {
					$xmlVars = array();
				}
				break;
		}

		if (count( @$xmlVars['siteFiles'] ) > 0 && !isset( $xmlVars['siteFiles'][0]['file'] )) {
			foreach ($xmlVars['siteFiles'] as $i => $v) {
				$xmlVars['siteFiles'][$i] = array(
					'file' => $v,
					'special' =>  intval( $v == $row->element.'.php' )
				);
			}
		}

		if (!isset( $xmlVars['params'] )) {
			$xmlVars['params'] = '<params />';
		}

		mosFS::load( '@admin_html' );

		mambotScreens::editXML( $xmlVars, $row );
	}

	/**
	 * Saves the xml setup file
	 */
	function saveXML() {
		global $_LANG, $mainframe;

		mosFS::load( '/includes/mambo.files.xml.php' );

		$element	= mosGetParam( $_POST, 'element', '' );
		$vars		= mosGetParam( $_POST, 'vars', array() );

		$meta 	= mosGetParam( $vars, 'meta', array() );
		$group 	= mosGetParam( $meta, 'group', '' );

		$basePath = mosFS::getNativePath( $mainframe->getCfg( 'absolute_path' ) . '/mambots/' . $group );

		$file = $basePath . $element .'.xml';
		if (!file_exists( $file )) {
			$msg = $_LANG->_( 'File not found' );
			$this->setRedirect( 'index2.php?option=com_mambots', $msg );
			return false;
		}

		$msg = mosXMLFS::write( 'mambot', $vars, $file )
			? $_LANG->_( 'File Saved' )
			: $_LANG->_( 'Error saving file' );

		$this->setRedirect( 'index2.php?option=com_mambots', $msg );
	}

	/**
	 * Show mambot list
	 */
	function view() {
		global $database, $option, $mainframe;
		global $_LANG;

		$client = 0;
		mosFS::load( 'includes/mambo.files.xml.php' );

		// form data
		$limit 			= $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $GLOBALS['mosConfig_list_limit'] );
		$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );

		$folder			= $mainframe->getUserStateFromRequest( "folder{$option}{$client}", 'folder', null );
		$filter_state	= $mainframe->getUserStateFromRequest( "filter_state{$option}{$client}", 'filter_state', null );
		$filter_access	= $mainframe->getUserStateFromRequest( "filter_access{$option}{$client}", 'filter_access', null );
		$search 		= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
		$search 		= trim( strtolower( $search ) );

		$orderCol	= mosGetParam( $_REQUEST, 'orderCol' );
		$orderDirn	= mosGetParam( $_REQUEST, 'orderDirn', 0 );
		if ( empty( $orderCol ) ) {
			$orderCol = 'folder';
		}

		$vars = array(
			'folder' 	=> $folder,
			'orderCol' 	=> $orderCol,
			'orderDirn' => $orderDirn,
		);

		$options = array(
			'folder' 		=> $folder,
			'state' 		=> $filter_state,
			'access' 		=> $filter_access,
			'search' 		=> $search,
			'orderby' 		=> $database->getEscaped( $orderCol ) . ' ' . ($orderDirn ? 'DESC' : 'ASC'),
			'limit'			=> $limit,
			'limitstart'	=> $limitstart
		);
		$mambots = new mosMambotViews( $database );
		$total 	= $mambots->getView( 'items', $options, true );
		$rows 	= $mambots->getView( 'items', $options );

		// load navigation files
		mosFS::load( '@pageNavigationAdmin' );
		$pageNav = new mosPageNav( $total, $limitstart, $limit );

		$lists['folders'] = $mambots->getView( 'folders' );

		// pull name and description from mambot xml
		$i = 0;
		foreach ( $rows as $row ) {
			$xml = ReadMambotXML( $row, $client );
			$rows[$i]->descrip 	= $xml[0];
			$i++;
		}

		// get list of State for dropdown filter
		$lists['published'] = array(
			patHTML::makeOption( '1', $_LANG->_( 'Published' ) ),
			patHTML::makeOption( '0', $_LANG->_( 'Unpublished' ) ),
		);
		patHTML::selectArray( $lists['published'], $filter_state );

		// get list of Access for dropdown filter
		$lists['access'] = array(
			patHTML::makeOption( '0', $_LANG->_( 'Public' ) ),
			patHTML::makeOption( '1', $_LANG->_( 'Registered' ) ),
			patHTML::makeOption( '2', $_LANG->_( 'Special' ) ),
		);
		patHTML::selectArray( $lists['access'], $filter_access );

		$search = stripslashes( $search );

		mosFS::load( '@admin_html' );

		mambotScreens::view( $rows, $lists, $search, $pageNav, $vars );
	}

	/**
	* Compiles information to add or edit a module
	* @param string The current GET/POST option
	* @param integer The unique id of the record to edit
	*/
	function edit() {
		global $database, $my, $mainframe, $task, $option;
		global $mosConfig_absolute_path;
	  	global $_LANG;

		$uid	= mosGetParam( $_REQUEST, 'cid', null );
		mosArrayToInts( $uid, 0 );
		$id 	= intval( $uid[0] );

		if ( !$uid && $task <> 'new' ) {
			mosErrorAlert( $_LANG->_( 'Select an item to Edit' ) );
		}

		$lists 	= array();
		$row 	= new mosMambot($database);

		// load the row from the db table
		$row->load( $uid );

		// fail if checked out not by 'me'
		if ($row->isCheckedOut()) {
			mosErrorAlert( $_LANG->_( 'The module' ) .' '. $row->title .' '. $_LANG->_( 'descBeingEditted' ) );
		}

		// get list of groups
		if ($row->access == 99 || $row->client_id == 1) {
			$lists['access'] = $_LANG->_( 'Administrator' ) .'<input type="hidden" name="access" value="99" />';
		} else {
			// build the html select list for the group access
			$lists['access'] = mosAdminMenus::Access( $row );
		}

		if ($uid) {
			$row->checkout( $my->id );

			if ( $row->ordering > -10000 && $row->ordering < 10000 ) {
				// build the html select list for ordering
				$query = "SELECT ordering AS value, name AS text"
				. "\n FROM #__mambots"
				. "\n WHERE folder = '$row->folder'"
				. "\n AND published > 0"
				. "\n AND ordering > -10000"
				. "\n AND ordering < 10000"
				. "\n ORDER BY ordering"
				;
				$order = mosGetOrderingList( $query );
				$lists['ordering'] = mosHTML::selectList( $order, 'ordering', 'class="inputbox" size="1"', 'value', 'text', intval( $row->ordering ) );
			} else {
				$lists['ordering'] = '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $_LANG->_( 'This mambot cannot be reordered' );
			}
			$lists['folder'] = '<input type="hidden" name="folder" value="'. $row->folder .'" />'. $row->folder;

			// XML library
			mosFS::load( 'includes/domit/xml_domit_lite_include.php' );
			// xml file for module
			$xmlfile = $mosConfig_absolute_path . '/mambots/' .$row->folder . '/' . $row->element .'.xml';
			$xmlDoc = new DOMIT_Lite_Document();
			$xmlDoc->resolveErrors( true );
			if ($xmlDoc->loadXML( $xmlfile, false, true )) {
				$root = &$xmlDoc->documentElement;
				if ($root->getTagName() == 'mosinstall' && $root->getAttribute( 'type' ) == 'mambot' ) {
					$element = &$root->getElementsByPath( 'description', 1 );
					$row->description = $element ? trim( $element->getText() ) : '';

				}
			}
		} else {
			$row->folder 		= '';
			$row->ordering 		= 999;
			$row->published 	= 1;
			$row->description 	= '';

			$folders = mosReadDirectory( $mosConfig_absolute_path . '/mambots/' );
			$folders2 = array();
			foreach ($folders as $folder) {
				if (is_dir( $mosConfig_absolute_path . '/mambots/' . $folder ) && ( $folder <> 'CVS' ) ) {
					$folders2[] = mosHTML::makeOption( $folder );
				}
			}
			$lists['folder'] 	= mosHTML::selectList( $folders2, 'folder', 'class="inputbox" size="1"', 'value', 'text', null );
			$lists['ordering'] 	= '<input type="hidden" name="ordering" value="'. $row->ordering .'" />'. $_LANG->_( 'DESCNEWITEMSDEFAULTLASTPLACE' );
		}

		$lists['published'] = mosHTML::yesnoRadioList( 'published', 'class="inputbox"', $row->published );

		// get params definitions
		$params = new mosParameters( $row->params, $mainframe->getPath( 'bot_xml', $row->folder.'/'.$row->element ), 'mambot' );

	  	$mainframe->set('disableMenu', true);

		mosFS::load( '@admin_html' );

		HTML_modules::editMambot( $row, $lists, $params, $option );
	}

	/**
	* Saves the mambot after an edit form submit
	*/
	function save() {
		global $database, $mosConfig_absolute_path;
		global $_LANG;

		$params = mosGetParam( $_POST, 'params', '' );
		if (is_array( $params )) {
			$txt = array();
			foreach ($params as $k=>$v) {
				$txt[] = "$k=$v";
			}

	 		$_POST['params'] = mosParameters::textareaHandling( $txt );
		}

		$row = new mosMambot( $database );

		if (!$row->bind( $_POST )) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->check()) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}
		$row->checkin();

		$row->updateOrder( "folder = '$row->folder' AND ordering > -10000 AND ordering < 10000" );

		$task = mosGetParam( $_REQUEST, 'task', '' );

		switch ( $task ) {
			case 'apply':
				$msg = $_LANG->_( 'Successfully Saved changes to Mambot' ) .': '. $row->name;
				mosRedirect( 'index2.php?option=com_mambots&task=edit&cid='. $row->id, $msg );

			case 'save':
			default:
				$msg = $_LANG->_( 'Successfully Saved Mambot' ) .': '. $row->name;
				mosRedirect( 'index2.php?option=com_mambots', $msg );
				break;
		}
	}

	/**
	 * Remove the selected mambot
	 */
	function remove() {
		global $database, $mainframe;
		global $_LANG;

		$cid 		= mosGetParam( $_REQUEST, 'cid', array(0) );
		$client 	= mosGetParam( $_REQUEST, 'client', '' );
		$client_id 	= $mainframe->getClientID( $client );

		$installer = mosMambotFactory::createInstaller();
		if ($installer->uninstall( $cid[0], $client_id )) {
			$msg = $_LANG->_( 'Success' );
		} else {
			$msg = $installer->error();
		}

		mosFS::load( '@admin_html' );
		mambotScreens::installDone( $installer );
	}

	/**
	* Cancels an edit operation
	*/
	function cancel() {
		global $database;

		$row = new mosMambot( $database );
		$row->bind( $_POST );
		$row->checkin();

		mosRedirect( 'index2.php?option=com_mambots' );
	}
}

$tasker = new mambotTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();


$cid	= mosGetParam( $_REQUEST, 'cid', null );
mosArrayToInts( $cid, 0 );

switch ( $task ) {
	case 'publish':
	case 'unpublish':
		publishMambot( $cid, ($task == 'publish') );
		break;

	case 'orderup':
	case 'orderdown':
		orderMambot( $cid[0], ($task == 'orderup' ? -1 : 1) );
		break;

	case 'accesspublic':
	case 'accessregistered':
	case 'accessspecial':
		accessMenu( $cid[0], $task );
		break;

	case 'saveorder':
		saveOrder( $cid );
		break;

	case 'checkin':
		checkin( $cid[0] );
		break;
}

/**
* Publishes or Unpublishes one or more modules
* @param array An array of unique category id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function publishMambot( $cid=null, $publish=1 ) {
	global $database, $my;
  	global $_LANG;

	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		mosErrorAlert( $_LANG->_( 'Select a mambot to' ) .' '. $action );
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__mambots"
	. "\n SET published = '$publish'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row = new mosMambot( $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( 'index2.php?option=com_mambots' );
}

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderMambot( $uid, $inc ) {
	global $database;

	$row = new mosMambot( $database );
	$row->load( $uid );
	$row->move( $inc, "folder='$row->folder' AND ordering > -10000 AND ordering < 10000"  );

	mosRedirect( 'index2.php?option=com_mambots' );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access ) {
	global $database;

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

	$row = new mosMambot( $database );
	$row->load( $uid );
	$row->access = $access;

	if ( !$row->check() ) {
		return $row->getError();
	}
	if ( !$row->store() ) {
		return $row->getError();
	}

	mosRedirect( 'index2.php?option=com_mambots' );
}

function saveOrder( &$cid ) {
	global $database;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row 		= new mosMambot( $database );
	$conditions = array();

	// update ordering values
	for ( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				mosErrorAlert( $row->getError() );
			} // if
			// remember to updateOrder this group
			$condition = "folder = '$row->folder' AND ordering > -10000 AND ordering < 10000";
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
		$row->updateOrder( $cond[1] );
	} // foreach

	$msg 	= 'New ordering saved';
	mosRedirect( 'index2.php?option=com_mambots', $msg );
} // saveOrder


function ReadMambotXML( $mambot ) {
	global $mosConfig_absolute_path;

	// XML library
	mosFS::load( 'includes/domit/xml_domit_lite_include.php' );

	$path = $mosConfig_absolute_path .'/mambots/'. $mambot->folder .'/'. $mambot->element .'.xml';

	// xml file for module
	$xmlfile = $path;

	$xml[0] = 'No description';
	// check to see if xml file exists
	if ( file_exists( $xmlfile ) ) {
		$xmlDoc = new DOMIT_Lite_Document();
		$xmlDoc->resolveErrors( true );

		if ($xmlDoc->loadXML( $xmlfile, false, true )) {
			$root = &$xmlDoc->documentElement;

			if ( ( $root->getTagName() == 'mosinstall' ) && ( $root->getAttribute( 'type' ) == 'mambot' ) ) {
				// Module Description
				$element 	= &$root->getElementsByPath( 'description', 1 );
				$descrip 	= $element ? trim( $element->getText() ) : '';
			}
		}

		$xml[0] = addslashes( $descrip );
	}

	return $xml;
}

function checkin( $id ) {
	global $database;
	global $_LANG;

	$row = new mosMambot( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_mambots', $msg );
}
?>