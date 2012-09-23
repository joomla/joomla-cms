<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Pagination
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Pagination object representing a particular item in the pagination lists.
 *
 * @package     Joomla.Platform
 * @subpackage  Pagination
 * @since       11.1
 */
class JPaginationObject
{
	/**
	 * @var    string  The link text.
	 * @since  11.1
	 */
	public $text;

	/**
	 * @var    integer  The number of rows as a base offset.
	 * @since  11.1
	 */
	public $base;

	/**
	 * @var    string  The link URL.
	 * @since  11.1
	 */
	public $link;

	/**
	 * @var    integer  The prefix used for request variables.
	 * @since  11.1
	 */
	public $prefix;

	/**
	 * @var    boolean  Flag whether the object is the 'active' page
	 * @since  12.2
	 */
	public $active;

	/**
	 * Class constructor.
	 *
	 * @param   string   $text    The link text.
	 * @param   integer  $prefix  The prefix used for request variables.
	 * @param   integer  $base    The number of rows as a base offset.
	 * @param   string   $link    The link URL.
	 * @param   boolean  $active  Flag whether the object is the 'active' page
	 *
	 * @since   11.1
	 */
	public function __construct($text, $prefix = '', $base = null, $link = null, $active = false)
	{
		$this->text   = $text;
		$this->prefix = $prefix;
		$this->base   = $base;
		$this->link   = $link;
		$this->active = $active;
	}
}
