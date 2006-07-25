<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Set flag that this is a parent file
define( '_JEXEC', 1 );

define('JPATH_BASE', dirname(__FILE__) );

require_once(  JPATH_BASE.'/includes/defines.php'       );
require_once ( JPATH_BASE.'/includes/application.php'   );
require_once ( JPATH_BASE.'/includes/template.html.php' );

// create the mainframe object
$mainframe = new JAdministrator();
// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

// create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->getClientId() );

// set language
$mainframe->setLanguage($mainframe->getUserState( "application.lang", 'lang' ));

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// load a stored user state if it exists
$mainframe->loadStoredUserState();

// trigger the onStart events
$mainframe->triggerEvent( 'onAfterStart' );

JDEBUG ? $_PROFILER->mark( 'afterStartFramework' ) :  null;

/*
 * BACKWARDS COMPATABILITY
 * 	Set globals for:
 * 		- $database
 * 		- $my
 * ## THESE ARE DEPRECATED AND WILL BE REMOVED ##
 */
global $database, $my;
$database	=& JFactory::getDBO();
$user		=& JFactory::getUser();
$my			=& $user->getTable();

// initialise some common request directives
$option 	= $mainframe->getOption();
$task 		= JRequest::getVar( 'task' );
$section 	= JRequest::getVar( 'section' );
$id         = JRequest::getVar( 'id', 0, '', 'int' );
$cid		= JRequest::getVar( 'cid', null, 'post' );

// set for overlib check
$mainframe->set( 'loadOverlib', false );

$no_html 	= strtolower( JRequest::getVar( 'no_html', 0 ) );
$format 	= JRequest::getVar( 'format', $no_html ? 'raw' : 'html',  '', 'string'  );
$tmpl 	 	= JRequest::getVar( 'tmpl', isset($tmpl) ? $tmpl : 'index.php',  '', 'string'  );

if(empty($option)) {
	$tmpl = 'cpanel.php';
}

if (is_null(JSession::get('guest')) || JSession::get('guest')) {
	$tmpl = 'login.php';
}

// loads template file
$cur_template = $mainframe->getTemplate();

$params = array(
	'template' 	=> $cur_template,
	'file'		=> $tmpl,
	'directory'	=> JPATH_BASE.DS.'templates'
);

$document =& $mainframe->getDocument($format);
$document->setTitle( $mainframe->getCfg('sitename' ). ' - ' .JText::_( 'Administration' ));
$document->display($mainframe->getCfg('caching_tmpl'), $mainframe->getCfg('gzip'), $params );

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null ;

JDEBUG ? $_PROFILER->report( true, $mainframe->getCfg( 'debug_db' ) ) : null;
?>