<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Model;

\defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Event class for modifying batch copy data
 *
 * @since  4.0.0
 */
class BeforeBatchCopy extends AbstractImmutableEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   4.0.0
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!\array_key_exists('sourceTable', $arguments))
		{
			throw new BadMethodCallException("Argument 'sourceTable' is required for event $name");
		}

		if (!\array_key_exists('updatedTable', $arguments))
		{
			throw new BadMethodCallException("Argument 'updatedTable' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}
}
