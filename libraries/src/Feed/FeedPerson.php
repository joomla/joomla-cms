<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Feed;

defined('JPATH_PLATFORM') or die;

/**
 * Feed Person class.
 *
 * @since  12.3
 */
class FeedPerson
{
	/**
	 * The email address of the person.
	 *
	 * @var    string
	 * @since  12.3
	 */
	public $email;

	/**
	 * The full name of the person.
	 *
	 * @var    string
	 * @since  12.3
	 */
	public $name;

	/**
	 * The type of person.
	 *
	 * @var    string
	 * @since  12.3
	 */
	public $type;

	/**
	 * The URI for the person.
	 *
	 * @var    string
	 * @since  12.3
	 */
	public $uri;

	/**
	 * Constructor.
	 *
	 * @param   string  $name   The full name of the person.
	 * @param   string  $email  The email address of the person.
	 * @param   string  $uri    The URI for the person.
	 * @param   string  $type   The type of person.
	 *
	 * @since   12.3
	 */
	public function __construct($name = null, $email = null, $uri = null, $type = null)
	{
		$this->name = $name;
		$this->email = $email;
		$this->uri = $uri;
		$this->type = $type;
	}
}
