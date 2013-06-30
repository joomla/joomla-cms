<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Access
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JAccessRule class.
 *
 * @package     Joomla.Platform
 * @subpackage  Access
 * @since       11.4
 */
class JAccessRule
{
	/**
	 * A named array
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $data = array();

	/**
	 * Constructor.
	 *
	 * The input array must be in the form: array(-42 => true, 3 => true, 4 => false)
	 * or an equivalent JSON encoded string.
	 *
	 * @param   mixed  $identities  A JSON format string (probably from the database) or a named array.
	 *
	 * @since   11.1
	 */
	public function __construct($identities)
	{
		// Convert string input to an array.
		if (is_string($identities))
		{
			$identities = json_decode($identities, true);
		}

		$this->mergeIdentities($identities);
	}

	/**
	 * Get the data for the action.
	 *
	 * @return  array  A named array
	 *
	 * @since   11.1
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Merges the identities
	 *
	 * @param   mixed  $identities  An integer or array of integers representing the identities to check.
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function mergeIdentities($identities)
	{
		if ($identities instanceof JAccessRule)
		{
			$identities = $identities->getData();
		}

		if (is_array($identities))
		{
			foreach ($identities as $identity => $allow)
			{
				$this->mergeIdentity($identity, $allow);
			}
		}
	}

	/**
	 * Merges the values for an identity.
	 *
	 * @param   integer  $identity  The identity.
	 * @param   boolean  $allow     The value for the identity (true == allow, false == deny).
	 *
	 * @return  void
	 *
	 * @since   11.1
	 */
	public function mergeIdentity($identity, $allow)
	{
		$identity = (int) $identity;
		$allow = (int) ((boolean) $allow);

		// Check that the identity exists.
		if (isset($this->data[$identity]))
		{
			// Explicit deny always wins a merge.
			if ($this->data[$identity] !== 0)
			{
				$this->data[$identity] = $allow;
			}
		}
		else
		{
			$this->data[$identity] = $allow;
		}
	}

	/**
	 * Checks that this action can be performed by an identity.
	 *
	 * The identity is an integer where +ve represents a user group,
	 * and -ve represents a user.
	 *
	 * @param   mixed  $identities  An integer or array of integers representing the identities to check.
	 *
	 * @return  mixed  True if allowed, false for an explicit deny, null for an implicit deny.
	 *
	 * @since   11.1
	 */
	public function allow($identities)
	{
		// Implicit deny by default.
		$result = null;

		// Check that the inputs are valid.
		if (!empty($identities))
		{
			if (!is_array($identities))
			{
				$identities = array($identities);
			}

			foreach ($identities as $identity)
			{
				// Technically the identity just needs to be unique.
				$identity = (int) $identity;

				// Check if the identity is known.
				if (isset($this->data[$identity]))
				{
					$result = (boolean) $this->data[$identity];

					// An explicit deny wins.
					if ($result === false)
					{
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
	 * @return  string  JSON encoded string
	 *
	 * @since   11.1
	 */
	public function __toString()
	{
		return json_encode($this->data);
	}
}
