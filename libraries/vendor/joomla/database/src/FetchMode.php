<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database;

/**
 * Class defining the fetch mode for prepared statements
 *
 * The values of the constants in this class match the `PDO::FETCH_*` constants.
 *
 * @since  __DEPLOY_VERSION__
 */
final class FetchMode
{
	/**
	 * Specifies that the fetch method shall return each row as an array indexed by column name as returned in the corresponding result set.
	 *
	 * If the result set contains multiple columns with the same name, the statement returns only a single value per column name.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 * @see    \PDO::FETCH_ASSOC
	 */
	public const ASSOCIATIVE = 2;

	/**
	 * Specifies that the fetch method shall return each row as an array indexed by column number as returned in the corresponding result set,
	 * starting at column 0.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 * @see    \PDO::FETCH_NUM
	 */
	public const NUMERIC = 3;

	/**
	 * Specifies that the fetch method shall return each row as an array indexed by both column name and number as returned in the corresponding
	 * result set, starting at column 0.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 * @see    \PDO::FETCH_BOTH
	 */
	public const MIXED = 4;

	/**
	 * Specifies that the fetch method shall return each row as an object with property names that correspond to the column names returned in the
	 * result set.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 * @see    \PDO::FETCH_OBJ
	 */
	public const STANDARD_OBJECT = 5;

	/**
	 * Specifies that the fetch method shall return only a single requested column from the next row in the result set.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 * @see    \PDO::FETCH_COLUMN
	 */
	public const COLUMN = 7;

	/**
	 * Specifies that the fetch method shall return a new instance of the requested class, mapping the columns to named properties in the class.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 * @see    \PDO::FETCH_CLASS
	 */
	public const CUSTOM_OBJECT = 8;

	/**
	 * Private constructor to prevent instantiation of this class
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function __construct()
	{
	}
}
