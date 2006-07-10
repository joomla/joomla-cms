<?php
/**
* @version $Id: index.php 1244 2005-11-29 02:39:31Z Jinx $
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

require_once ( JPATH_BASE .'/includes/defines.php' );
require_once ( JPATH_BASE .'/includes/application.php' );
require_once ( JPATH_BASE .'/includes/template.php'  );

// create the mainframe object
$mainframe = new JSite();

// set the configuration
$mainframe->setConfiguration(JPATH_CONFIGURATION . DS . 'configuration.php');

//get the database object (for backwards compatibility)
$database =& $mainframe->getDBO();

// load system plugin group
JPluginHelper::importPlugin( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

// create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->getClientId() );

// set language
$mainframe->setLanguage();

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

JDEBUG ? $_PROFILER->mark( 'afterStartFramework' ) : null;

// initialise some common request directives
$option = $mainframe->getOption();
$Itemid = $mainframe->getItemid();

//TODO :: should we show a login screen here ?
$menu =& JMenu::getInstance();
if(!$menu->authorize($Itemid, $mainframe->getUser())) {
	JError::raiseError( 403, JText::_('Not Authorised') );
}

// set for overlib check
$mainframe->set( 'loadOverlib', false );

$cur_template = JRequest::getVar( 'template', $mainframe->getTemplate(), 'default', 'string' );
$no_html 	  = JRequest::getVar( 'no_html', 0, '', 'int' );
$format 	  = JRequest::getVar( 'format', $no_html ? 'raw' : 'html',  '', 'string'  );
$tmpl 	 	  = JRequest::getVar( 'tmpl', isset($tmpl) ? $tmpl : 'index.php',  '', 'string'  );


if ($mainframe->getCfg('offline') && $user->get('gid') < '23' ) {
	$tmpl = 'offline.php';
}

$params = array(
	'outline'   => JRequest::getVar('tp', 0 ),
	'template' 	=> $cur_template,
	'file'		=> $tmpl,
	'directory'	=> JPATH_BASE.DS.'templates'
);

$document =& $mainframe->getDocument($format);
$document->setTitle( $mainframe->getCfg('sitename' ));

// trigger the onBeforeDisplay events
$mainframe->triggerEvent( 'onBeforeDisplay' );

$document->display( $mainframe->getCfg('caching_tmpl'), $mainframe->getCfg('gzip'), $params);

// trigger the onAfterDisplay events
$mainframe->triggerEvent( 'onAfterDisplay' );

JDEBUG ? $_PROFILER->mark( 'afterDisplayOutput' ) : null;

JDEBUG ? $_PROFILER->report( true, $mainframe->getCfg( 'debug_db' ) ) : null;

?>