<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pagination;

defined('JPATH_PLATFORM') or die;

/**
 * Pagination object representing a particular item in the pagination lists.
 *
 * @since  1.5
 */
class PaginationObject
{
	/**
	 * @var    string  The link text.
	 * @since  1.5
	 */
	public $text;

	/**
	 * @var    integer  The number of rows as a base offset.
	 * @since  1.5
	 */
	public $base;

	/**
	 * @var    string  The link URL.
	 * @since  1.5
	 */
	public $link;

	/**
	 * @var    integer  The prefix used for request variables.
	 * @since  1.6
	 */
	public $prefix;

	/**
	 * @var    boolean  Flag whether the object is the 'active' page
	 * @since  3.0
	 */
	public $active;

	/**
	 * Class constructor.
	 *
	 * @param   string   $text    The link text.
	 * @param   string   $prefix  The prefix used for request variables.
	 * @param   integer  $base    The number of rows as a base offset.
	 * @param   string   $link    The link URL.
	 * @param   boolean  $active  Flag whether the object is the 'active' page
	 *
	 * @since   1.5
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
