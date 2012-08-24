<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Feed
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_BASE') or die;

/**
 * Feed Link class.
 *
 * @package     Joomla.Libraries
 * @subpackage  Feed
 * @since       3.0
 */
class JFeedLink
{
	/**
	 * @var    string
	 * @since  3.0
	 */
	public $uri;

	/**
	 * @var    string
	 * @since  3.0
	 */
	public $relation;

	/**
	 * @var    string
	 * @since  3.0
	 */
	public $type;

	/**
	 * @var    string
	 * @since  3.0
	 */
	public $language;

	/**
	 * @var    string
	 * @since  3.0
	 */
	public $title;

	/**
	 * @var    integer
	 * @since  3.0
	 */
	public $length;

	/**
	 * Constructor.
	 *
	 * @param   string   $uri       The URI to the linked resource.
	 * @param   string   $relation  The relationship between the feed and the linked resource.
	 * @param   string   $type      The resource type.
	 * @param   string   $language  The language of the resource found at the given URI.
	 * @param   string   $title     The title of the resource.
	 * @param   integer  $length    The length of the resource in bytes.
	 *
	 * @since   3.0
	 */
	public function __construct($uri = null, $relation = null, $type = null, $language = null, $title = null, $length = null)
	{
		$this->uri = $uri;
		$this->relation = $relation;
		$this->type = $type;
		$this->language = $language;
		$this->title = $title;

		// Validate the length input.
		if (isset($length) && !is_numeric($length))
		{
			throw new InvalidArgumentException('Length must be numeric.');
		}
		$this->length = (int) $length;
	}
}
