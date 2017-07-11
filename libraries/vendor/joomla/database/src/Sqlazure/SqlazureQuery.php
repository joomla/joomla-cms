<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Sqlazure;

use Joomla\Database\Sqlsrv\SqlsrvQuery;

/**
 * SQL Azure Query Building Class.
 *
 * @since  1.0
 */
class SqlazureQuery extends SqlsrvQuery
{
	/**
	 * The character(s) used to quote SQL statement names such as table names or field names, etc.
	 *
	 * If a single character string the same character is used for both sides of the quoted name, else the first character will be used for the
	 * opening quote and the second for the closing quote.
	 *
	 * @var    string
	 * @since  1.0
	 */
	protected $name_quotes = '';
}
