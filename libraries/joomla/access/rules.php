<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Access
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.access.rule');

/**
 * @package		Joomla.Framework
 * @subpackage	Access
 * @since		1.6
 */
class JRules
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
	public function __construct($input = '')
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
				$this->mergeAction($action, $identities);
			}
		}
	}

	/**
	 * Get the data for the action.
	 *
	 * @return	array	A named array of JRule objects.
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Method to merge a collection of JRules.
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
		else if ($actions instanceof JRules)
		{
			$data = $actions->getData();

			foreach ($data as $name => $identities) {
				$this->mergeAction($name, $identities);
			}
		}
	}

	/**
	 * @param	string	The name of the action.
	 */
	public function mergeAction($action, $identities)
	{
		if (isset($this->_data[$action]))
		{
			// If exists, merge the action.
			$this->_data[$action]->mergeIdentities($identities);
		}
		else
		{
			// If new, add the action.
			$this->_data[$action] = new JRule($identities);
		}
	}

	/**
	 * Checks that an action can be performed by an identity.
	 *
	 * The identity is an integer where +ve represents a user group,
	 * and -ve represents a user.
	 *
	 * @param	string	The name of the action.
	 * @param	mixed	An integer representing the identity, or an array of identities
	 *
	 * @return	mixed
	 */
	public function allow($action, $identity)
	{
// TODO: Remove debug when finished testing.
//echo "<br>Action: $action, ".print_r($identity, 1);
		// Check we have information about this action.
		if (isset($this->_data[$action])) {
//print_r($this->_data[$action]);
			return $this->_data[$action]->allow($identity);
		}
		return null;
	}

	/**
	 * Get the allowed actions for an identity.
	 *
	 * @param	mixed	An integer representing the identity, or an array of identities
	 */
	function getAllowed($identity)
	{
		// Sweep for the allowed actions.
		$allowed = new JObject;
		foreach ($this->_data as $name => &$action)
		{
			if ($action->allow($identity)) {
				$allowed->set($name, true);
			}
		}
		return $allowed;
	}

	/**
	 * Magic method to convert the object to JSON string representation.
	 *
	 * @return	string
	 */
	public function __toString()
	{
		$temp = array();
		foreach ($this->_data as $name => $rule)
		{
			// Convert the action to JSON, then back into an array otherwise
			// re-encoding will quote the JSON for the identities in the action.
			$temp[$name] = json_decode((string) $rule);
		}
		return json_encode($temp);
	}
}
