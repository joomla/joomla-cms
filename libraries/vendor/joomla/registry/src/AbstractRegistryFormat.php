<?php
/**
 * Part of the Joomla Framework Registry Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry;

/**
 * Abstract Format for Registry
 *
 * @since       1.0
 * @deprecated  2.0  Format objects should directly implement the FormatInterface
 */
abstract class AbstractRegistryFormat implements FormatInterface
{
	/**
	 * @var    AbstractRegistryFormat[]  Format instances container.
	 * @since  1.0
	 * @deprecated  2.0  Object caching will no longer be supported
	 */
	protected static $instances = array();

	/**
	 * Returns a reference to a Format object, only creating it
	 * if it doesn't already exist.
	 *
	 * @param   string  $type     The format to load
	 * @param   array   $options  Additional options to configure the object
	 *
	 * @return  AbstractRegistryFormat  Registry format handler
	 *
	 * @deprecated  2.0  Use Factory::getFormat() instead
	 * @since   1.0
	 * @throws  \InvalidArgumentException
	 */
	public static function getInstance($type, array $options = array())
	{
		return Factory::getFormat($type, $options);
	}
}
