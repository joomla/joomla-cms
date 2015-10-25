<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

// no direct access
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * The classes CBCookie, CBSessionStorage and CBSession that were in here have moved to libraries/CBLib/CB/Legacy folder.
 */

/**
 * Checks that all globals are safe to known PHP and Zend bugs
 *
 * @deprecated 2.0  Don't use direct access files at all
 */
function cbCheckSafeGlobals( )
{
	static $banned	=	array( '_files', '_env', '_get', '_post', '_cookie', '_server', '_session', 'globals' );

	$check			=	array( &$_FILES, &$_ENV, &$_GET, &$_POST, &$_COOKIE, &$_SERVER );

	for ( $i = 0, $n = count( $check ) ; $i < $n ; $i++ ) {
		foreach ( array_keys( $check[$i] ) as $k ) {
			// check for PHP globals injection bug and for PHP Zend_Hash_Del_Key_Or_Index bug:
			if ( in_array( strtolower( $k ), $banned ) || is_numeric( $k ) ) {
				die( sprintf( 'Illegal variable %s.', addslashes( htmlspecialchars( $k ) ) ) );
			}
		}
	}
}

/**
 * Unregister super-globals if register_globals is set
 *
 * @deprecated 2.0  Don't use direct access files at all
 */
function cbUnregisterGlobals( )
{
	if ( ini_get( 'register_globals' ) ) {
		$check		=	array( &$_SERVER, &$_ENV, &$_FILES, &$_COOKIE, &$_POST, &$_GET );

		if ( isset( $_SESSION ) ) {
			array_unshift ( $check, $_SESSION );
		}

		for ( $i = 0, $n = count( $check ) ; $i < $n ; $i++ ) {
			foreach ( array_keys( $check[$i] ) as $key ) {
				if ( $key != 'GLOBALS' ) {
					unset( $GLOBALS[$key] );
				}
			}
		}
	}
}
