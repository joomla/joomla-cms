<?php
/**
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the structure of a common response header
 *
 * @package     Joomla.Platform
 * @subpackage  AmazonS3
 * @since       ??.?
 */
class JResponseHeader
{
	/**
	 * @var    String	The name of the request header.
	 * @since  ??.?
	 */
	protected $name;

	/**
	 * @var    String	The value of the request header.
	 * @since  ??.?
	 */
	protected $value;

	/**
	 * @var    Array	The valid values for the header.
	 * @since  ??.?
	 */
	protected $validValues;

	/**
	 * Constructor.
	 *
	 * @param   String   $name         The name of the request header
	 * @param   String   $value        The value of the request header
	 * @param   Boolean  $validValues  The list of valid values
	 *
	 * @since   ??.?
	 */
	public function __construct($name, $value, $validValues = NULL)
	{
		// If the list of valid values is not empty, we check if the value is valid
		if ($validValues != NULL) {
			if (! in_array($value, $validValues)) {
				return null;
			}
		}

		$this->name = $name;
		$this->value = $value;
		$this->validValues = $validValues;
	}
}
