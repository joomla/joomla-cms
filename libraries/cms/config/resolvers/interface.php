<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 * @since       3.3
 */
interface JConfigResolverInterface {

	/**
	 * Determine whether a value exists in configuration.
	 * 
	 * @param string $key
	 * @return bool
	 */
	public function exists($key);

	/**
	 * Get a value from configuration.
	 * 
	 * @param string $key
	 * @param string $default
	 * @return string
	 */
	public function get($key, $default = null);

	/**
	 * Set a value to the configuration.
	 * 
	 * @param string $key
	 * @param string $value
	 * @return static
	 */
	public function set($key, $value);

	/**
	 * Unset a value.
	 * 
	 * @param string $key
	 * @return static
	 */
	public function unset($key);

	/**
	 * Export the configuration for future requests.
	 * 
	 * @return static
	 */
	public function export();

}