<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebAsset;

\defined('JPATH_PLATFORM') or die;

/**
 * Web Asset Item interface
 *
 * Asset Item are "read only" object, all properties must be set through class constructor.
 * Only properties allowed to be edited is an attributes and an options.
 * Changing an uri or a dependencies are not allowed, prefer to create a new asset instance.
 *
 * @since  4.0.0
 */
interface WebAssetItemInterface
{
	/**
	 * Return Asset name
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getName(): string;

	/**
	 * Return Asset version
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	public function getVersion(): string;

	/**
	 * Return dependencies list
	 *
	 * @return  array
	 *
	 * @since   4.0.0
	 */
	public function getDependencies(): array;

	/**
	 * Get the URI of the asset
	 *
	 * @param   boolean  $resolvePath  Whether need to search for a real paths
	 *
	 * @return string
	 *
	 * @since   4.0.0
	 */
	public function getUri($resolvePath = true): string;

	/**
	 * Get the option
	 *
	 * @param   string  $key      An option key
	 * @param   string  $default  A default value
	 *
	 * @return mixed
	 *
	 * @since   4.0.0
	 */
	public function getOption(string $key, $default = null);

	/**
	 * Set the option
	 *
	 * @param   string  $key    An option key
	 * @param   string  $value  An option value
	 *
	 * @return self
	 *
	 * @since   4.0.0
	 */
	public function setOption(string $key, $value = null): WebAssetItemInterface;

	/**
	 * Get all options of the asset
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
	public function getOptions(): array;

	/**
	 * Get the attribute
	 *
	 * @param   string  $key      An attributes key
	 * @param   string  $default  A default value
	 *
	 * @return mixed
	 *
	 * @since   4.0.0
	 */
	public function getAttribute(string $key, $default = null);

	/**
	 * Set the attribute
	 *
	 * @param   string  $key    An attribute key
	 * @param   string  $value  An attribute value
	 *
	 * @return self
	 *
	 * @since   4.0.0
	 */
	public function setAttribute(string $key, $value = null): WebAssetItemInterface;

	/**
	 * Get all attributes
	 *
	 * @return array
	 *
	 * @since   4.0.0
	 */
	public function getAttributes(): array;

}
