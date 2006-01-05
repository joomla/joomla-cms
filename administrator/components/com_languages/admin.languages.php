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
defined( '_JEXEC' ) or die( 'Restricted access' );

// ensure user has access to this function
if (!$acl->acl_check( 'com_languages', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}


require_once( JApplicationHelper::getPath( 'admin_html' ) );

$task 	= trim( strtolower( mosGetParam( $_REQUEST, 'task', '' ) ) );
$cid 	= mosGetParam( $_REQUEST, 'cid', array(0) );
$client = mosGetParam( $_REQUEST, 'client', 'site' );

if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'install':
		josRedirect( 'index2.php?option=com_installer&task=installer&client='. $client );
		break;

	case 'uninstall':
		removeLanguage( $cid[0], $option, $client );
		break;

	case 'publish':
		publishLanguage( $cid[0], $option, $client );
		break;

	case 'cancel':
		mosRedirect( "index2.php?option=$option" );
		break;

	default:
		viewLanguages( $option, $client );
		break;
}

/**
* Compiles a list of installed languages
*/
function viewLanguages( $option, $client = 'site') {
	global $mainframe;
	global $mosConfig_lang, $mosConfig_list_limit;

	$limit 		= $mainframe->getUserStateFromRequest( "viewlistlimit", 'limit', $mosConfig_list_limit );
	$limitstart = $mainframe->getUserStateFromRequest( "view{$option}limitstart", 'limitstart', 0 );

	$path = JLanguage::getLanguagePath(constant('JPATH_'.strtoupper($client)));

	$rows = array();
	$rowid = 0;

	$dirs = JFolder::folders( $path );
	foreach ($dirs as $dir) {
		$files = JFolder::files( $path . $dir, '^([_A-Za-z]*)\.xml$' );
		foreach ($files as $file) {
			// Read the file to see if it's a valid template XML file
			$xmlDoc =& JFactory::getXMLParser();
			$xmlDoc->resolveErrors( true );
			if (!$xmlDoc->loadXML( $path . $dir . DS . $file, false, true )) {
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
			$row->language 	= substr($file,0,-4);
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

			$lang = ($client == 'site') ? 'lang' : 'lang_'.$client;

			// if current than set published
			if ( $mainframe->getCfg($lang) == $row->language) {
				$row->published	= 1;
			} else {
				$row->published = 0;
			}

			$row->checked_out = 0;
			$row->mosname = strtolower( str_replace( " ", "_", $row->name ) );
			$rows[] = $row;
			$rowid++;
		}
	}


	require_once( JPATH_ADMINISTRATOR . '/includes/pageNavigation.php' );
	$pageNav = new mosPageNav( count( $rows ), $limitstart, $limit );

	$rows = array_slice( $rows, $pageNav->limitstart, $pageNav->limit );

	HTML_languages::showLanguages( $rows, $pageNav, $option, $client );
}

/**
* Publish, or make current, the selected language
*/
function publishLanguage( $p_lname, $option, $client = 'site' )
{
	$config = '';

	$lang = ($client == 'site') ? '\$mosConfig_lang' : '\$mosConfig_lang_'.$client;
	echo $lang;

	$fp = fopen("../configuration.php","r");
	while(!feof($fp)){
		$buffer = fgets($fp,4096);

		switch($client)
		{
			case 'site' :
			{
				if (strstr($buffer,"\$mosConfig_lang") && !strstr($buffer,"\$mosConfig_lang_administrator")){
					$config .= "\$mosConfig_lang = \"$p_lname\";\n";
				} else {
					$config .= $buffer;
				}
			} break;
			case 'administrator' :
			{
				if (strstr($buffer,"\$mosConfig_lang_administrator")){
					$config .= "\$mosConfig_lang_administrator = \"$p_lname\";\n";
				} else {
					$config .= $buffer;
				}
			} break;
		}


	}
	fclose($fp);

	if ($fp = fopen("../configuration.php","w")){
		fputs($fp, $config, strlen($config));
		fclose($fp);
		josRedirect("index2.php?option=com_languages&client=".$client,JText::_( 'Configuration succesfully updated!' ) );
	} else {
		josRedirect("index2.php?option=com_languages&client=".$client,JText::_( 'ERRORCONFIGWRITEABLE' ) );
	}

}

/**
* Remove the selected language
*/
function removeLanguage( $cid, $option, $client = 'site' ) {
	global $mainframe;

	$lang = ($client == 'site') ? 'lang' : 'lang_'.$client;

	if ($mainframe->getCfg($lang) == $cid) {
		mosErrorAlert(JText::_( 'You can not delete language in use.', true ));
	}

	josRedirect( 'index2.php?option=com_installer&type=language&client='. $client .'&task=remove&eid[]='. $cid );

}
?>
