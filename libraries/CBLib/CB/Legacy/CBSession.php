<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:58 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

defined('CBLIB') or die();

/**
 * CBSession Class implementation
 * This class implements CB independant database-based scalable cookies-only-based secure sessions.
 * @deprecated 2.0 Use PHP sessions instead
 */
class CBSession
{
	private $_session_id		=	null;
	private $_session_var		=	null;
	private $_cookie_name		=	'cb_web_session';
	private $_life_time			=	null;
	private $_sessionRecord		=	null;
	private $_mode;
	/**
	 * Mini-db
	 * @var CBSessionStorage
	 */
	private $_db				=	null;

	/**
	 * Constructor
	 * @access private
	 *
	 * @param  string     $mode  'cookie' for most secure way (requires cookies enabled), 'sessionid': set id by session
	 * @return CBSession
	 */
	public function __construct( $mode = 'cookie' )
	{
		$this->_mode		=	$mode;
		// Read the maxlifetime setting from PHP:
		$this->_life_time	=	max( get_cfg_var( 'session.gc_maxlifetime' ), 43200 );	// 12 hours minimum
		$dbConnect			=	new CBSessionStorage();
		$this->_db			=	$dbConnect->connect();
	}

	/**
	 * Gets singleton
	 *
	 * @param  string     $mode  'cookie' for most secure way (requires cookies enabled), 'sessionid': set id by session
	 * @return CBSession
	 */
	public static function getInstance( $mode = 'cookie' )
	{
		static $session					=	array();
		if ( ! isset( $session[$mode] ) ) {
			$session[$mode]				=	new CBSession( $mode );
		}
		return $session[$mode];
	}

	/**
	 * session_start() creates a session or resumes the current one based on the current session id
	 * that's being passed via a cookie.
	 *
	 * If you want to use a named session, you must call session_name() before calling session_start().
	 *
	 * session_start() will register internal output handler for URL rewriting when trans-sid is enabled.
	 * If a user uses ob_gzhandler or like with ob_start(), the order of output handler is important for proper output.
	 * For example, user must register ob_gzhandler before session start.
	 *
	 * @param  boolean  $noNewSession  False: Create new session if none there, True: Do not create new session
	 * @return boolean                 True: ok, False: already started
	 */
	public function session_start( $noNewSession = false )
	{

		if ( $this->_session_var !== null ) {
			// session already started:
			return false;
		}
		if ( $this->_mode == 'cookie' ) {
			$cookie							=	CBCookie::getcookie( $this->_cookie_name, null );
			if ( $cookie !== null ) {
				// session existing in browser:
				$session_id					=	substr( $cookie, 0, 32 );
			} else {
				$session_id					=	null;
			}
		} elseif ( $this->_mode == 'sessionid' ) {
			$session_id						=	substr( $this->_session_id, 0, 32 );
		} else {
			return false;
		}

		if ( $session_id ) {
			$session_data				=	$this->read( $session_id );
			if ( $session_data ) {
				// session found in database:
				$session_var			=	unserialize( $session_data );
				if ( ( $session_var !== false ) && ( $this->_validateSession( $session_id, $session_data ) ) ) {
					// valid session has been retrieved:
					$this->_session_id	=	$session_id;
					$this->_session_var	=	$session_var;
					return true;
				}
			}
		}
		if ( $noNewSession ) {
			return false;
		}
		// no valid session has been found: create a new one:
		$this->_session_id				=	$this->generateRandSessionid( 32 );
		$this->_session_var				=	array( 'cbsessions.verify' => $this->generateRandSessionid( 32 ) );
		$this->_validateSession();		// set the session
		if ( $this->_mode == 'cookie' ) {
			$this->_sendSessionCookies();
		}
		return true;
	}

	/**
	 * Sends out the session cookies
	 *
	 * @return boolean  FALSE if headers already sent.
	 */
	private function _sendSessionCookies()
	{
		global $_SERVER;

		$isHttps			=	(isset($_SERVER['HTTPS']) && ( !empty( $_SERVER['HTTPS'] ) ) && ($_SERVER['HTTPS'] != 'off') );
		return CBCookie::setcookie( $this->_cookie_name, $this->_session_id, false, null, null, $isHttps, true );
	}

	/**
	 * Regenerates a new session id, keeping session data
	 *
	 */
	public function session_regenerate( )
	{
		if ( ! $this->_session_id ) {
			// tries to load existing session:
			$this->session_start( true );
		}
		if ( $this->_session_id ) {
			$this->destroy( $this->_session_id );
		}
		$this->_session_id		=	$this->generateRandSessionid( 32 );
		return $this->_sendSessionCookies();
	}

	/**
	 * End the current session and store session data.
	 *
	 * @return bool
	 */
	public function session_write_close( )
	{
		// store:
		if ( ! $this->write( $this->_session_id, serialize( $this->_session_var ) ) ) {
			die( 'Session write error!' );
		}
		// timeout old sessions:
		$this->gc();
		return true;
	}

	/**
	 * Gets value of the session variable, change it with $this->set()
	 * (not a reference, use $this->get_reference() for reference)
	 *
	 * @param  string  $name
	 * @return mixed          NULL if not set
	 */
	public function get( $name )
	{
		if ( isset( $this->_session_var[$name] ) ) {
			return $this->_session_var[$name];
		} else {
			$null						=	null;
			return $null;
		}
	}

	/**
	 * Sets a value to the session variable $name (Not a reference)
	 *
	 * @param  string  $name
	 * @param  mixed   $value
	 */
	public function set( $name, $value )
	{
		$this->_session_var[$name]		=	$value;
	}

	/**
	 * Gets a reference to the session variable (which can be changed)
	 *
	 * @param  string  $name
	 * @return mixed          If empty/new: NULL
	 */
	public function & getReference( $name )
	{
		if ( ! isset( $this->_session_var[$name] ) ) {
			$this->_session_var[$name]	=	null;
		}
		return $this->_session_var[$name];
	}

	/**
	 * Unset current session
	 *
	 * @return bool           True success, False failed
	 */
	public function session_unset()
	{
		if ( $this->_session_id ) {
			$this->destroy( $this->_session_id );
			$this->_session_id	=	null;
			$this->_session_var	=	null;
		}
	}

	/**
	 * Sets/Gets current session id for get (warning: lower security)
	 *
	 * @param  string  $id  new     if change
	 * @return string       current if no change ( $id = null ) if session started already
	 */
	public function session_id( $id = null )
	{
		if ( $id == null ) {
			if ( $this->_session_var !== null ) {
				// session started, can return id:
				return $this->_session_id;
			} else {
				return '';
			}
		} elseif ( strlen( $id ) == 32 ) {
			$current				=	$this->_session_id;
			if ( $id ) {
				$this->_session_id	=	$id;
			}
			return $current;
		} else {
			return false;
		}
	}

	/**
	 * Generates a random session_id of chars and numbers
	 * (Similar to cbMakeRandomString)
	 * @access private
	 *
	 * @param  int    $stringLength
	 * @return string
	 */
	public function generateRandSessionid( $stringLength = 32 )
	{
		$chars			=	'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$len			=	strlen( $chars );
		$rndString		=	'';

		$stat			=	@stat( __FILE__ );
		if ( ! is_array( $stat ) ) {
			$stat		=	array();
		}
		$stat[]			=	php_uname();
		$stat[]			=	uniqid( '', true );
		$stat[]			=	microtime();
		//$stat[]		=	$_CB_framework->getCfg( 'secret' );
		$stat[]			=	mt_rand( 0, mt_getrandmax() );
		mt_srand( crc32( implode( ' ', $stat ) ) );

		for ( $i = 0; $i < $stringLength; $i++ ) {
			$rndString	.=	$chars[mt_rand( 0, $len - 1 )];
		}
		return $rndString;
	}

	/**
	 * Validate the session id with internal records of the browser and check values.
	 *
	 * @return boolean
	 */
	private function _validateSession( )
	{
		// check if browser user agent has changed:
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) )	{
			$browser	=	$this->get( 'cbsession.agent' );
			if ( $browser === null ) {
				$this->set( 'cbsession.agent', $_SERVER['HTTP_USER_AGENT']);
			} elseif ( $_SERVER['HTTP_USER_AGENT'] !== $browser ) {
				return false;
			}
		}
		/* PROBLEM: COMMENTED OUT FOR NOW:
				// check if client IP received (could be fake through proxy) matches:
				if ( $this->_getClientIp() != $this->_sessionRecord['client_ip'] ) {
					return false;
				}

				// check if initial session connection had no proxy and now suddenly we have one:
				$incoming_ip			=	$this->_getIcomingIp();
				if ( ( $incoming_ip != $this->_sessionRecord['client_ip'] )
					&& ( $this->_sessionRecord['incoming_ip'] == $this->_sessionRecord['client_ip'] ) )
				{
					return false;
				}
		*/
		// Things seem to match, check the validation cookie:	//TBD later
		return true;
	}

	/**
	 * Gets the remote IP address of client (or of proxy if a proxy is forwarding)
	 *
	 * @return string
	 */
	private function _getIcomingIp( )
	{
		global $_SERVER;

		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Gets the remote client IP address (checking for proxy-forwarded IPs)
	 *
	 * @return string
	 */
	private function _getClientIp()
	{
		global $_SERVER;

		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$forwarded_ip_array	=	explode(',',$_SERVER['HTTP_X_FORWARDED_FOR']);
			$client_ip			=	$forwarded_ip_array[count($forwarded_ip_array) - 1];
		} else {
			$client_ip			=	$_SERVER['REMOTE_ADDR'];
		}
		return $client_ip;
	}

	/**
	 * Reads session record from database
	 *
	 * @param  string  $id
	 * @return string       or NULL if record innexistant or expired
	 */
	private function read( $id )
	{
		if ( $this->_mode == 'cookie' ) {
			$id					.=	'/';			// 33rd character in case of cookies
		}
		// Fetch session data from the selected database
		$sql					=	'SELECT * FROM `#__comprofiler_sessions`'
			.	' WHERE `session_id` = ' . $this->_db->Quote( $id )
			.	' AND `expiry_time` >= UNIX_TIMESTAMP()'
		;
		$this->_db->setQuery( $sql );
		$this->_sessionRecord	=	$this->_db->loadAssoc();
		if ( $this->_sessionRecord !== null ) {
			return $this->_sessionRecord['session_data'];
		}
		return null;
	}

	/**
	 * Writes session record to database
	 *
	 * @param  string  $id
	 * @param  string  $data
	 * @return bool
	 */
	private function write( $id, $data )
	{
		global $_CB_framework;

		if ( $this->_mode == 'cookie' ) {
			$id					.=	'/';			// 33rd character in case of cookies
		}
		// Prepare new values:
		$v						=	array();
		$v['session_id']		=	$this->_db->Quote( $id );
		$v['session_data']		=	$this->_db->Quote( $data );
		$v['expiry_time']		=	'UNIX_TIMESTAMP()+' . (int) $this->_life_time;
		if ( $_CB_framework ) {
			$v['ui']			=	(int) $_CB_framework->getUi();
			if ( $_CB_framework->myId() ) {
				$v['username']	=	$this->_db->Quote( $_CB_framework->myUsername() );
				$v['userid']	=	(int) $_CB_framework->myId();
			}
		}

		if ( $this->_sessionRecord !== null ) {
			// UPDATE existing:
			$sets				=	array();
			foreach ( $v as $col => $escapedVal ) {
				$sets[]			=	$this->_db->NameQuote( $col ) . ' = ' . $escapedVal;
			}
			$where				=	array_shift( $sets );
			$sql				=	'UPDATE `#__comprofiler_sessions` SET ' . implode( ', ', $sets )
				.	' WHERE ' . $where;
			$this->_db->setQuery( $sql );
			$okUpdate			=	$this->_db->query();
			if ( $okUpdate ) {
				return $okUpdate;
			}
		}
		// INSERT new: add IP address for first record:
		$v['client_ip']		=	$this->_db->Quote( $this->_getClientIp() );
		$v['incoming_ip']	=	$this->_db->Quote( $this->_getIcomingIp() );

		$columns			=	array();
		$escValues			=	array();
		foreach ( $v as $col => $escapedVal ) {
			$columns[]		=	$this->_db->NameQuote( $col );
			$escValues[]		=	$escapedVal;
		}
		$sql				=	'INSERT INTO `#__comprofiler_sessions`'
			.	' (' . implode( ',', $columns ) . ')'
			.	' VALUES(' . implode( ',', $escValues ) . ')'
		;
		$this->_db->setQuery( $sql );
		return $this->_db->query();
	}

	/**
	 * Removes session $id from database
	 *
	 * @param  string $id
	 * @return bool
	 */
	public function destroy( $id )
	{
		if ( $this->_mode == 'cookie' ) {
			$id					.=	'/';			// 33rd character in case of cookies
		}
		$sql					=	'DELETE FROM `#__comprofiler_sessions`'
			.	' WHERE `session_id` = ' . $this->_db->Quote( $id )
		;
		$this->_db->setQuery( $sql );
		return $this->_db->query();
	}

	/**
	 * Garbage Collection
	 * Delete all records who have passed the expiration time
	 *
	 * @return bool
	 */
	public function gc()
	{
		$sql					=	'DELETE FROM `#__comprofiler_sessions` WHERE `expiry_time` < UNIX_TIMESTAMP();';
		$this->_db->setQuery( $sql );
		return $this->_db->query();
	}
}
