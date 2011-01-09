<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * @package		Joomla.Framework
 * @subpackage	Access
 * @since		1.6
 */
class JRule
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
	 * Get the data for the action.
	 *
	 * @return	array	A named array identities.
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Merges the identities
	 */
	public function mergeIdentities($identities)
	{
		if ($identities instanceof JRule) {
			$identities = $identities->getData();
		}

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
		$allow		= (int) ((boolean) $allow);

		// Check that the identity exists.
		if (isset($this->_data[$identity]))
		{
			// Explicit deny always wins a merge.
			if ($this->_data[$identity] !== 0) {
				$this->_data[$identity] = $allow;
			}
		}
		else {
			$this->_data[$identity] = $allow;
		}
	}

	/**
	 * Checks that this action can be performed by an identity.
	 *
	 * The identity is an integer where +ve represents a user group,
	 * and -ve represents a user.
	 *
	 * @param	mixed		An integer or array of integers representing the identities to check.
	 *
	 * @return	mixed		True if allowed, false for an explicit deny, null for an implicit deny.
	 */
	public function allow($identities)
	{
		// Implicit deny by default.
		$result = null;

		// Check that the inputs are valid.
		if (!empty($identities))
		{
			if (!is_array($identities)) {
				$identities = array($identities);
			}

			foreach ($identities as $identity)
			{
				// Technically the identity just needs to be unique.
				$identity = (int) $identity;

				// Check if the identity is known.
				if (isset($this->_data[$identity]))
				{
					$result = (boolean) $this->_data[$identity];

					// An explicit deny wins.
					if ($result === false) {
						break;
					}
				}

			}
		}

		return $result;
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
