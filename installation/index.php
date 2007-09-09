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

define( 'DS', DIRECTORY_SEPARATOR );

require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

// create the mainframe object
$mainframe =& JFactory::getApplication('installation');

// initialuse the application
$mainframe->initialise();

// render the application
$mainframe->render();

/**
 * RETURN THE RESPONSE
 */
echo JResponse::toString();
