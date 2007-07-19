<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installation
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

define( '_JEXEC', 1 );

define( 'JPATH_BASE', dirname( __FILE__ ) );

require_once( JPATH_BASE .'/includes/defines.php' );
require_once( JPATH_BASE .'/includes/application.php' );

// create the mainframe object
$mainframe = new JInstallation();

// create the session
$mainframe->loadSession('installation');

// initialuse the application
$mainframe->initialise();

// render the application
$mainframe->render();

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString();
