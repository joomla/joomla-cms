<?php
/**
* @version $Id: admin.modules.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Modules
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!($acl->acl_check( 'com_modules', 'manage', 'users', $my->usertype, 'modules', 'all' )
 | $acl->acl_check( 'com_modules', 'install', 'users', $my->usertype, 'modules', 'all' ))) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@class' );

/**
 * @package Mambo
 * @subpackage Modules
 */
class moduleTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function moduleTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		$this->setAccessControl( 'com_modules', 'manage' );

		$this->registerTask( 'new', 'selectnew' );
		$this->registerTask( 'apply', 'save' );
		$this->registerTask( 'refreshFiles', 'editXML' );

		$this->registerTask( 'installUpload', 'install' );
		$this->registerTask( 'installFromDir', 'install' );
	}

	/**
	 * Options for creating a new component
	 */
	function createOptions() {
		mosFS::load( '@admin_html' );
		moduleScreens::createOptions();
	}

	/**
	 * Create a new component
	 */
	function create() {
		global $_LANG, $mainframe, $database;

		$name		= mosGetParam( $_POST, 'modname', '' );
		$client_id	= mosGetParam( $_POST, 'client_id', 0 );
		$meta		= mosGetParam( $_POST, 'meta', array() );
		$options	= mosGetParam( $_POST, 'options', array() );

		if (empty( $name )) {
			$this->setRedirect( 'index2.php?option=com_modules&task=createOptions', $_LANG->_( 'Please enter a name' ) );
			return false;
		}

		$baseName = preg_replace( '#[^_A-Za-z0-9]#', '', $name );
		$element = 'mod_' . $baseName;
		$meta['name'] = $baseName;
		$meta['basename'] = $baseName;
		$meta['classname'] = ucfirst( $baseName );
		$meta['client'] = $mainframe->getClientName( $client_id );

		mosFS::load( 'administrator/components/com_modules/modules.builder.php' );

		$basePath = $mainframe->getBasePath( $client_id ) . 'modules';
		$basePath = mosFS::getNativePath( $basePath );
		$baseTmplPath = mosFS::getNativePath( $basePath . 'tmpl' );

		// create the base path
		if (!mosFS::autocreatePath( $basePath )) {
			$this->setRedirect( 'index2.php?option=com_components&task=createOptions', $_LANG->_( 'Failed to create base folder' ) );
			return false;
		}
		// create the tmpl path
		if (!mosFS::autocreatePath( $baseTmplPath )) {
			$this->setRedirect( 'index2.php?option=com_components&task=createOptions', $_LANG->_( 'Failed to create tmpl folder' ) );
			return false;
		}

		// create the template object
		$tmpl =& moduleCreator::createTemplate( array() );
		$tmpl->readTemplatesFromInput( 'php.html' );
		$tmpl->readTemplatesFromInput( 'html.html' );
		$tmpl->readTemplatesFromInput( 'xml.html' );
		$tmpl->addGlobalVars( $meta );
		$tmpl->addVars( 'options', $options );

	// ---- PHP FILES ----

		// main html.php file
		$file = $basePath . $element . '.php';
		$buffer = moduleCreator::phpMain( $tmpl );
		mosFS::write( $file, $buffer );

	// ---- PHP HTML TEMPLATE FILES ----

		// class php file
		$file = $baseTmplPath . $element . '.html';
		$buffer = moduleCreator::htmlMain( $tmpl );
		mosFS::write( $file, $buffer );

	// ---- XML FILES ----

		// mosinstall
		$file = $basePath . $element . '.xml';
		$buffer = moduleCreator::xmlMain( $tmpl );
		mosFS::write( $file, $buffer );

	// ---- MODULE TABLE ----
		$msg = '';

		$query = 'SELECT id' .
				' FROM #__modules' .
				' WHERE module = ' . $database->Quote( $element ) .
				' AND client_id = ' . intval( $client_id )
				;
		$database->setQuery( $query );
		if (!$database->loadResult()) {
			$row = new mosModule( $database );
			$row->title		= $element;
			$row->ordering	= 0;
			$row->position	= 'left';
			$row->showtitle	= 1;
			$row->iscore	= 0;
			$row->access	= ($client_id == '1' ? 99 : 0);
			$row->client_id	= $client_id;
			$row->module = $element;

			if ($row->store()) {
				$query = 'INSERT INTO #__modules_menu VALUES (' . $database->Quote( $row->id ) . ', 0)';
				$database->setQuery( $query );
				if(!$database->query()) {
					$msg = $row->getError();
				}
			} else {
				$msg = $row->getError();
			}
		}
		$this->setRedirect( 'index2.php?option=com_modules&client=' . $client_id, $msg );
	}

	/**
	 * Manage modules
	 */
	function manage() {
		global $database;

		$modules = new mosModuleViews( $database );
		$rows = $modules->getView( 'noncore' );

		mosFS::load( '@admin_html' );
		moduleScreens::manage( $rows );
	}

	/**
	 * Generic install page
	 */
	function installOptions() {
		mosFS::load( '@admin_html' );
		moduleScreens::installOptions();
	}

	/**
	 * Installs a module
	 */
	function install() {
		$userfile = mosGetParam( $_FILES, 'userfile', null );

		$installer = mosModuleFactory::createInstaller();
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

		moduleScreens::installDone( $installer );
	}

	/**
	 * Options for packaging
	 */
	function packageOptions() {
		global $_LANG;

		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );

		$row = new mosModule( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( 'index2.php?option=com_modules', $_LANG->_( 'Module not found' ) );
			return false;
		}

		mosFS::load( '@admin_html' );

		moduleScreens::packageOptions( $row );
	}

	/**
	 * Build the package
	 */
	function package() {
		global $_LANG, $mainframe;

		$redirect = 'index2.php?option=com_modules';

		$compress = mosGetParam( $_POST, 'compress', 'gz' );
		$fileName = mosGetParam( $_POST, 'filename', 'noname' );

		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
		$row = new mosModule( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Module not found' ) );
			return false;
		}

		mosFS::load( 'includes/mambo.files.xml.php' );
		$basePath = mosModule::getBasePath( $row->client_id );

		$xmlFile = $basePath . $row->module . '.xml';

		if (!mosXMLFS::read( $xmlFile, 'module', $vars )) {
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

		moduleScreens::listFiles( $files );
	}

	/**
	 * Delete a set of language files
	 */
	function deleteFile() {
		global $_LANG;

		$cid 	= mosGetParam( $_POST, 'cid', array() );
		$path 	= mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$redirect = 'index2.php?option=com_modules&task=listFiles';
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

		mosFS::load( 'includes/mambo.files.xml.php' );

		$xmlVars 	= mosGetParam( $_POST, 'vars', array() );
		varsStripSlashes( $xmlVars );
		$cid 		= mosGetParam( $_POST, 'cid', array(0) );

		$row = new mosModule( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( 'index2.php?option=com_modules', $_LANG->_( 'Module not found' ) );
			return false;
		}

		$basePath = mosModule::getBasePath( $row->client_id );

		switch ( $this->getTask() ) {
			case 'refreshFiles':
				// module files
				$exclude = mosGetParam( $_POST, 'exclude', '' );
				$xmlVars = mosGetParam( $_POST, 'vars', array() );
				varsStripSlashes( $xmlVars );

				$dir 	= $basePath;
				$files 	= mosFS::listFiles( $dir, '^' . $row->module, true, true );
				$temp 	= array();

				foreach ( $files as $i => $v ) {
					if (empty( $exclude ) || ($exclude && !eregi( $exclude, $v ) )) {
						$file = str_replace( '\\', '/', str_replace( $dir . DIRECTORY_SEPARATOR, '', $v ) );
						$temp[] = array(
							'file' 		=> $file,
							'special' 	=>  intval( $file == $row->module.'.php' )
						);
					}
				}
				$xmlVars['siteFiles'] = $temp;
				break;

			default:
				$cid 		= mosGetParam( $_POST, 'cid', array(0) );

				$file = mosFS::getNativePath( $basePath . $row->module .'.xml', false );

				if (file_exists( $file )) {
					mosXMLFS::read( $file, 'module', $xmlVars );
				} else {
					$xmlVars = array();
				}
				break;
		}

		if (count( @$xmlVars['siteFiles'] ) > 0 && !isset( $xmlVars['siteFiles'][0]['file'] )) {
			foreach ($xmlVars['siteFiles'] as $i => $v) {
				$xmlVars['siteFiles'][$i] = array(
					'file' 		=> $v,
					'special' 	=>  intval( $v == $row->module.'.php' )
				);
			}
		}

		if (!isset( $xmlVars['params'] )) {
			$xmlVars['params'] = '<params />';
		}

		$mainframe->set('disableMenu', true);

		mosFS::load( '@admin_html' );

		moduleScreens::editXML( $xmlVars, $row );
	}

	/**
	 * Saves the xml setup file
	 */
	function saveXML() {
		global $_LANG;

		mosFS::load( 'includes/mambo.files.xml.php' );

		$cid 		= mosGetParam( $_POST, 'cid', array(0) );

		$row = new mosModule( $GLOBALS['database'] );
		if (!$row->load( intval( $cid[0] ) )) {
			$this->setRedirect( 'index2.php?option=com_modules&task=manage', $_LANG->_( 'Module not found' ) );
			return false;
		}

		$vars		= mosGetParam( $_POST, 'vars', array() );

		$basePath 	= mosModule::getBasePath( $row->client_id );
		$file 		= $basePath . $row->module .'.xml';

		if (!file_exists( $file )) {
			$msg = $_LANG->_( 'File not found' );
			$this->setRedirect( 'index2.php?option=com_modules&task=manage'. $row->client_id, $msg );
			return false;
		}

		$msg = ( mosXMLFS::write( 'module', $vars, $file ) ? $_LANG->_( 'File Saved' ) : $_LANG->_( 'Error saving file' ) );

		$this->setRedirect( 'index2.php?option=com_modules&task=manage', $msg );
	}

	/**
	 * Show module list
	 */
	function view() {
		global $database, $option, $mainframe;
		global $_LANG;

		// form data
		$client			= mosGetParam( $_REQUEST, 'client', '' );
		$client 		= mosMainFrame::getClientID( $client );
		$client_name	= mosMainFrame::getClientName( $client );

		$limit 			= $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $GLOBALS['mosConfig_list_limit'] );
		$limitstart 	= $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );

		$filter_position	= $mainframe->getUserStateFromRequest( "filter_position{$option}{$client}", 'filter_position', '' );
		$filter_module		= $mainframe->getUserStateFromRequest( "filter_type{$option}{$client}", 'filter_type', '' );
		$filter_state		= $mainframe->getUserStateFromRequest( "filter_state{$option}{$client}", 'filter_state', NULL );
		$filter_access		= $mainframe->getUserStateFromRequest( "filter_access{$option}{$client}", 'filter_access', NULL );
		$search 			= $mainframe->getUserStateFromRequest( "search{$option}", 'search', '' );
		$search 		= trim( strtolower( $search ) );

		$orderCol	= mosGetParam( $_REQUEST, 'orderCol' , 'position');
		$orderDirn	= mosGetParam( $_REQUEST, 'orderDirn', 0 );

		$vars = array (
			'client_id' 	=> $client,
			'client_name' 	=> $client_name,
			'orderCol' 		=> $orderCol,
			'orderDirn' 	=> $orderDirn,
		);

		$options = array (
			'client'		=> $client,
			'position'		=> $filter_position,
			'module'		=> $filter_module,
			'state'			=> $filter_state,
			'access'		=> $filter_access,
			'search'		=> $search,
			'orderby'		=> $database->getEscaped( $orderCol ) . ' ' . ($orderDirn ? 'DESC' : 'ASC'),
			'limit'			=> $limit,
			'limitstart'	=> $limitstart
		);
		$modules 	= new mosModuleViews( $database );
		$total 		= $modules->getView( 'items', $options, true );
		$rows 		= $modules->getView( 'items', $options );

		// load navigation files
		mosFS::load( '@pageNavigationAdmin' );
		$pageNav = new mosPageNav( $total, $limitstart, $limit );

		// get list of State for dropdown filter
		$lists['state'] = array(
			patHTML::makeOption( '1', $_LANG->_( 'Published' ) ),
			patHTML::makeOption( '0', $_LANG->_( 'Unpublished' ) ),
		);
		patHTML::selectArray( $lists['state'], $filter_state );

		// get list of Access for dropdown filter
		$lists['access'] = array(
			patHTML::makeOption( '0', $_LANG->_( 'Public' ) ),
			patHTML::makeOption( '1', $_LANG->_( 'Registered' ) ),
			patHTML::makeOption( '2', $_LANG->_( 'Special' ) ),
		);
		patHTML::selectArray( $lists['access'], $filter_access );

		// positions
		$options = array(
			'client' => $client
		);

		$lists['positions'] = $modules->getView( 'positions', $options );
		patHTML::selectArray( $lists['positions'], $filter_position );

		$lists['modules'] = $modules->getView( 'modules', $options );
		patHTML::selectArray( $lists['modules'], $filter_module );

		ReadModuleXML( $rows, $client );

		$search = stripslashes( $search );

		mosFS::load( '@admin_html' );

		moduleScreens::view( $rows, $lists, $search, $pageNav, $vars );
	}

	/**
	 * Edit the module
	 */
	function edit() {
		global $database, $my, $mainframe;
		global $_LANG;

		$cid 	= mosGetParam( $_REQUEST, 'cid', 0 );
		if ( is_array( $cid )) {
			$id = intval( $cid[0] );
		} else {
		    $id = $cid;
		}
		$client	= mosGetParam( $_REQUEST, 'client', 0 );

		$lists = array();

		$row = new mosModule( $database );
		// load the row from the db table
		$row->load( $id );
		// fail if checked out not by 'me'
		if ($row->isCheckedOut()) {
			mosErrorAlert( $_LANG->_( 'The module' ) .' '. $row->title .' '. $_LANG->_( 'descBeingEditted' ) );
		}

		if ($id) {
			// existing record
			$row->checkout( $my->id );
		} else {
			// new record
			$row->position 	= 'left';
			$row->showtitle = true;
			$row->published = 1;
			$row->client_id = $client;
		}

		mosMakeHtmlSafe( $row );

		// if a module name is found in referencing url this indicates this is a `created` module - via the `New` button
		$module 			= mosGetParam( $_REQUEST, 'module', '' );
		if ($module) {
			$row->module 	= $module;
		}

		$query = "SELECT position, ordering, showtitle, title"
		. "\n FROM #__modules"
		. "\n WHERE client_id = ". $database->Quote( $row->client_id )
		. "\n ORDER BY ordering"
		;
		$database->setQuery( $query );
		if (!($orders = $database->loadObjectList())) {
			mosErrorAlert( $database->stderr() );
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

		for ( $i=0, $n=count( $orders ); $i < $n; $i++ ) {
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
		if ($id) {
			$query = "SELECT menuid AS value"
			. "\n FROM #__modules_menu"
			. "\n WHERE moduleid = '$row->id'"
			;
			$database->setQuery( $query );
			$lookup = $database->loadObjectList();
		} else {
			$lookup = array( mosHTML::makeOption( 0, $_LANG->_( 'All' ) ) );
		}

		if ($row->access == 99 || $row->client_id == 1) {
			$lists['access'] 			= 'Administrator<input type="hidden" name="access" value="99" />';
			$lists['showtitle'] 		= 'N/A <input type="hidden" name="showtitle" value="1" />';
			$lists['selections'] 		= 'N/A';
		} else {
			if ($client == 'admin') {
				$lists['access'] 		= 'N/A';
				$lists['selections'] 	= 'N/A';
			} else {
				$lists['access'] 		= mosAdminMenus::Access( $row );
				mosFS::load( '@class', 'com_menus' );
				$lists['selections'] 	= mosMenuFactory::buildMenuLinks( $lookup, 1, 0 );
			}
			$lists['showtitle'] = mosHTML::yesnoRadioList( 'showtitle', 'class="inputbox"', $row->showtitle );
		}

		// build the html select list for published
		$lists['published'] 			= mosAdminMenus::Published( $row );

		$row->description = '';
		// XML library
		mosFS::load( '@domit' );

		// xml file for module
		if ($row->module && ($row->module <> 'custom')) {
			$xmlfile = $mainframe->getPath( 'mod' . $row->client_id . '_xml', $row->module );
		} else {
		// special case for custom/new modules
			if ($row->id && strstr( $row->params, 'rssurl=' )) {
			// backward compatability for custom modules used to create rss feeds
				$xmlfile = $mainframe->getPath( 'mod' . $row->client_id . '_xml', 'mod_rss' );
			} else {
				$xmlfile = $mainframe->getPath( 'mod' . $row->client_id . '_xml', 'custom' );
			}
		}

		$xmlDoc = new DOMIT_Lite_Document();
		$xmlDoc->resolveErrors( true );
		if ($xmlDoc->loadXML( $xmlfile, false, true) ) {
			$root = &$xmlDoc->documentElement;

			if ($root->getTagName() == 'mosinstall' && $root->getAttribute( 'type' ) == 'module') {
				$element = &$root->getElementsByPath( 'description', 1 );
				$row->description = $element ? trim( $element->getText() ) : '';
			}
		}

		// get params definitions
		$params = new mosParameters( $row->params, $xmlfile, 'module' );

		$mainframe->set('disableMenu', true);

		mosFS::load( '@admin_html' );

		HTML_modules::editModule( $row, $orders2, $lists, $params, 'com_modules' );
	}

	/**
	* Saves the module after an edit form submit
	*/
	function save() {
		global $database;
		global $_LANG;

		$client	= mosGetParam( $_REQUEST, 'client', 0 );
		$task	= mosGetParam( $_REQUEST, 'task', '' );

		$params = mosGetParam( $_POST, 'params', '' );
		if (is_array( $params )) {
			$txt = array();
			foreach ($params as $k=>$v) {
				$txt[] = "$k=$v";
			}
			$_POST['params'] = mosParameters::textareaHandling( $txt );
		}

		$row = new mosModule( $database );
		if (!$row->bind( $_POST, 'selections' )) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->check()) {
			mosErrorAlert( $row->getError() );
		}
		if (!$row->store()) {
			mosErrorAlert( $row->getError() );
		}
		$row->checkin();
		if ($client == 'admin') {
			$where = "client_id='1'";
		} else {
			$where = "client_id='0'";
		}
		$row->updateOrder( "position = '$row->position' AND ( $where )" );

		$menus = mosGetParam( $_POST, 'selections', array() );

		$query = "DELETE FROM #__modules_menu"
		. "\n WHERE moduleid = '$row->id'"
		;
		$database->setQuery( $query );
		$database->query();

		foreach ( $menus as $menuid ) {
			// this check for the blank spaces in the select box that have been added for cosmetic reasons
			if ($menuid <> '-999') {
				$query = "INSERT INTO #__modules_menu"
				. "\n SET moduleid = '$row->id', menuid = '$menuid'";
				$database->setQuery( $query );
				$database->query();
			}
		}

		//clear module cache
		$cache = mosFactory::getCache($row->module);
		$cache->clean($row->module);

		switch ( $task ) {
			case 'apply':
				$msg = $_LANG->_( 'Successfully Saved changes to Module' ) .': '. $row->title;
				mosRedirect( 'index2.php?option=com_modules&client='. $client .'&task=edit&cid='. $row->id, $msg );
				break;

			case 'save':
			default:
				$msg = $_LANG->_( 'Successfully Saved Module' ) .': '. $row->title;
				mosRedirect( 'index2.php?option=com_modules&client='. $client, $msg );
				break;
		}
	}

	/**
	* Displays a list to select the creation of a new module
	*/
	function selectnew() {
		global $mosConfig_absolute_path, $mainframe;

		$client	= mosGetParam( $_REQUEST, 'client', '' );

		// path to search for modules
		if ($client) {
			$dir = $mosConfig_absolute_path .'/administrator/modules/';
		} else {
			$dir = $mosConfig_absolute_path .'/modules/';
		}

		if (is_dir( $dir )) {
			// generate list of module files
			$files_php = mosReadDirectory( $dir, "\.xml$" );


			// custom file
			$i = 0;
			foreach ( $files_php as $file ) {
				$modules[$i]->file 		= $file;
				$modules[$i]->module 	= str_replace( '.xml', '', $file );
				$i++;
			}
		}

		ReadModuleXML( $modules, $client );

		// sort array of objects alphabetically by name
		SortArrayObjects( $modules, 'name' );

		$mainframe->set('disableMenu', true);

		mosFS::load( '@admin_html' );

		HTML_modules::addModule( $modules, $client );
	}

	/**
	* Deletes one or more modules
	*
	* Also deletes associated entries in the #__module_menu table.
	* @param array An array of unique category id numbers
	*/
	function remove() {
		global $database, $my;
		global $_LANG;

		$client	= mosGetParam( $_REQUEST, 'client', '' );
		$cid 	= mosGetParam( $_POST, 'cid', array(0) );

		if (count( $cid ) < 1) {
			mosErrorAlert( $_LANG->_( 'Select a module to delete' ) );
		}

		$cids = implode( ',', $cid );

		$query = "SELECT *"
		. "\n FROM #__modules"
		. "\n WHERE id IN ( $cids )"
		;
		$database->setQuery( $query );
		if (!($rows = $database->loadObjectList())) {
			mosErrorAlert( $database->getErrorMsg() );
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
			if ($row->module == 'mod_mainmenu') {
				if (strstr( $row->params, 'mainmenu' )) {
					mosErrorAlert( $_LANG->_( 'You cannot delete mod_mainmenu module that displays the `mainmenu` as it is a core Menu' ) );
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
				mosErrorAlert( $database->getErrorMsg() );
			}
			$query = "DELETE FROM #__modules_menu"
			. "\n WHERE moduleid IN ( $cids )"
			;
			$database->setQuery( $query );
			if (!$database->query()) {
				mosErrorAlert( $database->getErrorMsg() );
			}
			$mod = new mosModule( $database );
			$mod->ordering = 0;
			$mod->updateOrder( "position='left'" );
			$mod->updateOrder( "position='right'" );
		}

		if (count( $err )) {
			$cids = addslashes( implode( "', '", $err ) );
			mosErrorAlert( $_LANG->_( 'Module(s)' ) .": \'". $cids ."\' ". $_LANG->_( 'WARNCANNOTBEDELETEDMAMBOMODULES' ) );
		}

		$msg = $_LANG->_( 'Module Deleted' );
		mosRedirect( 'index2.php?option=com_modules&client='. $client, $msg );
	}

	/**
	 * Remove the selected module
	 */
	function uninstall() {
		global $database, $mainframe;
		global $_LANG;

		$cid 		= mosGetParam( $_POST, 'cid', array(0) );
		$client 	= mosGetParam( $_POST, 'client', '' );
		$client_id 	= $mainframe->getClientID( $client );

		$installer 	= mosModuleFactory::createInstaller();
		if ($installer->uninstall( $cid[0], $client_id )) {
			$msg = $_LANG->_( 'Success' );
		} else {
			$msg = $installer->error();
		}

		mosFS::load( '@admin_html' );

		moduleScreens::installDone( $installer );
	}

	/**
	* Cancels an edit operation
	*/
	function cancel() {
		global $database;

		$client = mosGetParam( $_POST, 'client', '' );

		$row = new mosModule( $database );
		// ignore array elements
		$row->bind( $_POST, 'selections params' );
		$row->checkin();

		mosRedirect( 'index2.php?option=com_modules&client='. $client );
	}
}

$tasker = new moduleTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();

$cid 		= mosGetParam( $_POST, 'cid', array(0) );
$client 	= mosGetParam( $_REQUEST, 'client', '' );
$moduleid 	= mosGetParam( $_REQUEST, 'moduleid', null );
if ($cid[0] == 0 && isset( $moduleid )) {
	$cid[0] = $moduleid;
}

switch ( $task ) {
	case 'copy':
		copyModule( intval( $cid[0] ), $client );
		break;


	case 'publish':
	case 'unpublish':
		publishModule( $cid, ($task == 'publish'), $client );
		break;

	case 'orderup':
	case 'orderdown':
		orderModule( $cid[0], ( $task == 'orderup' ? -1 : 1 ) );
		break;

	case 'accessPublic':
	case 'accessRegistered':
	case 'accessSpecial':
		accessMenu( $cid[0], $task, $client );
		break;

	case 'saveorder':
		saveOrder( $cid, $client );
		break;

	case 'checkin':
		checkin( $id, $client );
		break;

	case 'preview':
		HTML_modules::popupPreview();
		break;
}

/**
* Compiles information to add or edit a module
* @param string The current GET/POST option
* @param integer The unique id of the record to edit
*/
function copyModule( $uid, $client ) {
	global $database, $my;
	global $_LANG;

	if (!$uid) {
		mosErrorAlert( $_LANG->_( 'Select a module to Copy' ) );
	}

	$row = new mosModule( $database );
	// load the row from the db table
	$row->load( $uid );
	$row->title 	= $_LANG->_( 'Copy of' ) .' '. $row->title;
	$row->id 		= 0;
	$row->iscore 	= 0;
	$row->published = 0;

	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}
	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}
	$row->checkin();
	if ($client == 'admin') {
		$where = "client_id='1'";
	} else {
		$where = "client_id='0'";
	}
	$row->updateOrder( "position = '$row->position' AND ( $where )" );

	$query = "SELECT menuid"
	. "\n FROM #__modules_menu"
	. "\n WHERE moduleid = '$uid'"
	;
	$database->setQuery( $query );
	$rows = $database->loadResultArray();

	foreach( $rows as $menuid ) {
		$query = "INSERT INTO #__modules_menu"
		. "\n SET moduleid = '$row->id', menuid = '$menuid'"
		;
		$database->setQuery( $query );
		$database->query();
	}

	$msg = $_LANG->_( 'Module Copied' ) .' ['. $row->title .']';
	mosRedirect( 'index2.php?option=com_modules&client='. $client, $msg );
}

/**
* Publishes or Unpublishes one or more modules
* @param array An array of unique record id numbers
* @param integer 0 if unpublishing, 1 if publishing
*/
function publishModule( $cid=null, $publish=1, $client ) {
	global $database, $my;
	global $_LANG;

	if (count( $cid ) < 1) {
		$action = $publish ? 'publish' : 'unpublish';
		echo "<script> alert('". $_LANG->_( 'Select a module to' ) ." ". $action ."'); window.history.go(-1);</script>\n";
		exit;
	}

	$cids = implode( ',', $cid );

	$query = "UPDATE #__modules"
	. "\n SET published = '$publish'"
	. "\n WHERE id IN ( $cids )"
	. "\n AND ( checked_out = 0 OR ( checked_out = '$my->id' ) )"
	;
	$database->setQuery( $query );
	if (!$database->query()) {
		mosErrorAlert( $database->getErrorMsg() );
	}

	if (count( $cid ) == 1) {
		$row = new mosModule( $database );
		$row->checkin( $cid[0] );
	}

	mosRedirect( 'index2.php?option=com_modules&client='. $client );
}

/**
* Moves the order of a record
* @param integer The unique id of record
* @param integer The increment to reorder by
*/
function orderModule( $uid, $inc ) {
	global $database;

	$client = mosGetParam( $_POST, 'client', '' );

	$row = new mosModule( $database );
	$row->load( $uid );
	if ($client == 'admin') {
		$where = "client_id='1'";
	} else {
		$where = "client_id='0'";
	}

	$row->move( $inc, "position = '$row->position' AND ( $where )"  );
	if ($client) {
		$client = '&client=admin' ;
	} else {
		$client = '';
	}

	mosRedirect( 'index2.php?option=com_modules&client='. $client );
}

/**
* changes the access level of a record
* @param integer The increment to reorder by
*/
function accessMenu( $uid, $access, $client ) {
	global $database;

	switch ( $access ) {
		case 'accessPublic':
			$access = 1;
			break;

		case 'accessRegistered':
			$access = 2;
			break;

		case 'accessSpecial':
			$access = 0;
			break;
	}

	$row = new mosModule( $database );
	$row->load( $uid );
	$row->access = $access;

	if (!$row->check()) {
		mosErrorAlert( $row->getError() );
	}
	if (!$row->store()) {
		mosErrorAlert( $row->getError() );
	}

	mosRedirect( 'index2.php?option=com_modules&client='. $client );
}

function saveOrder( &$cid, $client ) {
	global $database;
	global $_LANG;

	$total		= count( $cid );
	$order 		= mosGetParam( $_POST, 'order', array(0) );
	$row 		= new mosModule( $database );
	$conditions = array();

	// update ordering values
	for( $i=0; $i < $total; $i++ ) {
		$row->load( $cid[$i] );
		if ($row->ordering != $order[$i]) {
			$row->ordering = $order[$i];
			if (!$row->store()) {
				mosErrorAlert( $database->getErrorMsg() );
			}
			// remember to updateOrder this group
			$condition = "position='$row->position' AND client_id='$row->client_id'";
			$found = false;
			foreach ( $conditions as $cond )
				if ($cond[1]==$condition) {
					$found = true;
					break;
				}
			if (!$found) $conditions[] = array($row->id, $condition);
		}
	}

	// execute updateOrder for each group
	foreach ( $conditions as $cond ) {
		$row->load( $cond[0] );
		$row->updateOrder( $cond[1] );
	}

	$msg 	= $_LANG->_( 'New ordering saved' );
	mosRedirect( 'index2.php?option=com_modules&client='. $client, $msg );
}

function checkin( $id, $client ) {
	global $database;
	global $_LANG;

	$row = new mosModule( $database );
	$row->load( $id );
	// checkin item
	$row->checkin();

	$msg = $_LANG->_( 'Item Checked In' );
	mosRedirect( 'index2.php?option=com_modules&client='. $client, $msg );
}

function ReadModuleXML( &$rows, $client ) {
	// pull name and description from module xml
	mosFS::load( 'includes/mambo.files.xml.php' );
	$basePath = mosModule::getBasePath( $client );
	// cache the xml results because a single module can have multiple instances
	$moduleXmlCache = array();

	foreach ($rows as $i => $row) {
		if ($row->module == '') {
			$rows[$i]->name 	= 'custom';
			$rows[$i]->module 	= 'custom';
			$rows[$i]->descrip 	= 'Custom created module, using Module Manager `New` function';
		} else {
			$xmlVars = null;
			if (isset( $moduleXmlCache[$row->module] )) {
				$xmlVars = &$moduleXmlCache[$row->module];
			} else {
				$file = mosFS::getNativePath( $basePath . $row->module .'.xml', false );

				if (file_exists( $file )) {
					mosXMLFS::read( $file, 'module', $xmlVars );
					$moduleXmlCache[$row->module] = $xmlVars;
				}
			}
			if ($xmlVars) {
				$rows[$i]->name 	= addslashes( $xmlVars['meta']['name'] );
				$rows[$i]->descrip 	= addslashes( $xmlVars['meta']['description'] );
			}
		}
	}
	// free some memory
	unset( $xmlVars );
	unset( $moduleXmlCache );
}
?>