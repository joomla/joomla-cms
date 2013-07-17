<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Common items for operations on the service
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsService extends JAmazons3Object
{
	/**
	 * @var    JAmazons3OperationsServiceGet  Amazons3 API object for GET operations on the service.
	 * @since  ??.?
	 */
	protected $get;

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JAmazons3Object  Amazons3 API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JAmazons3OperationsService' . ucfirst($name);

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
