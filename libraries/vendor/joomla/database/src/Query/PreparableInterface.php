<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Query;

use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

@trigger_error(
	sprintf(
		'%1$s is deprecated and will be removed in 3.0, all query objects should implement %2$s instead.',
		PreparableInterface::class,
		QueryInterface::class
	),
	E_USER_DEPRECATED
);

/**
 * Joomla Database Query Preparable Interface.
 *
 * Adds bind/unbind methods as well as a getBounded() method to retrieve the stored bounded variables on demand prior to query execution.
 *
 * @since       1.0
 * @deprecated  3.0  Capabilities will be required in Joomla\Database\QueryInterface
 */
interface PreparableInterface
{
	/**
	 * Method to add a variable to an internal array that will be bound to a prepared SQL statement before query execution.
	 *
	 * @param   array|string|integer  $key            The key that will be used in your SQL query to reference the value. Usually of
	 *                                                the form ':key', but can also be an integer.
	 * @param   mixed                 $value          The value that will be bound. It can be an array, in this case it has to be
	 *                                                same length of $key; The value is passed by reference to support output
	 *                                                parameters such as those possible with stored procedures.
	 * @param   array|string          $dataType       Constant corresponding to a SQL datatype. It can be an array, in this case it
	 *                                                has to be same length of $key
	 * @param   integer               $length         The length of the variable. Usually required for OUTPUT parameters.
	 * @param   array                 $driverOptions  Optional driver options to be used.
	 *
	 * @return  $this
	 *
	 * @since   1.0
	 */
	public function bind($key, &$value, $dataType = ParameterType::STRING, $length = 0, $driverOptions = []);

	/**
	 * Method to unbind a bound variable.
	 *
	 * @param   array|string|integer  $key  The key or array of keys to unbind.
	 *
	 * @return  $this
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function unbind($key);

	/**
	 * Retrieves the bound parameters array when key is null and returns it by reference. If a key is provided then that item is returned.
	 *
	 * @param   mixed  $key  The bounded variable key to retrieve.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 */
	public function &getBounded($key = null);
}
