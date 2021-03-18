<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * An enumeration of priorities for event listeners that you are encouraged to use when adding them in the Dispatcher.
 *
 * @since  1.0
 */
final class Priority
{
	/**
	 * Indicates the event listener should have a minimum priority.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public const MIN = -3;

	/**
	 * Indicates the event listener should have a low priority.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public const LOW = -2;

	/**
	 * Indicates the event listener should have a below normal priority.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public const BELOW_NORMAL = -1;

	/**
	 * Indicates the event listener should have a normal priority. This is the default priority.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public const NORMAL = 0;

	/**
	 * Indicates the event listener should have a above normal priority.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public const ABOVE_NORMAL = 1;

	/**
	 * Indicates the event listener should have a high priority.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public const HIGH = 2;

	/**
	 * Indicates the event listener should have a maximum priority.
	 *
	 * @var    integer
	 * @since  1.0
	 */
	public const MAX = 3;

	/**
	 * Disallow instantiation of this class
	 *
	 * @since   1.0
	 */
	private function __construct()
	{
	}
}
