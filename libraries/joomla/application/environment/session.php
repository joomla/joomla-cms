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

/** @const HTTP_SESSION_STARTED - The session was started with the current request */
define("HTTP_SESSION_STARTED",      1);
/** @const HTTP_SESSION_STARTED - No new session was started with the current request */
define("HTTP_SESSION_CONTINUED",    2);

/**
* Class for managing HTTP sessions
*
* Provides access to session-state values as well as session-level
* settings and lifetime management methods.
* Based on the standart PHP session handling mechanism
* it provides for you more advanced features such as
* database container, idle and expire timeouts, etc.
*
* This class has many influences from the PEAR HTTP_Session module
*
* @static
* @author		Johan Janssens <johan.janssens@joomla.org>
* @package		Joomla.Framework
* @subpackage	Application
* @since		1.1
*/
class JSession
{
	/**
     * Initializes session data
     *
     * Creates a session (or resumes the current one
     * based on the session id being passed
     * via a GET variable or a cookie).
     * You can provide your own name and/or id for a session.
     *
     * @static
     * @access public
     * @param  $name  string Name of a session, default is 'SessionID'
     * @param  $id    string Id of a session which will be used
     *                       only when the session is new
     * @return void
     * @see    session_name()
     * @see    session_id()
     * @see    session_start()
  	 */
	function start($name = 'SessionID', $id = null)
    {
        JSession::name($name);
        if (is_null(JSession::_detectID())) {
            JSession::id($id ? $id : JSession::_createID());
        }
        session_start();
        if (!isset($_SESSION['__HTTP_Session_Info'])) {
            $_SESSION['__HTTP_Session_Info'] = HTTP_SESSION_STARTED;
        } else {
            $_SESSION['__HTTP_Session_Info'] = HTTP_SESSION_CONTINUED;
        }
    }

	 /**
     * Writes session data and ends session
     *
     * Session data is usually stored after your script
     * terminated without the need to call JSession::stop(),
     * but as session data is locked to prevent concurrent
     * writes only one script may operate on a session at any time.
     * When using framesets together with sessions you will
     * experience the frames loading one by one due to this
     * locking. You can reduce the time needed to load all the
     * frames by ending the session as soon as all changes
     * to session variables are done.
     *
     * @static
     * @access public
     * @return void
     * @see    session_write_close()
     */
    function pause()
    {
        session_write_close();
    }

	/**
     * Frees all session variables and destroys all data
     * registered to a session
     *
     * This method resets the $_SESSION variable and
     * destroys all of the data associated
     * with the current session in its storage (file or DB).
     * It forces new session to be started after this method
     * is called. It does not unset the session cookie.
     *
     * @static
     * @access public
     * @return void
     * @see    session_unset()
     * @see    session_destroy()
     */
    function destroy()
    {
        session_unset();
        session_destroy();

        // set session handlers again to avoid fatal error in case HTTP_Session::start() will be called afterwards
        if (isset($GLOBALS['HTTP_Session_Container']) && is_a($GLOBALS['HTTP_Session_Container'], 'HTTP_Session_Container')) {
            $GLOBALS['HTTP_Session_Container']->set();
        }
    }

	  /**
     * Free all session variables
     *
     * @todo   TODO Save expire and idle timestamps?
     * @static
     * @access public
     * @return void
     */
    function clear()
    {
		$info = $_SESSION['__HTTP_Session_Info'];
        session_unset();
		$_SESSION['__HTTP_Session_Info'] = $info;
    }

	 /**
     * Sets new name of a session
     *
     * @static
     * @access public
     * @param  string $name New name of a sesion
     * @return string Previous name of a session
     * @see    session_name()
     */
    function name($name = null)
    {
        return isset($name) ? session_name($name) : session_name();
    }

    /**
     * Sets new ID of a session
     *
     * @static
     * @access public
     * @param  string $id New ID of a sesion
     * @return string Previous ID of a session
     * @see    session_id()
     */
    function id($id = null)
    {
        return isset($id) ? session_id($id) : session_id();
    }

    /**
     * Sets the maximum expire time
     *
     * @static
     * @access public
     * @param  integer $time Time in seconds
     * @param  bool    $add  Add time to current expire time or not
     * @return void
     */
    function setExpire($time, $add = false)
    {
        if ($add) {
            if (!isset($_SESSION['__HTTP_Session_Expire_TS'])) {
                $_SESSION['__HTTP_Session_Expire_TS'] = time() + $time;
            }

            // update session.gc_maxlifetime
            $currentGcMaxLifetime = JSession::setGcMaxLifetime(null);
            JSession::setGcMaxLifetime($currentGcMaxLifetime + $time);

        } elseif (!isset($_SESSION['__HTTP_Session_Expire_TS'])) {
                $_SESSION['__HTTP_Session_Expire_TS'] = $time;
        }
    }

    /**
     * Sets the maximum idle time
     *
     * Sets the time-out period allowed
     * between requests before the session-state
     * provider terminates the session.
     *
     * @static
     * @access public
     * @param  integer $time Time in seconds
     * @return void
     */
    function setIdle($time)
    {
        $_SESSION['__HTTP_Session_Idle'] = $time;
    }

    /**
     * Returns the time up to the session is valid
     *
     * @static
     * @access public
     * @return integer Time when the session idles
     */
    function sessionValidThru()
    {
		if (!isset($_SESSION['__HTTP_Session_Idle_TS']) || !isset($GLOBALS['__HTTP_Session_Idle'])) {
            return 0;
        } else {
            return $_SESSION['__HTTP_Session_Idle_TS'] + $_SESSION['__HTTP_Session_Idle'];
        }
    }

    /**
     * Check if session is expired
     *
     * @static
     * @access public
     * @return boolean
     */
    function isExpired()
    {
        if (isset($_SESSION['__HTTP_Session_Expire_TS']) && $_SESSION['__HTTP_Session_Expire_TS'] < time()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if session is idle
     *
     * @static
     * @access public
     * @return boolean
     */
    function isIdle()
    {
		if (isset($_SESSION['__HTTP_Session_Idle_TS']) && (($_SESSION['__HTTP_Session_Idle_TS'] + $_SESSION['__HTTP_Session_Idle']) < time())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the idletime
     *
     * @static
     * @access public
     * @return void
     */
    function updateIdle()
    {
        $_SESSION['__HTTP_Session_Idle_TS'] = time();
    }

    /**
     * If optional parameter is specified it indicates
     * whether the module will use cookies to store
     * the session id on the client side
     *
     * It returns the previous value of this property
     *
     * @static
     * @access public
     * @param  boolean $useCookies If specified it will replace the previous value
     *                             of this property
     * @return boolean The previous value of the property
     */
    function useCookies($useCookies = null)
    {
        $return = ini_get('session.use_cookies') ? true : false;
        if (isset($useCookies)) {
            ini_set('session.use_cookies', $useCookies ? 1 : 0);
        }
        return $return;
    }

    /**
     * Gets a value indicating whether the session
     * was created with the current request
     *
     * You MUST call this method only after you have started
     * the session with the JSession::start() method.
     *
     * @static
     * @access public
     * @return boolean true if the session was created
     *                 with the current request, false otherwise
     */
    function isNew()
    {
        // The best way to check if a session is new is to check
        // for existence of a session data storage
        // with the current session id, but this is impossible
        // with the default PHP module wich is 'files'.
        // So we need to emulate it.
        return !isset($_SESSION['__HTTP_Session_Info']) ||
            $_SESSION['__HTTP_Session_Info'] == HTTP_SESSION_STARTED;
    }

    /**
     * Register variable with the current session
     *
     * @static
     * @access public
     * @param  string $name Name of a global variable
     * @return void
     * @see    session_register()
     */
    function register($name)
    {
        session_register($name);
    }

    /**
     * Unregister a variable from the current session
     *
     * @static
     * @access public
     * @param  string $name Name of a global variable
     * @return void
     * @see    session_unregister()
     */
    function unregister($name)
    {
        session_unregister($name);
    }

    /**
     * Returns session variable
     *
     * @static
     * @access public
     * @param  string $name    Name of a variable
     * @param  mixed  $default Default value of a variable if not set
     * @return mixed  Value of a variable
     */
    function &get($name, $default = null)
    {
        if (!isset($_SESSION[$name]) && isset($default)) {
            $_SESSION[$name] = $default;
        }
        return $_SESSION[$name];
    }

    /**
     * Sets session variable
     *
     * @access public
     * @param  string $name  Name of a variable
     * @param  mixed  $value Value of a variable
     * @return mixed  Old value of a variable
     */
    function set($name, $value)
    {
        $old = isset($_SESSION[$name]) ?  $_SESSION[$name] : null;
        
		if (null === $value) {
            unset($_SESSION[$name]);
        } else {
            $_SESSION[$name] = $value;
        }
		
        return $old;
    }

    /**
     * Returns local variable of a script
     *
     * Two scripts can have local variables with the same names
     *
     * @static
     * @access public
     * @param  string $name    Name of a variable
     * @param  mixed  $default Default value of a variable if not set
     * @return mixed  Value of a local variable
     */
    function &getLocal($name, $default = null)
    {
        $local = md5(JSession::localName());
        if (!is_array($_SESSION[$local])) {
            $_SESSION[$local] = array();
        }
        if (!isset($_SESSION[$local][$name]) && isset($default)) {
            $_SESSION[$local][$name] = $default;
        }
        return $_SESSION[$local][$name];
    }

    /**
     * Sets local variable of a script.
     * Two scripts can have local variables with the same names.
     *
     * @static
     * @access public
     * @param  string $name  Name of a local variable
     * @param  mixed  $value Value of a local variable
     * @return mixed  Old value of a local variable
     */
    function setLocal($name, $value)
    {
        $local = md5(JSession::localName());
        if (!is_array($_SESSION[$local])) {
            $_SESSION[$local] = array();
        }
        $return = $_SESSION[$local][$name];
        if (null === $value) {
            unset($_SESSION[$local][$name]);
        } else {
            $_SESSION[$local][$name] = $value;
        }
        return $return;
    }

    /**
     * Sets new local name
     *
     * @static
     * @access public
     * @param  string New local name
     * @return string Previous local name
     */
    function localName($name = null)
    {
        $return = @$GLOBALS['__HTTP_Session_Localname'];
        if (!empty($name)) {
            $GLOBALS['__HTTP_Session_Localname'] = $name;
        }
        return $return;
    }

    /**
     * If optional parameter is specified it indicates
     * whether the session id will automatically be appended to
     * all links
     *
     * It returns the previous value of this property
     *
     * @static
     * @access public
     * @param  boolean $useTransSID If specified it will replace the previous value
     *                              of this property
     * @return boolean The previous value of the property
     */
    function useTransSID($useTransSID = null)
    {
        $return = ini_get('session.use_trans_sid') ? true : false;
        if (isset($useTransSID)) {
            ini_set('session.use_trans_sid', $useTransSID ? 1 : 0);
        }
        return $return;
    }

    /**
     * If optional parameter is specified it determines the number of seconds
     * after which session data will be seen as 'garbage' and cleaned up
     *
     * It returns the previous value of this property
     *
     * @static
     * @access public
     * @param  boolean $gcMaxLifetime If specified it will replace the previous value
     *                                of this property
     * @return boolean The previous value of the property
     */
    function setGcMaxLifetime($gcMaxLifetime = null)
    {
        $return = ini_get('session.gc_maxlifetime');

        if (isset($gcMaxLifetime) && is_int($gcMaxLifetime) && $gcMaxLifetime >= 1) {
            ini_set('session.gc_maxlifetime', $gcMaxLifetime);
        }
        return $return;
    }

    /**
     * If optional parameter is specified it determines the
     * probability that the gc (garbage collection) routine is started
     * and session data is cleaned up
     *
     * It returns the previous value of this property
     *
     * @static
     * @access public
     * @param  boolean $gcProbability If specified it will replace the previous value
     *                                of this property
     * @return boolean The previous value of the property
     */
    function setGcProbability($gcProbability = null)
    {
        $return = ini_get('session.gc_probability');
        if (isset($gcProbability) && is_int($gcProbability) && $gcProbability >= 1 && $gcProbability <= 100) {
            ini_set('session.gc_probability', $gcProbability);
        }
        return $return;
    }

	 /**
     * Tries to find any session id in $_GET, $_POST or $_COOKIE
     *
     * @static
     * @access private
     * @return string Session ID (if exists) or null
     */
    function _detectID()
    {
        if (JSession::useCookies()) {
            if (isset($_COOKIE[JSession::name()])) {
                return $_COOKIE[JSession::name()];
            }
        } else {
            if (isset($_GET[JSession::name()])) {
                return $_GET[JSession::name()];
            }
            if (isset($_POST[JSession::name()])) {
                return $_POST[JSession::name()];
            }
        }
        return null;
    }
	
	 /**
     * Create a session id
     *
     * @static
     * @access private
     * @return string Session ID
     */
	function _createID()
	{	
		if (phpversion() <= '4.2.1') {
			$agent = getenv( 'HTTP_USER_AGENT' );
		} else {
			$agent = $_SERVER['HTTP_USER_AGENT'];
		}
		return md5( $agent . uniqid(dechex(rand())) . $_SERVER['REMOTE_ADDR'] );
	}
}

?>