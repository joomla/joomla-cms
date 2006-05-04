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
	$file = 'login.php';
}

// set language
$mainframe->setLanguage($mainframe->getUserState( "application.lang", 'lang' ));

// trigger the onStart events
$mainframe->triggerEvent( 'onAfterStart' );

JDEBUG ? $_PROFILER->mark( 'afterStartFramework' ) :  null;

// login the user
if ($option == 'login') {
	$mainframe->login();
}

// logout the user
if ($option == 'logout') {
	$mainframe->logout();
}

// get the information about the current user from the sessions table
$user   = & $mainframe->getUser();
$my		= $user->_table;

// set for overlib check
$mainframe->set( 'loadOverlib', false );

$no_html 	= strtolower( JRequest::getVar( 'no_html', 0 ) );
$format 	= JRequest::getVar( 'format', $no_html ? 'raw' : 'html',  '', 'string'  );
$tmpl 	 	= JRequest::getVar( 'file', isset($tmpl) ? $tmpl : 'index.php',  '', 'string'  );

// loads template file
$cur_template = $mainframe->getTemplate();

$document =& $mainframe->getDocument($format);
$document->setTitle( $mainframe->getCfg('sitename' ). ' - ' .JText::_( 'Administration' ));
$document->display( $cur_template, $tmpl, $mainframe->getCfg('gzip') );

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null ;

JDEBUG ? $_PROFILER->report() : null;
?>