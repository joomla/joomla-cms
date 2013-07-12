<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the structure of a common request header
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3HeadersRequest
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
	 * @var    Boolean	Whether the header is required or not for the request.
	 * @since  ??.?
	 */
	protected $required;

	/**
	 * Constructor.
	 *
	 * @param   String   $name      The name of the request header
	 * @param   String   $value     The value of the request header
	 * @param   Boolean  $required  Whether the header is required or not
	 *
	 * @since   ??.?
	 */
	public function __construct($name, $value, $required = false)
	{
		$this->name = $name;
		$this->value = $value;
		$this->required = $required;
	}
}
