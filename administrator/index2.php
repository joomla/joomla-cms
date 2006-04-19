<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once ( JPATH_BASE .'/includes/application.php' );
require_once ( JPATH_BASE .'/includes/template.html.php'    );

// initialise some common request directives
$task 		= JRequest::getVar( 'task' );
$section 	= JRequest::getVar( 'section' );
$no_html 	= strtolower( JRequest::getVar( 'no_html', 0 ) );
$id         = JRequest::getVar( 'id', 0, '', 'int' );
$cid		= JRequest::getVar( 'cid', null, 'post' );

// create the mainframe object
$mainframe = new JAdministrator();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

//get the database object
$database =& $mainframe->getDBO();

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

// create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->getClientId() );

// load a stored user state if it exists
$mainframe->loadStoredUserState();

// Get the global option variable and create the pathway
$option = strtolower( JRequest::getVar( 'option', 'com_admin' ) );
$mainframe->_createPathWay( );	

if (is_null(JSession::get('guest')) || JSession::get('guest')) {
	josRedirect( 'index.php');
}

// set language 
$mainframe->setLanguage($mainframe->getUserState( "application.lang", 'lang' ));

// trigger the onStart events
$mainframe->triggerEvent( 'onAfterStart' );

JDEBUG ? $_PROFILER->mark( 'afterStartFramework' ) :  null;

// logout the user
if ($option == 'logout') {
	$mainframe->logout();
}

// get the information about the current user from the sessions table
$user   = & $mainframe->getUser();
$my		= $user->_table;

// set for overlib check
$mainframe->set( 'loadOverlib', false );

//render raw component output
if($no_html == 1) {
	$path = JApplicationHelper::getPath( 'admin', $option);
	
	//load common language files
	$lang =& $mainframe->getLanguage();
	$lang->load($option);
	require_once( $path );	
	exit();	
}

// loads template file
$cur_template = $mainframe->getTemplate();
$file     	  = 'index.php';

$document =& $mainframe->getDocument();
// Add the hidemainmenu var to the JDocument object so templates can adapt if needed
$document->addGlobalVar( 'hidemainmenu', (JRequest::getVar( 'hidemainmenu', '0' ))? '1' : '0');
$document->setTitle( $mainframe->getCfg('sitename' ). ' - ' .JText::_( 'Administration' ));
$document->display( $cur_template, $file, $mainframe->getCfg('gzip') );

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null ; 

if (JDEBUG) {
	echo $_PROFILER->report();
	echo $_PROFILER->getMemory();
}

echo "<br />";

// displays queries performed for page
if (JDEBUG)  {
	echo $database->_ticker . ' queries executed';
	echo '<pre>';
 	foreach ($database->_log as $k=>$sql) {
 		echo $k+1 . "\n" . $sql . '<hr />';
	}
}
?>