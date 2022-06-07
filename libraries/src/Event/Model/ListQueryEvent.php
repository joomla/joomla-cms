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
use Joomla\CMS\Event\AbstractEvent;
use Joomla\CMS\MVC\Model\ListModelInterface;

/**
 * Event class for modifying a list query
 *
 * @since  __DEPLOY_VERSION__
 */
class ListQueryEvent extends AbstractEvent
{
	/**
	 * Mandatory arguments:
	 * subject  ListModelInterface  The model instance we are operating on.
	 * context  string              The model context.
	 * query    QueryInterface      Database query.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($name, array $arguments = [])
	{
		if (!\array_key_exists('subject', $arguments))
		{
			throw new BadMethodCallException("Argument 'subject' of event {$this->name} is required but has not been provided");
		}

		if (!\array_key_exists('context', $arguments))
		{
			throw new BadMethodCallException("Argument 'context' is required for event $name");
		}

		if (!\array_key_exists('query', $arguments))
		{
			throw new BadMethodCallException("Argument 'query' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * Setter for the subject argument
	 *
	 * @param   ListModelInterface  $value  The value to set
	 *
	 * @return  ListModelInterface
	 *
	 * @throws  BadMethodCallException  If the argument is not of the expected type.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function setSubject($value)
	{
		if (!\is_object($value) || !($value instanceof ListModelInterface))
		{
			throw new BadMethodCallException("Argument 'subject' of event {$this->name} is not of the expected type");
		}

		return $value;
	}
}
