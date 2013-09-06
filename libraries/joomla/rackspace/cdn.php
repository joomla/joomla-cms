<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * API Operations for CDN Services
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceCdn extends JRackspaceObject
{
	/**
	 * @var    JRackspaceCdnAccount  Rackspace API object for CDN Account Services
	 * @since  ??.?
	 */
	protected $account;

	/**
	 * @var    JRackspaceCdnContainer Rackspace API object for CDN Container Services
	 * @since  ??.?
	 */
	protected $container;

	/**
	 * @var    JRackspaceCdnObject Rackspace API object for CDN Object Services
	 * @since  ??.?
	 */
	protected $object;

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JRackspaceObject  Rackspace API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JRackspaceCdn' . ucfirst($name);

		if (class_exists($class))
		{
			if (false == isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client);
			}

			return $this->$name;
		}

		throw new InvalidArgumentException(
			sprintf('Argument %s produced an invalid class name: %s', $name, $class)
		);
	}
}
