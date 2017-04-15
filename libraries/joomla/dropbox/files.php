<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Common items for Files
 *
 * @package     Joomla.Platform
 * @subpackage  Dropbox
 * @since       ??.?
 */
class JDropboxFiles extends JDropboxObject
{
	/**
	 * @var   JDropboxFilesGet  Dropbox API object for GET operations on Files
	 * @since  ??.?
	 */
	protected $get;

	/**
	 * @var   JDropboxFilesPost  Dropbox API object for POST operations on Files
	 * @since  ??.?
	 */
	protected $post;

	/**
	 * @var   JDropboxFilesPut  Dropbox API object for PUT operations on Files
	 * @since  ??.?
	 */
	protected $put;

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JDropboxObject  Dropbox API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JDropboxFiles' . ucfirst($name);

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
