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

jimport('pattemplate.patErrorManager');

/**
 * Error Handling Class
 * 
 * This class is an proxy of the patError class
 * 
 * @static
 * @package Joomla
 * @subpackage JFramework
 * @since 1.1
 */
class JError extends patErrorManager {

	/**
	* method for checking whether the return value of a pat application method is a pat
	* error object.
	*
	* @static
	* @access	public
	* @param	mixed	&$object
	* @return	boolean $result	True if argument is a patError-object, false otherwise.
	*/
    function isError( &$object ) {
		return patErrorManager::isError($object);
    }

   /**
	* wrapper for the {@link raise()} method where you do not have to specify the
	* error level - a {@link patError} object with error level E_ERROR will be returned.
	*
	* @static
	* @access	public
	* @param	string	$code	The application-internal error code for this error
	* @param	string	$msg	The error message, which may also be shown the user if need be.
	* @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	* @return	object	$error	The configured patError object
	* @see		patErrorManager
	*/
	function &raiseError( $code, $msg, $info = null ) {
		return patErrorManager::raise( E_ERROR, $code, $msg, $info );
	}

   /**
	* wrapper for the {@link raise()} method where you do not have to specify the
	* error level - a {@link patError} object with error level E_WARNING will be returned.
	*
	* @static
	* @access	public
	* @param	string	$code	The application-internal error code for this error
	* @param	string	$msg	The error message, which may also be shown the user if need be.
	* @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	* @return	object	$error	The configured patError object
	* @see		patErrorManager
	*/
	function &raiseWarning( $code, $msg, $info = null ) {
		return patErrorManager::raise( E_WARNING, $code, $msg, $info );
	}

   /**
	* wrapper for the {@link raise()} method where you do not have to specify the
	* error level - a {@link patError} object with error level E_NOTICE will be returned.
	*
	* @static
	* @access	public
	* @param	string	$code	The application-internal error code for this error
	* @param	string	$msg	The error message, which may also be shown the user if need be.
	* @param	mixed	$info	Optional: Additional error information (usually only developer-relevant information that the user should never see, like a database DSN).
	* @return	object	$error	The configured patError object
	* @see		patErrorManager
	*/
	function &raiseNotice( $code, $msg, $info = null ) {
		return patErrorManager::raise( E_NOTICE, $code, $msg, $info );
	}
}
?>