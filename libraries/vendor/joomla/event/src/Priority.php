<?php
/**
 * Part of the Joomla Framework Event Package
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Event;

/**
 * An enumeration of priorities for event listeners,
 * that you are encouraged to use when adding them in the Dispatcher.
 *
 * @since  1.0
 */
final class Priority
{
	const MIN = -3;
	const LOW = -2;
	const BELOW_NORMAL = -1;
	const NORMAL = 0;
	const ABOVE_NORMAL = 1;
	const HIGH = 2;
	const MAX = 3;
}
