<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\CMS\Event\Workflow;

\defined('JPATH_PLATFORM') or die;

use BadMethodCallException;
use Joomla\CMS\Event\AbstractImmutableEvent;
use function explode;

/**
 * Event class for WebAsset events
 *
 * @since  4.0.0
 */
abstract class AbstractEvent extends AbstractImmutableEvent
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
		if (!\array_key_exists('subject', $arguments))
		{
			throw new BadMethodCallException("Argument 'subject' of event {$this->name} is required but has not been provided");
		}

		if (!\array_key_exists('extension', $arguments))
		{
			throw new BadMethodCallException("Argument 'extension' of event {$this->name} is required but has not been provided");
		}

		if (strpos($arguments['extension'], '.') === false)
		{
			throw new BadMethodCallException("Argument 'extension' of event {$this->name} has wrong format. Valid format: 'component.section'");
		}

		if (!\array_key_exists('extensionName', $arguments) || !\array_key_exists('section', $arguments))
		{
			$parts = explode('.', $arguments['extension']);

			$arguments['extensionName'] = $arguments['extensionName'] ?? $parts[0];
			$arguments['section']       = $arguments['section'] ?? $parts[1];
		}

		parent::__construct($name, $arguments);
	}
}
