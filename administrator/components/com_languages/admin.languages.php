<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Languages
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!$acl->acl_check( 'com_languages', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('ALERTNOTAUTH') );
}


require_once( $mainframe->getPath( 'admin_html' ) );
// XML library
require_once( "$mosConfig_absolute_path/includes/domit/xml_domit_lite_include.php" );

$task 	= trim( strtolower( mosGetParam( $_REQUEST, 'task', '' ) ) );
$cid 	= mosGetParam( $_REQUEST, 'cid', array(0) );

if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'new':
		mosRedirect( 'index2.php?option=com_installer&element=language' );
		break;

	case 'edit_source':
		editLanguageSource( $cid[0], $option );
		break;

	case 'save_source':
		saveLanguageSource( $option );
		break;

	case 'remove':
		removeLanguage( $cid[0], $option );
		break;

	case 'publish':
		publishLanguage( $cid[0], $option );
		break;

	case 'cancel':
		mosRedirect( "index2.php?option=$option" );
		break;

	default:
		viewLanguages( $option );
		break;
}

/**
* Compiles a list of installed languages
*/
function viewLanguages( $option ) {
	global $languages;
	global $mainframe;
	global $mosConfig_lang, $mosConfig_absolute_path, $mosConfig_list_limit;
	global $_LANG;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );

	// get current languages
	$cur_language = $mosConfig_lang;

	$rows = array();
	// Read the template dir to find templates
	$languageBaseDir = mosPathName(mosPathName($mosConfig_absolute_path) . "language");

	$rowid = 0;

	$xmlFilesInDir = mosReadDirectory($languageBaseDir,'.xml$');

	$dirName = $languageBaseDir;
	foreach($xmlFilesInDir as $xmlfile) {
		// Read the file to see if it's a valid template XML file
		$xmlDoc = new DOMIT_Lite_Document();
		$xmlDoc->resolveErrors( true );
		if (!$xmlDoc->loadXML( $dirName . $xmlfile, false, true )) {
			continue;
		}

		$root = &$xmlDoc->documentElement;

		if ($root->getTagName() != 'mosinstall') {
			continue;
		}
		if ($root->getAttribute( "type" ) != "language") {
			continue;
		}

		$row 			= new StdClass();
		$row->id 		= $rowid;
		$row->language 	= substr($xmlfile,0,-4);
		$element 		= &$root->getElementsByPath('name', 1 );
		$row->name 		= $element->getText();

		$element		= &$root->getElementsByPath('creationDate', 1);
		$row->creationdate = $element ? $element->getText() : 'Unknown';

		$element 		= &$root->getElementsByPath('author', 1);
		$row->author 	= $element ? $element->getText() : 'Unknown';

		$element 		= &$root->getElementsByPath('copyright', 1);
		$row->copyright = $element ? $element->getText() : '';

		$element 		= &$root->getElementsByPath('authorEmail', 1);
		$row->authorEmail = $element ? $element->getText() : '';

		$element 		= &$root->getElementsByPath('authorUrl', 1);
		$row->authorUrl = $element ? $element->getText() : '';

		$element 		= &$root->getElementsByPath('version', 1);
		$row->version 	= $element ? $element->getText() : '';

		// if current than set published
		if ($cur_language == $row->language) {
			$row->published	= 1;
		} else {
			$row->published = 0;
		}

		$row->checked_out = 0;
		$row->mosname = strtolower( str_replace( " ", "_", $row->name ) );
		$rows[] = $row;
		$rowid++;
	}

	require_once( $GLOBALS['mosConfig_absolute_path'] . '/administrator/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( count( $rows ), $limitstart, $limit );

	$rows = array_slice( $rows, $pageNav->limitstart, $pageNav->limit );

	HTML_languages::showLanguages( $cur_language, $rows, $pageNav, $option );
}

/**
* Publish, or make current, the selected language
*/
function publishLanguage( $p_lname, $option ) {
	global $mosConfig_lang;
	global $_LANG;

	$config = '';

	$fp = fopen("../configuration.php","r");
	while(!feof($fp)){
		$buffer = fgets($fp,4096);
		if (strstr($buffer,"\$mosConfig_lang")){
			$config .= "\$mosConfig_lang = \"$p_lname\";\n";
		} else {
			$config .= $buffer;
		}
	}
	fclose($fp);

	if ($fp = fopen("../configuration.php","w")){
		fputs($fp, $config, strlen($config));
		fclose($fp);
		mosRedirect("index2.php",$_LANG->_( 'Configuration succesfully updated!' ) );
	} else {
		mosRedirect("index2.php",$_LANG->_( 'ERRORCONFIGWRITEABLE' ) );
	}

}

/**
* Remove the selected language
*/
function removeLanguage( $cid, $option, $client = 'admin' ) {
	global $mosConfig_lang;
	global $_LANG;

	$client_id = $client=='admin' ? 1 : 0;

	$cur_language = $mosConfig_lang;

	if ($cur_language == $cid) {
		mosErrorAlert($_LANG->_( 'You can not delete language in use.' ));
	}

	/*$lang_path = "../language/$cid.php";
	$lang_ignore_path = "../language/$cid.ignore.php";
	$xml_path = "../language/$cid.xml";

	unlink($lang_path);
	unlink($lang_ignore_path);
	unlink($xml_path);
	*/

	mosRedirect( 'index2.php?option=com_installer&element=language&client='. $client .'&task=remove&cid[]='. $cid );

}

function editLanguageSource( $p_lname, $option) {
	$file = stripslashes( "../language/$p_lname.php" );
	global $_LANG;

	if ($fp = fopen( $file, "r" )) {
		$content = fread( $fp, filesize( $file ) );
		$content = htmlspecialchars( $content );

		HTML_languages::editLanguageSource( $p_lname, $content, $option );
	} else {
		mosRedirect( "index2.php?option=". $option ."&mosmsg=". $_LANG->_( 'Operation Failed: Could not open' ) ." ". $file );
	}
}

function saveLanguageSource( $option ) {
	global $_LANG;

	$language = mosGetParam( $_POST, 'language', '' );
	$filecontent = mosGetParam( $_POST, 'filecontent', '', _MOS_ALLOWHTML );

	if (!$language) {
		mosRedirect( "index2.php?option=". $option ."&mosmsg=". $_LANG->_( 'Operation failed: No language specified.' ) );
	}
	if (!$filecontent) {
		mosRedirect( "index2.php?option=". $option ."&mosmsg=". $_LANG->_( 'Operation failed: Content empty.' ) );
	}

	$file = "../language/$language.php";
	$enable_write = mosGetParam($_POST,'enable_write',0);
	$oldperms = fileperms($file);
	if ($enable_write) @chmod($file, $oldperms | 0222);

	clearstatcache();
	if (is_writable( $file ) == false) {
		mosRedirect( "index2.php?option=". $option ."&mosmsg=". $_LANG->_( 'Operation failed: The file is not writable.' ) );
	}

	if ($fp = fopen ($file, "w")) {
		fputs( $fp, stripslashes( $filecontent ) );
		fclose( $fp );
		if ($enable_write) {
			@chmod($file, $oldperms);
		} else {
			if (mosGetParam($_POST,'disable_write',0))
				@chmod($file, $oldperms & 0777555);
		} // if
		mosRedirect( "index2.php?option=$option" );
	} else {
		if ($enable_write) @chmod($file, $oldperms);
		mosRedirect( "index2.php?option=". $option ."&mosmsg=". $_LANG->_( 'WARNOPENFILE' ) );
	}
}
?>