<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Serializer\Events;

\defined('_JEXEC') or die;

use Joomla\CMS\Event\AbstractImmutableEvent;

/**
 * Event for getting extra data attributes for an API Entity
 *
 * @since  4.0.0
 */
final class OnGetApiAttributes extends AbstractImmutableEvent
{
	/**
	 * The attributes
	 *
	 * @var     array
	 * @since   4.0.0
	 */
	private $attributes = [];

	/**
	 * Constructor.
	 *
	 * Mandatory arguments:
	 * attributes   array           The main data for the object.
	 * context      string          The content type of the api resource.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @since   4.0.0
	 * @throws  \BadMethodCallException
	 */
	public function __construct($name, array $arguments = array())
	{
		if (!\array_key_exists('attributes', $arguments)
			|| \array_key_exists('attributes', $arguments) && !is_array($arguments['attributes']))
		{
			throw new \BadMethodCallException("Argument 'attributes' as an array is required for event $name");
		}

		if (!\array_key_exists('context', $arguments))
		{
			throw new \BadMethodCallException("Argument 'context' is required for event $name");
		}

		parent::__construct($name, $arguments);
	}

	/**
	 * The properties to be rendered.
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getAttributes(): array
	{
		return $this->attributes;
	}

	/**
	 * Set a named attribute to be rendered in the API.
	 *
	 * @param   string  $name   The name of the property to be rendered in the api
	 * @param   mixed   $value  The value of the named property to be rendered in the api.
	 *
	 * @return  void
	 * @since   4.0.0
	 */
	public function addAttribute($name, $value): void
	{
		$this->attributes[$name] = $value;
	}

	/**
	 * Set attributes to be rendered in the API.
	 *
	 * @param   array  $value  An array of key/value pairs for properties to be added to the api.
	 *
	 * @return  void
	 * @since   4.0.0
	 */
	public function addAttributes(array $value): void
	{
		$this->attributes = array_merge($this->attributes, $value);
	}
}
