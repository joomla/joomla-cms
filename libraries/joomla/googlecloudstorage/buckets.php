<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Common items for operations on buckets
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageBuckets extends JGooglecloudstorageObject
{
	/**
	 * @var    JGooglecloudstorageBucketsGet  Googlecloudstorage API object for
	 *                                        GET operations on buckets.
	 * @since  ??.?
	 */
	protected $get;

	/**
	 * @var    JGooglecloudstorageBucketsPut  Googlecloudstorage API object for
	 *                                        PUT operations on buckets.
	 * @since  ??.?
	 */
	protected $put;

	/**
	 * @var    JGooglecloudstorageBucketsDelete  Googlecloudstorage API object for
	 *                                           DELETE operations on buckets.
	 * @since  ??.?
	 */
	protected $delete;

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JGooglecloudstorageObject  Googlecloudstorage API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JGooglecloudstorageBuckets' . ucfirst($name);

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
