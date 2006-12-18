<?php
/**
* @version $Id$
* @package Joomla.Framework
* @subpackage Environment
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
* Class for managing HTTP sessions
*
* Provides access to session-state values as well as session-level
* settings and lifetime management methods.
* Based on the standart PHP session handling mechanism it provides
* for you more advanced features such as expire timeouts.
*
* @author		Johan Janssens <johan.janssens@joomla.org>
* @package		Joomla.Framework
* @subpackage	Environment
* @since		1.5
*/
class JSession extends JObject
{
	/**
	 * internal state
	 *
	 * @access protected
	 * @var	string $_state one of 'active'|'expired'|'destroyed|'error'
     * @see getState()
	 */
	var	$_state	=	'active';

	/**
	 * Maximum age of unused session
	 *
	 * @access protected
	 * @var	string $_expire minutes
	 */
	var	$_expire	=	null;

	/**
	* Constructor
	*
	* @access protected
	* @param string $id name-prefix used for internal storage of session-data
	* @param array $options optional parameters
	*/
	function __construct( $options = array() )
	{
		session_write_close();

		$this->_setOptions( $options );

		//load the session
		$this->_start();

		//initialise the session
		$this->_setCounter();
		$this->_setTimers();

		$this->_state =	'active';

		// perform security checks
		$this->_validate();
	}

	/**
	 * Get current state of session
	 *
	 * @access public
	 * @return string The session state
	 */
    function getState()
    {
		return $this->_state;
    }


	/**
	 * Get session name
	 *
	 * @access public
	 * @return string The session name
	 */
    function getName()
    {
		if( $this->_state === 'destroyed' ) {
			// @TODO : raise error
			return null;
		}
		return session_name();
    }

	/**
	 * Get session id
	 *
	 * @access public
	 * @return string The session name
	 */
    function getId()
    {
		if( $this->_state === 'destroyed' ) {
			// @TODO : raise error
			return null;
		}
		return session_id();
    }

   /**
    * Check whether this session is currently created
	*
	* @access public
	* @return boolean $result true on success
	*/
    function isNew()
    {
	    $counter = $this->get( 'session.counter' );
		if( $counter === 1 ) {
			return true;
		}
        return false;
    }

	 /**
     * Get date from session
     *
     * @static
     * @access public
     * @param  string $name    Name of a variable
     * @param  mixed  $default Default value of a variable if not set
     * @return mixed  Value of a variable
     */
    function &get($name, $default = null)
    {
		if($this->_state !== 'active') {
			// @TODO :: generated error here
			$error = null;
			return $error;
		}

		if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
        return $default;
    }

    /**
     * Save date into session
     *
     * @access public
     * @param  string $name  Name of a variable
     * @param  mixed  $value Value of a variable
     * @return mixed  Old value of a variable
     */
    function set($name, $value)
    {
         if($this->_state !== 'active') {
			// @TODO :: generated error here
			return null;
		}

		$old = isset($_SESSION[$name]) ?  $_SESSION[$name] : null;

		if (null === $value) {
            unset($_SESSION[$name]);
        } else {
            $_SESSION[$name] = $value;
        }

        return $old;
    }

	/**
	* Check wheter a session value exists
	*
	* @access public
	* @param string $name name of variable
	* @return boolean $result true if the variable exists
	*/
	function has( $name )
	{
		if( $this->_state !== 'active' ) {
			// @TODO :: generated error here
			return null;
		}

		return isset( $_SESSION[$name] );
	}

	/**
	* Unset data from session
	*
	* @access public
	* @param string $name name of variable
	* @return mixed $value the value from session or NULL if not set
	*/
	function clear( $name )
	{
		if( $this->_state !== 'active' ) {
			// @TODO :: generated error here
			return null;
		}

		$value	=	null;
		if( isset( $_SESSION[$name] ) ) {
			$value	=	$_SESSION[$name];
			unset( $_SESSION[$name] );
		}

		return $value;
	}

	/**
    * Start a session
    *
    * Creates a session (or resumes the current one based on the state of the session)
 	*
	* @access private
	* @return boolean $result true on success
	*/
    function _start()
    {
		//  start session if not startet
		if( $this->_state == 'restart' ) {
            session_id( $this->_createId() );
        }

		session_cache_limiter('none');
		session_start();

		// Check to see if the user id is already set
		// TODO :: this needs to be moved somewehere
		if(($userid = $this->get('session.user.id'))) {
			// And if they have a valid session entry in the table
			$db = JFactory::getDBO();
			$db->setQuery('SELECT session_id FROM #__session WHERE userid = '. $userid);
			$db->Query() or die($db->getErrorMsg());
			if(!$db->getNumRows()) {
				// No rows for this user, their session was wiped out :)
				$this->restart();
			}
		}

		// Send modified header for IE 6.0 Security Policy
		header('P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"');

        return true;
    }


	/**
     * Frees all session variables and destroys all data registered to a session
     *
     * This method resets the $_SESSION variable and destroys all of the data associated
     * with the current session in its storage (file or DB). It forces new session to be
     * started after this method is called. It does not unset the session cookie.
     *
     * @static
     * @access public
     * @return void
     * @see    session_unset()
     * @see    session_destroy()
     */
	function destroy()
    {
        // session was already destroyed
		if( $this->_state === 'destroyed' ) {
            return true;
		}

		// In order to kill the session altogether, like to log the user out, the session id
		// must also be unset. If a cookie is used to propagate the session id (default behavior),
		// then the session cookie must be deleted.
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-42000, '/');
		}

        session_unset();
        session_destroy();

		$this->_state = 'destroyed';
		return true;
	}

   /**
    * restart a destroyed or locked session
	*
	* @access public
	* @return boolean $result true on success
	* @see destroy
	*/
    function restart()
    {
        $this->destroy();
        if( $this->_state !==  'destroyed' ) {
           // @TODO :: generated error here
            return false;
        }

        $this->_state   =   'restart';
		$this->_start();
		$this->_state	=	'active';

		$this->_validate();
		$this->_setCounter();

        return true;
    }

	/**
	* Create a new session and copy variables from the old one
	*
	* @abstract
	* @access public
	* @return boolean $result true on success
	*/
    function fork()
    {
		if( $this->_state !== 'active' ) {
			// @TODO :: generated error here
			return false;
		}

		// save values
		$values	= $_SESSION;

		// keep session config
		$trans	=	ini_get( 'session.use_trans_sid' );
		if( $trans ) {
			ini_set( 'session.use_trans_sid', 0 );
		}
		$cookie	=	session_get_cookie_params();

		// create new session id
		$id	=	$this->_createId( strlen( $this->getId() ) );

		// kill session
		session_destroy();

		// restore config
		ini_set( 'session.use_trans_sid', $trans );
		session_set_cookie_params( $cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure'] );

		// restart session with new id
		session_id( $id );
		session_start();

		return true;
    }

	 /**
     * Writes session data and ends session
     *
     * Session data is usually stored after your script terminated without the need
     * to call JSession::pauze(),but as session data is locked to prevent concurrent
     * writes only one script may operate on a session at any time. When using
     * framesets together with sessions you will experience the frames loading one
     * by one due to this locking. You can reduce the time needed to load all the
     * frames by ending the session as soon as all changes to session variables are
     * done.
     *
     * @access public
     * @see    session_write_close()
     */
    function pause()
    {
        session_write_close();
    }

	 /**
     * Create a session id
     *
     * @static
     * @access private
     * @return string Session ID
     */
	function _createId( )
	{
		$agent = $_SERVER['HTTP_USER_AGENT'];
		$id    = md5( $agent . uniqid(dechex(rand())) . $_SERVER['REMOTE_ADDR'] );
		return $id;
	}

	/**
	* Create a token-string
	*
	* @access protected
	* @param int $length lenght of string
	* @return string $id generated token
	*/
	function _createToken( $length = 32 )
	{
		static $chars	=	'0123456789abcdef';
		$max			=	strlen( $chars ) - 1;
		$token			=	'';
		$name 			=  session_name();
		for( $i = 0; $i < $length; ++$i ) {
			$token .=	$chars[ (rand( 0, $max )) ];
		}

		return 'token_'.md5($token.$name);
	}

	/**
    * Set counter of session usage
	*
	* @access protected
	* @return boolean $result true on success
	*/
    function _setCounter()
    {
		$counter = $this->get( 'session.counter', 0 );
		++$counter;

		$this->set( 'session.counter', $counter );
    	return true;
    }

   /**
    * Set the session timers
	*
	* @access protected
	* @return boolean $result true on success
	*/
    function _setTimers()
    {
		if( !$this->has( 'session.timer.start' ) )
		{
            $start	=	time();

        	$this->set( 'session.timer.start' , $start );
        	$this->set( 'session.timer.last'  , $start );
        	$this->set( 'session.timer.now'   , $start );
        }

        $this->set( 'session.timer.last', $this->get( 'session.timer.now' ) );
        $this->set( 'session.timer.now', time() );

    	return true;
    }

	/**
	* set additional session options
	*
	* @access protected
	* @param array $options list of parameter
	* @return boolean $result true on success
	*/
    function _setOptions( &$options )
    {
		// set name
		if( isset( $options['name'] ) ) {
			session_name( md5($options['name']) );
		}

		// set id
		if( isset( $options['id'] ) ) {
			session_id( $options['id'] );
		}

		// set expire time
		if( isset( $options['expire'] ) ) {
			$this->_expire	=	$options['expire'];
		}

		return true;
    }

	/**
	* Do some checks for security reason
	*
	* - timeout check (expire)
	* - ip-fixiation
	* - browser-fixiation
	*
	* If one check failed, session data has to be cleaned.
	*
	* @access protected
    * @param boolean $restart reactivate session
	* @return boolean $result true on success
	*/
	function _validate( $restart = false )
	{
		// allow to restsart a session
		if( $restart )
		{
			$this->_state	=	'active';

			$this->set( 'session.client.address'		, null );
			$this->set( 'session.client.forwarded'	, null );
			$this->set( 'session.client.browser'	, null );
			$this->set( 'session.token'				, null );
		}

		// check if session has expired
		if( $this->_expire )
		{
			$curTime =	$this->get( 'session.timer.now' , 0  );
			$maxTime =	$this->get( 'session.timer.last', 0 ) + (60 * $this->_expire);

			// empty session variables
			if( $maxTime < $curTime ) {
				$this->_state	=	'expired';
				return false;
			}
		}

		// check for client-ip
		if( isset( $_SERVER['REMOTE_ADDR'] ) )
		{
			$ip	=	$this->get( 'session.client.address' );

			if( $ip === null )
			{
				$ip = $_SERVER['REMOTE_ADDR'];

				if(isset($_SERVER['HTTP_USER_AGENT']))
				{
					if (strpos('AOL', $_SERVER['HTTP_USER_AGENT']) !== false)
					{
						$address	= explode('.',$ip);
						$ip	   = $address[0] .'.'. $address[1] .'.'. $address[2];
					}
				}

				$this->set( 'session.client.address', $ip );
			}
			else if( $_SERVER['REMOTE_ADDR'] !== $ip )
			{
				$this->_state	=	'error';
				return false;
			}

			// some polite proxy server tell, for whom they forward the request for
			if( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
			{
				$forwarded	=	$this->get( 'session.client.forwarded' );

				if( $forwarded === null ) {
					$this->set( 'session.client.forwarded', $_SERVER['HTTP_X_FORWARDED_FOR'] );
				}
				else if( $_SERVER['HTTP_X_FORWARDED_FOR'] !== $forwarded )
				{
					$this->_state = 'error';
					return false;
				}
			}
		}

		// check for clients browser
		if( isset( $_SERVER['HTTP_USER_AGENT'] ) )
		{
			$browser = $this->get( 'session.client.browser' );

			if( $browser === null ) {
				$this->set( 'session.client.browser', $_SERVER['HTTP_USER_AGENT'] );
			}
			else if( $_SERVER['HTTP_USER_AGENT'] !== $browser ) {
				$this->_state	=	'error';
				return false;
			}
		}

		// check token!
		$token = $this->get( 'session.token' );

		// check if token is valid!
		if( $token !== null )
		{
			$match	=	false;
			// check token from request
			$var = JRequest::getVar( 'token', '', 'post' );
			if(!empty($var) && ($var != $token)) {
				$this->_state = 'error';
				return false;
			}

		}

		// save new token
		$token	=	$this->_createToken( 12 );
		$this->set( 'session.token', $token );
		return true;
	}
}
?>
