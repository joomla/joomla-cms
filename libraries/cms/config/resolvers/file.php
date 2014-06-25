<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 * @since       3.3
 */
abstract class JConfigResolverFile implements JConfigResolverInterface {

	/**
	 * @var \JConfigResolverEnvironment
	 */
	protected $envResolver;

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var \Joomla\Registry\Registry
	 */
	protected $config;

	/**
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		$this->parseOptions($options);
		$this->load();
	}

	/**
	 * @param array $options
	 * @return void
	 */
	protected function parseOptions($options)
	{
		if (isset($options['environment']) and $options['environment'])
		{
			$this->envResolver = new JConfigResolverEnvironment(array());
		}

		if (isset($options['file']))
		{
			$this->file = $options['file'];
		}
	}

	/**
	 * Load configuration file into config.
	 * 
	 * @return void
	 */
	abstract protected function load();

	/**
	 * Determine whether a value exists in configuration.
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function exists($key)
	{
		return $this->config->exists($key) || ($this->envResolver ? $this->envResolver->exists($key) : false);
	}

	/**
	 * Get a value from configuration.
	 * 
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function get($key, $default = null)
	{
		if ((! $this->config->exists($key)) and $this->envResolver)
		{
			return $this->envResolver->get($key, $default);
		}

		return $this->config->get($key, $default);
	}

	/**
	 * Set a value to the configuration.
	 * 
	 * @param string $key
	 * @param string $value
	 * @return static
	 */
	public function set($key, $value)
	{
		$this->config->set($key, $value);

		return $this;
	}

	/**
	 * Unset a value.
	 * 
	 * @param string $key
	 * @return static
	 */
	public function unset($key)
	{
		return $this->set($key, null);
	}

}