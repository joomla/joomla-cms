<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Administration
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

$path = str_replace( '\\', '/', JPATH_BASE );
$parts = explode( '/', $path );

//Defines
DEFINE('JPATH_ROOT'         , implode( '/', $parts ) );
DEFINE('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator' );
DEFINE('JPATH_LIBRARIES'    , JPATH_ROOT . DIRECTORY_SEPARATOR . 'libraries');
DEFINE('JPATH_INSTALLATION' , JPATH_ROOT . DIRECTORY_SEPARATOR . 'installation');
?>
