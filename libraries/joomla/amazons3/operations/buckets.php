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
 * Common items for operations on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Amazons3
 * @since       ??.?
 */
class JAmazons3OperationsBuckets extends JAmazons3Object
{
	/**
	 * @var    JAmazons3OperationsBucketsDelete  Amazons3 API object for DELETE operations on buckets.
	 * @since  ??.?
	 */
	protected $delete;

	/**
	 * @var    JAmazons3OperationsBucketsGet  Amazons3 API object for GET operations on buckets.
	 * @since  ??.?
	 */
	protected $get;

	/**
	 * @var    JAmazons3OperationsBucketsGet  Amazons3 API object for HEAD operations on buckets.
	 * @since  ??.?
	 */
	protected $head;

	/**
	 * @var    JAmazons3OperationsBucketsPut  Amazons3 API object for PUT operations on buckets.
	 * @since  ??.?
	 */
	protected $put;

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
		$class = 'JAmazons3OperationsBuckets' . ucfirst($name);

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
