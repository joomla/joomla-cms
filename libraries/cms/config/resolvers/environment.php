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
class JConfigResolverEnvironment implements JConfigResolverInterface {

	/**
	 * @var string
	 */
	protected $prefix = 'J_';

	/**
	 * @param array $options
	 */
	public function __construct(array $options)
	{
		$this->parseOptions($options);
		$this->loadEnvFile(JPATH_PLATFORM . '/.env.php');
	}

	/**
	 * @param array $options
	 * @return void
	 */
	protected function parseOptions($options)
	{
		if (isset($options['prefix']))
		{
			$this->prefix = strtoupper($options['prefix']);
		}
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
		return getenv($this->format($key)) ?: $default;
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
		$key = $this->format($key);

		$_ENV[$key]    = $value;
		$_SERVER[$key] = $value;

		putenv("{$key}={$value}");

		return $this;
	}

	/**
	 * Export the configuration for future requests.
	 * 
	 * @return static
	 */
	public function export()
	{
		throw new \RuntimeException('Using the environment loader does not allow exporting.');
	}

	/**
	 * @param array $values
	 * @return void
	 */
	public function fromArray(array $values)
	{
		foreach ($values as $key => $value)
		{
			$this->set($key, $value);
		}
	}

	/**
	 * @param string $file
	 * @return void
	 */
	public function fromFile($file)
	{
		if (file_exists($file))
		{
			$vars = require $file;

			$this->fromArray($vars);
		}
	}

	/**
	 * @param string $key
	 * @return string
	 */
	protected function format($key)
	{
		$key = strtoupper($key);

		if (strpos($key, $this->prefix) !== 0)
		{
			return $this->prefix . $key;
		}

		return $key;
	}

}