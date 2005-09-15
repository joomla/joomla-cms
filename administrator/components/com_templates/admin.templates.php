<?php
/**
 * @version $Id: admin.templates.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Mambo
 * @subpackage Templates
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!$acl->acl_check( 'com_templates', 'manage', 'users', $GLOBALS['my']->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@admin_html' );
mosFS::load( '@class' );

/**
 * @package Languages
 * @subpackage Languages
 */
class templateTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function templateTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'view' );

		// set task level access control
		$this->setAccessControl( 'com_templates', 'manage' );
		$this->registerTask( 'preview2', 'preview' );

		$this->registerTask( 'saveHTML', 'saveSource' );
		$this->registerTask( 'applyHTML', 'saveSource' );
		$this->registerTask( 'saveCSS', 'saveSource' );
		$this->registerTask( 'applyHTML', 'saveSource' );

		$this->registerTask( 'refreshFiles', 'editXML' );

		$this->registerTask( 'installUpload', 'install' );
		$this->registerTask( 'installFromDir', 'install' );

		$this->registerTask( 'default', 'setDefault' );
	}

	/**
	 * Generic install page
	 */
	function installOptions() {
		templateScreens::installOptions();
	}

	/**
	 * Installs template
	 */
	function install() {
		$userfile = mosGetParam( $_FILES, 'userfile', null );

		$installer = mosTemplateFactory::createInstaller();
		if ($this->getTask() == 'installUpload') {
			if (!$installer->uploadArchive( $userfile )) {
				$msg = $installer->error();
				$this->setRedirect( 'index2.php?option=com_templates&task=installOptions', $msg );
			}
			if (!$installer->extractArchive()) {
				$msg = $installer->error();
				$this->setRedirect( 'index2.php?option=com_templates&task=installOptions', $msg );
			}
		} else {
			$installer->installDir( $userfile );
		}
		if (!$installer->install()) {
			$installer->cleanupInstall();
			$msg = $installer->error();
			$this->setRedirect( 'index2.php?option=com_templates&task=installOptions', $msg );
		}
		$installer->cleanupInstall();

		templateScreens::installDone( $installer->elementName(), $installer->errno(), $installer->error() );
	}

	/**
	 * Options for packaging
	 */
	function packageOptions() {
		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
		$client = mosGetParam( $_REQUEST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$element = $cid[0];

		templateScreens::packageOptions( $element, $client );
	}

	/**
	 * Build the package
	 */
	function package() {
		global $_LANG;

		$compress = mosGetParam( $_POST, 'compress', 'gz' );
		$element = mosGetParam( $_POST, 'element', '' );
		$fileName = mosGetParam( $_POST, 'filename', $element );
		$client = mosGetParam( $_POST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$redirect = 'index2.php?option=com_templates&client='. $client;
		if (empty( $element )) {
			$this->setRedirect( $redirect, $_LANG->_( 'Template not supplied' ) );
			return false;
		}

		mosFS::load( 'includes/mambo.files.xml.php' );
		$basePath = mosTemplate::getBasePath( $client ) . $element . DIRECTORY_SEPARATOR;

		$xmlFile = $basePath . 'templateDetails.xml';

		if (!mosXMLFS::read( $xmlFile, 'template', $vars )) {
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
		$this->setRedirect( 'index2.php?option=com_templates&task=listFiles', $msg );
	}

	/**
	 * List files in /files directory
	 */
	function listFiles() {
		$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$files = mosFS::listFiles( $path, '\.(tar|gz)$' );
		foreach ($files as $i=>$file) {
			$files[$i] = array(
				'file' => $file,
				'fsize' => number_format( filesize( $path . $file ) ),
				'mtime' => date ("d-m-Y H:i:s", filemtime( $path . $file ) ),
				'perms' => mosFS::getPermissions( $path . $file )
			);
		}
		templateScreens::listFiles( $files );
	}

	/**
	 * Delete a set of language files
	 */
	function deleteFile() {
		global $_LANG;

		$cid = mosGetParam( $_POST, 'cid', array() );
		$path = mosFS::getNativePath( dirname( __FILE__ ) . '/files' );

		$redirect = 'index2.php?option=com_templates&task=listFiles';
		if (count( $cid ) < 1) {
			$msg = $_LANG->_( 'errorNoFile' );
			mosRedirect( $redirect, $msg );
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
		mosFS::load( '/includes/mambo.files.xml.php' );

		$client = mosGetParam( $_REQUEST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$basePath = mosTemplate::getBasePath( $client );

		switch ($this->getTask()) {
			case 'refreshFiles':
				// template files
				$element = mosGetParam( $_POST, 'element', array(0) );
				$xmlVars = mosGetParam( $_POST, 'vars', array() );
				$exclude = mosGetParam( $_POST, 'exclude', '' );
				varsStripSlashes( $vars );

				$dir = mosFS::getNativePath( $basePath . $element, false );
				$files = mosFS::listFiles( $dir, '.', true, true );
				$templ = array();
				foreach ($files as $i => $v) {
					if (empty( $exclude ) || ($exclude && !eregi( $exclude, $v ) )) {
						$temp[] = array( 'file' => str_replace( $dir . DIRECTORY_SEPARATOR, '', $v ) );
					}
				}
				$xmlVars['siteFiles'] = $temp;
				break;

			default:
				$cid = mosGetParam( $_POST, 'cid', array(0) );
				$element = $cid[0];

				$file = mosFS::getNativePath( $basePath . $element .'/templateDetails.xml', false );
				if (file_exists( $file )) {
					mosXMLFS::read( $file, 'template', $xmlVars );
				} else {
					$xmlVars = array();
				}
				break;
		}

		templateScreens::editXML( $xmlVars, $element, $client );
	}

	/**
	 * Saves the xml setup file
	 */
	function saveXML() {
		global $_LANG;

		mosFS::load( '/includes/mambo.files.xml.php' );
		$client		= mosGetParam( $_REQUEST, 'client', '' );
		$client_id	= mosMainFrame::getClientID( $client );
		$element	= mosGetParam( $_POST, 'element', '' );
		$vars		= mosGetParam( $_POST, 'vars', array() );

		$basePath	= mosTemplate::getBasePath( $client );

		$file = mosFS::getNativePath( $basePath . $element .'/templateDetails.xml', false );
		if (!file_exists( $file )) {
			$msg = $_LANG->_( 'File not found' );
			$this->redirect( 'index2.php?option=com_templates&client='. $client, $msg );
			return false;
		}

		$vars['client'] = mosMainFrame::getClientName( $client_id );

		$msg = mosXMLFS::write( 'template', $vars, $file )
			? $_LANG->_( 'File Saved' )
			: $_LANG->_( 'Error saving file' );

		$this->setRedirect( 'index2.php?option=com_templates&client=' . $client, $msg );
	}

	/**
	 * List the templates
	 */
	function view() {
		global $database, $mainframe, $option;
		global $mosConfig_absolute_path, $mosConfig_list_limit;

		mosFS::load( 'includes/mambo.files.xml.php' );

		// form data
		$limit 		= $mainframe->getUserStateFromRequest( 'viewlistlimit', 'limit', $mosConfig_list_limit );
		$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );

		$client		= mosGetParam( $_REQUEST, 'client', '' );
		$client 	= mosMainFrame::getClientID( $client );

		$basePath 	= mosTemplate::getBasePath( $client );
		$folders 	= mosFS::listFolders( $basePath, '.' );

		$tMenu 		= new mosTemplatesMenu( $database );
		$curTemplate = $tMenu->getCurrent( $client, 0 );

		$rows = array();
		foreach ($folders as $folder) {
			$xmlFile = mosFS::getNativePath( $basePath. $folder . '/templateDetails.xml', false );
			if (file_exists( $xmlFile )) {
				if (mosXMLFS::read( $xmlFile, 'template', $vars )) {
					$row = new stdClass;

					if (isset( $vars['meta'] )) {
						// append the vars to the row object
						foreach ($vars['meta'] as $k => $v) {
							$row->{'xml_' . $k} = $v;
						}
					}
					$menus = $tMenu->getMenus( $client, $folder );
					$assigned = 0;
					foreach ($menus as $menu) {
						if ($menu['menuid']!=0) {
							$assigned = 1;
						}
					}
					$row->assigned = $assigned;
					$row->default = ($folder == $curTemplate);
					$row->folder = $folder;
					$rows[] = $row;
				}
			}
		}

		mosFS::load( '@pageNavigationAdmin' );

		$total 		= count( $rows );
		$pageNav 	= new mosPageNav( $total, $limitstart, $limit );
		$rows 		= array_slice( $rows, $pageNav->limitstart, $pageNav->limit );

		templateScreens::view( $rows, $pageNav, $client );
	}

	/**
	 * Shows preview screen
	 */
	function preview() {
		$tp = ($this->getTask() == 'preview2');
		templateScreens::preview( $tp );
	}

	/**
	 * Edit form for the template index.php file
	 */
	function editHTML() {
		global $database;
		global $_LANG;

		$cid 	= mosGetParam( $_REQUEST, 'cid', array(0) );
		$client = mosGetParam( $_REQUEST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$element = $cid[0];

		$basePath = mosTemplate::getBasePath( $client );
		$file = mosFS::getNativePath( $basePath . $element .'/index.php', false );
		if (!mosFS::read( $file, $content )) {
			$redirect = 'index2.php?option=com_templates&client='. $client;
			$this->setRedirect( $redirect, $_LANG->_( 'Could not find file ' ) );
			return false;
		}

		$oPositions = new mosTemplatePosition( $database );
		$positions 	= $oPositions->select();

		$vars = array(
			'writable' => intval( is_writable( $file ) ),
			'chmodable' => intval( mosIsChmodable( $file ) ),
			'file' => $file,
			'content' => &$content,
			'element' => $element
		);

		templateScreens::editHTML( $vars, $positions, $client );
	}

	/**
	 * Edit form for the template index.php file
	 */
	function editCSS() {
		global $_LANG;

		$cid 	= mosGetParam( $_REQUEST, 'cid', array(0) );
		$client = mosGetParam( $_REQUEST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$element = $cid[0];

		$basePath 	= mosTemplate::getBasePath( $client );
		$file 		= mosFS::getNativePath( $basePath . $element .'/css/template_css' . ($_LANG->rtl() == 1 ? '_rtl.css': '.css'), false );
		if (!mosFS::read( $file, $content )) {
			$redirect = 'index2.php?option=com_templates&client='. $client;
			$this->setRedirect( $redirect, $_LANG->_( 'Could not find file ' ) );
			return false;
		}

		$vars = array(
			'writable' => intval( is_writable( $file ) ),
			'chmodable' => intval( mosIsChmodable( $file ) ),
			'file' => $file,
			'content' => &$content,
			'element' => $element
		);

		templateScreens::editCSS( $vars, $client );
	}

	/**
	 * Save html source
	 */
	function saveSource() {
		global $mosConfig_absolute_path;
		global $_LANG;

	    $enable_write 	= mosGetParam( $_POST, 'enable_write', 0 );
		$buffer 	= mosGetParam( $_POST, 'filecontent', '', _MOS_ALLOWRAW );
		$client		= mosGetParam( $_REQUEST, 'client', '' );
		$client_id	= mosMainFrame::getClientID( $client );
		$element	= mosGetParam( $_POST, 'element', '' );

		$basePath	= mosTemplate::getBasePath( $client );

		switch ($this->getTask()) {
			case 'saveHTML':
			case 'applyHTML':
				$file = $basePath . $element . '/index.php';
				break;

			case 'saveCSS':
			case 'applyCSS':
				$file = $basePath . $element . '/css/template_css' . $_LANG->rtl() ? '_rtl':'' . '.css';
				break;
		}

		if (!file_exists( $file )) {
			$msg = $_LANG->_( 'Error. Template file not found' );
			$this->redirect( 'index2.php?option=com_templates&client='. $client, $msg );
			return false;
		}
		if (empty( $buffer )) {
			$msg = $_LANG->_( 'Error. Source empty.' );
			$this->redirect( 'index2.php?option=com_templates&client='. $client, $msg );
			return false;
		}

		$oldperms = fileperms( $file );
		if ($enable_write) {
			@chmod( $file, $oldperms | 0222 );
		}

		clearstatcache();

		if (is_writable( $file ) == false) {
			$msg = $_LANG->_( 'Error. File is not writable.' );
			$this->redirect( 'index2.php?option=com_templates&client='. $client, $msg );
			return false;
		}

		if (mosFS::write( $file, stripslashes( $buffer ) )) {
			if ($enable_write) {
				chmod( $file, $oldperms );
			} else {
				if ( mosGetParam($_POST,'disable_write',0) ) {
					chmod($file, $oldperms & 0777555);
				}
			} // if

			$msg = 'File saved';
			switch ($this->getTask()) {
				case 'applyHTML':
					$this->setRedirect( 'index2.php?option=com_templates&task=editHTML&cid[]='. $element .'&client='. $client, $msg );
					break;

				default:
					$this->setRedirect( 'index2.php?option=com_templates&client='. $client, $msg );
					break;
			}

		} else {
			// Saving Uwriteable
			if ( $enable_write ) {
				chmod($file, $oldperms);
			}

			$msg = $_LANG->_( 'DESCFAILEDTOOPENFILEFORWRITING' );
			$this->redirect( 'index2.php?option=com_templates&client='. $client, $msg );
		}
	}

	/**
	 * Edit the template/module positions
	 */
	function positions() {
		$client = mosGetParam( $_REQUEST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$oPositions = new mosTemplatePosition( $GLOBALS['database'] );
		$positions = $oPositions->select();

		$n = count( $positions );
		for ($i = $n + 1; $i <= 50; $i++ ) {
			$o = new stdClass;
			$o->id = $i;
			$positions[] = $o;
		}

		templateScreens::positions( $positions, $client );
	}

	/**
	 * Save the template/module positions
	 */
	function savePositions() {
		global $_LANG;

		$redirect = 'index2.php?option=com_templates&task=positions';

		$positions 		= mosGetParam( $_POST, 'position', array() );
		$descriptions 	= mosGetParam( $_POST, 'description', array() );

		$oPositions = new mosTemplatePosition( $GLOBALS['database'] );

		if (!$oPositions->clear()) {
			$this->setRedirect( $redirect, $oPositions->getError() );
			return false;
		}

		foreach ($positions as $id=>$position) {
		    $position = trim( $position );
		    $description = mosGetParam( $descriptions, $id, '' );
			if ($position != '') {
				$oPositions->id = $id;
				$oPositions->position = $position;
				$oPositions->description = $description;
				if (!$oPositions->insert()) {
					$this->setRedirect( $redirect, $oPositions->getError() );
					return false;
				}
			}
		}
		$this->setRedirect( $redirect, $_LANG->_( 'Positions saved' ) );
	}

	/**
	 * Form to assign the menu/template mapping
	 */
	function assign() {
		global $database;

		$cid = mosGetParam( $_REQUEST, 'cid', array(0) );
		$client = mosGetParam( $_REQUEST, 'client', '' );
		$client = mosMainFrame::getClientID( $client );

		$element = $database->getEscaped( $cid[0] );

		// get selected pages for $menulist
		if ( $element ) {
			$query = "SELECT menuid AS value"
			. "\n FROM #__templates_menu"
			. "\n WHERE client_id = '0'"
			. "\n AND template = '$element'"
			;
			$database->setQuery( $query );
			$lookup = $database->loadObjectList();
		}

		// build the html select list
		mosFS::load( '@class', 'com_menus' );
		$menulist = mosMenuFactory::buildMenuLinks( $lookup, 0, 1 );

		templateScreens::assign( $element, $menulist, $client );
	}

	/**
	 * Saves the template-menu mapping
	 */
	function saveAssign() {
		global $database, $mainframe;

		$client		= mosGetParam( $_REQUEST, 'client', '' );
		$client_id	= $mainframe->getClientID( $client );
		$menus 		= mosGetParam( $_POST, 'selections', array() );
		$element 	= mosGetParam( $_POST, 'element', '' );

		$query = "DELETE FROM #__templates_menu"
		. "\n WHERE client_id = '0'"
		. "\n AND template = '$element'"
		. "\n AND menuid <> '0'"
		;
		$database->setQuery( $query );
		$database->query();

		if (!in_array( '', $menus )) {
			foreach ( $menus as $menuid ){
				// If 'None' is not in array
				if ( $menuid <> -999 ) {
					// check if there is already a template assigned to this menu item
					$query = "DELETE FROM #__templates_menu"
					. "\n WHERE client_id = '0'"
					. "\n AND menuid = '$menuid'"
					;
					$database->setQuery( $query );
					$database->query();

					$query = "INSERT INTO #__templates_menu"
					. "\n SET client_id = '0', template = '$element', menuid = '$menuid'"
					;
					$database->setQuery( $query );
					$database->query();
				}
			}
		}

		$this->setRedirect( 'index2.php?option=com_templates&client='. $client );
	}

	/**
	 * Publish, or make current, the selected template
	 */
	function setDefault() {
		global $database, $mainframe;

		$cid 	= mosGetParam( $_REQUEST, 'cid', array( '' ) );
		$client = mosGetParam( $_REQUEST, 'client', '' );

		$tMenuPos = new mosTemplatesMenu( $database );
		$tMenuPos->client_id 	= $mainframe->getClientID( $client );
		$tMenuPos->template 	= $cid[0];
		$tMenuPos->setDefault();

		$this->setRedirect( 'index2.php?option=com_templates&client='. $client );
	}

	/**
	 * Remove the selected template
	 */
	function remove() {
		global $database, $mainframe;
		global $_LANG;

		$cid 		= mosGetParam( $_REQUEST, 'cid', array(0) );
		$client 	= mosGetParam( $_REQUEST, 'client', '' );
		$client_id 	= $mainframe->getClientID( $client );

		$installer = mosTemplateFactory::createInstaller();
		if ($installer->uninstall( $cid[0], $client_id )) {
			$msg = $_LANG->_( 'Success' );
		} else {
			$msg = $installer->error();
		}

		$this->setRedirect( 'index2.php?option=com_templates&client='. $client, $msg );
	}
}

$tasker = new templateTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>