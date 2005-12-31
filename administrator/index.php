<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
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

require_once ( 'includes/defines.php'     );
require_once ( 'includes/application.php' );
require_once ( 'includes/template.php'    );

$option = mosGetParam( $_REQUEST, 'option', NULL );

// create the mainframe object
$mainframe =& new JAdministrator($option);

//get the database object
$database =& $mainframe->getDBO();

// load system plugin group
JPluginHelper::importGroup( 'system' );

// trigger the onStart events
$mainframe->triggerEvent( 'onBeforeStart' );

//get the acl object (for backwards compatibility)
$acl =& JFactory::getACL();

//create the session
$mainframe->setSession( $mainframe->getCfg('live_site').$mainframe->_client );

// trigger the onAfterStart events
$mainframe->triggerEvent( 'onAfterStart' );

if ($option == 'login') 
{
	if (!$mainframe->login()) 
	{
		$mainframe->logout();
		mosErrorAlert( JText::_( 'LOGIN_INCORRECT' ) );
	}
	
	$mainframe->setUserState( 'lang', mosGetParam( $_REQUEST, 'lang', $mosConfig_lang ) );
	JSession::pause();

	mosRedirect( 'index2.php' );
}

$cur_template = $mainframe->getTemplate();

$document =& $mainframe->getDocument();
$document->parse($cur_template, 'login.php');

initDocument($document); //initialise the document

$document->display( 'login.php', $mainframe->getCfg('gzip') );
?>