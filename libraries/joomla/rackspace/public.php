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
 * Common items for public access to the cloud account
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspacePublic extends JRackspaceObject
{
	/**
	 * @var    JRackspacePublicTempurl  Rackspace API object for creating a Temporary URL
	 * @since  ??.?
	 */
	protected $tempurl;

	/**
	 * @var    JRackspacePublicFormpost Rackspace API object for FormPost
	 * @since  ??.?
	 */
	protected $formpost;

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
		$class = 'JRackspacePublic' . ucfirst($name);

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
