<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 8/24/14 11:36 PM $
* @package CBLib\Session
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Session;

use CBLib\Input\InputInterface;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\ParametersStore;
use CBLib\Registry\ParamsInterface;

defined('CBLIB') or die();

/**
 * CBLib\Session\SessionState Class implementation
 *
 * Usage:
 * Add SessionState at DI-constructor params of the class
 * set $sessionState->stateIsForDomain( 'myFormStateName' ) before using it
 * then just use $sessionState->get( 'inputName' ) instead of $input->get():
 * If Input has the key, it will update automatically the state, if not, use state if state exists
 */
class SessionState extends ParametersStore implements SessionStateInterface
{
	/**
	 * Prefix for Session storage of this SessionState
	 */
	const SESSION_PREFIX			=	'cb.state.';
	/**
	 * The parsed params
	 * @var  array  The parsed params
	 * @deprecated  Not valid in this class
	 */
	protected $params   	=   array();
	/**
	 * @var SessionInterface
	 */
	protected $session;
	/**
	 * @var InputInterface
	 */
	protected $input;
	/**
	 * Prefix (starting with 'cb.state,' and ending with '.') for the Session storage of the corresponding Input state
	 * @var string
	 */
	protected $sessionKeyPrefix		=	SessionState::SESSION_PREFIX;

	/**
	 * Constructor
	 *
	 * @param  SessionInterface  $session
	 * @param  InputInterface    $input
	 */
	public function __construct( SessionInterface $session, InputInterface $input )
	{
		$this->session	=	$session;
		$this->input	=	$input;
	}

	/*
	 * Sets the domain for $this SessionState
	 *
	 * @param  string  $domain  (can be hierarchical, separated with '.')
	 * @return void
	 */
	public function stateIsForDomain( $domain )
	{
		if ( $domain == null ) {
			$this->sessionKeyPrefix	=	'';

			return;
		}

		$this->sessionKeyPrefix		=	SessionState::SESSION_PREFIX . $domain . '.';
	}

	/**
	 * Gets a state value (from Input if value is set, or from Session, if value is not set)
	 *
	 * @param  string                 $key      The name of the param
	 * @param  mixed|GetterInterface  $default  [optional] Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param  string|array           $type     [optional] default: null: raw. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return string|array
	 *
	 * @throws \InvalidArgumentException  If $key has a namespace/ in it.
	 */
	public function get( $key, $default = null, $type = null )
	{
		if ( $this->input->has( $key ) ) {
			$value	=	$this->input->get( $key, $default, $type );

			$this->set( $key, $value );

			return $value;
		}

		return $this->session->get( $this->sessionKeyPrefix . $key, $default, $type );
	}

	/**
	 * Check if a state already exists in this Session.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function has( $key )
	{
		return $this->session->has( $this->sessionKeyPrefix . $key );
	}

	/**
	 * Sets a value to the SessionState
	 *
	 * @param  string  $key    The name of the param or sub-param, e.g. a.b.c
	 * @param  string  $value  The value of the parameter
	 * @return void
	 *
	 * @throws \InvalidArgumentException  If $key has a namespace/ in it.
	 */
	public function set( $key, $value )
	{
		// Check for namespaced set( 'namespace/key' ) which we do not allow:
		if ( strpos( $key, '/' ) !== false )
		{
			throw new \InvalidArgumentException( 'Invalid domain-based key given to ' . __CLASS__ . '::' .  __FUNCTION__ );
		}

		$this->session->set( $this->sessionKeyPrefix . $key, $value );
	}

	/**
	 * Get sub-Registry
	 *
	 * @param   string                         $key  Name of index or name-encoded registry array selection, e.g. a.b.c
	 * @return  SessionStateInterface|boolean        Sub-Registry or boolean FALSE if not existing or not a set of inputs
	 */
	public function subTree( $key )
	{
		$input				=	$this->input->subTree( $key );
		$session			=	$this->session->subTree( $this->sessionKeyPrefix . $key );

		/** @var self $subSessionState */
		$subSessionState	=	new static( $session, $input );

		$subSessionState->stateIsForDomain( null );

		return $subSessionState;
	}

	/**
	 * Check if a parameters path exists without checking parents.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function hasInThis( $key )
	{
		return $this->session->hasInThis( $this->sessionKeyPrefix . $key );
	}

	/**
	 * Un-Sets a param
	 *
	 * @param  string  $key    The name of the param
	 * @return void
	 */
	public function unsetEntry( $key )
	{
		$this->session->unsetEntry( $key );
	}

	/**
	 * Returns an array of all current params
	 *
	 * @return array
	 */
	public function asArray( )
	{
		$inputVars				=	$this->input->asArray();
		$sessionVars			=	$this->session->asArray();

		$namedSessionVars		=	array();
		$sessionKeyPrefixLen	=	strlen( $this->sessionKeyPrefix );

		foreach ( $sessionVars as $k => $v ) {
			if ( substr( $k, 0, $sessionKeyPrefixLen ) !== $this->sessionKeyPrefix ) {
				continue;
			}

			$namedSessionVars[substr( $k, $sessionKeyPrefixLen )]	=	$v;
		}

		// Return union of keyed inputs and session (with priority to input):
		return $inputVars + $namedSessionVars;
	}

	/**
	 * Empties the Parameters
	 *
	 * @return  static  $this for chaining with ->load()
	 */
	public function reset( )
	{
		$this->session->reset();
		return $this;
	}

	/**
	 * Adds loading of a associative array of values, or an hierarchical object, or a JSON string into the params
	 * Does not reset the Parameters, to reset, chain with ->reset()->load()
	 *
	 * @param   string|array|object|ParamsInterface  $jsonStringOrArrayOrObjectOrParameters  Associative array of values or Object to load
	 * @return  void
	 */
	public function load( $jsonStringOrArrayOrObjectOrParameters )
	{
		$this->session->load( $jsonStringOrArrayOrObjectOrParameters );
	}

	/**
	 * ArrayAccess Interface implementation: Sets an index
	 *
	 * @param  string  $offset
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet( $offset, $value )
	{
		$this->session->offsetSet( $offset, $value );
	}

	/**
	 * ArrayAccess Interface implementation: Gets an index value
	 *
	 * @param  string  $offset
	 * @return mixed
	 *
	 * @throws \UnexpectedValueException
	 */
	public function offsetGet( $offset )
	{
		return $this->get( $offset );
	}
}
