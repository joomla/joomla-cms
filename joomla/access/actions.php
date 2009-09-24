<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * @package 	Joomla.Framework
 * @subpackage	Access
 * @since		1.6
 */
class JActions
{
	/**
	 * @var	array	A named array
	 */
	protected $_data = array();

	/**
	 * Constructor.
	 *
	 * The input array must be in the form: array('action' => array(-42 => true, 3 => true, 4 => false))
	 * or an equivalent JSON encoded string, or an object where properties are arrays.
	 *
	 * @param	mixed	A JSON format string (probably from the database), or a nested array.
	 */
	public function __construct($input)
	{
		// Convert in input to an array.
		if (is_string($input)) {
			$input = json_decode($input, true);
		}
		else if (is_object($input)) {
			$input = (array) $input;
		}

		if (is_array($input))
		{
			// Top level keys represent the actions.
			foreach ($input as $action => $identities) {
				$this->addAction($action, $identities);
			}
		}
	}

	/**
	 * Get the data for the action.
	 *
	 * @return	array	A named array of JAction objects.
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Method to merge a collection of JActions.
	 *
	 * @param	mixed
	 */
	public function mergeCollection($input)
	{
		// Check if the input is a
		if (is_array($input))
		{
			foreach ($input as $actions) {
				$this->merge($actions);
			}
		}
	}

	/**
	 * Method to merge actions with this object.
	 *
	 * @param	mixed
	 */
	public function merge($actions)
	{
		if (is_string($actions)) {
			$actions = json_decode($actions, true);
		}

		if (is_array($actions))
		{
			foreach ($actions as $action => $identities) {
				$this->mergeAction($action, $identities);
			}
		}
		else if ($actions instanceof JActions)
		{
			$data = $actions->getData();

			foreach ($data as $action => $identities) {
				$this->mergeAction($action, $identities);
			}
		}
	}

	/**
	 * @param	string	The name of the action.
	 *
	 * @return	array	A reference to the action identities array (chaining supported).
	 */
	public function &mergeAction($action, $identities)
	{
		if (!isset($this->_data[$action]))
		{
			// If new, add the action.
			$this->_data[$action] = new JAction($identities);
		}
		else
		{
			// If exists, merge the action.
			$this->_data[$action]->mergeIdentities($identities);
		}
		return $this->_data[$action];
	}

	/**
	 * Checks that an action can be performed by an identity.
	 *
	 * The identity is an integer where +ve represents a user group,
	 * and -ve represents a user.
	 *
	 * @param	string		The name of the action.
	 * @param	int			An integer representing the identity.
	 *
	 * @return	mixed
	 */
	public function allow($action, $identity)
	{
		if (empty($action))
		{
			// Sweep for the allowed actions.
			$allowed = new JObject;
			foreach ($this->_data as $name => $action)
			{
				if ($this->_data[$action]->allow($identity)) {
					$allowed->set($name, true);
				}
			}
			return $allowed;
		}
		else
		{
			// Check we have information about this action.
			if (isset($this->_data[$action])) {
				return $this->_data[$action]->allow($identity);
			}
		}
		return false;
	}

	/**
	 * Magic method to convert the object to string representation.
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return json_encode((string) $this->_data);
	}
}

/**
 * @package 	Joomla.Framework
 * @subpackage	Access
 * @since		1.6
 */
class JAction
{
	/**
	 * @var	array	A named array
	 */
	protected $_data = array();

	/**
	 * Constructor.
	 *
	 * The input array must be in the form: array(-42 => true, 3 => true, 4 => false)
	 * or an equivalent JSON encoded string.
	 *
	 * @param	mixed	A JSON format string (probably from the database), or a named array.
	 */
	public function __construct($identities)
	{
		// Convert string input to an array.
		if (is_string($identities)) {
			$identities = json_decode($identities, true);
		}

		$this->mergeIdentities($identities);
	}

	/**
	 * Merges the identities
	 */
	public function mergeIdentities($identities)
	{
		if (is_array($identities))
		{
			foreach ($identities as $identity => $allow) {
				$this->mergeIdentity($identity, $allow);
			}
		}
	}

	/**
	 * Merges the value for an identity.
	 *
	 * @param	int		The identity.
	 * @param	boolean	The value for the identity (true == allow, false == deny).
	 */
	public function mergeIdentity($identity, $allow)
	{
		$identity	= (int) $identity;
		$allow		= (boolean) $allow;

		// Check that the identity exists.
		if (isset($this->_data[$identity])) {
			$this->_data[$identity] = $allow;
		}
		else
		{
			// Explicit deny always wins a merge.
			if ($this->_data[$identity] !== false) {
				$this->_data[$identity] = $allow;
			}
		}
	}

	/**
	 * Checks that this action can be performed by an identity.
	 *
	 * The identity is an integer where +ve represents a user group,
	 * and -ve represents a user.
	 *
	 * @param	int			An integer representing the identity.
	 */
	public function allow($identity)
	{
		// Check that the inputs are valid.
		if (!empty($identity))
		{
			// Technically the identity just needs to be unique.
			$identity = (int) $identity;

			// Check if the identity is known.
			if (isset($this->_data[$identity])) {
				return $this->_data[$identity];
			}
		}
		return false;
	}

	/**
	 * Convert this object into a JSON encoded string.
	 *
	 * @return	string
	 */
	public function __toString()
	{
		return json_encode($this->_data);
	}
}
