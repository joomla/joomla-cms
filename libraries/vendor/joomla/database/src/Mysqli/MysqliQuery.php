<?php
/**
 * Part of the Joomla Framework Database Package
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Database\Mysqli;

use Joomla\Database\DatabaseQuery;
use Joomla\Database\Query\MysqlQueryBuilder;

/**
 * MySQLi Query Building Class.
 *
 * @since  1.0
 */
class MysqliQuery extends DatabaseQuery
{
	use MysqlQueryBuilder;

	/**
	 * The list of zero or null representation of a datetime.
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $nullDatetimeList = ['0000-00-00 00:00:00', '1000-01-01 00:00:00'];
}
