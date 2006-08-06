<?php
/**
 * @version $Id: globals.php-off 47 2005-09-15 02:55:27Z rhuk $
 * @package Joomla
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
 * GNU General Public License or other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

/**
 * Use 1 to emulate register_globals = on [DEPRECATED!]
 *
 * Use 0 to emulate regsiter_globals = off
 */
define( 'RG_EMULATION', 0 );

/**
 * Adds an array to the GLOBALS array and checks that the GLOBALS variable is
 * not being attacked
 * @param array
 * @param boolean True if the array is to be added to the GLOBALS
 */
function checkInputArray( &$array, $globalise=false ) {
	static $banned = array( '_files', '_env', '_get', '_post', '_cookie', '_server', '_session', 'globals' );

	foreach ($array as $key => $value) {
		$key = urldecode( $key );
		$intval = intval( $key );
		// PHP GLOBALS injection bug 
		$failed = in_array( strtolower( $key ), $banned );
		// PHP Zend_Hash_Del_Key_Or_Index bug
		$failed |= ((string)$intval == (string)$key && $intval != 0);
		if ($failed) {
			die( 'Illegal variable <b>' . implode( '</b> or <b>', $banned ) . '</b> passed to script.' );
		}
		if ($globalise) {
			$GLOBALS[$key] = $value;
		}
	}
}

/**
 * Emulates register globals = off
 */
function unregisterGlobals () {
	checkInputArray( $_FILES );
	checkInputArray( $_ENV );
	checkInputArray( $_GET );
	checkInputArray( $_POST );
	checkInputArray( $_COOKIE );
	checkInputArray( $_SERVER );

	if (isset( $_SESSION )) {
		checkInputArray( $_SESSION );
	}

	$REQUEST = $_REQUEST;
	$GET = $_GET;
	$POST = $_POST;
	$COOKIE = $_COOKIE;
	if (isset ( $_SESSION )) {
		$SESSION = $_SESSION;
	}
	$FILES = $_FILES;
	$ENV = $_ENV;
	$SERVER = $_SERVER;
	foreach ($GLOBALS as $key => $value) {
		if ( $key != 'GLOBALS' ) {
			unset ( $GLOBALS [ $key ] );
		}
	}
	$_REQUEST = $REQUEST;
	$_GET = $GET;
	$_POST = $POST;
	$_COOKIE = $COOKIE;
	if (isset ( $SESSION )) {
		$_SESSION = $SESSION;
	}
	$_FILES = $FILES;
	$_ENV = $ENV;
	$_SERVER = $SERVER;
}

// force register_globals = off
unregisterGlobals();
?>
