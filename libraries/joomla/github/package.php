<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API package class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       ¿
 */
abstract class JGithubPackage extends JGithubObject
{
	/**
	 * @var string
	 * @since ¿
	 */
	protected $name = '';

	/**
	 * @var array
	 * @since ¿
	 */
	protected $packages = array();

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @throws RuntimeException
	 * @since   ¿
	 *
	 * @return  JGithubPackage  GitHub API package object.
	 */
	public function __get($name)
	{
		if (false == in_array($name, $this->packages))
		{
			throw new RuntimeException(sprintf('%1$s - Unknown package %2$s', __METHOD__, $name));
		}

		if (false == isset($this->$name))
		{
			$className = 'JGithubPackage' . ucfirst($this->name) . ucfirst($name);

			$this->$name = new $className($this->options, $this->client);
		}

		return $this->$name;
	}
}
