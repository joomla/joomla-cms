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
 * Common items for operations on objects
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageObjects extends JGooglecloudstorageObject
{
	/**
	 * @var    JGooglecloudstorageObjectsGet  Googlecloudstorage API object for
	 *                                        GET operations on objects.
	 * @since  ??.?
	 */
	protected $get;

	/**
	 * @var    JGooglecloudstorageObjectsHead  Googlecloudstorage API object for
	 *                                         HEAD operations on objects.
	 * @since  ??.?
	 */
	protected $head;

	/**
	 * @var    JGooglecloudstorageObjectsPut  Googlecloudstorage API object for
	 *                                        PUT operations on objects.
	 * @since  ??.?
	 */
	protected $put;

	/**
	 * @var    JGooglecloudstorageObjectsPost  Googlecloudstorage API object for
	 *                                         POST operations on objects.
	 * @since  ??.?
	 */
	protected $post;

	/**
	 * @var    JGooglecloudstorageObjectsDelete  Googlecloudstorage API object for
	 *                                           DELETE operations on objects.
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
		$class = 'JGooglecloudstorageObjects' . ucfirst($name);

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
